const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');
const { compile, createRenderer, h, nextTick, reactive } = require('vue');
const originalDocument = global.document;
global.document = { activeElement: null };
test.after(() => {
    if (originalDocument === undefined) delete global.document;
    else global.document = originalDocument;
});

const root = path.resolve(__dirname, '../../resources/js/components/stat-builder');
const modules = new Map();

function load(filename, dependencies = {}) {
    const cacheable = !Object.keys(dependencies).length;
    if (cacheable && modules.has(filename)) return modules.get(filename);
    const full = path.join(root, filename);
    let source = fs.readFileSync(full, 'utf8');
    let template;
    if (filename.endsWith('.vue')) {
        const descriptor = parse(source).descriptor;
        source = descriptor.script.content;
        template = descriptor.template.content;
    }
    const { code } = transformSync(source, {
        babelrc: false, configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const module = { exports: {} };
    new Function('require', 'module', 'exports', code)(specifier => {
        if (specifier in dependencies) return dependencies[specifier];
        if (specifier === 'vuex') return { mapGetters: () => ({}) };
        if (specifier === 'vuedraggable') return {
            props: ['modelValue'],
            render() {
                return h('div', this.modelValue.map((element, index) => this.$slots.item({ element, index })));
            },
        };
        if (specifier.endsWith('registry')) return load('registry.js');
        if (specifier.endsWith('.vue')) return { render: () => h('unrelated-component') };
        throw new Error('Unexpected import: ' + specifier);
    }, module, module.exports);
    if (template) module.exports.default.render = compile(template);
    if (cacheable) modules.set(filename, module.exports);
    return module.exports;
}

const source = {
    key: 'users', kind: 'users', measures: [],
    fields: [
        { key: 'name', label: 'Utilisateur', type: 'string' },
        { key: 'calls_count', label: 'Appels', type: 'number' },
        { key: 'followup', label: 'Suivi', type: 'number', description: 'Suivis effectués par l’utilisateur' },
    ],
};
const followups = [
    { value: 10, label: 'NRP', category: 'Suivi' },
    { value: 20, label: 'SAV', category: 'Suivi' },
    { value: 30, label: 'RDV TEL', category: 'Suivi' },
];

function mount(t, config = { columns: [{ field: 'name', label: '' }] }, extraProps = {}) {
    function element(tag) {
        const node = {
            tag, tagName: tag.toUpperCase(), children: [], parent: null,
            ownerDocument: { activeElement: null }, listeners: {},
            addEventListener(name, handler) { this.listeners[name] = handler; },
            removeEventListener(name) { delete this.listeners[name]; },
        };
        Object.defineProperty(node, 'options', { get: () => nodes(node, child => child.tag === 'option') });
        return node;
    }
    const renderer = createRenderer({
        createElement: element,
        createText: text => ({ text }), createComment: comment => ({ comment }),
        setText: (node, text) => { node.text = text; },
        setElementText: (node, text) => { node.text = text; node.children = []; },
        patchProp: (node, key, _old, value) => { node[key] = value; },
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
    const state = reactive(config);
    const host = element('host');
    const app = renderer.createApp(load('config/ColumnsSection.vue').default, {
        config: state, source, followups, ...extraProps,
    });
    const warnings = [], errors = [];
    app.config.warnHandler = message => { warnings.push(message); };
    app.config.errorHandler = error => { errors.push(error); };
    const vm = app.mount(host);
    if (errors.length) throw errors[0];
    if (warnings.length) throw new Error(warnings[0]);
    t.after(() => {
        app.unmount();
        assert.deepEqual(errors, []);
        assert.deepEqual(warnings, []);
    });
    return { vm, state, host };
}

function nodes(root, predicate) {
    return [root, ...(root.children || []).flatMap(child => nodes(child, predicate))].filter(predicate);
}
const followupSelects = root => nodes(root, node => node.tag === 'select' && node['aria-label'] === 'Suivi à compter');
const text = root => [root.text || '', ...(root.children || []).map(text)].join('');

test('choosing Suivi opens its second select and saves a numeric column for the selected project status', async t => {
    const { vm, state, host } = mount(t);
    assert.ok(vm.available.some(field => field.key === 'followup'));
    vm.toAdd = 'followup';
    await nextTick();
    assert.equal(state.columns[1].field, 'followup');
    const selects = followupSelects(host);
    assert.equal(selects.length, 1);
    assert.deepEqual(nodes(selects[0], node => node.tag === 'option').map(node => text(node).trim()), [
        'Choisir un suivi…', 'NRP', 'SAV', 'RDV TEL',
    ]);
    selects[0].onChange({ target: { value: '10' } });
    await nextTick();
    assert.equal(state.columns[1].field, 'followup_10');
    assert.equal(vm.fieldOf('followup_10').label, 'Suivi → NRP');
    assert.equal(vm.fieldOf('followup_10').type, 'number');
    assert.ok(text(host).includes('Suivi → NRP'));
});

test('several different statuses can be counted without duplicate columns', async t => {
    const { vm, state, host } = mount(t);
    vm.toAdd = 'followup';
    await nextTick();
    assert.equal(vm.available.some(field => field.key === 'followup'), false, 'one pending selector at a time');
    vm.setFollowup(state.columns[1], '10');
    await nextTick();
    assert.equal(vm.available.some(field => field.key === 'followup'), true);
    vm.toAdd = 'followup';
    await nextTick();
    const second = followupSelects(host)[1];
    assert.deepEqual(nodes(second, node => node.tag === 'option').map(node => node.value), ['', '20', '30']);
    vm.setFollowup(state.columns[2], '10');
    assert.equal(state.columns[2].field, 'followup', 'duplicate selection is ignored');
    second.onChange({ target: { value: '20' } });
    await nextTick();
    assert.deepEqual(state.columns.map(column => column.field), ['name', 'followup_10', 'followup_20']);
});

test('changing and removing a selected status keep sorting valid and retain a custom column name', async t => {
    const { vm, state, host } = mount(t, {
        columns: [{ field: 'followup_10', label: 'Mes relances' }],
        sorting: { field: 'followup_10', direction: 'desc' },
    });
    followupSelects(host)[0].onChange({ target: { value: '30' } });
    await nextTick();
    assert.equal(state.columns[0].field, 'followup_30');
    assert.equal(state.columns[0].label, 'Mes relances');
    assert.deepEqual(state.sorting, { field: 'followup_30', direction: 'desc' });
    nodes(host, node => node.tag === 'button' && node['data-tip'] === 'Supprimer la colonne')[0].onClick();
    await nextTick();
    assert.deepEqual(state.columns, []);
    assert.deepEqual(state.sorting, { field: '', direction: 'desc' });
});

test('reopening saved columns restores the selected status, its default title and sorting option', t => {
    const { vm, host } = mount(t, { columns: [{ field: 'followup_20', label: '' }] });
    assert.equal(followupSelects(host)[0].value, '20');
    assert.equal(vm.sortable[0].label, 'Suivi → SAV');
    assert.ok(text(host).includes('Suivi → SAV'));
});

test('identical names in different categories remain selectable and show their category', async t => {
    const { vm, host } = mount(t, { columns: [{ field: 'followup', label: '' }] }, {
        followups: [followups[0], { value: 40, label: 'NRP', category: 'Autre suivi' }],
    });
    assert.deepEqual(nodes(followupSelects(host)[0], node => node.tag === 'option').map(node => text(node).trim()), [
        'Choisir un suivi…', 'NRP — Suivi', 'NRP — Autre suivi',
    ]);
    vm.setFollowup(vm.config.columns[0], '40');
    await nextTick();
    assert.equal(vm.config.columns[0].field, 'followup_40');
});

test('unavailable saved statuses can be replaced and cannot be used for sorting', async t => {
    const { vm, state, host } = mount(t, { columns: [{ field: 'followup_999', label: '' }] });
    assert.ok(text(followupSelects(host)[0]).includes('Suivi indisponible'));
    assert.deepEqual(vm.sortable, []);
    vm.setFollowup(state.columns[0], '20');
    await nextTick();
    assert.equal(state.columns[0].field, 'followup_20');
});

test('ordinary event source columns keep their original aggregate choices', t => {
    const { vm, host } = mount(t, { columns: [{ field: 'duration', label: '', agg: 'avg' }] }, {
        source: {
            kind: 'events', fields: [{ key: 'duration', label: 'Durée', type: 'number', measure: true }],
            measures: [{ key: 'duration', aggs: ['sum', 'avg'], default: 'sum' }],
        },
    });
    assert.equal(followupSelects(host).length, 0);
    assert.deepEqual(vm.aggsOf('duration'), ['sum', 'avg']);
    assert.equal(vm.defaultAgg('duration'), 'sum');
});

test('wizard validation requires a valid project status and rejects duplicate followup counts', () => {
    const wizard = load('wizard/Wizard.vue').default;
    const validate = columns => wizard.methods.configError.call({
        step: 2, meta: { options: { followup: followups } },
    }, { type: 'table', dataSource: 'users', columns });
    assert.match(validate([{ field: 'followup' }]), /Choisissez le suivi/);
    assert.match(validate([{ field: 'followup_999' }]), /disponible dans ce projet/);
    assert.match(validate([{ field: 'followup_bad' }]), /disponible dans ce projet/);
    assert.match(validate([{ field: 'followup_10' }, { field: 'followup_10' }]), /déjà présent/);
    assert.equal(validate([{ field: 'name' }, { field: 'followup_10' }, { field: 'followup_20' }]), '');
});

test('generic Suivi is a column selector and does not become an unusable user filter', () => {
    const filters = load('config/FiltersSection.vue').default;
    const fields = filters.computed.fields.call({ source: {
        ...source,
        fields: [...source.fields, { key: 'recording', label: 'Écouter', type: 'audio_action' }],
    } });
    assert.deepEqual(fields.map(field => field.key), ['name', 'calls_count']);
    const dimensions = [{ key: 'user', label: 'Utilisateur' }];
    assert.equal(filters.computed.fields.call({ source: { kind: 'events', dimensions } }), dimensions);
});

function deferred() {
    let resolve, reject;
    const promise = new Promise((done, fail) => { resolve = done; reject = fail; });
    return { promise, resolve, reject };
}

function dashboard(get) {
    const builder = load('StatBuilder.vue', { '@/apis/api.service': { get } }).default;
    const ticks = [];
    const vm = {
        ...builder.data(), project: { slug: 'first-project' },
        $nextTick: callback => ticks.push(callback),
    };
    return { vm, load: () => builder.methods.load.call(vm), tick: () => ticks.splice(0).forEach(callback => callback()) };
}

test('dashboard followup metadata is cached within a project and refreshed when switching projects', async () => {
    const requests = [];
    const fixture = dashboard(async url => {
        requests.push(url);
        return { data: url.endsWith('/meta')
            ? { options: { followup: [{ value: url.includes('first-project') ? 10 : 20, label: url.includes('first-project') ? 'NRP' : 'SAV' }] } }
            : { widgets: [], globalFilters: {} } };
    });
    await fixture.load();
    assert.equal(fixture.vm.meta.options.followup[0].label, 'NRP');
    fixture.tick();
    await fixture.load();
    assert.equal(requests.filter(url => url.endsWith('/meta')).length, 1);
    fixture.vm.project = { slug: 'second-project' };
    await fixture.load();
    assert.equal(fixture.vm.metaProject, 'second-project');
    assert.equal(fixture.vm.meta.options.followup[0].label, 'SAV');
    assert.deepEqual(requests.filter(url => url.endsWith('/meta')), [
        'project/first-project/stat-dashboard/meta', 'project/second-project/stat-dashboard/meta',
    ]);
});

test('an old project response cannot replace followups or stop the new project loading state', async () => {
    const first = deferred(), second = deferred();
    const fixture = dashboard(url => url.includes('first-project') ? first.promise : second.promise);
    const oldLoad = fixture.load();
    fixture.vm.project = { slug: 'second-project' };
    const newLoad = fixture.load();
    first.resolve({ data: { options: { followup: [followups[0]] }, widgets: [{ id: 'old' }] } });
    await oldLoad;
    assert.equal(fixture.vm.loading, true);
    assert.equal(fixture.vm.meta, null);
    assert.equal(fixture.vm.dashboard, null);
    second.resolve({ data: { options: { followup: [followups[1]] }, widgets: [{ id: 'new' }] } });
    await newLoad;
    fixture.tick();
    assert.equal(fixture.vm.loading, false);
    assert.equal(fixture.vm.ready, true);
    assert.equal(fixture.vm.meta.options.followup[0].value, 20);
    assert.deepEqual(fixture.vm.dashboard.widgets, [{ id: 'new' }]);
});

test('old project failures cannot overwrite a newer successful dashboard', async () => {
    const oldRequest = deferred();
    const fixture = dashboard(url => url.includes('first-project') ? oldRequest.promise : Promise.resolve({
        data: { options: { followup: [followups[1]] }, widgets: [] },
    }));
    const oldLoad = fixture.load();
    fixture.vm.project = { slug: 'second-project' };
    await fixture.load();
    fixture.tick();
    oldRequest.reject({ response: { data: { message: 'Old project failed' } } });
    await oldLoad;
    assert.equal(fixture.vm.loadError, null);
    assert.equal(fixture.vm.ready, true);
    assert.equal(fixture.vm.meta.options.followup[0].value, 20);
});

test('switching projects clears old options, cancels pending autosave and ignores its scheduled ready callback', async () => {
    const next = deferred();
    const fixture = dashboard(url => url.includes('first-project')
        ? Promise.resolve({ data: { options: { followup: [followups[0]] }, widgets: [] } })
        : next.promise);
    await fixture.load();
    const saves = [];
    fixture.vm.saveTimer = setTimeout(() => saves.push('old dashboard'), 0);
    fixture.vm.wizard.open = true;
    fixture.vm.project = { slug: 'second-project' };
    const pending = fixture.load();
    fixture.tick();
    assert.equal(fixture.vm.meta, null);
    assert.equal(fixture.vm.wizard.open, false);
    assert.equal(fixture.vm.ready, false);
    assert.equal(fixture.vm.loading, true);
    await new Promise(resolve => setTimeout(resolve, 5));
    assert.deepEqual(saves, []);
    next.resolve({ data: { options: { followup: [followups[1]] }, widgets: [] } });
    await pending;
    fixture.tick();
    assert.equal(fixture.vm.ready, true);
    assert.equal(fixture.vm.meta.options.followup[0].value, 20);
});
