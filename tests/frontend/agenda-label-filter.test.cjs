const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");
const { createStore } = require("vuex");

const sourceRoot = path.resolve(__dirname, "../../resources/js");
const compiled = new Map();
const dates = { startedAt: "2026-10-15", endedAt: "2026-10-15" };
const labels = {
    first: { id: 11, category_id: 7 },
    second: { id: 12, category_id: 7 },
    otherCategory: { id: 21, category_id: 8 },
};
const event = (id, categories) => ({
    id, user_id: 519, users: [],
    started_at: "2026-10-15 09:00:00", ended_at: "2026-10-15 10:00:00",
    prospect: categories ? { id: id * 100, categories } : null,
});
const events = [
    event(1, { 7: [11] }),
    event(2, { 7: [12], 8: [21] }),
    event(3),
    event(4, { 7: [11], 8: [21] }),
];
const ids = (items) => items.map((item) => item.id);

// Run the production LabelRow and Users computed model with the real Vuex
// action and agenda range cache. Only HTTP and unrelated view dependencies are
// replaced. An empty withProspects object still restricts events to prospects,
// matching the API's whereHas behavior.
function harness({ prospectFilters, load } = {}) {
    const loaded = new Map();
    const pending = [];
    const requests = [];
    let store;
    const storeFacade = {
        commit: (...args) => store.commit(...args),
        dispatch(...args) {
            const promise = store.dispatch(...args);
            pending.push(promise);
            return promise;
        },
    };
    const projectService = {
        async get(slug, { params }) {
            const filters = JSON.parse(params.filters);
            requests.push({ slug, filters });
            if (load) return { data: await load(filters) };
            const criteria = Object.entries(filters.withProspects || {})
                .filter(([key]) => key.startsWith("withCategory_"));
            const data = events.filter((item) => {
                if (filters.withProspects && !item.prospect) return false;
                return criteria.every(([key, wanted]) => {
                    const values = item.prospect?.categories[key.slice("withCategory_".length)] || [];
                    return wanted.some((id) => values.some((value) => String(value) === String(id)));
                });
            });
            return { data };
        },
    };
    function loadSource(file) {
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
            if (name === "@/apis/project/event") return projectService;
            if (name === "@/apis/project/user/setting" || name === "@/apis/common") return {};
            if (name.endsWith(".vue")) return {};
            if (name.startsWith("@/")) return loadSource(`${name.slice(2)}.js`);
            throw new Error(`Unexpected dependency ${name}`);
        }, module, module.exports);
        return module.exports;
    }
    const projectStore = loadSource("store/project/event.js").default;
    const actions = loadSource("actions/project/event.js");
    store = createStore({
        modules: {
            auth: { state: { authenticated: true, user: { id: 7 } } },
            project: {
                state: { project: {
                    id: 1, slug: "alpha", events: [], eventsFields: "prospect,user,users",
                    eventsParams: {
                        ...dates, withUsers: [519],
                        ...(prospectFilters ? { withProspects: prospectFilters } : {}),
                    },
                } },
                actions: projectStore.actions, getters: projectStore.getters,
                mutations: projectStore.mutations,
            },
        },
    });
    function context(file, overrides = {}) {
        const component = loadSource(file).default;
        const vm = { $store: store, $emit() {}, ...overrides };
        for (const [key, method] of Object.entries(component.methods || {})) vm[key] = method.bind(vm);
        for (const [key, computed] of Object.entries(component.computed || {})) {
            if (Object.hasOwn(vm, key)) continue;
            const get = typeof computed === "function" ? computed : computed.get;
            const set = typeof computed === "object" && computed.set ? computed.set.bind(vm) : undefined;
            Object.defineProperty(vm, key, { get: get.bind(vm), set });
        }
        return vm;
    }
    const users = context("components/event/filters/Users.vue");
    function row(label) {
        const vm = context("components/event/filters/LabelRow.vue", {
            label,
            $emit(name, value) {
                assert.equal(name, "update:modelValue");
                users.filterLabels = value;
            },
        });
        Object.defineProperty(vm, "modelValue", { get: () => users.filterLabels });
        return vm;
    }
    async function settled() {
        while (pending.length) await Promise.all(pending.splice(0));
    }
    return { store, actions, users, row, requests, settled, context };
}

test("checking then unchecking the last label reloads and restores all events including events without a prospect", async () => {
    const h = harness();
    await h.store.dispatch(h.actions.FETCH_EVENTS);
    assert.deepEqual(ids(h.store.getters.events), [1, 2, 3, 4]);
    const row = h.row(labels.first);
    row.change({ target: { checked: true } });
    await h.settled();
    assert.equal(row.isChecked, true);
    assert.deepEqual(h.users.filterLabels, { 7: [11] });
    assert.deepEqual(ids(h.store.getters.events), [1, 4]);
    row.change({ target: { checked: false } });
    await h.settled();
    assert.equal(row.isChecked, false);
    assert.deepEqual(h.users.filterLabels, {});
    assert.equal(Object.hasOwn(h.store.state.project.project.eventsParams, "withProspects"), false);
    assert.deepEqual(ids(h.store.getters.events), [1, 2, 3, 4]);
    assert.equal(h.requests.length, 3);
    assert.deepEqual(h.requests[1].filters.withProspects, { withCategory_7: [11] });
    assert.equal(Object.hasOwn(h.requests[2].filters, "withProspects"), false);
});

test("unchecking one label retains the other selected label in its category", async () => {
    const h = harness();
    const first = h.row(labels.first);
    const second = h.row(labels.second);
    first.change({ target: { checked: true } });
    await h.settled();
    second.change({ target: { checked: true } });
    await h.settled();
    assert.deepEqual(ids(h.store.getters.events), [1, 2, 4]);
    first.change({ target: { checked: false } });
    await h.settled();
    assert.equal(first.isChecked, false);
    assert.equal(second.isChecked, true);
    assert.deepEqual(h.requests.at(-1).filters.withProspects, { withCategory_7: [12] });
    assert.deepEqual(ids(h.store.getters.events), [2]);
});

test("clearing a category preserves the selected labels in other categories", async () => {
    const h = harness({ prospectFilters: { withCategory_7: [11], withCategory_8: [21] } });
    const first = h.row(labels.first);
    const other = h.row(labels.otherCategory);
    assert.deepEqual(h.users.filterLabels, { 7: [11], 8: [21] });
    first.change({ target: { checked: false } });
    await h.settled();
    assert.equal(first.isChecked, false);
    assert.equal(other.isChecked, true);
    assert.deepEqual(h.requests.at(-1).filters.withProspects, { withCategory_8: [21] });
    assert.deepEqual(ids(h.store.getters.events), [2, 4]);
});

test("clearing labels preserves unrelated prospect criteria", async () => {
    const h = harness({ prospectFilters: {
        withCategory_7: [11], keyword: "contact", withMenus: [3],
    } });
    assert.deepEqual(h.users.filterLabels, { 7: [11] });
    h.row(labels.first).change({ target: { checked: false } });
    await h.settled();
    assert.deepEqual(h.store.state.project.project.eventsParams.withProspects, {
        keyword: "contact", withMenus: [3],
    });
    assert.deepEqual(h.requests.at(-1).filters.withProspects, {
        keyword: "contact", withMenus: [3],
    });
});

test("checking and unchecking does not mutate an existing Vuex category array", async () => {
    const original = Object.freeze([11]);
    const h = harness({ prospectFilters: { withCategory_7: original } });
    h.row(labels.second).change({ target: { checked: true } });
    await h.settled();
    assert.deepEqual(original, [11]);
    assert.deepEqual(h.users.filterLabels, { 7: [11, 12] });
    const selected = h.store.state.project.project.eventsParams.withProspects.withCategory_7;
    Object.freeze(selected);
    h.row(labels.first).change({ target: { checked: false } });
    await h.settled();
    assert.deepEqual(selected, [11, 12]);
    assert.deepEqual(h.users.filterLabels, { 7: [12] });
});

test("a missing category can be unchecked without throwing or retaining an empty prospect filter", async () => {
    const h = harness();
    assert.doesNotThrow(() => h.row(labels.first).change({ target: { checked: false } }));
    await h.settled();
    assert.deepEqual(h.users.filterLabels, {});
    assert.equal(Object.hasOwn(h.requests[0].filters, "withProspects"), false);
    assert.deepEqual(ids(h.store.getters.events), [1, 2, 3, 4]);
});

test("label selection matches numeric IDs stored as strings and never adds duplicates", async () => {
    const h = harness({ prospectFilters: { withCategory_7: ["11", "12"] } });
    const first = h.row(labels.first);
    assert.equal(first.isChecked, true);
    first.change({ target: { checked: true } });
    await h.settled();
    assert.deepEqual(h.users.filterLabels, { 7: ["11", "12"] });
    first.change({ target: { checked: false } });
    await h.settled();
    assert.deepEqual(h.users.filterLabels, { 7: ["12"] });
    assert.equal(first.isChecked, false);
    assert.deepEqual(ids(h.store.getters.events), [2]);
});

test("unchecking a label while its filtered response is pending cannot restore that filtered list", async () => {
    let resolveFiltered;
    const filtered = new Promise((resolve) => { resolveFiltered = resolve; });
    const h = harness({ load: (filters) => filters.withProspects ? filtered : events });
    const row = h.row(labels.first);
    row.change({ target: { checked: true } });
    await new Promise((resolve) => setImmediate(resolve));
    row.change({ target: { checked: false } });
    await new Promise((resolve) => setImmediate(resolve));
    assert.deepEqual(ids(h.store.getters.events), [1, 2, 3, 4]);
    resolveFiltered([events[0], events[3]]);
    await h.settled();
    assert.deepEqual(ids(h.store.getters.events), [1, 2, 3, 4]);
    assert.equal(row.isChecked, false);
    assert.equal(h.requests.length, 2);
});
