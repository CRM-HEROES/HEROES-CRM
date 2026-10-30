const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { baseParse } = require('@vue/compiler-dom');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../../resources/js');
function load(filename, dependencies = {}) {
    let source = fs.readFileSync(path.join(root, filename), 'utf8');
    if (filename.endsWith('.vue')) source = parse(source).descriptor.script.content;
    const { code } = transformSync(source, {
        babelrc: false, configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const module = { exports: {} };
    new Function('require', 'module', 'exports', code)(key => {
        if (key in dependencies) return dependencies[key];
        if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        return {};
    }, module, module.exports);
    return module.exports.default;
}

const store = load('store/project/prospect.js');
const row = load('components/prospect/table/Row.vue', { vuex: { mapGetters: () => ({}) } });
const { descriptor } = parse(fs.readFileSync(path.join(root, 'components/prospect/table/Row.vue'), 'utf8'));
const anchors = [];
function walk(node) {
    if (node.tag === 'a') anchors.push(node);
    for (const child of node.children || []) walk(child);
}
walk(baseParse(descriptor.template.content));

function classes(prospect, menu) {
    const anchor = anchors.find(node => node.props.some(prop => prop.name === 'else-if'
        && prop.exp.content.includes(`menu == '${menu}'`)));
    const expression = anchor.props.find(prop => prop.name === 'bind' && prop.arg.content === 'class').exp.content;
    return new Function('prospect', 'hasUnreadCommunication', 'isOptionFiltered', `return (${expression})`)(
        prospect, row.methods.hasUnreadCommunication.bind({ prospect }), () => false
    ).filter(Boolean);
}

function prospect(id = 1) {
    return {
        id, messages: [{ id: 1 }], sms: [{ id: 1 }], interactions: [{ id: 1 }],
        unread_communications: { sms: [1, 2], email: [3, 4], voicemail: [5, 6] },
    };
}

test('each icon stays red until every incoming communication of its type has been consulted', () => {
    for (const [menu, type, ids] of [
        ['sms', 'sms', [1, 2]], ['message', 'email', [3, 4]], ['interaction', 'voicemail', [5, 6]],
    ]) {
        const p = prospect();
        const state = { project: { slug: 'test', prospect: p, prospects: [p, prospect(2)] } };
        const mark = ids => store.mutations.MARK_PROSPECT_COMMUNICATIONS_READ(state, {
            projectSlug: 'test', prospectId: 1, type, ids,
        });
        assert.deepEqual(classes(p, menu), ['has-unread', 'has-items']);
        mark([ids[0]]);
        mark([ids[0]]); // Repeated receipts must not clear another unread message.
        assert.deepEqual(classes(p, menu), ['has-unread', 'has-items']);
        mark([ids[1]]);
        assert.deepEqual(classes(p, menu), ['has-items']);
        assert.deepEqual(state.project.prospects[1].unread_communications[type], ids);
        assert.equal(Object.values(p.unread_communications).filter(list => list.length > 0).length, 2);
    }
});

test('confirmed reads update both the selected prospect and its separate table row', () => {
    const selected = prospect(), listed = prospect();
    const state = { project: { slug: 'test', prospect: selected, prospects: [listed] } };
    store.mutations.MARK_PROSPECT_COMMUNICATIONS_READ(state, {
        projectSlug: 'test', prospectId: '1', type: 'sms', ids: ['1'],
    });
    assert.deepEqual(selected.unread_communications.sms, [2]);
    assert.deepEqual(listed.unread_communications.sms, [2]);
});

test('late receipts from another project leave the displayed icons unchanged', () => {
    const p = prospect();
    store.mutations.MARK_PROSPECT_COMMUNICATIONS_READ({ project: { slug: 'other', prospects: [p] } }, {
        projectSlug: 'test', prospectId: 1, type: 'sms', ids: [1, 2],
    });
    assert.deepEqual(classes(p, 'sms'), ['has-unread', 'has-items']);
});

test('processing voicemails also updates the separate call panel prospect', () => {
    const selected = prospect(), listed = prospect(), interactionProspect = prospect();
    const state = { project: { slug: 'test', prospect: selected, interactionProspect, prospects: [listed] } };
    store.mutations.MARK_PROSPECT_COMMUNICATIONS_READ(state, {
        projectSlug: 'test', prospectId: 1, type: 'voicemail', ids: [5, 6],
    });
    assert.deepEqual(interactionProspect.unread_communications.voicemail, []);
    assert.deepEqual(classes(listed, 'interaction'), ['has-items']);
});

test('a newly received message turns a previously consulted icon red again', () => {
    const p = prospect();
    p.unread_communications.sms = [];
    assert.deepEqual(classes(p, 'sms'), ['has-items']);
    p.unread_communications.sms.push(9);
    assert.deepEqual(classes(p, 'sms'), ['has-unread', 'has-items']);
    delete p.unread_communications;
    assert.deepEqual(classes(p, 'sms'), ['has-items']);
});
