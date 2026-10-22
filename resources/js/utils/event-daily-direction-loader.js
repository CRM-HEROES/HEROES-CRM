const statuses = new Set(["ready", "pending", "empty"]);

export function normalizeDailyDirection(data, userId, date) {
    const parse = (value, fallback) => typeof value === "string" ? JSON.parse(value) : value ?? fallback;
    const status = statuses.has(data?.status) ? data.status : undefined;
    return {
        user_id: userId,
        direction_at: date,
        events: parse(data?.events, []),
        direction: status === "empty" ? null : parse(data?.direction, null),
        searching: status ? status === "pending" : data?.searching === true || data?.searching == 1,
        ...(status ? { status } : {}),
    };
}

export function dailyDirectionTimeoutError() {
    const error = new Error("The route is still being calculated. Try again shortly.");
    error.code = "DAILY_DIRECTION_TIMEOUT";
    error.status = "pending";
    return error;
}

// Only opening a map requests a calculation. While it is queued, all opened maps
// share the small agenda status batches; the full Google response is read once ready.
export async function loadDailyDirection({
    userId,
    date,
    loadDetail,
    loadSummary,
    isCurrent,
    onCancel,
    wait,
    timeout = 60 * 1000,
    pollInterval = 2 * 1000,
    now = Date.now,
}) {
    const deadline = now() + timeout;
    const cancelled = {};
    // Stop waiting immediately on a context change, including when an HTTP
    // response is delayed. Late HTTP results cannot start another read.
    const read = (load) => new Promise((resolve, reject) => {
        let removeCancel;
        let settled = false;
        let timer;
        const finish = (value, failed = false) => {
            if (settled) return;
            settled = true;
            clearTimeout(timer);
            removeCancel?.();
            if (failed) reject(value);
            else resolve(value);
        };
        if (!isCurrent()) return finish(cancelled);
        timer = setTimeout(() => finish(dailyDirectionTimeoutError(), true), Math.max(0, deadline - now()));
        removeCancel = onCancel?.(() => finish(cancelled));
        try {
            Promise.resolve(isCurrent() ? load() : cancelled)
                .then((value) => finish(value), (error) => finish(error, true));
        } catch (error) {
            finish(error, true);
        }
    });
    let data = await read(loadDetail);

    while (isCurrent()) {
        if (data === cancelled) return null;
        const normalized = normalizeDailyDirection(data, userId, date);
        // Responses without a status retain the compatibility behavior of the
        // previous API, including retrying an old searching response on demand.
        if (data?.status !== "pending") return normalized;

        const remaining = deadline - now();
        if (remaining <= 0) throw dailyDirectionTimeoutError();
        const retry = Number(data.retry_after) * 1000;
        const delay = Math.min(remaining, Math.max(1000, Math.min(10000, retry || pollInterval)));
        if (!await wait(delay) || !isCurrent()) return null;

        const summary = await read(loadSummary);
        if (!isCurrent()) return null;
        if (summary?.status === "empty") {
            return normalizeDailyDirection({ ...summary, direction: null, status: "empty" }, userId, date);
        }
        if (summary?.status === "ready") {
            data = await read(loadDetail);
        }
    }
    return null;
}
