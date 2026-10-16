const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");

const sourceRoot = path.resolve(__dirname, "../../resources/js");
const compiledSources = new Map();

// Exercise the production stores while isolating their HTTP services and the
// unrelated child stores imported by the aggregate project store.
function loadStores(menuService = {}) {
    const modules = new Map();
    const emptyStore = { state: {}, actions: {}, mutations: {}, getters: {} };

    function load(relativePath) {
        if (modules.has(relativePath)) {
            return modules.get(relativePath).exports;
        }

        if (!compiledSources.has(relativePath)) {
            compiledSources.set(
                relativePath,
                transformSync(
                    readFileSync(path.join(sourceRoot, relativePath), "utf8"),
                    {
                        filename: relativePath,
                        babelrc: false,
                        configFile: false,
                        plugins: ["@babel/plugin-transform-modules-commonjs"],
                    }
                ).code
            );
        }

        const module = { exports: {} };
        modules.set(relativePath, module);

        function requireDependency(specifier) {
            if (specifier === "@/apis/project/menu") return menuService;
            if (specifier === "@/apis/project") return {};
            if (specifier === "@/actions/project") {
                return load("actions/project.js");
            }
            if (specifier === "@/actions/project/menu") {
                return load("actions/project/menu.js");
            }
            if (specifier === "./project/menu") {
                return load("store/project/menu.js");
            }
            if (specifier.startsWith("./project/")) return emptyStore;
            throw new Error(`Unexpected dependency: ${specifier}`);
        }

        new Function("require", "module", "exports", compiledSources.get(relativePath))(
            requireDependency,
            module,
            module.exports
        );
        return module.exports;
    }

    const store = load("store/project.js");
    const projectActions = load("actions/project.js");
    const menuActions = load("actions/project/menu.js");
    const state = { projects: [], project: {} };
    const context = {
        state,
        commit(type, value) {
            assert.equal(typeof store.mutations[type], "function");
            store.mutations[type](state, value);
        },
    };

    return {
        state,
        setProject(project) {
            context.commit(projectActions.SET_PROJECT, project);
        },
        fetchMenus(params) {
            return store.actions[menuActions.FETCH_MENUS](context, params);
        },
    };
}

function menu(id, overrides = {}) {
    return {
        id,
        name: `Menu ${id}`,
        filters: { processed: 0 },
        color: "#000000",
        bgcolor: "#FFFFFF",
        ...overrides,
    };
}

function deferred() {
    let resolve;
    const promise = new Promise((done) => {
        resolve = done;
    });
    return { promise, resolve };
}

for (const responseOrder of ["project first", "menus first"]) {
    test(`refresh retains zero and nonzero counts when ${responseOrder}`, async () => {
        const response = deferred();
        const countedMenus = [
            menu(1, { count: 0, for: "prospect", order: 1 }),
            menu(2, { count: 50, for: "order", order: 2 }),
        ];
        const harness = loadStores({ get: () => response.promise });
        harness.setProject({ slug: "alpha" });
        const pendingMenus = harness.fetchMenus();
        const projectResponse = {
            slug: "alpha",
            name: "Updated project",
            menus: [
                menu(1, { name: "Updated menu", users: [{ pivot: { default: true } }] }),
                menu(2),
            ],
        };

        if (responseOrder === "project first") {
            harness.setProject(projectResponse);
            response.resolve({ data: countedMenus });
            await pendingMenus;
        } else {
            response.resolve({ data: countedMenus });
            await pendingMenus;
            harness.setProject(projectResponse);
            assert.equal(harness.state.project.menus[0].name, "Updated menu");
            assert.equal(harness.state.project.menus[0].users[0].pivot.default, true);
        }

        assert.deepEqual(harness.state.project.menus.map(({ count }) => count), [0, 50]);
        assert.deepEqual(harness.state.project.menus.map(({ order }) => order), [1, 2]);
        assert.deepEqual(harness.state.project.menus.map((item) => item.for), ["prospect", "order"]);
        assert.equal(harness.state.project.name, "Updated project");
    });
}

test("project details without menus retain the existing counters", () => {
    const harness = loadStores();
    harness.setProject({ slug: "alpha", menus: [menu(1, { count: 0 })] });
    harness.setProject({ slug: "alpha", name: "Renamed project" });

    assert.equal(harness.state.project.menus[0].count, 0);
    assert.equal(harness.state.project.name, "Renamed project");
});

test("counts come from the active project when its cached summary has no menus", () => {
    const harness = loadStores();
    harness.setProject({ slug: "alpha" });
    harness.state.project = {
        slug: "alpha",
        menus: [menu(1, { count: 17 })],
    };
    harness.setProject({ slug: "alpha", menus: [menu(1)] });

    assert.equal(harness.state.project.menus[0].count, 17);
});

test("null counts retain a known count for unchanged filters", () => {
    const harness = loadStores();
    harness.setProject({ slug: "alpha", menus: [menu(1, { count: 17 })] });
    harness.setProject({ slug: "alpha", menus: [menu(1, { count: null })] });

    assert.equal(harness.state.project.menus[0].count, 17);
});

test("changed filters discard a prior count until the menu request returns", () => {
    const harness = loadStores();
    harness.setProject({ slug: "alpha", menus: [menu(1, { count: 17 })] });
    harness.setProject({
        slug: "alpha",
        menus: [menu(1, { filters: { processed: 1 } })],
    });

    assert.equal(harness.state.project.menus[0].count, undefined);
    assert.deepEqual(harness.state.project.menus[0].filters, { processed: 1 });
});

test("explicit counts replace prior counts, including zero and changed filters", () => {
    const harness = loadStores();
    harness.setProject({
        slug: "alpha",
        menus: [menu(1, { count: 17 }), menu(2, { count: 0 })],
    });
    harness.setProject({
        slug: "alpha",
        menus: [
            menu(1, { count: 0, filters: { processed: 1 } }),
            menu(2, { count: 8 }),
        ],
    });

    assert.deepEqual(harness.state.project.menus.map(({ count }) => count), [0, 8]);
});

test("the incoming menu list removes deleted menus and includes new menus", () => {
    const harness = loadStores();
    harness.setProject({
        slug: "alpha",
        menus: [menu(1, { count: 17 }), menu(2, { count: 20 })],
    });
    harness.setProject({ slug: "alpha", menus: [menu(2), menu(3)] });

    assert.deepEqual(harness.state.project.menus.map(({ id }) => id), [2, 3]);
    assert.equal(harness.state.project.menus[0].count, 20);
    assert.equal(harness.state.project.menus[1].count, undefined);

    harness.setProject({ slug: "alpha", menus: [] });
    assert.deepEqual(harness.state.project.menus, []);
});

test("matching menu IDs in different projects do not share counters", () => {
    const harness = loadStores();
    harness.setProject({ slug: "alpha", menus: [menu(1, { count: 17 })] });
    harness.setProject({ slug: "beta", menus: [menu(1)] });

    assert.equal(harness.state.project.slug, "beta");
    assert.equal(harness.state.project.menus[0].count, undefined);
    assert.equal(harness.state.projects.find(({ slug }) => slug === "alpha").menus[0].count, 17);
});

test("delayed menu responses cannot replace another project's menus", async () => {
    const response = deferred();
    const calls = [];
    const harness = loadStores({
        get(slug, params) {
            calls.push({ slug, params });
            return response.promise;
        },
    });
    harness.setProject({ slug: "alpha", menus: [menu(1)] });
    const params = { for: "prospect" };
    const pendingMenus = harness.fetchMenus(params);
    harness.setProject({ slug: "beta", menus: [menu(1, { count: 3 })] });
    const oldMenus = [menu(1, { count: 99 })];
    response.resolve({ data: oldMenus });

    assert.equal(await pendingMenus, oldMenus);
    assert.deepEqual(calls, [{ slug: "alpha", params }]);
    assert.equal(harness.state.project.slug, "beta");
    assert.equal(harness.state.project.menus[0].count, 3);
});

test("menu responses are ignored when the current project has been cleared", async () => {
    const response = deferred();
    const harness = loadStores({ get: () => response.promise });
    harness.setProject({ slug: "alpha" });
    const pendingMenus = harness.fetchMenus();
    harness.setProject(null);
    response.resolve({ data: [menu(1, { count: 99 })] });

    await pendingMenus;
    assert.equal(harness.state.project, null);
});

test("fetching menus without a current project makes no HTTP request", async () => {
    let requests = 0;
    const harness = loadStores({
        get() {
            requests += 1;
            return Promise.resolve({ data: [] });
        },
    });

    assert.deepEqual(await harness.fetchMenus(), []);
    harness.setProject(null);
    assert.deepEqual(await harness.fetchMenus(), []);
    assert.equal(requests, 0);
});
