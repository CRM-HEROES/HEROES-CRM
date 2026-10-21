const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");
const { createStore } = require("vuex");

const sourceRoot = path.resolve(__dirname, "../../resources/js");
const compiledSources = new Map();

// Load production code, replacing only its HTTP and router dependencies.
// Each harness has its own module instances, including the singleton cache.
function loadModules(services = {}, cacheOptions) {
    const modules = new Map();
    const dependencies = {
        "@/apis/project/event": services.events || {},
        "@/apis/project/prospect/event": services.prospectEvents || {},
        "@/apis/event": {},
        axios: {},
        "../router": {},
    };

    function load(relativePath) {
        if (modules.has(relativePath)) return modules.get(relativePath).exports;
        if (!compiledSources.has(relativePath)) {
            compiledSources.set(relativePath, transformSync(
                readFileSync(path.join(sourceRoot, relativePath), "utf8"),
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
        const requireDependency = (specifier) => {
            if (Object.hasOwn(dependencies, specifier)) return dependencies[specifier];
            if (specifier.startsWith("@/")) return load(`${specifier.substring(2)}.js`);
            throw new Error(`Unexpected dependency: ${specifier}`);
        };
        new Function("require", "module", "exports", compiledSources.get(relativePath))(
            requireDependency, module, module.exports
        );
        return module.exports;
    }

    const helper = load("utils/event-daily-direction-cache.js");
    if (cacheOptions) helper.default = helper.createDailyDirectionCache(cacheOptions);
    return {
        load,
        ...helper,
    };
}

function integrationHarness({ events = [], services = {}, cacheOptions } = {}) {
    const modules = loadModules(services, cacheOptions);
    const eventStore = modules.load("store/project/event.js").default;
    const prospectEventStore = modules.load("store/project/prospect/event.js").default;
    const globalEventStore = modules.load("store/event.js").default;
    const authStore = modules.load("store/auth.js").default;
    const eventActions = modules.load("actions/project/event.js");
    const prospectActions = modules.load("actions/project/prospect/event.js");
    const globalActions = modules.load("actions/event.js");
    const plugin = modules.load("store/event-daily-direction-cache.js").default;
    const prospect = { id: 9, events: [...events] };
    const project = { slug: "alpha", events: [...events], prospect, prospects: [prospect] };
    const store = createStore({
        modules: {
            auth: {
                ...authStore,
                state: { ...authStore.state, authenticated: true, user: { id: 7 } },
            },
            project: {
                state: { project },
                actions: { ...eventStore.actions, ...prospectEventStore.actions },
                mutations: {
                    ...eventStore.mutations,
                    ...prospectEventStore.mutations,
                    setTestProject(state, next) { state.project = next; },
                },
                getters: eventStore.getters,
            },
            event: { ...globalEventStore, state: { ...globalEventStore.state } },
        },
        mutations: { RESET() {} },
        plugins: [plugin],
    });

    return {
        ...modules,
        store,
        eventActions,
        prospectActions,
        globalActions,
        fetch(userId = 526, date = "2026-10-15", currentEvents = events) {
            return store.dispatch(eventActions.FETCH_EVENT_USER_DAILY_DIRECTION, {
                user: { id: userId }, direction_at: date, events: currentEvents,
            });
        },
        get(userId = 526, date = "2026-10-15", currentEvents = events) {
            return store.getters.getEventUserDailyDirection({ id: userId }, date, currentEvents);
        },
    };
}

function event(id = 1, overrides = {}) {
    return {
        id,
        user_id: 526,
        calendar_id: 2,
        calendar: { type: "physical" },
        started_at: "2026-10-15 09:00:00",
        ended_at: "2026-10-15 10:00:00",
        location: "Paris",
        latitude: 48.85,
        longitude: 2.35,
        ...overrides,
    };
}

function direction(overrides = {}) {
    return { events: [1], direction: { routes: [{ summary: "Route" }] }, searching: false, ...overrides };
}

function deferred() {
    let resolve;
    let reject;
    const promise = new Promise((done, fail) => { resolve = done; reject = fail; });
    return { promise, resolve, reject };
}

const tick = () => new Promise((resolve) => setImmediate(resolve));

test("cache reuses a route only for the same scope, user, date and event signature", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const events = [event()];
    const result = direction();
    let calls = 0;
    const load = async () => { calls++; return result; };

    assert.equal(await cache.fetch("session-alpha", 526, "2026-10-15", events, load), result);
    assert.equal(await cache.fetch("session-alpha", "526", "2026-10-15", events, load), result);
    assert.equal(calls, 1);
    assert.equal(cache.get("session-beta", 526, "2026-10-15", events), undefined);
    assert.equal(cache.get("session-alpha", 527, "2026-10-15", events), undefined);
    assert.equal(cache.get("session-alpha", 526, "2026-10-16", events), undefined);
});

test("signature ignores ordering and cosmetic fields but includes route changes with unchanged IDs", () => {
    const { dailyDirectionEventsSignature: signature } = loadModules();
    const first = event();
    const second = event(2);
    assert.equal(signature([first, null, second]), signature([second, { ...first, name: "Renamed", confirmed: true }]));
    assert.equal(signature([first]), signature([{ ...first, id: "1", user_id: "526" }]));
    for (const change of [
        { id: 3 }, { user_id: 527 }, { calendar_id: 3 },
        { calendar: { type: "phone" } },
        { started_at: "2026-10-15 11:00:00" }, { ended_at: "2026-10-15 12:00:00" },
        { location: "Lyon" }, { latitude: 45.76 }, { longitude: 4.83 },
    ]) {
        assert.notEqual(signature([first]), signature([{ ...first, ...change }]));
    }
});

test("backend event IDs may differ from the visible event list without defeating caching", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const visible = [event(1)];
    const result = direction({ events: [1, 2, 3] });
    let calls = 0;
    const load = async () => { calls++; return result; };
    await cache.fetch("scope", 526, "2026-10-15", visible, load);
    await cache.fetch("scope", 526, "2026-10-15", visible, load);
    assert.equal(calls, 1);
    cache.invalidate([2]);
    assert.equal(cache.get("scope", 526, "2026-10-15", visible), undefined);
});

test("simultaneous requests share one promise and a changed signature starts a new request", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const response = deferred();
    let calls = 0;
    const load = () => { calls++; return response.promise; };
    const first = cache.fetch("scope", 526, "2026-10-15", [event()], load);
    const same = cache.fetch("scope", 526, "2026-10-15", [event()], load);
    assert.equal(first, same);
    assert.equal(calls, 1);
    const changed = cache.fetch("scope", 526, "2026-10-15", [event(1, { location: "Lyon" })], load);
    assert.notEqual(first, changed);
    assert.equal(calls, 2);
    response.resolve(direction());
    assert.equal(await first, null);
    assert.ok(await changed);
});

test("invalidating an event ID discards its pending response", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const response = deferred();
    const pending = cache.fetch("scope", 526, "2026-10-15", [event()], () => response.promise);
    cache.invalidate([1]);
    response.resolve(direction());
    assert.equal(await pending, null);
    assert.equal(cache.get("scope", 526, "2026-10-15", [event()]), undefined);
});

test("old and new user/date pairs are invalidated even when cached routes did not contain the event", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const oldEvent = event();
    const movedEvent = event(1, { user_id: 527, started_at: "2026-10-16 09:00:00", ended_at: "2026-10-16 10:00:00" });
    for (const [userId, date] of [[526, "2026-10-15"], [527, "2026-10-16"], [528, "2026-10-17"]]) {
        await cache.fetch("scope", userId, date, [], async () => direction({ events: [] }));
    }
    cache.invalidate([oldEvent, movedEvent]);
    assert.equal(cache.get("scope", 526, "2026-10-15", []), undefined);
    assert.equal(cache.get("scope", 527, "2026-10-16", []), undefined);
    assert.ok(cache.get("scope", 528, "2026-10-17", []));
});

test("an invalidated request's finally cannot remove the replacement request", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const oldResponse = deferred();
    const newResponse = deferred();
    const events = [event()];
    const oldRequest = cache.fetch("scope", 526, "2026-10-15", events, () => oldResponse.promise);
    cache.invalidate([1]);
    const newRequest = cache.fetch("scope", 526, "2026-10-15", events, () => newResponse.promise);
    oldResponse.resolve(direction({ direction: { summary: "stale" } }));
    assert.equal(await oldRequest, null);
    assert.equal(cache.fetch("scope", 526, "2026-10-15", events, () => assert.fail("duplicate request")), newRequest);
    const result = direction();
    newResponse.resolve(result);
    assert.equal(await newRequest, result);
    assert.equal(cache.get("scope", 526, "2026-10-15", events), result);
});

test("failed and searching requests are retryable and never poison the cache", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const events = [event()];
    await assert.rejects(cache.fetch("scope", 526, "2026-10-15", events, async () => { throw new Error("offline"); }), /offline/);
    assert.equal(await cache.fetch("scope", 526, "2026-10-15", events, async () => direction({ searching: true })), null);
    assert.equal(cache.get("scope", 526, "2026-10-15", events), undefined);
    const result = direction();
    assert.equal(await cache.fetch("scope", 526, "2026-10-15", events, async () => result), result);
});

test("routes expire after the normal TTL and empty directions after the short TTL", async () => {
    const { createDailyDirectionCache } = loadModules();
    let now = 0;
    const cache = createDailyDirectionCache({ ttl: 1000, emptyTtl: 30, now: () => now });
    await cache.fetch("scope", 526, "2026-10-15", [], async () => direction());
    await cache.fetch("scope", 527, "2026-10-15", [], async () => direction({ direction: null, events: [] }));
    now = 29;
    assert.ok(cache.get("scope", 527, "2026-10-15", []));
    now = 30;
    assert.equal(cache.get("scope", 527, "2026-10-15", []), undefined);
    assert.ok(cache.get("scope", 526, "2026-10-15", []));
    now = 1000;
    assert.equal(cache.get("scope", 526, "2026-10-15", []), undefined);
});

test("cache evicts the least recently used route when its capacity is reached", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache({ maxEntries: 2 });
    for (const userId of [526, 527]) await cache.fetch("scope", userId, "2026-10-15", [], async () => direction());
    cache.get("scope", 526, "2026-10-15", []);
    await cache.fetch("scope", 528, "2026-10-15", [], async () => direction());
    assert.equal(cache.get("scope", 527, "2026-10-15", []), undefined);
    assert.ok(cache.get("scope", 526, "2026-10-15", []));
    assert.ok(cache.get("scope", 528, "2026-10-15", []));
});

test("reads wait for every optimistic write to finish before starting HTTP", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    const releaseFirst = cache.beginWrite();
    let calls = 0;
    const pending = cache.fetch("scope", 526, "2026-10-15", [], async () => { calls++; return direction(); });
    const releaseSecond = cache.beginWrite();
    releaseFirst();
    await tick();
    assert.equal(calls, 0);
    releaseSecond();
    assert.ok(await pending);
    assert.equal(calls, 1);
});

test("clear discards cached and pending routes and releases write gates", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache();
    await cache.fetch("scope", 527, "2026-10-15", [], async () => direction());
    const response = deferred();
    const oldRequest = cache.fetch("scope", 526, "2026-10-15", [], () => response.promise);
    cache.beginWrite();
    const waitingRequest = cache.fetch("scope", 528, "2026-10-15", [], () => assert.fail("cleared waiter started HTTP"));
    cache.clear();
    response.resolve(direction());
    assert.equal(await oldRequest, null);
    assert.equal(await waitingRequest, null);
    assert.equal(cache.get("scope", 527, "2026-10-15", []), undefined);
    assert.ok(await cache.fetch("scope", 529, "2026-10-15", [], async () => direction()));
});

test("post-write directions are returned but not cached until the settling delay expires", async () => {
    const { createDailyDirectionCache } = loadModules();
    let now = 0;
    const cache = createDailyDirectionCache({ settleTime: 5000, now: () => now });
    const events = [event()];
    let calls = 0;
    const load = async () => { calls++; return direction(); };
    cache.deferCaching(events);

    const first = await cache.fetch("scope", 526, "2026-10-15", events, load);
    assert.ok(first.direction);
    assert.equal(cache.get("scope", 526, "2026-10-15", events), undefined);
    now = 4999;
    await cache.fetch("scope", 526, "2026-10-15", events, load);
    assert.equal(cache.get("scope", 526, "2026-10-15", events), undefined);
    now = 5000;
    const settled = await cache.fetch("scope", 526, "2026-10-15", events, load);
    assert.equal(await cache.fetch("scope", 526, "2026-10-15", events, load), settled);
    assert.equal(cache.get("scope", 526, "2026-10-15", events), settled);
    assert.equal(calls, 3);
});

test("settling directions still share HTTP requests and leave unrelated user/days cacheable", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache({ settleTime: 5000, now: () => 0 });
    const response = deferred();
    const events = [event()];
    cache.deferCaching(events);
    const first = cache.fetch("scope", 526, "2026-10-15", events, () => response.promise);
    const second = cache.fetch("scope", 526, "2026-10-15", events, () => assert.fail("duplicate direction request"));
    assert.equal(first, second);
    response.resolve(direction());
    assert.equal(await first, await second);
    assert.equal(cache.get("scope", 526, "2026-10-15", events), undefined);
    const otherRoute = await cache.fetch("scope", 527, "2026-10-15", [], async () => direction({ events: [] }));
    assert.equal(cache.get("scope", 527, "2026-10-15", []), otherRoute);
    const otherDay = await cache.fetch("scope", 526, "2026-10-16", [], async () => direction({ events: [] }));
    assert.equal(cache.get("scope", 526, "2026-10-16", []), otherDay);
});

test("a read started during settling is not cached when its response arrives after the delay", async () => {
    const { createDailyDirectionCache } = loadModules();
    let now = 0;
    const cache = createDailyDirectionCache({ settleTime: 5000, now: () => now });
    const events = [event()];
    const response = deferred();
    cache.deferCaching(events);
    now = 4999;
    const pending = cache.fetch("scope", 526, "2026-10-15", events, () => response.promise);
    now = 6000;
    cache.deferCaching([event(99, { user_id: 527, started_at: "2026-10-16 09:00:00", ended_at: "2026-10-16 10:00:00" })]);
    response.resolve(direction());
    assert.ok(await pending);
    assert.equal(cache.get("scope", 526, "2026-10-15", events), undefined);
    const settled = await cache.fetch("scope", 526, "2026-10-15", events, async () => direction());
    assert.equal(cache.get("scope", 526, "2026-10-15", events), settled);
});

test("clear removes post-write settling delays together with the old session cache", async () => {
    const { createDailyDirectionCache } = loadModules();
    const cache = createDailyDirectionCache({ settleTime: 5000, now: () => 0 });
    const events = [event()];
    cache.deferCaching(events);
    cache.clear();
    const result = await cache.fetch("scope", 526, "2026-10-15", events, async () => direction());
    assert.equal(cache.get("scope", 526, "2026-10-15", events), result);
});

test("the production action normalizes API JSON once and shares its cache with the getter", async () => {
    const calls = [];
    const harness = integrationHarness({
        events: [event()],
        services: { events: { dailyDirection(user, date) {
            calls.push([user.id, date]);
            return Promise.resolve({ data: { events: "[1,2]", direction: '{"routes":[]}', searching: 0 } });
        } } },
    });
    const result = await harness.fetch();
    assert.deepEqual(result.events, [1, 2]);
    assert.deepEqual(result.direction, { routes: [] });
    assert.equal(result.searching, false);
    assert.equal(harness.get(), result);
    assert.equal(await harness.fetch(), result);
    assert.deepEqual(calls, [[526, "2026-10-15"]]);
    assert.equal(await harness.fetch(null), null);
    assert.equal(JSON.stringify(harness.store.state).includes('"routes"'), false);
});

test("parallel production actions make only one direction request per user/day", async () => {
    const response = deferred();
    let calls = 0;
    const harness = integrationHarness({ events: [event()], services: { events: { dailyDirection: () => {
        calls++;
        return response.promise;
    } } } });
    const first = harness.fetch();
    const second = harness.fetch();
    assert.equal(calls, 1);
    response.resolve({ data: direction() });
    assert.equal(await first, await second);
    assert.equal(calls, 1);
});

test("agenda and map callers share a route when only the agenda supplies events", async () => {
    let calls = 0;
    const events = [event()];
    const harness = integrationHarness({ events, services: { events: { dailyDirection: async () => {
        calls++;
        return { data: direction() };
    } } } });
    const result = await harness.fetch();
    const fromMap = await harness.store.dispatch(harness.eventActions.FETCH_EVENT_USER_DAILY_DIRECTION, {
        user: { id: 526 }, direction_at: "2026-10-15",
    });
    assert.equal(fromMap, result);
    assert.equal(harness.store.getters.getEventUserDailyDirection({ id: 526 }, "2026-10-15"), result);
    assert.equal(calls, 1);
});

test("an older global copy cannot override a project edit when sharing agenda and map routes", async () => {
    let calls = 0;
    const original = event();
    const moved = event(1, { location: "Lyon" });
    const harness = integrationHarness({ events: [original], services: { events: { dailyDirection: async () => {
        calls++;
        return { data: direction() };
    } } } });
    harness.store.commit(harness.globalActions.SET_GLOBAL_EVENTS, [original]);
    harness.store.commit(harness.eventActions.UPDATE_EVENT, moved);
    const result = await harness.fetch(526, "2026-10-15", [moved]);
    const fromMap = await harness.store.dispatch(harness.eventActions.FETCH_EVENT_USER_DAILY_DIRECTION, {
        user: { id: 526 }, direction_at: "2026-10-15",
    });
    assert.equal(fromMap, result);
    assert.equal(calls, 1);
});

for (const response of [null, { events: null, direction: null }, { events: "[]", direction: "null" }]) {
    test(`the production action caches empty API response ${JSON.stringify(response)}`, async () => {
        let calls = 0;
        const harness = integrationHarness({ services: { events: { dailyDirection: async () => { calls++; return { data: response }; } } } });
        const result = await harness.fetch();
        assert.deepEqual(result.events, []);
        assert.equal(result.direction, null);
        assert.equal(await harness.fetch(), result);
        assert.equal(calls, 1);
    });
}

test("the production action retries HTTP errors and API searching responses", async () => {
    let calls = 0;
    const harness = integrationHarness({ services: { events: { dailyDirection: async () => {
        calls++;
        if (calls === 1) throw new Error("503");
        return { data: calls === 2 ? { searching: "1" } : direction() };
    } } } });
    await assert.rejects(harness.fetch(), /503/);
    assert.equal(await harness.fetch(), null);
    assert.ok(await harness.fetch());
    assert.equal(calls, 3);
});

for (const namespace of ["event", "prospect"]) {
    test(`${namespace} create, update and delete invalidate routes through the production plugin`, async () => {
        const existing = event();
        const created = event(3);
        let calls = 0;
        const service = {
            create: async () => ({ data: created }),
            update: async () => ({}),
            destroy: async () => ({}),
        };
        const harness = integrationHarness({
            events: [existing],
            services: {
                events: { ...service, dailyDirection: async () => { calls++; return { data: direction() }; } },
                prospectEvents: service,
            },
        });
        const actions = namespace === "event" ? harness.eventActions : harness.prospectActions;
        const add = namespace === "event" ? actions.ADD_EVENT : actions.ADD_PROSPECT_EVENT;
        const update = namespace === "event" ? actions.UPDATE_EVENT : actions.UPDATE_PROSPECT_EVENT;
        const remove = namespace === "event" ? actions.REMOVE_EVENT : actions.REMOVE_PROSPECT_EVENT;
        await harness.fetch();
        await harness.store.dispatch(add, created);
        assert.equal(harness.get(), undefined);
        await harness.fetch();
        await harness.store.dispatch(update, { id: 1, location: "Lyon" });
        assert.equal(harness.get(), undefined);
        await harness.fetch();
        await harness.store.dispatch(remove, 1);
        assert.equal(harness.get(), undefined);
        await harness.fetch();
        assert.equal(calls, 4);
    });
}

test("moving an event invalidates its old and new user/day even for empty cached routes", async () => {
    const original = event();
    const moved = event(1, { user_id: 527, started_at: "2026-10-16 09:00:00", ended_at: "2026-10-16 10:00:00" });
    const harness = integrationHarness({
        events: [original],
        services: { events: { update: async () => ({}), dailyDirection: async () => ({ data: direction({ events: [] }) }) } },
    });
    await harness.fetch(526, "2026-10-15", []);
    await harness.fetch(527, "2026-10-16", []);
    await harness.fetch(528, "2026-10-17", []);
    await harness.store.dispatch(harness.eventActions.UPDATE_EVENT, moved);
    assert.equal(harness.get(526, "2026-10-15", []), undefined);
    assert.equal(harness.get(527, "2026-10-16", []), undefined);
    assert.ok(harness.get(528, "2026-10-17", []));
});

test("an optimistic mutation blocks fresh direction HTTP until its save finishes", async () => {
    const response = deferred();
    let calls = 0;
    const harness = integrationHarness({
        events: [event()],
        services: { events: {
            update: () => response.promise,
            dailyDirection: async () => { calls++; return { data: direction() }; },
        } },
    });
    const moved = event(1, { location: "Lyon" });
    harness.store.commit(harness.eventActions.UPDATE_EVENT, moved);
    const save = harness.store.dispatch(harness.eventActions.UPDATE_EVENT, moved);
    const duringSave = harness.fetch(526, "2026-10-15", [moved]);
    await tick();
    assert.equal(calls, 0);
    response.resolve({});
    await save;
    assert.equal(await duringSave, null);
    assert.ok(await harness.fetch(526, "2026-10-15", [moved]));
    assert.equal(calls, 1);
});

test("failed writes release the HTTP gate and leave the route retryable", async () => {
    const response = deferred();
    const harness = integrationHarness({
        events: [event()],
        services: { events: { update: () => response.promise, dailyDirection: async () => ({ data: direction() }) } },
    });
    const save = harness.store.dispatch(harness.eventActions.UPDATE_EVENT, { id: 1, location: "Lyon" });
    const duringSave = harness.fetch();
    response.reject(new Error("save failed"));
    await assert.rejects(save, /save failed/);
    assert.equal(await duringSave, null);
    assert.ok(await harness.fetch());
});

test("the plugin defers caching for five seconds after the write finishes, then caches normally", async () => {
    let now = 0;
    let calls = 0;
    const response = deferred();
    const moved = event(1, { location: "Lyon" });
    const harness = integrationHarness({
        events: [event()],
        cacheOptions: { settleTime: 5000, now: () => now },
        services: { events: {
            update: () => response.promise,
            dailyDirection: async () => { calls++; return { data: direction() }; },
        } },
    });
    await harness.fetch();
    const save = harness.store.dispatch(harness.eventActions.UPDATE_EVENT, moved);
    // Even a slow PUT needs a fresh settling window after its completion.
    now = 6000;
    response.resolve({});
    await save;
    assert.ok(await harness.fetch(526, "2026-10-15", [moved]));
    assert.equal(harness.get(526, "2026-10-15", [moved]), undefined);
    now = 10999;
    assert.ok(await harness.fetch(526, "2026-10-15", [moved]));
    assert.equal(harness.get(526, "2026-10-15", [moved]), undefined);
    now = 11000;
    const settled = await harness.fetch(526, "2026-10-15", [moved]);
    assert.equal(harness.get(526, "2026-10-15", [moved]), settled);
    assert.equal(await harness.fetch(526, "2026-10-15", [moved]), settled);
    assert.equal(calls, 4);
});

test("cosmetic event edits keep a cached direction", async () => {
    const harness = integrationHarness({
        events: [event()],
        services: { events: { update: async () => ({}), dailyDirection: async () => ({ data: direction() }) } },
    });
    const result = await harness.fetch();
    await harness.store.dispatch(harness.eventActions.UPDATE_EVENT, { id: 1, name: "Renamed", confirmed: true });
    assert.equal(harness.get(), result);
});

test("project and global refreshes invalidate routes only when route data changes", async () => {
    const initial = event();
    const harness = integrationHarness({ events: [initial], services: { events: { dailyDirection: async () => ({ data: direction() }) } } });
    const result = await harness.fetch();
    harness.store.commit(harness.eventActions.SET_EVENTS, [{ ...initial, name: "Renamed" }]);
    assert.equal(harness.get(), result);
    harness.store.commit(harness.eventActions.SET_EVENTS, [event(1, { latitude: 45.76 })]);
    assert.equal(harness.get(), undefined);
    await harness.fetch();
    harness.store.commit(harness.globalActions.SET_GLOBAL_EVENTS, [initial]);
    assert.equal(harness.get(), undefined);
    const globalResult = await harness.fetch();
    harness.store.commit(harness.globalActions.SET_GLOBAL_EVENTS, []);
    assert.equal(harness.get(), globalResult);
    harness.store.commit(harness.globalActions.SET_GLOBAL_EVENTS, [event(1, { longitude: 4.83 })]);
    assert.equal(harness.get(), undefined);
});

test("remounting the agenda and restoring its events reuses the cached direction", async () => {
    let calls = 0;
    const events = [event()];
    const harness = integrationHarness({ events, services: { events: { dailyDirection: async () => {
        calls++;
        return { data: direction() };
    } } } });
    const result = await harness.fetch();
    harness.store.commit(harness.eventActions.SET_EVENTS, []);
    harness.store.commit(harness.eventActions.SET_EVENTS, events.map((item) => ({ ...item })));
    assert.equal(await harness.fetch(), result);
    assert.equal(calls, 1);
});

test("successive moves invalidate the latest previous user/day snapshot", async () => {
    let now = 0;
    const first = event();
    const second = event(1, { user_id: 527, started_at: "2026-10-16 09:00:00", ended_at: "2026-10-16 10:00:00" });
    const third = event(1, { user_id: 528, started_at: "2026-10-17 09:00:00", ended_at: "2026-10-17 10:00:00" });
    const harness = integrationHarness({
        events: [first],
        cacheOptions: { settleTime: 5000, now: () => now },
        services: { events: { update: async () => ({}), dailyDirection: async () => ({ data: direction({ events: [] }) }) } },
    });
    await harness.store.dispatch(harness.eventActions.UPDATE_EVENT, second);
    now = 5000;
    await harness.fetch(527, "2026-10-16", []);
    await harness.fetch(528, "2026-10-17", []);
    assert.ok(harness.get(527, "2026-10-16", []));
    assert.ok(harness.get(528, "2026-10-17", []));
    await harness.store.dispatch(harness.eventActions.UPDATE_EVENT, third);
    assert.equal(harness.get(527, "2026-10-16", []), undefined);
    assert.equal(harness.get(528, "2026-10-17", []), undefined);
});

for (const [label, mutation, payload] of [
    ["authenticated user", "auth/SET_USER", { id: 8 }],
    ["logout", "auth/SET_AUTHENTICATED", false],
    ["impersonation", "auth/SET_IMPERSONATING", true],
    ["impersonated project", "auth/SET_IMPERSONATE_PROJECT", "beta"],
    ["project", "setTestProject", { slug: "beta", events: [] }],
    ["root reset", "RESET", undefined],
]) {
    test(`${label} changes clear cached and pending directions`, async () => {
        const response = deferred();
        let calls = 0;
        const harness = integrationHarness({ services: { events: { dailyDirection: async () => {
            calls++;
            return calls === 2 ? response.promise : { data: direction() };
        } } } });
        await harness.fetch(526);
        const pending = harness.fetch(527);
        harness.store.commit(mutation, payload);
        assert.equal(harness.get(526), undefined);
        response.resolve({ data: direction() });
        assert.equal(await pending, null);
        assert.ok(await harness.fetch(526));
        assert.equal(calls, 3);
    });
}
