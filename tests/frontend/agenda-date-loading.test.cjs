const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const { transformSync } = require("@babel/core");
const { parse: parseSfc } = require("@vue/compiler-sfc");
const { compile, createRenderer, h, nextTick, reactive } = require("vue");

const sourceRoot = path.resolve(__dirname, "../../resources/js");
const compiledSources = new Map();
const dateString = (value) => {
    const date = new Date(value);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
};

// Mount the production options with Vue's renderer so created hooks, computed
// dates and reactive watchers execute together. Only DOM layout and HTTP/store
// effects are replaced; no copy of the view's loading logic lives here.
function mountAgenda(view, options = {}) {
    const state = reactive({
        project: { id: 7, slug: "vision_laser" },
        agendaDate: options.date || "2026-10-15",
        agendaDisplayMode: options.mode || "week",
        agendaOtherProjects: options.otherProjects || false,
        agendaWeekEnd: options.weekend || false,
        agendaFilter: true,
        agendaList: options.list || false,
        projectUserSettingsAgendaMinimumHour: 6,
        projectUserSettingsAgendaMaximumHour: 21,
        projectUserSettingsEventsColorByLabel: null,
        events: [],
        eventsParams: {},
        globalEventsParams: {},
    });
    const dispatches = [];
    const commits = [];
    const childLifecycle = [];
    const getters = new Proxy({}, {
        get(_target, key) {
            if (key === "eventsParamsValue") return (name) => state.eventsParams[name];
            if (key === "eventsParamExists") return (name) => state.eventsParams[name] !== undefined;
            return state[key];
        },
    });
    const store = {
        getters,
        commit(type, value) {
            commits.push({ type, value });
            if (type === "setEventParams") state.eventsParams = { ...value };
            if (type === "setGlobalEventParams") state.globalEventsParams = { ...value };
            if (type === "addEventParams") state.eventsParams[value.key] = value.value;
            if (type === "addGlobalEventParams") state.globalEventsParams[value.key] = value.value;
            if (type === "setAgendaDate") state.agendaDate = value;
            if (type === "setEvents") state.events = [...value];
        },
        dispatch(type, payload) {
            dispatches.push({
                type,
                payload: payload && JSON.parse(JSON.stringify(payload)),
                filters: JSON.parse(JSON.stringify(type === "fetchGlobalEvents" ? state.globalEventsParams : state.eventsParams)),
            });
            return Promise.resolve([]);
        },
    };
    const previousGlobals = new Map();
    const elements = new Map();
    let vm;
    class AgendaDate extends Date {
        constructor(...arguments_) {
            super(...(arguments_.length ? arguments_ : [options.now || "2026-10-21T12:00:00"]));
        }
    }
    function replaceGlobal(name, value) {
        previousGlobals.set(name, Object.getOwnPropertyDescriptor(global, name));
        Object.defineProperty(global, name, { configurable: true, writable: true, value });
    }
    const windowListeners = new Map();
    const width = options.width || 990;
    const frameCallbacks = new Map();
    let frameId = 0;
    replaceGlobal("window", {
        location: { href: `https://crm.example.test/project/vision_laser/event${options.query || ""}` },
        innerWidth: options.mobile ? 390 : 1200,
        innerHeight: 800,
        addEventListener(name, callback) { windowListeners.set(name, callback); },
        removeEventListener(name, callback) { if (windowListeners.get(name) === callback) windowListeners.delete(name); },
        requestAnimationFrame(callback) { frameCallbacks.set(++frameId, callback); return frameId; },
        cancelAnimationFrame(id) { frameCallbacks.delete(id); },
    });
    replaceGlobal("requestAnimationFrame", window.requestAnimationFrame);
    replaceGlobal("cancelAnimationFrame", window.cancelAnimationFrame);
    replaceGlobal("document", {
        getElementById(id) {
            if (!vm || !id.startsWith("hc-agenda-column-")) return null;
            const index = vm.weekDates.findIndex((date) => dateString(date) === id.substring("hc-agenda-column-".length));
            if (index < 0) return null;
            return {
                offsetLeft: index * 330,
                getBoundingClientRect() {
                    const body = elements.get("body");
                    const left = index * 330 - body.scrollLeft;
                    const top = index * 200 - body.scrollTop;
                    return { left, right: left + 330, width: 330, top, bottom: top + 200, height: 200 };
                },
            };
        },
    });
    replaceGlobal("dateToString", (date) => `${dateString(date)} 00:00:00`);
    replaceGlobal("deviceType", () => options.mobile ? "mobile" : "desktop");

    function load(relativePath) {
        if (!compiledSources.has(relativePath)) {
            let source = readFileSync(path.join(sourceRoot, relativePath), "utf8");
            if (relativePath.endsWith(".vue")) source = source.match(/<script>([\s\S]*?)<\/script>/)[1];
            compiledSources.set(relativePath, transformSync(source, {
                filename: relativePath,
                babelrc: false,
                configFile: false,
                plugins: ["@babel/plugin-transform-modules-commonjs"],
            }).code);
        }
        const module = { exports: {} };
        new Function("require", "module", "exports", "Date", compiledSources.get(relativePath))((specifier) => {
            if (specifier === "vuex") return { mapGetters: (names) => Object.fromEntries(names.map((name) => [name, function () { return this.$store.getters[name]; }])) };
            if (specifier === "vue") return require("vue");
            if (specifier === "@/store") return store;
            if (specifier.startsWith("@/actions/")) return load(`${specifier.substring(2)}.js`);
            if (specifier.startsWith("@/utils/")) return load(`${specifier.substring(2)}.js`);
            if (specifier.endsWith(".vue")) return options.parent ? {
                data() { return { sourceProject: store.getters.project.slug }; },
                created() { childLifecycle.push({ event: "created", project: this.sourceProject }); },
                unmounted() { childLifecycle.push({ event: "unmounted", project: this.sourceProject }); },
                render: () => null,
            } : {};
            throw new Error(`Unexpected dependency: ${specifier}`);
        }, module, module.exports, AgendaDate);
        return module.exports;
    }
    function element(tag) {
        const listeners = new Map();
        const node = {
            tag, children: [], parent: null, listeners,
            scrollLeft: 0, scrollTop: 0, clientWidth: width, clientHeight: 600,
            scrollWidth: 3300, scrollHeight: 1600,
            getBoundingClientRect: () => ({ width: tag === "columns" ? 3300 : width, height: tag === "columns" ? 1600 : 600, left: 0, top: 0, right: width, bottom: 600 }),
            addEventListener(name, callback) { listeners.set(name, callback); },
            removeEventListener(name, callback) { if (listeners.get(name) === callback) listeners.delete(name); },
            scrollBy(value) { this.scrollLeft += value.left || 0; this.scrollTop += value.top || 0; },
            querySelectorAll: () => [],
        };
        elements.set(tag, node);
        return node;
    }
    const renderer = createRenderer({
        createElement: element,
        createText: (text) => ({ text }),
        createComment: (text) => ({ text }),
        setText: (node, text) => { node.text = text; },
        setElementText: (node, text) => { node.text = text; },
        parentNode: (node) => node.parent,
        nextSibling(node) { return node.parent?.children[node.parent.children.indexOf(node) + 1] || null; },
        patchProp: (node, key, _previous, value) => { node[key] = value; },
        insert(node, parent, anchor) {
            if (node.parent) node.parent.children = node.parent.children.filter((child) => child !== node);
            node.parent = parent;
            const index = anchor ? parent.children.indexOf(anchor) : -1;
            if (index >= 0) parent.children.splice(index, 0, node);
            else parent.children.push(node);
        },
        remove(node) { if (node.parent) node.parent.children = node.parent.children.filter((child) => child !== node); },
    });
    const componentPath = options.parent
        ? "components/event/Agenda.vue"
        : `components/event/agenda/${view}/${view[0].toUpperCase()}${view.substring(1)}.vue`;
    const component = load(componentPath).default;
    const childFilter = { mounted: () => store.dispatch("fetchEvents"), render: () => null };
    const render = options.parent
        ? compile(parseSfc(readFileSync(path.join(sourceRoot, componentPath), "utf8")).descriptor.template.content)
        : () => h("root", [
        h("body", { ref: "body" }),
        h("columns", { ref: "columns" }),
        ...(options.childFetch ? [h(childFilter)] : []),
    ]);
    const app = renderer.createApp({ ...component, render });
    app.config.globalProperties.$store = store;
    vm = app.mount(element("host"));
    let disposed = false;
    return {
        vm, state, dispatches, commits, elements, windowListeners, frameCallbacks, childLifecycle,
        projectRequests: () => dispatches.filter(({ type }) => type === "fetchEvents"),
        globalRequests: () => dispatches.filter(({ type }) => type === "fetchGlobalEvents"),
        async flush() {
            await nextTick();
            for (const [id, callback] of [...frameCallbacks]) { frameCallbacks.delete(id); callback(); }
            await nextTick();
        },
        unmount() {
            if (disposed) return;
            disposed = true;
            app.unmount();
            for (const [name, descriptor] of previousGlobals) {
                if (descriptor) Object.defineProperty(global, name, descriptor);
                else delete global[name];
            }
        },
    };
}

function rangeOf(request) {
    return request.payload?.range || request.filters;
}
function assertBounded(request, start, end) {
    const range = rangeOf(request);
    assert.match(range.startedAt || "", /^\d{4}-\d{2}-\d{2}$/);
    assert.match(range.endedAt || "", /^\d{4}-\d{2}-\d{2}$/);
    assert.ok(range.startedAt <= range.endedAt, JSON.stringify(range));
    if (start) assert.equal(range.startedAt, start);
    if (end) assert.equal(range.endedAt, end);
}

test("day initializes the selected date before its first request and keeps URL user filters", async (t) => {
    const query = `?filters=${encodeURIComponent(JSON.stringify({ withUsers: [526], startedAt: "2020-01-01", endedAt: "2030-12-31" }))}`;
    const agenda = mountAgenda("day", { query });
    t.after(() => agenda.unmount());
    await agenda.flush();
    assert.equal(agenda.state.agendaDate, "2026-10-15");
    assert.equal(agenda.projectRequests().length, 1);
    assertBounded(agenda.projectRequests()[0], "2026-10-15", "2026-10-15");
    assert.deepEqual(agenda.projectRequests()[0].filters.withUsers, [526]);
    assert.equal(agenda.globalRequests().length, 0);
});

test("changing the selected day fetches only that day's bounds", async (t) => {
    const agenda = mountAgenda("day");
    t.after(() => agenda.unmount());
    await agenda.flush();
    agenda.state.agendaDate = "2026-11-02";
    await agenda.flush();
    assert.equal(agenda.projectRequests().length, 2);
    assertBounded(agenda.projectRequests()[1], "2026-11-02", "2026-11-02");
});

for (const view of ["day", "month"]) {
    test(`${view} keeps date-only selections on the intended local day west of UTC`, async (t) => {
        const previousTimezone = process.env.TZ;
        process.env.TZ = "America/New_York";
        const agenda = mountAgenda(view, { date: "2026-11-01" });
        t.after(() => {
            agenda.unmount();
            if (previousTimezone === undefined) delete process.env.TZ;
            else process.env.TZ = previousTimezone;
        });
        await agenda.flush();
        if (view === "day") {
            assert.equal(dateString(agenda.vm.currentDate), "2026-11-01");
            assertBounded(agenda.projectRequests()[0], "2026-11-01", "2026-11-01");
        } else {
            assert.equal(agenda.vm.date.month, 10);
            assertBounded(agenda.projectRequests()[0], "2026-11-02", "2026-12-04");
        }
        agenda.state.agendaDate = "2026-12-01";
        await agenda.flush();
        assertBounded(agenda.projectRequests().at(-1), view === "day" ? "2026-12-01" : "2026-11-30", view === "day" ? "2026-12-01" : "2027-01-01");
    });
}

for (const weekend of [false, true]) {
    test(`month preserves selected date and bounds the first request to the displayed ${weekend ? "seven" : "five"}-day grid`, async (t) => {
        const agenda = mountAgenda("month", { weekend });
        t.after(() => agenda.unmount());
        await agenda.flush();
        assert.equal(agenda.state.agendaDate, "2026-10-15");
        assert.equal(agenda.projectRequests().length, 1);
        assertBounded(agenda.projectRequests()[0], weekend ? "2026-09-27" : "2026-09-28", weekend ? "2026-10-31" : "2026-10-30");
        assert.equal(agenda.globalRequests().length, 0);
    });
}

test("month change loads the new grid without an intermediate unbounded request", async (t) => {
    const agenda = mountAgenda("month");
    t.after(() => agenda.unmount());
    await agenda.flush();
    agenda.state.agendaDate = "2026-11-18";
    await agenda.flush();
    assert.equal(agenda.projectRequests().length, 2);
    assertBounded(agenda.projectRequests()[1], "2026-11-02", "2026-12-04");
});

test("month navigation on the 31st still selects the immediately adjacent month", async (t) => {
    const agenda = mountAgenda("month", { date: "2026-01-31", now: "2026-01-31T12:00:00" });
    t.after(() => agenda.unmount());
    await agenda.flush();
    agenda.vm.scrollRight();
    await new Promise((resolve) => setTimeout(resolve, 80));
    await agenda.flush();
    assert.equal(agenda.state.agendaDate, "2026-02-01");
    agenda.vm.scrollLeft();
    await new Promise((resolve) => setTimeout(resolve, 80));
    await agenda.flush();
    assert.equal(agenda.state.agendaDate, "2026-01-01");
});

test("recomputing the month grid does not change its first date", async (t) => {
    const agenda = mountAgenda("month");
    t.after(() => agenda.unmount());
    await agenda.flush();
    const computeWeeks = agenda.vm.$options.computed.weeks;
    const first = computeWeeks.call(agenda.vm);
    const second = computeWeeks.call(agenda.vm);
    assert.equal(dateString(first[0][0]), "2026-09-28");
    assert.equal(dateString(second[0][0]), "2026-09-28");
});

for (const view of ["day", "month", "week"]) {
    test(`${view} loads other projects only while their display is enabled`, async (t) => {
        const agenda = mountAgenda(view);
        t.after(() => agenda.unmount());
        await agenda.flush();
        assert.ok(agenda.projectRequests().length > 0);
        assert.ok(agenda.projectRequests().every((request) => { assertBounded(request); return true; }));
        assert.equal(agenda.globalRequests().length, 0);
        const ownCount = agenda.projectRequests().length;
        agenda.state.agendaOtherProjects = true;
        await agenda.flush();
        assert.equal(agenda.globalRequests().length, 1);
        assertBounded(agenda.globalRequests()[0]);
        assert.deepEqual(agenda.globalRequests()[0].filters.withoutProjects, [7]);
        assert.equal(agenda.projectRequests().length, ownCount);
        agenda.state.agendaOtherProjects = false;
        await agenda.flush();
        agenda.state.agendaDate = "2026-11-02";
        await agenda.flush();
        assert.equal(agenda.globalRequests().length, 1);
    });

    test(`${view} bounds both first requests when other projects are already enabled`, async (t) => {
        const agenda = mountAgenda(view, { otherProjects: true });
        t.after(() => agenda.unmount());
        await agenda.flush();
        assert.equal(agenda.projectRequests().length, 1);
        assert.equal(agenda.globalRequests().length, 1);
        assertBounded(agenda.projectRequests()[0]);
        assertBounded(agenda.globalRequests()[0]);
        assert.equal(rangeOf(agenda.projectRequests()[0]).startedAt, rangeOf(agenda.globalRequests()[0]).startedAt);
        assert.equal(rangeOf(agenda.projectRequests()[0]).endedAt, rangeOf(agenda.globalRequests()[0]).endedAt);
    });
}

test("week initially fetches the viewport with a one-day margin and removes listeners when closed", async (t) => {
    const agenda = mountAgenda("week", { width: 990 });
    t.after(() => agenda.unmount());
    await agenda.flush();
    const requests = agenda.projectRequests();
    assert.equal(requests.length, 1);
    const range = rangeOf(requests[0]);
    assertBounded(requests[0]);
    assert.ok(range.startedAt <= "2026-10-15" && range.endedAt >= "2026-10-15");
    let weekdays = 0;
    for (let date = new Date(`${range.startedAt}T12:00:00`); dateString(date) <= range.endedAt; date.setDate(date.getDate() + 1)) {
        if (![0, 6].includes(date.getDay())) weekdays++;
    }
    assert.ok(weekdays <= 6, `three columns plus a partially visible column and margin fetched ${weekdays} days`);
    assert.deepEqual(requests[0].payload.retainRange, {
        startedAt: agenda.vm.windowStartedAt,
        endedAt: agenda.vm.windowEndedAt,
    });
    assert.ok(requests[0].payload.retainRange.startedAt <= range.startedAt);
    assert.ok(requests[0].payload.retainRange.endedAt > range.endedAt);
    const body = agenda.elements.get("body");
    assert.equal(typeof body.listeners.get("scroll"), "function");
    agenda.unmount();
    assert.equal(body.listeners.has("scroll"), false);
    assert.equal(agenda.windowListeners.has("resize"), false);
});

test("week prepares bounded params before a mounted child filter can request events", async (t) => {
    const agenda = mountAgenda("week", { childFetch: true });
    t.after(() => agenda.unmount());
    await agenda.flush();
    assert.equal(agenda.projectRequests().length, 2);
    for (const request of agenda.projectRequests()) {
        assertBounded(request);
        const range = rangeOf(request);
        assert.ok(range.startedAt <= "2026-10-15" && range.endedAt >= "2026-10-15");
    }
});

test("week combines a burst of scroll events into one viewport request", async (t) => {
    const agenda = mountAgenda("week");
    t.after(() => agenda.unmount());
    await agenda.flush();
    const initialCount = agenda.projectRequests().length;
    const body = agenda.elements.get("body");
    body.scrollLeft = 1000;
    for (let index = 0; index < 10; index++) body.listeners.get("scroll")();
    assert.equal(agenda.frameCallbacks.size, 1);
    await agenda.flush();
    assert.equal(agenda.projectRequests().length, initialCount + 1);
    assertBounded(agenda.projectRequests().at(-1), "2026-10-14", "2026-10-21");
});

for (const mobile of [false, true]) {
    test(`${mobile ? "mobile list" : "desktop week"} shifts keep the visible anchor and a bounded date window`, async (t) => {
        const agenda = mountAgenda("week", { mobile, list: mobile });
        t.after(() => agenda.unmount());
        await agenda.flush();
        const body = agenda.elements.get("body");
        const count = agenda.vm.weekDates.length;
        for (let index = 0; index < 6; index++) {
            if (mobile) {
                body.scrollHeight = count * 200;
                body.scrollTop = body.scrollHeight - body.clientHeight;
            } else {
                body.scrollLeft = body.scrollWidth - body.clientWidth;
            }
            const before = agenda.vm.visibleColumns()[0];
            await agenda.vm.addFutureEvents();
            await agenda.flush();
            const after = agenda.vm.visibleColumns()[0];
            assert.equal(dateString(after.date), dateString(before.date));
            assert.equal(mobile ? after.rect.top : after.rect.left, mobile ? before.rect.top : before.rect.left);
            assert.equal(agenda.vm.weekDates.length, count);
            const request = agenda.projectRequests().at(-1);
            assertBounded(request);
            assert.deepEqual(request.payload.retainRange, {
                startedAt: agenda.vm.windowStartedAt,
                endedAt: agenda.vm.windowEndedAt,
            });
        }
        assert.equal(agenda.globalRequests().length, 0);
    });
}

test("closing the week cancels a queued scroll request", async (t) => {
    const agenda = mountAgenda("week");
    t.after(() => agenda.unmount());
    await agenda.flush();
    const initialCount = agenda.projectRequests().length;
    agenda.elements.get("body").listeners.get("scroll")();
    assert.equal(agenda.frameCallbacks.size, 1);
    agenda.unmount();
    assert.equal(agenda.frameCallbacks.size, 0);
    assert.equal(agenda.projectRequests().length, initialCount);
});

for (const mobile of [false, true]) {
    test(`${mobile ? "mobile" : "desktop"} week navigation follows the displayed date after scrolling far ahead`, async (t) => {
        const agenda = mountAgenda("week", { mobile, list: mobile });
        t.after(() => agenda.unmount());
        await agenda.flush();
        const body = agenda.elements.get("body");
        for (let index = 0; index < 6; index++) {
            if (mobile) {
                body.scrollHeight = agenda.vm.weekDates.length * 200;
                body.scrollTop = body.scrollHeight - body.clientHeight;
            } else {
                body.scrollLeft = body.scrollWidth - body.clientWidth;
            }
            await agenda.vm.addFutureEvents();
        }
        const center = mobile ? body.clientHeight / 2 : body.clientWidth / 2;
        const visible = agenda.vm.visibleColumns();
        const anchor = mobile ? visible[0] : visible.find(({ rect }) => rect.left <= center && rect.right > center);
        const displayed = new Date(anchor.date);
        assert.ok(dateString(displayed) > "2026-11-01");
        const next = new Date(displayed);
        next.setDate(next.getDate() + 7);
        agenda.vm.nextDate();
        await agenda.flush();
        assert.equal(agenda.state.agendaDate, dateString(next));
        agenda.vm.prevDate();
        await agenda.flush();
        assert.equal(agenda.state.agendaDate, dateString(displayed));
    });
}

for (const mode of ["day", "week", "month", "planning"]) {
    test(`${mode} agenda remounts for another project and retains its instance when the same project refreshes`, async (t) => {
        const agenda = mountAgenda("agenda", { parent: true, mode });
        t.after(() => agenda.unmount());
        await agenda.flush();
        assert.deepEqual(agenda.childLifecycle, [{ event: "created", project: "vision_laser" }]);
        agenda.state.project = { ...agenda.state.project, name: "Updated project name" };
        await agenda.flush();
        assert.equal(agenda.childLifecycle.length, 1);
        agenda.state.project = { id: 8, slug: "another_project" };
        await agenda.flush();
        assert.deepEqual(agenda.childLifecycle.filter(({ event }) => event === "created"), [
            { event: "created", project: "vision_laser" },
            { event: "created", project: "another_project" },
        ]);
        assert.deepEqual(agenda.childLifecycle.filter(({ event }) => event === "unmounted"), [
            { event: "unmounted", project: "vision_laser" },
        ]);
    });
}

test("agenda waits for a project while project selection is being replaced", async (t) => {
    const agenda = mountAgenda("agenda", { parent: true });
    t.after(() => agenda.unmount());
    await agenda.flush();
    agenda.state.project = null;
    await agenda.flush();
    assert.equal(agenda.childLifecycle.filter(({ event }) => event === "created").length, 1);
    assert.equal(agenda.childLifecycle.filter(({ event }) => event === "unmounted").length, 1);
    agenda.state.project = {};
    await agenda.flush();
    assert.equal(agenda.childLifecycle.filter(({ event }) => event === "created").length, 1);
    agenda.state.project = { id: 9, slug: "loaded_project" };
    await agenda.flush();
    assert.deepEqual(agenda.childLifecycle.filter(({ event }) => event === "created").at(-1), {
        event: "created", project: "loaded_project",
    });
});
