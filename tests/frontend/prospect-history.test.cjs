const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');
const { compile, createRenderer, h, nextTick, reactive } = require('vue');

const sourceRoot = path.resolve(__dirname, '../../resources/js');
const tableRoot = 'components/prospect/table/';
const compiled = new Map();

function load(filename, dependencies = {}) {
    if (!compiled.has(filename)) {
        let source = fs.readFileSync(path.join(sourceRoot, filename), 'utf8');
        if (filename.endsWith('.vue')) source = parse(source).descriptor.script.content;
        compiled.set(filename, transformSync(source, {
            babelrc: false, configFile: false,
            plugins: ['@babel/plugin-transform-modules-commonjs'],
        }).code);
    }
    const module = { exports: {} };
    new Function('require', 'module', 'exports', compiled.get(filename))(specifier => {
        if (specifier in dependencies) return dependencies[specifier];
        if (specifier === 'dayjs') return require('dayjs');
        if (specifier === 'vuex') return {
            mapGetters: names => Object.fromEntries((Array.isArray(names)
                ? names.map(name => [name, name]) : Object.entries(names)).map(([local, name]) => [local, function () {
                    return this.$testGetters[name];
                }])),
        };
        if (specifier.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        if (specifier === '@/composables/mouse') return { useDrag: () => ({ dragging: false }) };
        if (specifier.endsWith('/HistoryCell.vue')) return component('cell/HistoryCell.vue', dependencies);
        if (specifier.endsWith('/history/Items.vue')) return {
            props: ['items'], render() { return h('history-items', { items: this.items }); },
        };
        if (specifier.endsWith('.vue')) return { render: () => h('unrelated-component') };
        return {};
    }, module, module.exports);
    return module.exports;
}

function component(relative, dependencies = {}) {
    const filename = tableRoot + relative;
    const descriptor = parse(fs.readFileSync(path.join(sourceRoot, filename), 'utf8')).descriptor;
    return { ...load(filename, dependencies).default, render: compile(descriptor.template.content) };
}

// Execute the production template and computed properties together. The host
// records rendered nodes and handlers; layout, browser DOM and HTTP are absent.
function mount(t, options, props = {}, getters = {}) {
    function element(tag) {
        return {
            tag, children: [], parent: null,
            contains(candidate) {
                for (let node = candidate; node; node = node.parent) if (node === this) return true;
                return false;
            },
            blur() { this.blurred = true; },
        };
    }
    const renderer = createRenderer({
        createElement: element,
        createText: text => ({ text }), createComment: text => ({ comment: text }),
        setText: (node, text) => { node.text = text; },
        setElementText: (node, text) => { node.text = text; node.children = []; },
        patchProp: (node, key, _previous, value) => { node[key] = value; },
        parentNode: node => node.parent,
        nextSibling: node => node.parent?.children[node.parent.children.indexOf(node) + 1] || null,
        insert(node, parent, anchor) {
            if (node.parent) node.parent.children = node.parent.children.filter(child => child !== node);
            node.parent = parent;
            const index = anchor ? parent.children.indexOf(anchor) : -1;
            if (index >= 0) parent.children.splice(index, 0, node);
            else parent.children.push(node);
        },
        remove(node) {
            if (node.parent) node.parent.children = node.parent.children.filter(child => child !== node);
        },
    });
    const host = element('host');
    const input = reactive({ ...props });
    const app = renderer.createApp({ render: () => h(options, { ...input, ref: 'target' }) });
    const testGetters = reactive({
        prospectsParamExists: () => false, categories: [], project: { slug: 'test' }, ...getters,
    });
    app.config.globalProperties.$testGetters = testGetters;
    app.config.globalProperties.$t = key => key;
    // Existing table cells declare their column object as an Array prop.
    app.config.warnHandler = message => {
        if (!message.includes('Invalid prop: type check failed for prop "column"')) throw new Error(message);
    };
    app.directive('tooltip', {});
    const instance = app.mount(host);
    t.after(() => app.unmount());
    return {
        vm: instance.$refs.target, root: host.children[0], getters: testGetters,
        async setProps(values) { Object.assign(input, values); await nextTick(); },
    };
}

function nodes(root, predicate) {
    return [root, ...(root.children || []).flatMap(child => nodes(child, predicate))].filter(predicate);
}
const details = root => nodes(root, node => node.class === 'hc-history-cell-details');
const items = root => nodes(root, node => node.tag === 'history-items');

const badge = root => nodes(root, node => node.class === 'hc-history-cell-count');
const row = (id, created_at = '2026-10-22T09:00:00Z') => ({ id, created_at });
const summary = (latest = row(8), count = 3, id = 7) => ({
    id, history_latest: latest, history_count: count,
});
const ids = root => items(root).flatMap(node => node.items.map(item => item.id));

function deferred() {
    let resolve, reject;
    const promise = new Promise((done, fail) => { resolve = done; reject = fail; });
    return { promise, resolve, reject };
}

async function settle() {
    await new Promise(setImmediate);
    await nextTick();
}

function historyCell(t, prospect = summary(), response = []) {
    const requests = [];
    const service = {
        history: async (...args) => {
            requests.push(args);
            const data = typeof response === 'function' ? await response(...args) : await response;
            return { data };
        },
    };
    return {
        ...mount(t, component('cell/HistoryCell.vue', { '@/apis/project/prospect': service }), { prospect }),
        requests,
    };
}

async function hover(cell) {
    cell.root.onMouseenter();
    await nextTick();
}

async function leave(cell) {
    cell.root.onMouseleave();
    await nextTick();
}

test('mount renders only the latest summary and its real count without fetching full history', async t => {
    const latest = Object.freeze(row(8, '2026-10-22T11:30:00+03:00'));
    const prospect = Object.freeze(summary(latest, 27));
    const cell = historyCell(t, prospect);
    await settle();
    assert.deepEqual(cell.requests, []);
    assert.deepEqual(ids(cell.root), [8]);
    assert.equal(badge(cell.root)[0].textContent, '27');
    assert.equal(cell.vm.latestDate, '22/10');
    assert.equal(cell.root.tabindex, 0);
    assert.equal(details(cell.root).length, 0);
    assert.deepEqual(prospect, summary(latest, 27));
});

test('missing and empty summaries stay empty and unfocusable without a history request', async t => {
    for (const prospect of [{ id: 7 }, summary(null, 0), { id: 7, history_count: 0 }]) {
        const cell = historyCell(t, prospect);
        await settle();
        assert.equal(cell.root.tabindex, null);
        assert.equal(items(cell.root).length, 0);
        assert.equal(badge(cell.root).length, 0);
        assert.deepEqual(cell.requests, []);
        await hover(cell);
        assert.deepEqual(cell.requests, []);
    }
});

test('hover fetches full history once and sorts actual dates without mutating the response', async t => {
    const full = Object.freeze([
        Object.freeze(row(1, '2026-10-22T11:30:00+03:00')),
        Object.freeze(row(2, '2026-10-22T09:00:00Z')),
        Object.freeze(row(3, '2026-10-21T20:00:00Z')),
        Object.freeze(row(4, null)),
    ]);
    const cell = historyCell(t, summary(full[1], 4), full);
    await hover(cell);
    await settle();
    assert.deepEqual(cell.requests, [['test', 7]]);
    assert.deepEqual(ids(details(cell.root)[0]), [2, 1, 3]);
    assert.deepEqual(full.map(item => item.id), [1, 2, 3, 4]);
    await leave(cell);
    assert.equal(details(cell.root).length, 0);
    assert.deepEqual(ids(cell.root), [2]);
    assert.equal(badge(cell.root)[0].textContent, '4');
    await hover(cell);
    await settle();
    assert.deepEqual(cell.requests, [['test', 7]]);
    assert.deepEqual(ids(details(cell.root)[0]), [2, 1, 3]);
});

test('focus also fetches lazily, keeps details open inside the panel and Escape closes them', async t => {
    const cell = historyCell(t, summary(row(2), 2), [row(2), row(1)]);
    cell.root.onFocusin();
    await settle();
    assert.deepEqual(cell.requests, [['test', 7]]);
    assert.deepEqual(ids(details(cell.root)[0]), [2, 1]);
    await hover(cell);
    await leave(cell);
    assert.equal(details(cell.root).length, 1);
    cell.root.onFocusout({ relatedTarget: items(details(cell.root)[0])[0] });
    await nextTick();
    assert.equal(details(cell.root).length, 1);
    cell.root.onFocusout({ relatedTarget: null });
    await nextTick();
    assert.equal(details(cell.root).length, 0);
    await hover(cell);
    cell.root.onFocusin();
    cell.root.onKeydown({ key: 'Escape', stopPropagation() {} });
    await nextTick();
    assert.equal(details(cell.root).length, 0);
    assert.equal(cell.root.blurred, true);
    assert.equal(cell.requests.length, 1);
});

test('repeated hover and focus deduplicate a pending request and cache its response', async t => {
    const request = deferred();
    const cell = historyCell(t, summary(), request.promise);
    await hover(cell);
    await leave(cell);
    await hover(cell);
    cell.root.onFocusin();
    await nextTick();
    assert.deepEqual(cell.requests, [['test', 7]]);
    request.resolve([row(8), row(6), row(4)]);
    await settle();
    assert.deepEqual(ids(details(cell.root)[0]), [8, 6, 4]);
    cell.root.onFocusout({ relatedTarget: null });
    await leave(cell);
    await hover(cell);
    assert.equal(cell.requests.length, 1);
});

test('changing prospect while a request is pending ignores the previous response', async t => {
    const oldRequest = deferred(), newRequest = deferred();
    const cell = historyCell(t, summary(), (_project, id) => id === 7 ? oldRequest.promise : newRequest.promise);
    await hover(cell);
    await cell.setProps({ prospect: summary(row(20), 2, 9) });
    assert.deepEqual(cell.requests, [['test', 7], ['test', 9]]);
    newRequest.resolve([row(20), row(19)]);
    await settle();
    assert.deepEqual(ids(details(cell.root)[0]), [20, 19]);
    oldRequest.resolve([row(8), row(1)]);
    await settle();
    assert.deepEqual(ids(details(cell.root)[0]), [20, 19]);
    await leave(cell);
    assert.deepEqual(ids(cell.root), [20]);
});

test('switching projects with the same prospect id invalidates cached and pending history', async t => {
    const oldRequest = deferred(), newRequest = deferred();
    const cell = historyCell(t, summary(), project => project === 'test' ? oldRequest.promise : newRequest.promise);
    await hover(cell);
    cell.getters.project = { slug: 'other' };
    await nextTick();
    assert.deepEqual(cell.requests, [['test', 7], ['other', 7]]);
    newRequest.resolve([row(80), row(79)]);
    await settle();
    oldRequest.resolve([row(8), row(1)]);
    await settle();
    assert.deepEqual(ids(details(cell.root)[0]), [80, 79]);
    await leave(cell);
    await hover(cell);
    assert.equal(cell.requests.length, 2);
});

test('a changed summary invalidates cached history and reloads it only when expanded', async t => {
    let response = [row(8), row(6), row(4)];
    const cell = historyCell(t, summary(), () => response);
    await hover(cell);
    await settle();
    await leave(cell);
    response = [row(9), row(8), row(6), row(4)];
    await cell.setProps({ prospect: summary(row(9), 4) });
    assert.equal(cell.requests.length, 1);
    assert.deepEqual(ids(cell.root), [9]);
    assert.equal(badge(cell.root)[0].textContent, '4');
    await hover(cell);
    await settle();
    assert.equal(cell.requests.length, 2);
    assert.deepEqual(ids(details(cell.root)[0]), [9, 8, 6, 4]);
});

test('a changed latest payload with the same count invalidates a pending response', async t => {
    const oldRequest = deferred(), newRequest = deferred();
    let attempt = 0;
    const cell = historyCell(t, summary(), () => (++attempt === 1 ? oldRequest.promise : newRequest.promise));
    await hover(cell);
    await cell.setProps({ prospect: summary({ ...row(8), name: 'Updated description' }, 3) });
    assert.equal(cell.requests.length, 2);
    newRequest.resolve([{ ...row(8), name: 'Updated description' }, row(6), row(4)]);
    await settle();
    oldRequest.resolve([{ ...row(8), name: 'Old description' }, row(6), row(4)]);
    await settle();
    assert.equal(items(details(cell.root)[0])[0].items[0].name, 'Updated description');
});

test('failed fetch exposes retry and a successful retry remains cached', async t => {
    let attempts = 0;
    const cell = historyCell(t, summary(), async () => {
        if (++attempts === 1) throw new Error('Temporary failure');
        return [row(8), row(6), row(4)];
    });
    await hover(cell);
    await settle();
    const retry = nodes(details(cell.root)[0], node => node.tag === 'button')[0];
    assert.ok(retry, 'the history panel offers a retry after a failed request');
    retry.onClick({ stopPropagation() {} });
    await settle();
    assert.equal(cell.requests.length, 2);
    assert.deepEqual(ids(details(cell.root)[0]), [8, 6, 4]);
    await leave(cell);
    await hover(cell);
    assert.equal(cell.requests.length, 2);
});

test('the history panel CSS bounds height and enables scrolling for long histories', () => {
    const descriptor = parse(fs.readFileSync(path.join(sourceRoot, tableRoot, 'cell/HistoryCell.vue'), 'utf8')).descriptor;
    const rule = descriptor.styles.map(style => style.content).join('\n')
        .match(/\.hc-history-cell-details\s*\{([^}]+)\}/)[1];
    assert.match(rule, /max-height:\s*(?:[1-9][0-9]*(?:px|vh|rem)|min\()/);
    assert.match(rule, /overflow(?:-y)?:\s*(?:auto|scroll)\s*;/);
});

test('adding the history column produces a working table column, header and summary cell', async t => {
    const slide = load(tableRoot + 'slides/add-column/Slide.vue').default;
    const state = {
        fields: [], categories: [], threads: [], calendars: [], prospectsTableSetting: [],
        eventFields: [], $t: key => key,
    };
    const choice = slide.computed.columns.call(state).find(column => column.columnId === 'history');
    let saved;
    await slide.methods.addColumn.call({
        ...state, setProspectsTableSetting: settings => { saved = settings; },
        $nextTick: nextTick, scrollToCorrespondingColumn: () => {},
    }, choice);
    const table = load(tableRoot + 'Table.vue').default;
    const [column] = table.computed.columns.call({ ...state, projectUserSettingsProspectsTable: saved });
    assert.equal(column.category, 'history');
    assert.equal(column.name, choice.name);

    const header = mount(t, component('HeaderCell.vue'), { column });
    assert.equal(header.root.id, choice.headerId);
    assert.equal(nodes(header.root, node => node.tag === 'label')[0].children[0].textContent, choice.name);
    assert.equal(nodes(header.root, node => node.tag === 'unrelated-component').length, 0);

    const cell = mount(t, component('Cell.vue'), { column, prospect: summary(row(8), 3) });
    assert.deepEqual(ids(cell.root), [8]);
    assert.equal(badge(cell.root)[0].textContent, '3');
});

function updateHarness(prospects, response = { history_latest: null, history_count: 0 }) {
    const requests = [], commits = [];
    const store = load('store/project/prospect.js', {
        '@/apis/project/prospect': { update: async (...args) => { requests.push(args); return { data: await response }; } },
    }).default;
    const context = {
        state: { project: { slug: 'test', prospects } },
        commit: (...args) => commits.push(args),
    };
    return { requests, commits, context, run: params => store.actions.UPDATE_PROSPECT(context, params) };
}

test('updates refresh the displayed history summary without fetching complete history', async () => {
    for (const prospect of [summary(), summary(null, 0)]) {
        const updated = { history_latest: row(12), history_count: 4 };
        const harness = updateHarness([prospect], updated);
        const params = Object.freeze({ id: 7, name: 'Updated' });
        await harness.run(params);
        assert.deepEqual(harness.requests, [['test', 7, { id: 7, name: 'Updated', withHistory: true }]]);
        assert.deepEqual(harness.commits, [
            ['UPDATE_PROSPECT', params], ['UPDATE_PROSPECT', { id: 7, ...updated }],
        ]);
        assert.deepEqual(params, { id: 7, name: 'Updated' });
    }
});

test('updates without a displayed history preserve the original request and optimistic update', async () => {
    for (const prospects of [[{ id: 7 }], [], undefined]) {
        const harness = updateHarness(prospects, { history_latest: row(12), history_count: 4 });
        const params = Object.freeze({ id: 7, name: 'Updated' });
        await harness.run(params);
        assert.deepEqual(harness.requests, [['test', 7, params]]);
        assert.equal(harness.requests[0][2], params);
        assert.deepEqual(harness.commits, [['UPDATE_PROSPECT', params]]);
    }
});

test('an incomplete update response cannot erase the existing summary', async () => {
    const harness = updateHarness([summary()], { name: 'Updated' });
    const params = { id: 7, name: 'Updated' };
    await harness.run(params);
    assert.deepEqual(harness.commits, [['UPDATE_PROSPECT', params]]);
});

test('a delayed update cannot replace summary after switching projects with the same prospect id', async () => {
    const request = deferred();
    const harness = updateHarness([summary()], request.promise);
    const params = Object.freeze({ id: 7, name: 'Updated' });
    const pending = harness.run(params);
    assert.deepEqual(harness.commits, [['UPDATE_PROSPECT', params]]);
    harness.context.state.project = { slug: 'other', prospects: [summary(row(30), 15)] };
    request.resolve({ history_latest: row(12), history_count: 4 });
    await pending;
    assert.equal(harness.requests[0][0], 'test');
    assert.deepEqual(harness.commits, [['UPDATE_PROSPECT', params]]);
});
