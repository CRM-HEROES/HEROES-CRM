const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");
const { createStore } = require("vuex");

const sourceRoot = path.resolve(__dirname, "../../resources/js");
const compiled = new Map();
function modules(projectService = {}, globalService = {}) {
    const loaded = new Map();
    function load(file) {
        if (loaded.has(file)) return loaded.get(file).exports;
        if (!compiled.has(file)) compiled.set(file, transformSync(readFileSync(path.join(sourceRoot, file), "utf8"), {
            filename: file, babelrc: false, configFile: false,
            plugins: ["@babel/plugin-transform-modules-commonjs"],
        }).code);
        const module = { exports: {} };
        loaded.set(file, module);
        new Function("require", "module", "exports", compiled.get(file))((name) => {
            if (name === "@/apis/project/event") return projectService;
            if (name === "@/apis/event") return globalService;
            if (name.startsWith("@/")) return load(`${name.slice(2)}.js`);
            throw new Error(`Unexpected dependency ${name}`);
        }, module, module.exports);
        return module.exports;
    }
    return { load, ...load("utils/agenda-event-range-cache.js") };
}

const range = (start, end = start) => ({ startedAt: `2026-10-${start}`, endedAt: `2026-10-${end}` });
const event = (id, start, end = start, extra = {}) => ({
    id, started_at: `2026-10-${start} 09:00:00`, ended_at: `2026-10-${end} 10:00:00`, ...extra,
});
const deferred = () => {
    let resolve, reject;
    const promise = new Promise((done, fail) => { resolve = done; reject = fail; });
    return { promise, resolve, reject };
};
const tick = () => new Promise((resolve) => setImmediate(resolve));
function loader(events = []) {
    const calls = [];
    return {
        calls,
        load: async (filters) => {
            calls.push(filters);
            return events.filter((item) => item.started_at.slice(0, 10) <= filters.overlapping.endedAt &&
                item.ended_at.slice(0, 10) >= filters.overlapping.startedAt);
        },
    };
}
function cacheHarness(options = {}, events = []) {
    const cache = modules().createAgendaEventRangeCache(options);
    const server = loader(events);
    const fetch = (start, end = start, extra = {}) => cache.fetch({
        scope: "alpha", filters: range(start, end), load: server.load, ...extra,
    });
    return { cache, ...server, fetch };
}

test("scrolling overlapping periods requests only the new days and clips the visible list", async () => {
    const { fetch, calls } = cacheHarness({}, [event(1, "12"), event(2, "15"), event(3, "20")]);
    assert.deepEqual((await fetch("12", "16")).map((item) => item.id), [1, 2]);
    assert.deepEqual((await fetch("15", "20")).map((item) => item.id), [2, 3]);
    assert.deepEqual(calls.map((call) => call.overlapping), [range("12", "16"), range("17", "20")]);
    assert.deepEqual((await fetch("12", "15")).map((item) => item.id), [1, 2]);
    assert.equal(calls.length, 2);
});

test("empty days are cached and returning to them makes no request", async () => {
    const { fetch, calls } = cacheHarness();
    assert.deepEqual(await fetch("12", "16"), []);
    await fetch("13", "15");
    assert.equal(calls.length, 1);
});

test("new days on both sides are fetched as two gaps", async () => {
    const { fetch, calls } = cacheHarness();
    await fetch("15", "16");
    await fetch("12", "20");
    assert.deepEqual(calls.map((call) => call.overlapping), [range("15", "16"), range("12", "14"), range("17", "20")]);
});

test("pending days are shared when scrolling before the first response", async () => {
    const { cache } = cacheHarness();
    const first = deferred();
    const calls = [];
    const load = async (filters) => {
        calls.push(filters.overlapping);
        return calls.length === 1 ? first.promise : [event(2, "20")];
    };
    const a = cache.fetch({ scope: "alpha", filters: range("12", "16"), load });
    await tick();
    const b = cache.fetch({ scope: "alpha", filters: range("15", "20"), load });
    await tick();
    assert.deepEqual(calls, [range("12", "16"), range("17", "20")]);
    first.resolve([event(1, "15")]);
    assert.equal(await a, null);
    assert.deepEqual((await b).map((item) => item.id), [1, 2]);
});

test("overnight events returned by two gaps are deduplicated by ID", async () => {
    const { fetch } = cacheHarness({}, [event(1, "14", "16")]);
    await fetch("14", "15");
    const events = await fetch("15", "16");
    assert.deepEqual(events.map((item) => item.id), [1]);
});

test("latest metadata for a multi-day event wins when scrolling backwards", async () => {
    const { cache } = cacheHarness();
    let count = 0;
    const load = async () => [event(1, "12", "20", { name: ++count === 1 ? "old" : "new" })];
    await cache.fetch({ scope: "alpha", filters: range("15", "20"), load });
    const data = await cache.fetch({ scope: "alpha", filters: range("12", "16"), load });
    assert.equal(data[0].name, "new");
});

test("filter, fields and authentication/project scope changes replace the coverage", async () => {
    const { fetch, calls } = cacheHarness();
    await fetch("12", "16", { filters: { ...range("12", "16"), withUsers: [1] } });
    await fetch("12", "16", { filters: { ...range("12", "16"), withUsers: [2] } });
    await fetch("12", "16", { fields: "calendar,users" });
    await fetch("12", "16", { fields: "calendar,users", scope: "beta" });
    assert.equal(calls.length, 4);
    assert.deepEqual(calls[1].withUsers, [2]);
    assert.ok(calls.every((call) => !("startedAt" in call) && !("endedAt" in call)));
});

test("filter key order alone does not invalidate coverage", async () => {
    const { fetch, calls } = cacheHarness();
    await fetch("12", "16", { filters: { ...range("12", "16"), withUsers: [1], withCalendars: [2] } });
    await fetch("12", "16", { filters: { withCalendars: [2], withUsers: [1], ...range("12", "16") } });
    assert.equal(calls.length, 1);
});

test("explicit date ranges do not depend on object property order", async () => {
    const { fetch, calls } = cacheHarness();
    assert.deepEqual(await fetch("12", "16", { range: { endedAt: "2026-10-16", startedAt: "2026-10-12" } }), []);
    assert.equal(calls.length, 1);
});

test("old filtered response cannot replace or populate the new query", async () => {
    const { cache } = cacheHarness();
    const old = deferred();
    const a = cache.fetch({ scope: "alpha", filters: range("12"), load: () => old.promise });
    await tick();
    const fresh = await cache.fetch({ scope: "beta", filters: range("12"), load: async () => [event(2, "12")] });
    old.resolve([event(1, "12")]);
    assert.equal(await a, null);
    assert.equal(fresh[0].id, 2);
});

test("expired days are reloaded while fresh adjacent days stay cached", async () => {
    let time = 0;
    const { fetch, calls } = cacheHarness({ ttl: 100, now: () => time });
    await fetch("12");
    time = 50;
    await fetch("12", "13");
    time = 101;
    await fetch("12", "13");
    assert.deepEqual(calls.map((call) => call.overlapping), [range("12"), range("13"), range("12")]);
});

test("failed days remain missing and can be retried", async () => {
    const { fetch, calls } = cacheHarness();
    await fetch("12", "13");
    await assert.rejects(fetch("12", "15", { load: async () => { throw new Error("offline"); } }), /offline/);
    await fetch("12", "15");
    assert.deepEqual(calls.map((call) => call.overlapping), [range("12", "13"), range("14", "15")]);
});

test("LRU limits retained days while preserving the current response", async () => {
    const { fetch, calls } = cacheHarness({ maxDays: 2 }, [event(1, "12"), event(2, "13"), event(3, "14")]);
    assert.equal((await fetch("12", "14")).length, 3);
    await fetch("13", "14");
    assert.equal(calls.length, 1);
    await fetch("12");
    assert.deepEqual(calls[1].overlapping, range("12"));
});

test("unbounded, malformed, impossible and reversed dates never reach the server", async () => {
    const { cache, calls, load } = cacheHarness();
    for (const filters of [{}, { startedAt: "2026-10-12" }, range("32"), range("20", "12"),
        { startedAt: "2026-02-30", endedAt: "2026-03-01" }]) {
        assert.equal(await cache.fetch({ scope: "alpha", filters, load }), null);
    }
    assert.equal(calls.length, 0);
});

test("writes invalidate in-flight results and subsequent reads wait for the save", async () => {
    const { cache } = cacheHarness();
    const old = deferred();
    let reads = 0;
    const load = async () => ++reads === 1 ? old.promise : [event(2, "12")];
    const reading = cache.fetch({ scope: "alpha", filters: range("12"), load });
    await tick();
    const release = cache.beginWrite();
    const fresh = cache.fetch({ scope: "alpha", filters: range("12"), load });
    await tick();
    assert.equal(reads, 1);
    old.resolve([event(1, "12")]);
    await tick();
    assert.equal(reads, 1);
    release();
    assert.equal((await fresh)[0].id, 2);
    assert.equal((await reading)[0].id, 2);
    assert.equal(reads, 2);
});

test("retaining already loaded DOM days stabilizes mobile lists without fetching extra days", async () => {
    const { fetch, calls } = cacheHarness({}, [event(1, "12"), event(2, "15"), event(3, "20")]);
    await fetch("12", "15");
    const data = await fetch("15", "16", { retainRange: range("10", "20") });
    assert.deepEqual(data.map((item) => item.id), [1, 2]);
    assert.deepEqual(calls.map((call) => call.overlapping), [range("12", "15"), range("16")]);
});

test("cache publishes before a later mutation in the caller's promise continuation", async () => {
    const { cache, load } = cacheHarness({}, [event(1, "12", "12", { title: "old" })]);
    await cache.fetch({ scope: "alpha", filters: range("12"), load });
    let shown;
    const reading = cache.fetch({ scope: "alpha", filters: range("12"), load, onData: (data) => { shown = data; } });
    await Promise.resolve().then(() => {
        const done = cache.beginWrite();
        shown = [event(1, "12", "12", { title: "saved" })];
        done();
    });
    await reading;
    assert.equal(shown[0].title, "saved");
});

function storeHarness(projectService, globalService = {}) {
    const source = modules(projectService, globalService);
    const projectStore = source.load("store/project/event.js").default;
    const eventStore = source.load("store/event.js").default;
    const actions = source.load("actions/project/event.js");
    const globals = source.load("actions/event.js");
    const store = createStore({
        modules: {
            auth: { state: { authenticated: true, user: { id: 7 } } },
            project: {
                state: { project: { id: 1, slug: "alpha", events: [], eventsParams: range("12", "16"), eventsFields: "calendar,user", agendaOtherProjects: false } },
                getters: projectStore.getters, actions: projectStore.actions,
                mutations: { ...projectStore.mutations, setTestProject(state, next) { state.project = next; } },
            },
            event: { ...eventStore, state: { globalEvents: [], globalEventsParams: { ...range("12", "16"), withoutProjects: [1] } } },
        },
        plugins: [source.load("store/agenda-event-range-cache.js").default],
    });
    return { store, actions, globals, ...source };
}

test("production project action sends overlap gaps and merges events for the current range", async () => {
    const server = loader([event(1, "15"), event(2, "20")]);
    const h = storeHarness({ get: async (slug, { params }) => {
        assert.equal(slug, "alpha");
        assert.equal(params.fields, "calendar,user");
        return { data: await server.load(JSON.parse(params.filters)) };
    } });
    await h.store.dispatch(h.actions.FETCH_EVENTS);
    h.store.commit(h.actions.ADD_EVENT_PARAMS, { key: "startedAt", value: "2026-10-15" });
    h.store.commit(h.actions.ADD_EVENT_PARAMS, { key: "endedAt", value: "2026-10-20" });
    await h.store.dispatch(h.actions.FETCH_EVENTS);
    assert.deepEqual(server.calls.map((call) => call.overlapping), [range("12", "16"), range("17", "20")]);
    assert.deepEqual(h.store.getters.events.map((item) => item.id), [1, 2]);
});

test("map action preserves its date filters and can intentionally load without dates", async () => {
    const calls = [];
    const h = storeHarness({ get: async (slug, { params }) => { calls.push(JSON.parse(params.filters)); return { data: [] }; } });
    await h.store.dispatch(h.actions.FETCH_EVENTS, { incremental: false });
    h.store.commit(h.actions.SET_EVENT_PARAMS, { withCalendars: [2] });
    await h.store.dispatch(h.actions.FETCH_EVENTS, { incremental: false });
    assert.deepEqual(calls, [range("12", "16"), { withCalendars: [2] }]);
});

test("production global action makes no HTTP request when other projects are hidden", async () => {
    let calls = 0;
    const h = storeHarness({}, { get: async () => { calls++; return { data: [] }; } });
    await h.store.dispatch(h.globals.FETCH_GLOBAL_EVENTS);
    assert.equal(calls, 0);
    h.store.commit(h.actions.SHOW_AGENDA_OTHER_PROJECTS, true);
    await h.store.dispatch(h.globals.FETCH_GLOBAL_EVENTS);
    assert.equal(calls, 1);
});

test("global response finishing after hiding other projects is ignored", async () => {
    const response = deferred();
    const h = storeHarness({}, { get: () => response.promise });
    h.store.commit(h.actions.SHOW_AGENDA_OTHER_PROJECTS, true);
    const request = h.store.dispatch(h.globals.FETCH_GLOBAL_EVENTS);
    await tick();
    h.store.commit(h.actions.SHOW_AGENDA_OTHER_PROJECTS, false);
    response.resolve({ data: [event(1, "15")] });
    await request;
    assert.deepEqual(h.store.state.event.globalEvents, []);
});

test("project response finishing after switching project cannot populate its agenda", async () => {
    const response = deferred();
    const h = storeHarness({ get: () => response.promise });
    const request = h.store.dispatch(h.actions.FETCH_EVENTS);
    await tick();
    h.store.commit("setTestProject", { slug: "beta", events: [], eventsParams: range("12", "16") });
    response.resolve({ data: [event(1, "15")] });
    await request;
    assert.deepEqual(h.store.getters.events, []);
});

test("production event edits clear coverage and block reads until the save finishes", async () => {
    const save = deferred();
    let gets = 0;
    const h = storeHarness({
        get: async () => { gets++; return { data: [event(1, "15", "15", { title: gets === 1 ? "old" : "saved" })] }; },
        update: () => save.promise,
    });
    await h.store.dispatch(h.actions.FETCH_EVENTS);
    const writing = h.store.dispatch(h.actions.UPDATE_EVENT, { id: 1, title: "saved" });
    const reading = h.store.dispatch(h.actions.FETCH_EVENTS);
    await tick();
    assert.equal(gets, 1);
    save.resolve();
    await writing;
    await reading;
    assert.equal(gets, 2);
    assert.equal(h.store.getters.events[0].title, "saved");
});

test("a save during initial HTTP loading automatically reloads the still-visible range", async () => {
    const response = deferred();
    let gets = 0;
    const h = storeHarness({
        get: async () => ++gets === 1 ? response.promise : { data: [event(1, "15", "15", { title: "saved" })] },
        update: async () => {},
    });
    const loading = h.store.dispatch(h.actions.FETCH_EVENTS);
    await tick();
    await h.store.dispatch(h.actions.UPDATE_EVENT, { id: 1, title: "saved" });
    response.resolve({ data: [event(1, "15", "15", { title: "old" })] });
    await loading;
    assert.equal(gets, 2);
    assert.equal(h.store.getters.events[0].title, "saved");
});
