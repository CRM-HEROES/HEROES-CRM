const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../..');
const compile = (relative) => {
    const source = fs.readFileSync(path.join(root, relative), 'utf8');
    const script = relative.endsWith('.vue') ? parse(source).descriptor.script.content : source;
    const { code } = transformSync(script, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });

    return (require_) => {
        const module_ = { exports: {} };
        new Function('module', 'exports', 'require', code)(module_, module_.exports, require_);
        return module_.exports;
    };
};

const graph = compile('resources/js/utils/workflow-graph.js')();
const i18n = compile('resources/js/utils/workflow-i18n.js')();
const conditions = compile('resources/js/utils/workflow-conditions.js')();

const component = compile('resources/js/components/workflow/NodeEditor.vue')((id) => ({
    './ConditionGroup.vue': {},
    './FieldInput.vue': {},
    '@/utils/workflow-graph': graph,
    '@/utils/workflow-i18n': i18n,
    '@/utils/workflow-conditions': conditions,
}[id])).default;

const catalog = {
    triggers: [],
    actions: [{ type: 'send_sms', fields: [{ key: 'provider', type: 'provider' }, { key: 'message', type: 'text' }] }],
    conditions: { filters: { prospect: [{ name: 'incomingCommunications', type: 'boolean' }] } },
};

/**
 * The panel as Vue would run it, without a browser: its data, its computed
 * properties and its methods on one object, and the updates it sends up.
 */
function panel(node) {
    const emitted = [];
    const vm = {
        ...component.data(),
        node,
        graph: { nodes: [node], edges: [] },
        catalog,
        readOnly: false,
        $emit: (event, payload) => emitted.push({ event, payload }),
        $te: () => false,
        $t: (key) => key,
    };

    for (const [name, method] of Object.entries(component.methods)) {
        vm[name] = method.bind(vm);
    }
    for (const [name, getter] of Object.entries(component.computed)) {
        Object.defineProperty(vm, name, { get: getter.bind(vm), configurable: true });
    }

    // What the parent does: it applies the change, and the prop changes
    vm.parentApplies = () => {
        vm.node = { ...vm.node, config: emitted.filter((e) => e.payload.config).at(-1).payload.config };
        component.watch['node.config'].call(vm);
    };

    return { vm, emitted, last: () => emitted.filter((e) => e.payload.config).at(-1).payload.config };
}

test('choosing an operator keeps the operator and its name (two updates in a row)', () => {
    const { vm, last } = panel({ id: 'a', type: 'action', config: { action: 'send_sms' } });

    // What the field does when an operator is chosen: the reference, then its name,
    // before the parent had the time to apply the first change
    vm.setPath('provider', 'sms:smsbox');
    vm.setConfig({ provider_label: 'SMSBOX' });

    assert.deepEqual(last(), { action: 'send_sms', provider: 'sms:smsbox', provider_label: 'SMSBOX' });
});

test('every field of a step is kept when several are changed in a row', () => {
    const { vm, last } = panel({ id: 'a', type: 'action', config: { action: 'send_sms' } });

    vm.setPath('provider', 'sms:smsbox');
    vm.setPath('message', 'Bonjour');
    vm.setConfig({ on_error: 'continue' });

    assert.deepEqual(last(), { action: 'send_sms', provider: 'sms:smsbox', message: 'Bonjour', on_error: 'continue' });
});

test('once the parent has applied a change the panel reads the new configuration again', () => {
    const { vm, last } = panel({ id: 'a', type: 'action', config: { action: 'send_sms' } });

    vm.setPath('provider', 'sms:smsbox');
    vm.parentApplies();

    assert.equal(vm.pending, null);
    assert.equal(vm.config.provider, 'sms:smsbox');

    // ... and the next change starts from it
    vm.setConfig({ message: 'Bonjour' });
    assert.deepEqual(last(), { action: 'send_sms', provider: 'sms:smsbox', message: 'Bonjour' });
});

test('a nested value (a campaign action) keeps its other settings', () => {
    const { vm, last } = panel({ id: 'a', type: 'action', config: { action: 'prospect-attach-user', value: { user: 3, extra: 'x' } } });

    vm.setPath('value.user', 8);

    assert.deepEqual(last().value, { user: 8, extra: 'x' });
});

test('choosing a trigger that watches a filter starts with a first condition to read and edit', () => {
    const { vm, last } = panel({ id: 'trigger', type: 'trigger', config: { trigger: 'prospect.created' } });

    vm.changeTrigger('prospect.filter_entered');

    assert.deepEqual(last(), {
        trigger: 'prospect.filter_entered',
        op: 'and',
        items: [{ filters: { incomingCommunications: true } }],
    });
});

test('editing the condition of a trigger keeps the trigger and its options', () => {
    const { vm, last } = panel({
        id: 'trigger',
        type: 'trigger',
        config: { trigger: 'prospect.filter_entered', once_per_subject: true, mode: 'all', filters: { processed: true } },
    });

    vm.setTree({ op: 'or', items: [{ filters: { incomingCommunications: true } }] });

    // The first format of the condition is replaced by the tree; the rest stays
    assert.deepEqual(last(), {
        trigger: 'prospect.filter_entered',
        once_per_subject: true,
        op: 'or',
        items: [{ filters: { incomingCommunications: true } }],
    });
});

test('a condition stored in the first format is shown as a tree', () => {
    const { vm } = panel({ id: 'c', type: 'condition', config: { mode: 'any', filters: { processed: true, createdDaysAgo: 2 } } });

    assert.deepEqual(vm.tree, {
        op: 'or',
        items: [{ filters: { processed: true } }, { filters: { createdDaysAgo: 2 } }],
    });
});

test('changing the action starts a clean configuration', () => {
    const { vm, last } = panel({ id: 'a', type: 'action', config: { action: 'send_sms', provider: 'sms:smsbox', message: 'x' } });

    vm.chooseAction('send_sms');

    assert.deepEqual(last(), { action: 'send_sms' });
});
