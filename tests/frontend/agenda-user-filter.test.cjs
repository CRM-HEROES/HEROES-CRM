const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");
const { createStore } = require("vuex");
const { createRenderer, nextTick } = require("vue");

const sourceRoot = path.resolve(__dirname, "../../resources/js");
const compiled = new Map();
const dates = { startedAt: "2026-10-15", endedAt: "2026-10-15" };
const event = (id, user, users = []) => ({
    id, user_id: user, users: users.map((id) => ({ id })),
    started_at: "2026-10-15 09:00:00", ended_at: "2026-10-15 10:00:00",
});
const ids = (events) => events.map((item) => item.id);
const tick = () => new Promise((resolve) => setImmediate(resolve));
function deferred() {
    let resolve;
    const promise = new Promise((done) => { resolve = done; });
    return { promise, resolve };
}

// Exercise the production filters, Vuex mutations/getters and range cache. Only
// HTTP and unrelated component imports/settings persistence are replaced.
function harness({ selected = [519, 606], projectService = {}, globalService = {} } = {}) {
    const loaded = new Map();
    let store;
    const settings = [];
    const storeFacade = {
        commit: (...args) => store.commit(...args),
        dispatch(type, payload) {
            if (type === "updateProjectUserSetting") {
                settings.push(payload);
                return Promise.resolve();
            }
            return store.dispatch(type, payload);
        },
    };
    function load(file) {
        if (loaded.has(file)) return loaded.get(file).exports;
        if (!compiled.has(file)) {
            let source = readFileSync(path.join(sourceRoot, file), "utf8");
            if (file.endsWith(".vue")) source = source.match(/<script>([\s\S]*?)<\/script>/)[1];
            compiled.set(file, transformSync(source, {
                filename: file, babelrc: false, configFile: false,
                plugins: ["@babel/plugin-transform-modules-commonjs"],
            }).code);
        }
        const module = { exports: {} };
        loaded.set(file, module);
        new Function("require", "module", "exports", compiled.get(file))((name) => {
            if (name === "vuex") return require("vuex");
            if (name === "@/store") return storeFacade;
            if (name === "@/apis/project/event") return { get: async () => ({ data: [] }), ...projectService };
            if (name === "@/apis/event") return { get: async () => ({ data: [] }), ...globalService };
            if (name === "@/apis/project/user/setting" || name === "@/apis/common") return {};
            if (name.endsWith(".vue")) return {};
            if (name.startsWith("@/")) return load(`${name.slice(2)}.js`);
            throw new Error(`Unexpected dependency ${name}`);
        }, module, module.exports);
        return module.exports;
    }
    const projectStore = load("store/project/event.js").default;
    const eventStore = load("store/event.js").default;
    const actions = load("actions/project/event.js");
    const globals = load("actions/event.js");
    store = createStore({
        modules: {
            auth: { state: { authenticated: true, user: { id: 7 } } },
            project: {
                state: { project: {
                    id: 1, slug: "alpha", events: [], eventsFields: "calendar,user,users",
                    eventsParams: { ...dates, ...(selected === undefined ? {} : { withUsers: selected }) },
                    agendaOtherProjects: true,
                } },
                actions: projectStore.actions, getters: { ...projectStore.getters, project: (state) => state.project },
                mutations: projectStore.mutations,
            },
            event: { ...eventStore, state: {
                globalEvents: [], globalEventsParams: { ...dates, withoutProjects: [1] },
            } },
        },
        plugins: [load("store/agenda-event-range-cache.js").default],
    });
    function context(file, overrides = {}) {
        const component = load(file).default;
        const vm = { $store: store, $emit() {}, ...overrides };
        for (const [key, value] of Object.entries(component.methods || {})) vm[key] = value.bind(vm);
        for (const [key, value] of Object.entries(component.computed || {})) {
            if (Object.hasOwn(vm, key)) continue;
            const get = typeof value === "function" ? value : value.get;
            const set = typeof value === "object" && value.set ? value.set.bind(vm) : undefined;
            Object.defineProperty(vm, key, { get: get.bind(vm), set });
        }
        return vm;
    }
    const setUsers = (withUsers) => store.commit(actions.ADD_EVENT_PARAMS, { key: "withUsers", value: withUsers });
    return { store, actions, globals, context, settings, setUsers, load };
}

function mountAgenda(h, t, mode = "week") {
    const previousWindow = Object.getOwnPropertyDescriptor(global, "window");
    const previousDevice = Object.getOwnPropertyDescriptor(global, "deviceType");
    Object.defineProperty(global, "window", { configurable: true, value: {
        location: { href: `https://crm.example.test/project/alpha/event?display-mode=${mode}` },
    } });
    Object.defineProperty(global, "deviceType", { configurable: true, value: () => "desktop" });
    const renderer = createRenderer({
        insert() {}, remove() {}, createElement: () => ({}), createText: () => ({}),
        createComment: () => ({}), parentNode: () => null, nextSibling: () => null,
        setText() {}, setElementText() {}, patchProp() {},
    });
    const app = renderer.createApp({ ...h.load("components/event/Agenda.vue").default, render: () => null });
    app.use(h.store);
    app.mount({});
    t.after(() => {
        app.unmount();
        if (previousWindow) Object.defineProperty(global, "window", previousWindow);
        else delete global.window;
        if (previousDevice) Object.defineProperty(global, "deviceType", previousDevice);
        else delete global.deviceType;
    });
}

test("unchecking All selects nobody and does not issue an event request", async () => {
    let calls = 0;
    const h = harness({ projectService: { get: async () => { calls++; return { data: [] }; } } });
    h.store.commit(h.actions.SET_EVENTS, [event(1, 519), event(2, 606)]);
    const users = h.context("components/event/filters/Users.vue", { filteredUsers: [{ id: 519 }, { id: 606 }] });
    users.allUsers = false;
    assert.deepEqual(h.store.state.project.project.eventsParams.withUsers, []);
    assert.deepEqual(h.store.getters.events, []);
    await tick();
    assert.equal(calls, 0);
    assert.deepEqual(h.store.state.project.project.events, []);
    assert.deepEqual(h.settings.at(-1), { key: "events.agenda.filters.users", value: [] });
});

test("unchecking the last individual user preserves an explicit empty selection", async () => {
    let calls = 0;
    const h = harness({ selected: [519], projectService: { get: async () => { calls++; return { data: [] }; } } });
    h.store.commit(h.actions.SET_EVENTS, [event(1, 519)]);
    const row = h.context("components/event/filters/UserRow.vue", { user: { id: 519 } });
    assert.equal(row.isChecked, true);
    row.change({ target: { checked: false } });
    assert.deepEqual(h.store.state.project.project.eventsParams.withUsers, []);
    assert.equal(row.isChecked, false);
    assert.deepEqual(h.store.getters.events, []);
    await tick();
    assert.equal(calls, 0);
    assert.deepEqual(h.settings.at(-1), { key: "events.agenda.filters.users", value: [] });
});

test("deselecting a user immediately hides their events while retaining selected participants", () => {
    const h = harness();
    const events = [event(1, 519), event(2, 606), event(3, 519, ["606"]), event(4, 700, [519])];
    h.store.commit(h.actions.SET_EVENTS, events);
    h.setUsers([606]);
    assert.deepEqual(ids(h.store.getters.events), [2, 3]);
    assert.deepEqual(ids(h.store.state.project.project.events), [1, 2, 3, 4]);
});

test("other-project events obey the same current selection immediately", () => {
    const h = harness();
    h.store.commit(h.globals.SET_GLOBAL_EVENTS, [event(1, 519), event(2, "606"), event(3, 519, [606])]);
    h.setUsers([606]);
    assert.deepEqual(ids(h.store.getters.globalEvents), [2, 3]);
    h.setUsers([]);
    assert.deepEqual(h.store.getters.globalEvents, []);
});

test("an absent user filter retains the existing unrestricted event-list behavior", () => {
    const h = harness();
    h.store.commit(h.actions.SET_EVENTS, [event(1, 519), event(2, 606)]);
    h.store.commit(h.actions.REMOVE_EVENT_PARAMS, { key: "withUsers" });
    assert.equal(Object.hasOwn(h.store.state.project.project.eventsParams, "withUsers"), false);
    assert.deepEqual(ids(h.store.getters.events), [1, 2]);
});

test("global requests use the current user selection rather than stale global parameters", async () => {
    const requests = [];
    const h = harness({ selected: [606], globalService: { get: async ({ params }) => {
        requests.push(JSON.parse(params.filters));
        return { data: [] };
    } } });
    h.store.commit(h.globals.ADD_GLOBAL_EVENT_PARAMS, { key: "withUsers", value: [519] });
    await h.store.dispatch(h.globals.FETCH_GLOBAL_EVENTS);
    assert.equal(requests.length, 1);
    assert.deepEqual(requests[0].withUsers, [606]);
    assert.deepEqual(requests[0].withoutProjects, [1]);
    h.setUsers([]);
    await h.store.dispatch(h.globals.FETCH_GLOBAL_EVENTS);
    assert.equal(requests.length, 1);
    assert.deepEqual(h.store.state.event.globalEvents, []);
});

test("an old project response cannot restore events after all users are unchecked", async () => {
    const response = deferred();
    const h = harness({ projectService: { get: () => response.promise } });
    const loading = h.store.dispatch(h.actions.FETCH_EVENTS);
    await tick();
    h.setUsers([]);
    const emptied = h.store.dispatch(h.actions.FETCH_EVENTS);
    response.resolve({ data: [event(1, 519)] });
    await Promise.all([loading, emptied]);
    assert.deepEqual(h.store.state.project.project.events, []);
    assert.deepEqual(h.store.getters.events, []);
});

test("an old global response cannot restore another project's deselected events", async () => {
    const response = deferred();
    const h = harness({ globalService: { get: () => response.promise } });
    const loading = h.store.dispatch(h.globals.FETCH_GLOBAL_EVENTS);
    await tick();
    h.setUsers([]);
    const emptied = h.store.dispatch(h.globals.FETCH_GLOBAL_EVENTS);
    response.resolve({ data: [event(1, 519)] });
    await Promise.all([loading, emptied]);
    assert.deepEqual(h.store.state.event.globalEvents, []);
    assert.deepEqual(h.store.getters.globalEvents, []);
});

test("the mounted agenda reloads visible other projects after the selection changes", async (t) => {
    const requests = [];
    const h = harness({ globalService: { get: async ({ params }) => {
        requests.push(JSON.parse(params.filters));
        return { data: [event(1, 606)] };
    } } });
    mountAgenda(h, t);
    h.setUsers([606]);
    await nextTick();
    await tick();
    assert.deepEqual(requests.map((request) => request.withUsers), [[606]]);
    assert.deepEqual(ids(h.store.getters.globalEvents), [1]);
    h.setUsers([]);
    await nextTick();
    await tick();
    assert.equal(requests.length, 1);
    assert.deepEqual(h.store.state.event.globalEvents, []);
});

for (const condition of ["hidden", "planning"]) {
    test(`selection changes do not load other projects when ${condition}`, async (t) => {
        let calls = 0;
        const h = harness({ globalService: { get: async () => { calls++; return { data: [] }; } } });
        if (condition === "hidden") h.store.commit(h.actions.SHOW_AGENDA_OTHER_PROJECTS, false);
        mountAgenda(h, t, condition === "planning" ? "planning" : "week");
        h.setUsers([606]);
        await nextTick();
        await tick();
        assert.equal(calls, 0);
    });
}

test("an explicitly saved empty user selection stays empty on the next agenda visit", () => {
    const h = harness();
    const getters = h.load("store/project/user/setting.js").default.getters;
    const read = (setting) => {
        const state = {};
        const values = { projectUserSettings: [{ key: "events.agenda.filters.users", value: setting }] };
        values.projectUserSetting = getters.projectUserSetting(state, values);
        return getters.projectUserSettingsAgendaUsers(state, values);
    };
    assert.deepEqual(read([]), []);
    assert.deepEqual(read([519]), [519]);
    assert.equal(read(null), null);
});
