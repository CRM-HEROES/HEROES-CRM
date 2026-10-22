const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");

const compiled = new Map();
function loadModules(services = {}) {
    const modules = new Map();
    function load(relativePath) {
        if (modules.has(relativePath)) return modules.get(relativePath).exports;
        if (!compiled.has(relativePath)) {
            compiled.set(relativePath, transformSync(
                readFileSync(path.resolve(__dirname, "../../resources/js", relativePath), "utf8"),
                {
                    filename: relativePath,
                    babelrc: false,
                    configFile: false,
                    plugins: ["@babel/plugin-transform-modules-commonjs"],
                }
            ).code);
        }
        const module = { exports: {} };
        modules.set(relativePath, module);
        new Function("require", "module", "exports", compiled.get(relativePath))((specifier) => {
            if (specifier === "@/apis/project/event") return services;
            if (specifier.startsWith("@/")) return load(`${specifier.substring(2)}.js`);
            throw new Error(`Unexpected dependency: ${specifier}`);
        }, module, module.exports);
        return module.exports;
    }
    return {
        load,
        ...load("utils/event-daily-direction-loader.js"),
        ...load("utils/event-daily-direction-cache.js"),
        ...load("utils/event-daily-direction-summary.js"),
    };
}

const event = { id: 1, user_id: 526, started_at: "2026-10-15 09:00:00" };
const pending = { status: "pending", events: "[1]", direction: null, searching: true, retry_after: 2 };
const ready = {
    status: "ready", events: "[1]", searching: false,
    direction: JSON.stringify({ status: "OK", routes: [{ legs: [] }] }),
};
const tick = () => new Promise((resolve) => setImmediate(resolve));
function deferred() {
    let resolve;
    const promise = new Promise((done) => { resolve = done; });
    return { promise, resolve };
}
function clock() {
    let time = 0;
    return { now: () => time, wait: async (milliseconds) => { time += milliseconds; return true; } };
}

test("queued directions poll only grouped summaries and read full Google data when ready", async () => {
    const { loadDailyDirection } = loadModules();
    let detailCalls = 0;
    let summaryCalls = 0;
    const result = await loadDailyDirection({
        userId: 526, date: "2026-10-15", isCurrent: () => true, ...clock(),
        loadDetail: async () => ++detailCalls === 1 ? pending : ready,
        loadSummary: async () => ({ status: ++summaryCalls === 1 ? "pending" : "ready" }),
    });
    assert.equal(detailCalls, 2);
    assert.equal(summaryCalls, 2);
    assert.equal(result.status, "ready");
    assert.equal(result.searching, false);
    assert.deepEqual(result.events, [1]);
    assert.equal(result.direction.status, "OK");
});

test("an empty grouped result finishes without downloading Google data again", async () => {
    const { loadDailyDirection } = loadModules();
    let calls = 0;
    const result = await loadDailyDirection({
        userId: 526, date: "2026-10-15", isCurrent: () => true, ...clock(),
        loadDetail: async () => { calls++; return pending; },
        loadSummary: async () => ({ status: "empty", events: [] }),
    });
    assert.equal(calls, 1);
    assert.equal(result.status, "empty");
    assert.equal(result.direction, null);
    assert.deepEqual(result.events, []);
});

test("legacy searching responses retain on-demand retry behavior", async () => {
    const { loadDailyDirection } = loadModules();
    const result = await loadDailyDirection({
        userId: 526, date: "2026-10-15", isCurrent: () => true, ...clock(),
        loadDetail: async () => ({ searching: "1", direction: "null" }),
        loadSummary: () => assert.fail("legacy response polled"),
    });
    assert.equal(result.searching, true);
    assert.equal(result.direction, null);
});

for (const status of ["pending", "missing"]) {
    test(`${status} summaries stop after 60 seconds with a retryable timeout`, async () => {
        const { loadDailyDirection } = loadModules();
        let calls = 0;
        await assert.rejects(loadDailyDirection({
            userId: 526, date: "2026-10-15", isCurrent: () => true, ...clock(),
            loadDetail: async () => pending,
            loadSummary: async () => { calls++; return { status }; },
        }), (error) => error.code === "DAILY_DIRECTION_TIMEOUT" && error.status === "pending");
        assert.equal(calls, 30);
    });
}

test("duplicate map requests share the complete queue wait and final route", async () => {
    const { createDailyDirectionCache, loadDailyDirection } = loadModules();
    const cache = createDailyDirectionCache();
    const summary = deferred();
    let calls = 0;
    const load = (control) => loadDailyDirection({
        ...control, userId: 526, date: "2026-10-15", wait: async () => true,
        loadDetail: async () => ++calls === 1 ? pending : ready,
        loadSummary: () => summary.promise,
    });
    const first = cache.fetch("scope", 526, "2026-10-15", [event], load);
    const second = cache.fetch("scope", 526, "2026-10-15", [event], load);
    assert.equal(first, second);
    await tick();
    summary.resolve({ status: "ready" });
    const result = await first;
    assert.equal(result, await second);
    assert.equal(calls, 2);
    assert.equal(cache.get("scope", 526, "2026-10-15", [event]), result);
});

test("simultaneous queued map requests share one small status batch", async () => {
    const { createDailyDirectionCache, createDailyDirectionSummaryBatcher, loadDailyDirection } = loadModules();
    const cache = createDailyDirectionCache();
    const detailCalls = new Map();
    let batchCalls = 0;
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        batchCalls++;
        assert.equal(pairs.length, 20);
        return { data: { data: pairs.map((pair) => ({ ...pair, status: "ready" })) } };
    }, { delay: 0 });
    const results = await Promise.all(Array.from({ length: 20 }, (_, offset) => {
        const userId = 500 + offset;
        return cache.fetch("scope", userId, "2026-10-15", [], (control) => loadDailyDirection({
            ...control, userId, date: "2026-10-15", wait: async () => true,
            loadDetail: async () => {
                const calls = (detailCalls.get(userId) || 0) + 1;
                detailCalls.set(userId, calls);
                return calls === 1 ? pending : ready;
            },
            loadSummary: () => batcher.request("scope", "alpha", userId, "2026-10-15"),
        }));
    }));
    assert.equal(batchCalls, 1);
    assert.ok(results.every((result) => result.status === "ready"));
    assert.ok([...detailCalls.values()].every((calls) => calls === 2));
});

test("appointment invalidation cancels the waiting timer before another status request", async () => {
    const { createDailyDirectionCache, loadDailyDirection } = loadModules();
    const cache = createDailyDirectionCache();
    const result = cache.fetch("scope", 526, "2026-10-15", [event], (control) => loadDailyDirection({
        ...control, userId: 526, date: "2026-10-15",
        loadDetail: async () => pending,
        loadSummary: () => assert.fail("invalidated route continued polling"),
    }));
    await tick();
    cache.invalidate([event]);
    assert.equal(await result, null);
    assert.equal(cache.get("scope", 526, "2026-10-15", [event]), undefined);
});

test("scope clearing stops a delayed HTTP response from starting a full detail read", async () => {
    const { createDailyDirectionCache, loadDailyDirection } = loadModules();
    const cache = createDailyDirectionCache();
    const summary = deferred();
    let calls = 0;
    const result = cache.fetch("scope", 526, "2026-10-15", [event], (control) => loadDailyDirection({
        ...control, userId: 526, date: "2026-10-15", wait: async () => true,
        loadDetail: async () => { calls++; return pending; },
        loadSummary: () => summary.promise,
    }));
    await tick();
    cache.clear();
    assert.equal(await result, null);
    summary.resolve({ status: "ready" });
    await tick();
    assert.equal(calls, 1);
});

test("a delayed status HTTP response is bounded by the same calculation timeout", async () => {
    const { loadDailyDirection } = loadModules();
    await assert.rejects(loadDailyDirection({
        userId: 526, date: "2026-10-15", isCurrent: () => true,
        timeout: 30, wait: async () => true,
        loadDetail: async () => pending,
        loadSummary: () => new Promise(() => {}),
    }), (error) => error.code === "DAILY_DIRECTION_TIMEOUT");
});

test("timeouts leave no cached result and the next click can retry", async () => {
    const { createDailyDirectionCache, loadDailyDirection } = loadModules();
    const cache = createDailyDirectionCache();
    await assert.rejects(cache.fetch("scope", 526, "2026-10-15", [], (control) => loadDailyDirection({
        ...control, ...clock(), userId: 526, date: "2026-10-15", timeout: 2000,
        loadDetail: async () => pending,
        loadSummary: async () => ({ status: "pending" }),
    })), (error) => error.code === "DAILY_DIRECTION_TIMEOUT");
    assert.equal(cache.get("scope", 526, "2026-10-15", []), undefined);
    const result = await cache.fetch("scope", 526, "2026-10-15", [], (control) => loadDailyDirection({
        ...control, userId: 526, date: "2026-10-15",
        loadDetail: async () => ready,
        loadSummary: () => assert.fail("ready detail polled"),
    }));
    assert.equal(result.status, "ready");
});

for (const [label, project, can] of [
    ["outside a project", undefined, undefined],
    ["without project agenda permission", "alpha", () => false],
]) {
    test(`the detail action polls the authorized user status ${label}`, async () => {
        let detailCalls = 0;
        let statusCalls = 0;
        const modules = loadModules({
            dailyDirection: async () => ({ data: ++detailCalls === 1 ? pending : ready }),
            dailyDirectionStatus: async (user, date) => {
                statusCalls++;
                assert.equal(user.id, 526);
                assert.equal(date, "2026-10-15");
                return { data: { status: "ready", searching: false } };
            },
            dailyDirectionSummaries: () => assert.fail("unauthorized project summary requested"),
        });
        const loader = modules.load("utils/event-daily-direction-loader.js");
        const loadDirection = loader.loadDailyDirection;
        loader.loadDailyDirection = (options) => loadDirection({ ...options, wait: async () => true });
        const actions = modules.load("store/project/event.js").default.actions;
        const actionName = modules.load("actions/project/event.js").FETCH_EVENT_USER_DAILY_DIRECTION;
        const rootState = { auth: { user: { id: 7 } }, project: { project: { slug: project, events: [] } } };
        const result = await actions[actionName]({
            state: rootState.project, rootState, rootGetters: { can }, commit() {},
        }, { user: { id: 526 }, direction_at: "2026-10-15" });
        assert.equal(result.status, "ready");
        assert.equal(detailCalls, 2);
        assert.equal(statusCalls, 1);
    });
}

test("the user status fallback can complete empty without full Google details", async () => {
    const modules = loadModules({
        dailyDirection: async () => ({ data: pending }),
        dailyDirectionStatus: async () => ({ data: { status: "empty", searching: false } }),
        dailyDirectionSummaries: () => assert.fail("project summary requested outside a project"),
    });
    const loader = modules.load("utils/event-daily-direction-loader.js");
    const loadDirection = loader.loadDailyDirection;
    loader.loadDailyDirection = (options) => loadDirection({ ...options, wait: async () => true });
    const actions = modules.load("store/project/event.js").default.actions;
    const actionName = modules.load("actions/project/event.js").FETCH_EVENT_USER_DAILY_DIRECTION;
    const rootState = { auth: { user: { id: 7 } }, project: { project: { events: [] } } };
    const result = await actions[actionName]({
        state: rootState.project, rootState, rootGetters: {}, commit() {},
    }, { user: { id: 526 }, direction_at: "2026-10-15" });
    assert.equal(result.status, "empty");
    assert.equal(result.direction, null);
    assert.deepEqual(result.events, []);
});
