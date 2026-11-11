const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const source = fs.readFileSync(path.resolve(__dirname, '../../resources/js/components/workflow/FieldInput.vue'), 'utf8');
const descriptor = parse(source).descriptor;

test('the template compiles, and the warning does not cut the chain of inputs', () => {
    const { errors, code } = compileTemplate({ source: descriptor.template.content, id: 'x', filename: 'FieldInput.vue' });

    assert.deepEqual(errors, []);

    // The warning is after the last "else": a v-if in the middle would give the
    // text input to every type of field, next to the right one
    const template = descriptor.template.content;
    assert.ok(template.indexOf('v-if="emptyList && type') > template.indexOf('<input v-else type="text"'));
});

test('ticking an item adds it, ticking it again removes it, others are kept', () => {
    const { code } = transformSync(descriptor.script.content, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const stubs = { vuex: { mapGetters: () => ({}) }, '@/store': {}, '@/apis/project/workflow': {}, '@/apis/project/label': {} };
    const module_ = { exports: {} };
    new Function('module', 'exports', 'require', code)(module_, module_.exports, (id) => stubs[id] || {});
    const component = module_.exports.default;

    const emitted = [];
    const vm = { modelValue: [3], $emit: (e, v) => emitted.push(v) };
    for (const [name, method] of Object.entries(component.methods)) vm[name] = method.bind(vm);

    vm.toggleId(5);
    vm.modelValue = [3, 5];
    vm.toggleId(3);

    assert.deepEqual(emitted, [[3, 5], [5]]);
    assert.equal(vm.isChosen(5), true);
    assert.equal(vm.isChosen(9), false);
});

test('the operators are loaded again when the action changes (SMS instead of email)', () => {
    const { code } = transformSync(descriptor.script.content, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const stubs = { vuex: { mapGetters: () => ({}) }, '@/store': {}, '@/apis/project/workflow': {}, '@/apis/project/label': {} };
    const module_ = { exports: {} };
    new Function('module', 'exports', 'require', code)(module_, module_.exports, (id) => stubs[id] || {});
    const component = module_.exports.default;

    const calls = [];
    const vm = { type: 'provider', providers: [{ ref: 'email:project' }], loadProviders: () => calls.push('load') };

    component.watch['field.capability'].call(vm);

    assert.deepEqual(vm.providers, []); // the email operator is not offered for a SMS
    assert.deepEqual(calls, ['load']);

    // And the editor starts a new field for each action
    const editor = fs.readFileSync(path.resolve(__dirname, '../../resources/js/components/workflow/NodeEditor.vue'), 'utf8');
    assert.ok(editor.includes(`v-for="field in actionDef.fields" :key="config.action + ':' + field.key"`));
});

test('an operator that is not configured opens its window instead of being chosen, and is chosen once configured', async () => {
    const { code } = transformSync(descriptor.script.content, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const committed = [];
    const stubs = {
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit: (...args) => committed.push(args) },
        '@/apis/project/workflow': {},
        '@/apis/project/label': {},
        '@/actions/modal': { OPEN_MODAL: 'openModal' },
    };
    const module_ = { exports: {} };
    new Function('module', 'exports', 'require', code)(module_, module_.exports, (id) => stubs[id] || {});
    const component = module_.exports.default;

    const emitted = [];
    const flashes = [];
    global.flashWarning = (m) => flashes.push(['warning', m.body]);
    global.flash = (m) => flashes.push(['ok', m.body]);

    const providers = [
        { ref: 'email:project', label: 'Brevo (email)', configured: false, setup: 'setting-email' },
        { ref: 'sms:smsbox', label: 'SMSBOX', configured: true, setup: 'setting-smsbox' },
        { ref: 'line:1', label: 'Line', configured: false, setup: null },
    ];
    const vm = {
        providers,
        modelValue: null,
        pendingProvider: null,
        $refs: { provider: { value: 'email:project' } },
        $emit: (event, value) => emitted.push([event, value]),
        $t: (key, params) => key + (params ? JSON.stringify(params) : ''),
    };
    for (const [name, method] of Object.entries(component.methods)) vm[name] = method.bind(vm);
    vm.loadProviders = async () => {}; // the list is the one above: no request

    // Not configured: its window opens, nothing is chosen, the list goes back to what it was
    vm.chooseProvider('email:project');
    assert.deepEqual(committed, [['openModal', 'setting-email']]);
    assert.deepEqual(emitted, []);
    assert.equal(vm.$refs.provider.value, '');
    assert.equal(vm.pendingProvider.ref, 'email:project');

    // The window is closed and it is configured now: it is chosen
    providers[0].configured = true;
    await vm.afterSetup();
    assert.deepEqual(emitted, [['update:modelValue', 'email:project'], ['provider-label', 'Brevo (email)']]);
    assert.equal(vm.pendingProvider, null);
    assert.equal(flashes.at(-1)[0], 'ok');

    // The window is closed without configuring: not chosen, and the user is told
    emitted.length = 0;
    providers[0].configured = false;
    vm.chooseProvider('email:project');
    await vm.afterSetup();
    assert.deepEqual(emitted, []);
    assert.equal(flashes.at(-1)[0], 'warning');

    // A configured operator is chosen at once; one without window is explained
    vm.chooseProvider('sms:smsbox');
    assert.deepEqual(emitted.map((e) => e[1]), ['sms:smsbox', 'SMSBOX']);
    vm.chooseProvider('line:1');
    assert.equal(flashes.at(-1)[0], 'warning');

    // What the list says
    assert.equal(vm.providerLabel(providers[0]), 'Brevo (email) (workflow.provider_setup.to_configure)');
    assert.equal(vm.providerLabel(providers[1]), 'SMSBOX');
});
