import EventService from "@/apis/project/event";
import { createDailyDirectionCache } from "@/utils/event-daily-direction-cache";

export const summaryCache = createDailyDirectionCache({
    maxEntries: 1000,
    emptyTtl: 5000,
    isReady: (data) => data.status === "ready",
});

export function toDailyDirectionSummary(data) {
    const direction = data.direction;
    const legs = direction?.routes?.[0]?.legs;
    const metric = (value) => ({
        text: typeof value?.text === "string" ? value.text : "",
        value: value?.value != null && Number.isFinite(Number(value.value)) ? Number(value.value) : 0,
    });
    return {
        user_id: data.user_id,
        direction_at: data.direction_at,
        status: data.status || (data.searching ? "pending" : direction?.status === "OK" ? "ready" : "empty"),
        events: data.events || [],
        legs: direction?.status === "OK" && Array.isArray(legs) ? legs.map((leg) => ({
            distance: metric(leg?.distance),
            duration: metric(leg?.duration),
        })) : [],
    };
}

export function createDailyDirectionSummaryBatcher(loadBatch, { delay = 20, maxPairs = 500 } = {}) {
    const groups = new Map();
    let generation = 0;
    const keyFor = (userId, date) => JSON.stringify([String(userId), date]);

    async function flush(group) {
        group.timer = null;
        group.running = true;
        try {
            while (group.queue.size && group.generation === generation) {
                const items = [...group.queue.values()].slice(0, maxPairs);
                for (const item of items) group.queue.delete(item.key);
                group.active = items;
                try {
                    const response = await loadBatch(group.project, items.map((item) => item.pair));
                    const summaries = response?.data?.data;
                    if (!Array.isArray(summaries)) throw new Error("Invalid daily direction summaries response");
                    const byPair = new Map(summaries.map((summary) => [keyFor(summary.user_id, summary.direction_at), summary]));
                    for (const item of items) {
                        item.resolve(group.generation === generation ? byPair.get(item.key) || null : null);
                    }
                } catch (error) {
                    for (const item of items) item.reject(error);
                } finally {
                    group.active = [];
                }
            }
        } finally {
            group.running = false;
            if (groups.get(group.key) === group) groups.delete(group.key);
        }
    }

    function request(scope, project, userId, date) {
        const groupKey = JSON.stringify([scope, project]);
        let group = groups.get(groupKey);
        if (!group) {
            group = { key: groupKey, project, generation, queue: new Map(), active: [], running: false, timer: null };
            groups.set(groupKey, group);
        }
        const key = keyFor(userId, date);
        if (group.queue.has(key)) return group.queue.get(key).promise;

        const item = { key, pair: { user_id: Number(userId), direction_at: date } };
        item.promise = new Promise((resolve, reject) => {
            item.resolve = resolve;
            item.reject = reject;
        });
        group.queue.set(key, item);
        if (!group.running && group.timer === null) {
            group.timer = setTimeout(() => flush(group), delay);
        }
        return item.promise;
    }

    function clear() {
        ++generation;
        for (const group of groups.values()) {
            clearTimeout(group.timer);
            for (const item of [...group.queue.values(), ...group.active]) item.resolve(null);
            group.queue.clear();
        }
        groups.clear();
    }

    return { request, clear };
}

export default createDailyDirectionSummaryBatcher((project, pairs) =>
    EventService.dailyDirectionSummaries(project, { pairs })
);
