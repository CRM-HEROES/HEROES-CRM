const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');
const { compile, createRenderer, h } = require('vue');
const dayjs = require('dayjs');

const sourceRoot = path.resolve(__dirname, '../../resources/js');
const historyRoot = 'components/prospect/profile/blocs/history/';
const loaded = new Map();

// Load the real history components and their helpers; only Vuex is replaced by
// getters supplied by each test. This exercises the templates shown in both the
// profile and the table's history popup without requiring a browser or HTTP.
function load(filename) {
    if (loaded.has(filename)) return loaded.get(filename);
    let source = fs.readFileSync(path.join(sourceRoot, filename), 'utf8');
    let template;
    if (filename.endsWith('.vue')) {
        const descriptor = parse(source).descriptor;
        source = descriptor.script.content;
        template = descriptor.template.content;
    }
    const code = transformSync(source, {
        babelrc: false, configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    }).code;
    const module = { exports: {} };
    new Function('require', 'module', 'exports', code)(specifier => {
        if (specifier === 'vuex') return {
            mapGetters: names => Object.fromEntries(names.map(name => [name, function () {
                return this.$testGetters[name];
            }])),
        };
        if (specifier.startsWith('@/') || specifier.startsWith('.')) {
            let target = specifier.startsWith('@/')
                ? specifier.slice(2)
                : path.join(path.dirname(filename), specifier);
            if (!path.extname(target)) target += '.js';
            return load(target);
        }
        return require(specifier);
    }, module, module.exports);
    if (template) module.exports.default.render = compile(template);
    loaded.set(filename, module.exports);
    return module.exports;
}

function mount(t, filename, item, getters = {}) {
    const element = tag => ({ tag, children: [], parent: null });
    const renderer = createRenderer({
        createElement: element,
        createText: text => ({ text }), createComment: comment => ({ comment }),
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
    const app = renderer.createApp({ render: () => h(load(historyRoot + filename).default, { item }) });
    app.config.globalProperties.$testGetters = { categories, users, ...getters };
    app.config.warnHandler = message => { throw new Error(message); };
    app.mount(host);
    t.after(() => app.unmount());
    return host;
}

const nodes = (root, predicate) => [root, ...(root.children || []).flatMap(child => nodes(child, predicate))].filter(predicate);
const withClass = (root, className) => nodes(root, node => String(node.class || '').split(' ').includes(className));
const text = node => String(node.textContent ?? node.text ?? '') + (node.children || []).map(text).join('');
const classText = (root, name) => withClass(root, name).map(text).join('');
const classTexts = (root, name) => withClass(root, name).map(text);
const labelText = root => classText(root, 'hc-prospect-profile-history-label-name');
const authorText = root => classText(root, 'hc-prospect-profile-history-row-user');
const historyRows = root => withClass(root, 'hc-prospect-profile-history-row');
const rowDates = root => classTexts(root, 'hc-prospect-profile-history-row-date');
const rowAuthors = root => classTexts(root, 'hc-prospect-profile-history-row-user');
const categories = [{ id: 1, name: 'Suivi', labels: [
    { id: 1, name: 'Nouveau Lead' },
    { id: 2, name: 'Nouveau distributeur' },
    { id: 3, name: 'NRP/ Répondeur 1' },
    { id: 4, name: 'NRP/ Répondeur 2' },
    { id: 5, name: 'RDV BE fixé', color: '#123456', bgcolor: '#abcdef' },
    { id: 6, name: 'RDV, à confirmer' },
    { id: 7, name: 'Satisfait' },
    { id: 8, name: 'SAV' },
    { id: 9, name: 'NRP' },
    { id: 10, name: 'RDV TEL' },
] }];
const users = [{ id: 1, name: 'Super Admin' }, { id: 2, name: 'Aria' }, { id: 3, name: 'Hana' }];
const revision = overrides => ({
    id: 20, type: 'revision', key: 'category->1', field: 'Suivi',
    old_value: null, new_value: 'RDV BE fixé', user_id: 2, user: users[1],
    created_at: '2026-10-30T13:24:00', ...overrides,
});

test('a preserved appointment status renders its tag, original date and Aria author', t => {
    const item = revision();
    const original = structuredClone(item);
    const root = mount(t, 'RevisionItem.vue', item);
    assert.equal(labelText(root), 'RDV BE fixé');
    assert.equal(classText(root, 'hc-prospect-profile-history-label-category'), 'Suivi : ');
    assert.equal(authorText(root), 'Aria');
    assert.equal(classText(root, 'hc-prospect-profile-history-row-date'), dayjs(new Date(item.created_at)).format('DD MMM YYYY HH:mm'));
    assert.equal(withClass(root, 'fa-tag').length, 1);
    assert.equal(withClass(root, 'fa-pen').length, 0);
    assert.deepEqual(item, original, 'rendering must retain the full audit record');
});

test('adding RDV BE fixé displays its compact Aria entry followed by the complete audit change', t => {
    const item = revision({
        old_value: 'Satisfait, SAV, NRP, RDV TEL',
        new_value: 'Satisfait, SAV, RDV BE fixé, NRP, RDV TEL',
    });
    const original = structuredClone(item);
    const root = mount(t, 'RevisionItem.vue', item);
    const rows = historyRows(root);
    assert.equal(rows.length, 2);
    assert.equal(labelText(rows[0]), 'RDV BE fixé');
    assert.equal(classText(rows[0], 'hc-prospect-profile-history-label-category'), 'Suivi : ');
    assert.equal(withClass(rows[0], 'fa-tag').length, 1);
    assert.equal(withClass(rows[1], 'fa-pen').length, 1);
    assert.equal(classText(rows[1], 'hc-prospect-profile-history-revision-field'), 'Suivi : ');
    assert.equal(classText(rows[1], 'hc-prospect-profile-history-revision-old-value'), item.old_value);
    assert.equal(classText(rows[1], 'hc-prospect-profile-history-revision-new-value'), item.new_value);
    assert.deepEqual(rowAuthors(root), ['Aria', 'Aria']);
    assert.deepEqual(rowDates(root), Array(2).fill(dayjs(new Date(item.created_at)).format('DD MMM YYYY HH:mm')));
    assert.deepEqual(item, original, 'compact status rendering must retain the full audit record');
});

test('later checkbox additions retain a compact status above full selections before and after', t => {
    const item = revision({
        old_value: 'Nouveau Lead, Nouveau distributeur, NRP/ Répondeur 2',
        new_value: 'Nouveau Lead, Nouveau distributeur, NRP/ Répondeur 1, NRP/ Répondeur 2',
        user_id: 1, user: users[0],
    });
    const original = structuredClone(item);
    const root = mount(t, 'RevisionItem.vue', item);
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-field'), 'Suivi : ');
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-old-value'), item.old_value);
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-new-value'), item.new_value);
    assert.equal(labelText(historyRows(root)[0]), 'NRP/ Répondeur 1');
    assert.deepEqual(rowAuthors(root), ['Super Admin', 'Super Admin']);
    assert.deepEqual(rowDates(root), Array(2).fill(dayjs(new Date(item.created_at)).format('DD MMM YYYY HH:mm')));
    assert.equal(withClass(root, 'fa-pen').length, 1);
    assert.equal(withClass(root, 'fa-tag').length, 1);
    assert.equal(withClass(root, 'fa-caret-right').length, 1);
    assert.deepEqual(item, original);
});

test('a single-status replacement displays the new tag followed by old and new audit values', t => {
    const root = mount(t, 'RevisionItem.vue', revision({ old_value: 'Nouveau Lead' }), {
        categories: [{ ...categories[0], unique: true }],
    });
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-old-value'), 'Nouveau Lead');
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-new-value'), 'RDV BE fixé');
    assert.equal(labelText(historyRows(root)[0]), 'RDV BE fixé');
    assert.equal(withClass(root, 'fa-pen').length, 1);
    assert.equal(withClass(root, 'fa-tag').length, 1);
    assert.deepEqual(rowAuthors(root), ['Aria', 'Aria']);
});

test('unchecking one selection retains the full selections before and after', t => {
    const root = mount(t, 'RevisionItem.vue', revision({
        old_value: 'Nouveau Lead, NRP/ Répondeur 1', new_value: 'Nouveau Lead',
    }));
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-old-value'), 'Nouveau Lead, NRP/ Répondeur 1');
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-new-value'), 'Nouveau Lead');
    assert.equal(historyRows(root).length, 1);
    assert.equal(withClass(root, 'fa-pen').length, 1);
    assert.equal(withClass(root, 'fa-tag').length, 0);
    assert.equal(nodes(root, node => node.tag === 'del').length, 0);
    assert.equal(authorText(root), 'Aria');
});

test('a modified selection preserves exact before and after text including commas inside labels', t => {
    const root = mount(t, 'RevisionItem.vue', revision({
        old_value: 'Nouveau Lead', new_value: 'Nouveau Lead, RDV, à confirmer',
    }));
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-old-value'), 'Nouveau Lead');
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-new-value'), 'Nouveau Lead, RDV, à confirmer');
    assert.equal(labelText(historyRows(root)[0]), 'RDV, à confirmer');
    assert.equal(withClass(root, 'fa-tag').length, 1);
    assert.equal(withClass(root, 'fa-pen').length, 1);
});

test('a first addition displays a status tag followed by the audit row for that addition', t => {
    const root = mount(t, 'RevisionItem.vue', revision({ old_value: '', new_value: 'RDV, à confirmer' }));
    assert.equal(labelText(root), 'RDV, à confirmer');
    assert.equal(withClass(root, 'fa-tag').length, 1);
    assert.equal(withClass(root, 'fa-pen').length, 1);
    assert.equal(classText(historyRows(root)[1], 'hc-prospect-profile-history-revision-new-value'), 'RDV, à confirmer');
    assert.deepEqual(rowAuthors(root), ['Aria', 'Aria']);
});

test('reattaching a removed status displays its new addition and audit at the reattachment time', t => {
    const item = revision({ id: 22, old_value: '', new_value: 'RDV BE fixé', created_at: '2026-10-31T09:15:00' });
    const root = mount(t, 'RevisionItem.vue', item);
    assert.equal(labelText(historyRows(root)[0]), 'RDV BE fixé');
    assert.equal(withClass(root, 'fa-tag').length, 1);
    assert.equal(withClass(root, 'fa-pen').length, 1);
    assert.deepEqual(rowDates(root), Array(2).fill(dayjs(new Date(item.created_at)).format('DD MMM YYYY HH:mm')));
});

test('multiple newly selected statuses each get a tag before the single full audit row', t => {
    const item = revision({
        old_value: 'Nouveau Lead',
        new_value: 'Nouveau Lead, NRP/ Répondeur 1, RDV BE fixé',
    });
    const root = mount(t, 'RevisionItem.vue', item);
    const rows = historyRows(root);
    assert.equal(rows.length, 3);
    assert.equal(labelText(rows[0]), 'NRP/ Répondeur 1');
    assert.equal(labelText(rows[1]), 'RDV BE fixé');
    assert.equal(withClass(rows[2], 'fa-pen').length, 1);
    assert.equal(classText(rows[2], 'hc-prospect-profile-history-revision-old-value'), item.old_value);
    assert.equal(classText(rows[2], 'hc-prospect-profile-history-revision-new-value'), item.new_value);
    assert.deepEqual(rowAuthors(root), ['Aria', 'Aria', 'Aria']);
});

test('unchecking the last selection preserves its former value with an edit icon', t => {
    const root = mount(t, 'RevisionItem.vue', revision({ old_value: 'RDV BE fixé', new_value: '' }));
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-old-value'), 'RDV BE fixé');
    assert.equal(withClass(root, 'hc-prospect-profile-history-revision-new-value').length, 0);
    assert.equal(withClass(root, 'fa-pen').length, 1);
    assert.equal(withClass(root, 'fa-tag').length, 0);
    assert.equal(authorText(root), 'Aria');
});

test('a historical status stays readable when category metadata is unavailable', t => {
    const root = mount(t, 'RevisionItem.vue', revision(), { categories: [] });
    assert.equal(labelText(root), 'RDV BE fixé');
    assert.equal(classText(root, 'hc-prospect-profile-history-label-category'), 'Suivi : ');
    assert.equal(authorText(root), 'Aria');
});

test('a later insertion keeps its compact tag and audit when category metadata is unavailable', t => {
    const item = revision({
        old_value: 'Satisfait, SAV',
        new_value: 'Satisfait, RDV BE fixé, SAV',
    });
    const root = mount(t, 'RevisionItem.vue', item, { categories: [] });
    const rows = historyRows(root);
    assert.equal(rows.length, 2);
    assert.equal(labelText(rows[0]), 'RDV BE fixé');
    assert.equal(classText(rows[0], 'hc-prospect-profile-history-label-category'), 'Suivi : ');
    assert.equal(classText(rows[1], 'hc-prospect-profile-history-revision-old-value'), item.old_value);
    assert.equal(classText(rows[1], 'hc-prospect-profile-history-revision-new-value'), item.new_value);
    assert.deepEqual(rowAuthors(root), ['Aria', 'Aria']);
});

test('ordinary prospect-field revisions retain their before/after values and edit icon', t => {
    const root = mount(t, 'RevisionItem.vue', revision({
        key: 'first_name', field: 'Prénom', old_value: 'Ancien prénom', new_value: 'Nouveau prénom',
    }));
    assert.equal(withClass(root, 'fa-pen').length, 1);
    assert.equal(withClass(root, 'fa-tag').length, 0);
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-old-value'), 'Ancien prénom');
    assert.equal(classText(root, 'hc-prospect-profile-history-revision-new-value'), 'Nouveau prénom');
    assert.equal(authorText(root), 'Aria');
});

test('direct assignments use the attached creator even if the global user list is incomplete', t => {
    const root = mount(t, 'LabelItem.vue', {
        id: 5, category_id: 1, name: 'RDV BE fixé', created_at: '2026-10-30T13:24:00',
        creator_id: 1, creator: { id: 2, name: 'Aria' },
    }, { users: [users[0]] });
    assert.equal(authorText(root), 'Aria');
});

test('assignment author comes from the pivot rather than the label-definition creator', t => {
    const root = mount(t, 'LabelItem.vue', {
        id: 5, category_id: 1, name: 'RDV BE fixé', created_at: '2026-10-30T13:24:00',
        creator_id: 1, pivot: { creator_id: 3 },
    });
    assert.equal(authorText(root), 'Hana');
});

test('an unknown pivot author does not fall back to the label-definition creator', t => {
    const root = mount(t, 'LabelItem.vue', {
        id: 5, category_id: 1, name: 'RDV BE fixé', created_at: '2026-10-30T13:24:00',
        creator_id: 1, pivot: { creator_id: null },
    });
    assert.equal(authorText(root), '');
});
