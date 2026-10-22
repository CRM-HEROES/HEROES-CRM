const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");
const { createStore } = require("vuex");

const sourceRoot = path.resolve(__dirname, "../../resources/js");
const compiledSources = new Map();

// Run the production batcher and Vuex actions, replacing only their I/O.
function loadModules(services = {}) {
    const modules = new Map();
    const dependencies = {
        "@/apis/project/event": services,
        "@/apis/project/prospect/event": {},
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

    return { load, ...load("utils/event-daily-direction-summary.js") };
}

function harness({ services = {}, events = [], can } = {}) {
    const modules = loadModules(services);
    const eventStore = modules.load("store/project/event.js").default;
    const authStore = modules.load("store/auth.js").default;
    const actions = modules.load("actions/project/event.js");
    const plugin = modules.load("store/event-daily-direction-cache.js").default;
    const store = createStore({
        modules: {
            auth: {
                ...authStore,
                state: { ...authStore.state, authenticated: true, user: { id: 7 } },
            },
            project: {
                state: { project: { slug: "alpha", events: [...events] } },
                actions: eventStore.actions,
                mutations: {
                    ...eventStore.mutations,
                    setTestProject(state, next) { state.project = next; },
                },
                getters: eventStore.getters,
            },
        },
        mutations: { RESET() {} },
        getters: can ? { can: () => can } : {},
        plugins: [plugin],
    });
    const fetch = (userId = 526, date = "2026-10-15", currentEvents = events) => store.dispatch(
        actions.FETCH_EVENT_USER_DAILY_DIRECTION_SUMMARY,
        { user: { id: userId }, direction_at: date, events: currentEvents }
    );
    const detail = (userId = 526, date = "2026-10-15", currentEvents = events) => store.dispatch(
        actions.FETCH_EVENT_USER_DAILY_DIRECTION,
        { user: { id: userId }, direction_at: date, events: currentEvents }
    );
    return { ...modules, actions, store, fetch, detail };
}

function summary(userId = 526, date = "2026-10-15", overrides = {}) {
    return {
        user_id: userId,
        direction_at: date,
        events: [1],
        status: "ready",
        legs: [{ distance: { value: 1200, text: "1.2 km" }, duration: { value: 180, text: "3 mins" } }],
        ...overrides,
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

const responseFor = (pairs) => ({ data: { data: pairs.map((pair) => summary(pair.user_id, pair.direction_at)) } });
const deferred = () => {
    let resolve;
    let reject;
    const promise = new Promise((done, fail) => { resolve = done; reject = fail; });
    return { promise, resolve, reject };
};
const tick = () => new Promise((resolve) => setTimeout(resolve, 5));
async function waitFor(predicate) {
    for (let count = 0; count < 100 && !predicate(); count++) await tick();
    assert.ok(predicate(), "expected asynchronous batch to start");
}

test("460 user/day requests become one summary HTTP request", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    const calls = [];
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        calls.push({ project, pairs });
        return responseFor(pairs);
    }, { delay: 0 });
    const requests = [];
    for (let day = 12; day < 22; day++) {
        for (let user = 500; user < 546; user++) {
            requests.push(batcher.request("scope", "alpha", user, `2026-10-${day}`));
        }
    }
    const results = await Promise.all(requests);
    assert.equal(calls.length, 1);
    assert.equal(calls[0].project, "alpha");
    assert.equal(calls[0].pairs.length, 460);
    assert.equal(new Set(calls[0].pairs.map((pair) => `${pair.user_id}:${pair.direction_at}`)).size, 460);
    assert.equal(results.length, 460);
    assert.equal(results[0].status, "ready");
    assert.equal(results[459].user_id, 545);
});

test("identical queued summary pairs share their result without duplicate HTTP pairs", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    const response = deferred();
    const calls = [];
    const batcher = createDailyDirectionSummaryBatcher((project, pairs) => {
        calls.push(pairs);
        return response.promise;
    }, { delay: 0 });
    const first = batcher.request("scope", "alpha", 526, "2026-10-15");
    const same = batcher.request("scope", "alpha", "526", "2026-10-15");
    await waitFor(() => calls.length === 1);
    assert.equal(calls[0].length, 1);
    response.resolve(responseFor(calls[0]));
    const results = await Promise.all([first, same]);
    assert.equal(results[0], results[1]);
    assert.equal(calls.length, 1);
});

test("summary batches keep authentication scopes and projects separate", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    const calls = [];
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        calls.push({ project, pairs });
        return responseFor(pairs);
    }, { delay: 0 });
    await Promise.all([
        batcher.request("session-a", "alpha", 526, "2026-10-15"),
        batcher.request("session-b", "alpha", 526, "2026-10-15"),
        batcher.request("session-a", "beta", 526, "2026-10-15"),
    ]);
    assert.equal(calls.length, 3);
    assert.deepEqual(calls.map(({ project }) => project).sort(), ["alpha", "alpha", "beta"]);
    assert.ok(calls.every(({ pairs }) => pairs.length === 1));
});

test("summary batches split at the API limit with only one HTTP request in flight", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    const calls = [];
    let active = 0;
    let peakActive = 0;
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        const response = deferred();
        calls.push({ pairs, response });
        active++;
        peakActive = Math.max(peakActive, active);
        await response.promise;
        active--;
        return responseFor(pairs);
    }, { delay: 0, maxPairs: 2 });
    const pending = Promise.all([526, 527, 528, 529, 530].map((user) =>
        batcher.request("scope", "alpha", user, "2026-10-15")
    ));
    for (let index = 0; index < 3; index++) {
        await waitFor(() => calls.length > index);
        assert.ok(calls[index].pairs.length <= 2);
        assert.equal(active, 1);
        calls[index].response.resolve();
    }
    assert.equal((await pending).length, 5);
    assert.equal(calls.length, 3);
    assert.equal(peakActive, 1);
});

test("a failed summary batch rejects its callers and permits a fresh retry", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    let calls = 0;
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        calls++;
        if (calls === 1) throw new Error("503");
        return responseFor(pairs);
    }, { delay: 0 });
    const results = await Promise.allSettled([
        batcher.request("scope", "alpha", 526, "2026-10-15"),
        batcher.request("scope", "alpha", 527, "2026-10-15"),
    ]);
    assert.ok(results.every((result) => result.status === "rejected" && result.reason.message === "503"));
    assert.equal((await batcher.request("scope", "alpha", 526, "2026-10-15")).status, "ready");
    assert.equal(calls, 2);
});

test("missing pairs in a successful summary response remain retryable", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    let calls = 0;
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        calls++;
        return calls === 1 ? { data: { data: [] } } : responseFor(pairs);
    }, { delay: 0 });
    assert.equal(await batcher.request("scope", "alpha", 526, "2026-10-15"), null);
    assert.equal((await batcher.request("scope", "alpha", 526, "2026-10-15")).status, "ready");
    assert.equal(calls, 2);
});

test("clearing the batcher discards unsent pairs without making an HTTP request", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    let calls = 0;
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        calls++;
        return responseFor(pairs);
    }, { delay: 10 });
    const pending = batcher.request("scope", "alpha", 526, "2026-10-15");
    batcher.clear();
    assert.equal(await pending, null);
    await new Promise((resolve) => setTimeout(resolve, 20));
    assert.equal(calls, 0);
});

test("clearing an active batch ignores its stale response and preserves its replacement", async () => {
    const { createDailyDirectionSummaryBatcher } = loadModules();
    const response = deferred();
    const calls = [];
    const batcher = createDailyDirectionSummaryBatcher(async (project, pairs) => {
        calls.push(pairs);
        return calls.length === 1 ? response.promise : responseFor(pairs);
    }, { delay: 0 });
    const stale = batcher.request("scope", "alpha", 526, "2026-10-15");
    await waitFor(() => calls.length === 1);
    batcher.clear();
    const fresh = batcher.request("scope", "alpha", 526, "2026-10-15");
    response.resolve(responseFor(calls[0]));
    assert.equal(await stale, null);
    assert.equal((await fresh).status, "ready");
    assert.equal(calls.length, 2);
});

test("production summary actions batch users and reuse cached user/day results", async () => {
    const calls = [];
    const app = harness({ services: {
        dailyDirectionSummaries: async (project, { pairs }) => {
            calls.push({ project, pairs });
            return responseFor(pairs);
        },
        dailyDirection: () => assert.fail("summary loading requested a Google Maps detail"),
    } });
    const [first, second] = await Promise.all([app.fetch(526), app.fetch(527)]);
    assert.equal(first.status, "ready");
    assert.equal(second.user_id, 527);
    assert.equal(calls.length, 1);
    assert.equal(calls[0].project, "alpha");
    assert.equal(calls[0].pairs.length, 2);
    assert.equal(await app.fetch(526), first);
    assert.equal(await app.fetch(527), second);
    assert.equal(calls.length, 1);
    assert.equal(await app.fetch(null), null);
});

test("summary batches include only the current user and explicitly authorized other users", async () => {
    const checkedPermissions = [];
    const calls = [];
    const app = harness({
        can: (permission) => {
            checkedPermissions.push(permission);
            return permission === "all.project.user.agenda.527";
        },
        services: {
            dailyDirectionSummaries: async (project, { pairs }) => {
                calls.push(pairs);
                return responseFor(pairs);
            },
        },
    });
    const denied = app.fetch(526);
    const own = app.fetch(7);
    const allowed = app.fetch(527);
    assert.equal(await denied, null);
    assert.equal((await own).user_id, 7);
    assert.equal((await allowed).user_id, 527);
    assert.equal(calls.length, 1);
    assert.deepEqual(calls[0].map((pair) => pair.user_id).sort((a, b) => a - b), [7, 527]);
    assert.ok(checkedPermissions.includes("all.project.user.agenda.526"));
    assert.ok(checkedPermissions.includes("all.project.user.agenda.527"));
});

test("the agenda cache retains all 460 user/day summaries from a large displayed period", async () => {
    let calls = 0;
    const app = harness({ services: {
        dailyDirectionSummaries: async (project, { pairs }) => {
            calls++;
            return responseFor(pairs);
        },
    } });
    const pairs = [];
    for (let day = 12; day < 22; day++) {
        for (let user = 500; user < 546; user++) pairs.push([user, `2026-10-${day}`]);
    }
    const first = await Promise.all(pairs.map(([user, date]) => app.fetch(user, date)));
    const cached = await Promise.all(pairs.map(([user, date]) => app.fetch(user, date)));
    assert.equal(calls, 1);
    assert.ok(first.every((result, index) => result === cached[index]));
});

test("production summary actions share identical reads queued and during HTTP", async () => {
    const response = deferred();
    const calls = [];
    const app = harness({ services: {
        dailyDirectionSummaries: async (project, { pairs }) => {
            calls.push(pairs);
            return response.promise;
        },
    } });
    const first = app.fetch();
    const queued = app.fetch();
    await waitFor(() => calls.length === 1);
    const duringHttp = app.fetch();
    assert.equal(calls[0].length, 1);
    response.resolve(responseFor(calls[0]));
    const results = await Promise.all([first, queued, duringHttp]);
    assert.equal(results[0], results[1]);
    assert.equal(results[0], results[2]);
    assert.equal(calls.length, 1);
});

test("a cached agenda summary cannot satisfy the map's full direction request", async () => {
    let summaryCalls = 0;
    let detailCalls = 0;
    const route = { status: "OK", routes: [{ legs: summary().legs, overview_polyline: { points: "map detail" } }] };
    const app = harness({ services: {
        dailyDirectionSummaries: async (project, { pairs }) => {
            summaryCalls++;
            return responseFor(pairs);
        },
        dailyDirection: async () => {
            detailCalls++;
            return { data: { events: [1], direction: route, searching: false } };
        },
    } });
    const agenda = await app.fetch();
    assert.equal(agenda.direction, undefined);
    assert.equal(JSON.stringify(agenda).includes("overview_polyline"), false);
    const map = await app.detail();
    assert.deepEqual(map.direction, route);
    assert.equal(await app.detail(), map);
    assert.equal(detailCalls, 1);
    assert.equal(summaryCalls, 1);
    assert.equal(JSON.stringify(app.store.state).includes("overview_polyline"), false);
});

test("full map routes supply agenda summaries without another HTTP request", async () => {
    let detailCalls = 0;
    const legs = [{
        ...summary().legs[0],
        steps: [{ polyline: { points: "private route geometry" } }],
        start_address: "Full address",
        end_location: { lat: 48.85, lng: 2.35 },
    }];
    const app = harness({ services: {
        dailyDirectionSummaries: () => assert.fail("a cached full route already supplies the summary"),
        dailyDirection: async () => {
            detailCalls++;
            return { data: { events: "[1]", direction: JSON.stringify({ status: "OK", routes: [{ legs }] }), searching: false } };
        },
    } });
    await app.detail();
    const result = await app.fetch();
    assert.equal(result.status, "ready");
    assert.deepEqual(result.events, [1]);
    assert.deepEqual(result.legs, summary().legs);
    assert.equal(JSON.stringify(result).includes("private route geometry"), false);
    assert.equal(detailCalls, 1);
});

test("converting route details to summaries keeps only distance and duration", () => {
    const { toDailyDirectionSummary } = loadModules();
    const result = toDailyDirectionSummary({
        user_id: 526,
        direction_at: "2026-10-15",
        events: [1, 2],
        searching: false,
        direction: {
            status: "OK",
            routes: [{
                overview_polyline: { points: "route geometry" },
                legs: [{
                    ...summary().legs[0],
                    steps: [{ instructions: "Turn left" }],
                    start_location: { lat: 48.85, lng: 2.35 },
                }],
            }],
        },
    });
    assert.equal(result.status, "ready");
    assert.deepEqual(result.legs, summary().legs);
    assert.deepEqual(result.events, [1, 2]);
    assert.equal(result.user_id, 526);
    assert.equal(result.direction_at, "2026-10-15");
    assert.equal(result.direction, undefined);
    assert.equal(JSON.stringify(result).includes("route geometry"), false);
    assert.equal(JSON.stringify(result).includes("Turn left"), false);
});

for (const status of ["pending", "empty", "missing"]) {
    test(`summary actions preserve and briefly cache ${status} status without fetching map details`, async () => {
        let calls = 0;
        const app = harness({ services: {
            dailyDirectionSummaries: async (project, { pairs }) => {
                calls++;
                return { data: { data: pairs.map((pair) => summary(pair.user_id, pair.direction_at, {
                    status, events: [], legs: [],
                })) } };
            },
            dailyDirection: () => assert.fail("summary loading requested map details"),
        } });
        const result = await app.fetch();
        assert.equal(result.status, status);
        assert.deepEqual(result.legs, []);
        assert.equal(await app.fetch(), result);
        assert.equal(calls, 1);
    });
}

test("summary actions ignore responses invalidated by a changed appointment", async () => {
    const response = deferred();
    const calls = [];
    const app = harness({ events: [event()], services: {
        dailyDirectionSummaries: async (project, { pairs }) => {
            calls.push(pairs);
            return calls.length === 1 ? response.promise : responseFor(pairs);
        },
    } });
    const stale = app.fetch();
    await waitFor(() => calls.length === 1);
    const moved = event(1, { location: "Lyon" });
    app.store.commit(app.actions.UPDATE_EVENT, moved);
    const replacement = app.fetch(526, "2026-10-15", [moved]);
    response.resolve(responseFor(calls[0]));
    assert.equal(await stale, null);
    assert.equal((await replacement).status, "ready");
    assert.equal(calls.length, 2);
});

test("appointment invalidation removes both full routes and agenda summaries", async () => {
    let summaryCalls = 0;
    let detailCalls = 0;
    const app = harness({ events: [event()], services: {
        dailyDirectionSummaries: async (project, { pairs }) => {
            summaryCalls++;
            return responseFor(pairs);
        },
        dailyDirection: async () => {
            detailCalls++;
            return { data: { events: [1], direction: { status: "OK", routes: [{ legs: summary().legs }] }, searching: false } };
        },
    } });
    await app.fetch();
    await app.detail();
    const moved = event(1, { location: "Lyon" });
    app.store.commit(app.actions.UPDATE_EVENT, moved);
    await app.fetch(526, "2026-10-15", [moved]);
    await app.detail(526, "2026-10-15", [moved]);
    assert.equal(summaryCalls, 2);
    assert.equal(detailCalls, 2);
});

for (const [label, mutation, payload] of [
    ["account", "auth/SET_USER", { id: 8 }],
    ["project", "setTestProject", { slug: "beta", events: [] }],
    ["reset", "RESET", undefined],
]) {
    test(`${label} changes clear cached and pending summary results`, async () => {
        const response = deferred();
        const calls = [];
        const app = harness({ services: {
            dailyDirectionSummaries: async (project, { pairs }) => {
                calls.push(pairs);
                return calls.length === 2 ? response.promise : responseFor(pairs);
            },
        } });
        await app.fetch(526);
        const stale = app.fetch(527);
        await waitFor(() => calls.length === 2);
        app.store.commit(mutation, payload);
        response.resolve(responseFor(calls[1]));
        assert.equal(await stale, null);
        assert.equal((await app.fetch(526)).status, "ready");
        assert.equal(calls.length, 3);
    });
}
