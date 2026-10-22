const dayMilliseconds = 24 * 60 * 60 * 1000;

function dayNumber(value) {
    if (typeof value !== "string" || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
    const timestamp = Date.parse(`${value}T00:00:00Z`);
    return Number.isFinite(timestamp) && new Date(timestamp).toISOString().slice(0, 10) === value
        ? timestamp / dayMilliseconds : null;
}

const dateString = (day) => new Date(day * dayMilliseconds).toISOString().slice(0, 10);
const validRange = (range) => {
    const start = dayNumber(range?.startedAt);
    const end = dayNumber(range?.endedAt);
    return start !== null && end !== null && start <= end ? { start, end } : null;
};

function stable(value) {
    if (Array.isArray(value)) return value.map(stable);
    if (value && typeof value === "object") {
        return Object.fromEntries(Object.keys(value).sort().map((key) => [key, stable(value[key])]));
    }
    return value;
}

function intersects(event, day) {
    return event?.started_at?.slice(0, 10) <= dateString(day) &&
        event?.ended_at?.slice(0, 10) >= dateString(day);
}

// Runtime data only: never persist requests or copies of an entire agenda in Vuex.
export function createAgendaEventRangeCache({ ttl = 60000, maxDays = 90, now = Date.now } = {}) {
    const days = new Map();
    const records = new Map();
    const pending = new Map();
    const writes = new Set();
    let queryKey;
    let lastRange;
    let generation = 0;

    function invalidate() {
        ++generation;
        days.clear();
        records.clear();
        pending.clear();
    }

    function clear() {
        invalidate();
        queryKey = lastRange = undefined;
    }

    function beginWrite() {
        invalidate();
        let resolve;
        const write = { promise: new Promise((done) => { resolve = done; }) };
        writes.add(write);
        return () => {
            invalidate();
            writes.delete(write);
            resolve();
        };
    }

    async function fetch({ scope, filters = {}, fields, range, retainRange, load, onData }) {
        const dates = range || filters;
        const requested = { startedAt: dates.startedAt, endedAt: dates.endedAt };
        const bounds = validRange(requested);
        const retained = validRange(retainRange) || bounds;
        // An agenda request must always have both dates, including its first request.
        if (!bounds) {
            clear();
            return null;
        }
        const { startedAt, endedAt, overlapping, ...otherFilters } = filters;
        const key = JSON.stringify([scope, stable(otherFilters), fields || ""]);
        if (queryKey !== key) {
            invalidate();
            queryKey = key;
        }
        const selection = JSON.stringify([requested, retained]);
        lastRange = selection;
        const isSelected = () => queryKey === key && lastRange === selection;
        const retry = () => fetch({ scope, filters, fields, range, retainRange, load, onData });

        while (writes.size) await Promise.all([...writes].map((write) => write.promise));
        // A write invalidates active reads; a read waiting for it uses the new generation.
        if (!isSelected()) return null;
        const version = generation;
        const dependencies = new Set();
        const gaps = [];
        let gap;
        for (let day = bounds.start; day <= bounds.end; day++) {
            const entry = days.get(day);
            if (entry && entry.expiresAt <= now()) days.delete(day);
            if (days.has(day) || pending.has(day)) {
                if (pending.has(day)) dependencies.add(pending.get(day));
                gap = null;
            } else {
                if (!gap) {
                    gap = { start: day, end: day };
                    gaps.push(gap);
                } else gap.end = day;
            }
        }

        for (const missing of gaps) {
            // Defer I/O until every day has been associated with its shared promise.
            const promise = Promise.resolve().then(() => load({
                ...otherFilters,
                overlapping: { startedAt: dateString(missing.start), endedAt: dateString(missing.end) },
            }, fields)).then((events) => {
                if (version !== generation) return;
                if (!Array.isArray(events)) throw new TypeError("Expected an array of agenda events");
                for (const event of events) records.set(String(event.id), event);
                for (let day = missing.start; day <= missing.end; day++) {
                    days.set(day, {
                        ids: events.filter((event) => intersects(event, day)).map((event) => String(event.id)),
                        expiresAt: now() + ttl,
                    });
                }
            }).finally(() => {
                for (let day = missing.start; day <= missing.end; day++) {
                    if (pending.get(day) === promise) pending.delete(day);
                }
            });
            for (let day = missing.start; day <= missing.end; day++) pending.set(day, promise);
            dependencies.add(promise);
        }
        try {
            await Promise.all(dependencies);
        } catch (error) {
            if (version !== generation && isSelected()) return retry();
            throw error;
        }
        // If a save interrupted this still-visible read, reload after it finishes.
        if (version !== generation) return isSelected() ? retry() : null;
        if (!isSelected()) return null;

        const events = new Map();
        for (let day = retained.start; day <= retained.end; day++) {
            const entry = days.get(day);
            if (!entry) continue;
            // Touch visible days before evicting the least recently used days.
            days.delete(day);
            days.set(day, entry);
            for (const id of entry.ids) {
                const event = records.get(id);
                if (event && intersects(event, day)) events.set(id, event);
            }
        }
        while (days.size > maxDays) days.delete(days.keys().next().value);
        const retainedIds = new Set([...days.values()].flatMap((entry) => entry.ids));
        for (const id of records.keys()) if (!retainedIds.has(id)) records.delete(id);
        // Only the latest requested range may replace the visible list.
        const result = [...events.values()].sort((a, b) => a.started_at.localeCompare(b.started_at) || String(a.id).localeCompare(String(b.id)));
        // Publish in this continuation: a later save cannot slip between the validity
        // check and a caller's Vuex commit after awaiting the returned promise.
        onData?.(result);
        return result;
    }

    return { fetch, invalidate, clear, beginWrite };
}

export const projectEventRange = createAgendaEventRangeCache();
export const globalEventRange = createAgendaEventRangeCache();

export const agendaEventScope = (state) => JSON.stringify([
    state.auth?.user?.id ?? null,
    state.auth?.authenticated ?? false,
    state.auth?.impersonating ?? false,
    state.auth?.impersonate_project ?? null,
    state.project?.project?.slug ?? null,
]);
