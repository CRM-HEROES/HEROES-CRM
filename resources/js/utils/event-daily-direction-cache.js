export function dailyDirectionEventsSignature(events = []) {
    return JSON.stringify(
        events.filter(Boolean).map((event) => [
            String(event.id ?? event),
            String(event.user_id ?? ""),
            String(event.calendar_id ?? ""),
            event.calendar?.type ?? "",
            event.started_at ?? "",
            event.ended_at ?? "",
            event.location ?? "",
            String(event.latitude ?? ""),
            String(event.longitude ?? ""),
        ]).sort((a, b) => a[0].localeCompare(b[0]))
    );
}

export function dailyDirectionCacheScope(rootState) {
    return JSON.stringify([
        rootState.auth?.user?.id ?? null,
        rootState.auth?.impersonating ?? false,
        rootState.auth?.impersonate_project ?? null,
        rootState.project?.project?.slug ?? null,
    ]);
}

export function dailyDirectionContextEvents(rootState, userId, date, events = []) {
    const known = new Map();
    for (const event of [
        ...(rootState.event?.globalEvents || []),
        ...(rootState.project?.project?.events || []),
        ...events,
    ]) {
        if (event?.id != null) known.set(String(event.id), event);
    }
    return [...known.values()].filter((event) =>
        event.user_id == userId && event.calendar?.type === "physical" && event.location &&
        event.started_at?.substring(0, 10) <= date && event.ended_at?.substring(0, 10) >= date
    );
}

export function createDailyDirectionCache({
    ttl = 5 * 60 * 1000,
    emptyTtl = 30 * 1000,
    settleTime = 5 * 1000,
    maxEntries = 100,
    now = Date.now,
    isReady = (data) => Boolean(data.direction),
} = {}) {
    const entries = new Map();
    const pending = new Map();
    const writes = new Set();
    const settling = new Map();
    const keyFor = (scope, userId, date) => JSON.stringify([scope, String(userId), date]);
    const eventIds = (events) => events.filter(Boolean).map((event) => String(event.id ?? event));

    function get(scope, userId, date, events = []) {
        const key = keyFor(scope, userId, date);
        const entry = entries.get(key);
        if (!entry) return undefined;

        if (entry.expiresAt <= now() || entry.signature !== dailyDirectionEventsSignature(events)) {
            entries.delete(key);
            return undefined;
        }

        entries.delete(key);
        entries.set(key, entry);
        return entry.data;
    }

    function fetch(scope, userId, date, events, load) {
        const cached = get(scope, userId, date, events);
        if (cached) return Promise.resolve(cached);

        const key = keyFor(scope, userId, date);
        const signature = dailyDirectionEventsSignature(events);
        const previous = pending.get(key);
        if (previous?.signature === signature) return previous.promise;
        previous?.cancel();

        const request = { userId: String(userId), date, signature, eventIds: eventIds(events) };
        const waiters = new Set();
        request.cancel = () => {
            for (const finish of [...waiters]) finish(false);
        };
        pending.set(key, request);
        const control = {
            isCurrent: () => pending.get(key) === request,
            onCancel: (callback) => {
                if (pending.get(key) !== request) {
                    callback();
                    return () => {};
                }
                waiters.add(callback);
                return () => waiters.delete(callback);
            },
            wait: (milliseconds) => new Promise((resolve) => {
                if (pending.get(key) !== request) return resolve(false);
                const finish = (current) => {
                    clearTimeout(timer);
                    waiters.delete(finish);
                    resolve(current);
                };
                const timer = setTimeout(() => finish(pending.get(key) === request), milliseconds);
                waiters.add(finish);
            }),
        };
        request.promise = (async () => {
            try {
                // Optimistic agenda edits must reach the server before reading its route.
                while (writes.size) {
                    await Promise.all([...writes].map((write) => write.promise));
                }
                if (pending.get(key) !== request) return null;

                const readStartedAt = now();
                const unsettled = new Set([...settling]
                    .filter(([, until]) => until > readStartedAt)
                    .map(([key]) => key));
                const data = await load(control);
                if (pending.get(key) !== request || !data || data.searching) return null;

                const ids = [...new Set([...request.eventIds, ...eventIds(data.events || [])])];
                if (unsettled.has(`day:${request.userId}:${date}`) ||
                    ids.some((id) => unsettled.has(`event:${id}`))) {
                    return data;
                }

                entries.set(key, {
                    ...request,
                    promise: undefined,
                    cancel: undefined,
                    eventIds: ids,
                    data,
                    expiresAt: now() + (isReady(data) ? ttl : emptyTtl),
                });
                while (entries.size > maxEntries) {
                    entries.delete(entries.keys().next().value);
                }
                return data;
            } finally {
                if (pending.get(key) === request) pending.delete(key);
            }
        })();
        return request.promise;
    }

    function invalidate(events) {
        for (const event of events.filter(Boolean)) {
            const id = String(event.id ?? event);
            const userId = event.user_id == null ? null : String(event.user_id);
            const date = event.started_at?.substring(0, 10);
            for (const collection of [entries, pending]) {
                for (const [key, entry] of collection) {
                    if (entry.eventIds.includes(id) || (userId === entry.userId && date === entry.date)) {
                        collection.delete(key);
                        entry.cancel?.();
                    }
                }
            }
        }
    }

    function beginWrite() {
        let release;
        const write = { promise: new Promise((resolve) => { release = resolve; }) };
        writes.add(write);
        write.release = () => {
            writes.delete(write);
            release();
        };
        return write.release;
    }

    function deferCaching(events) {
        for (const [key, until] of settling) {
            if (until <= now()) settling.delete(key);
        }
        // The server may still be recalculating immediately after a save.
        // Share immediate reads, but do not retain a possibly older result.
        for (const event of events.filter(Boolean)) {
            const id = event.id ?? (typeof event === "object" ? null : event);
            if (id != null) settling.set(`event:${id}`, now() + settleTime);
            if (event.user_id != null && event.started_at) {
                settling.set(`day:${event.user_id}:${event.started_at.substring(0, 10)}`, now() + settleTime);
            }
            for (const entry of [...entries.values(), ...pending.values()]) {
                if (entry.eventIds.includes(String(id))) {
                    settling.set(`day:${entry.userId}:${entry.date}`, now() + settleTime);
                }
            }
        }
    }

    function clear() {
        entries.clear();
        for (const request of pending.values()) request.cancel();
        pending.clear();
        settling.clear();
        for (const write of [...writes]) write.release();
    }

    return { get, fetch, invalidate, beginWrite, deferCaching, clear };
}

// Keep Google responses and promises out of the persisted Vuex state.
export default createDailyDirectionCache();
