const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/components/settings/Claude.vue');
const { descriptor } = parse(fs.readFileSync(filename, 'utf8'));
const { code } = transformSync(descriptor.script.content, {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});

function deferred() {
    let resolve;
    const promise = new Promise(done => { resolve = done; });
    return { promise, resolve };
}

async function flush() {
    await Promise.resolve();
    await Promise.resolve();
    await Promise.resolve();
}

function modal({ show = async () => ({ data: { configured: false } }), update = async () => ({ data: { configured: true } }) } = {}) {
    const reads = [], writes = [], commits = [];
    const dependencies = {
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit: (...args) => commits.push(args) },
        '@/actions/modal': { CLOSE_MODAL: 'closeModal' },
        '@/apis/user/claude': {
            show: (...args) => { reads.push(args); return show(...args); },
            update: (...args) => { writes.push(args); return update(...args); },
        },
    };
    const moduleObject = { exports: {} };
    new Function('require', 'module', 'exports', code)(
        name => {
            if (!(name in dependencies)) throw new Error(`Unexpected dependency: ${name}`);
            return dependencies[name];
        },
        moduleObject,
        moduleObject.exports
    );
    const component = moduleObject.exports.default;
    const vm = { ...component.data(), user: { id: 1 }, $t: key => key };
    for (const [name, method] of Object.entries(component.methods)) vm[name] = method.bind(vm);
    Object.defineProperty(vm, 'canSave', { get: () => component.computed.canSave.call(vm) });
    Object.defineProperty(vm, 'keyStatusMessage', { get: () => component.computed.keyStatusMessage.call(vm) });
    return { vm, component, reads, writes, commits };
}

test('the modal and settings layout templates compile', () => {
    for (const relative of [
        'resources/js/components/settings/Claude.vue',
        'resources/js/components/layouts/setting/Layout.vue',
        'resources/js/components/layouts/Main.vue',
    ]) {
        const file = path.resolve(__dirname, '../..', relative);
        const { descriptor: sfc } = parse(fs.readFileSync(file, 'utf8'));
        const result = compileTemplate({ source: sfc.template.content, filename: file, id: 'claude-setting' });
        assert.deepEqual(result.errors, [], relative);
    }
});

test('opening an existing configuration shows its status with an empty key field', async () => {
    const h = modal({ show: async () => ({ data: { configured: true } }) });
    h.vm.apiKey = 'old-unsaved-key';
    await h.vm.showSetting();
    assert.equal(h.vm.configured, true);
    assert.equal(h.vm.loaded, true);
    assert.equal(h.vm.apiKey, '');
    assert.equal(h.vm.active, true);
    assert.equal(h.vm.showingSetting, false);
    assert.equal(h.vm.canSave, false);
    assert.deepEqual(h.reads, [[]]);
});

test('saving adds or replaces the trimmed personal key and clears it before closing', async () => {
    const h = modal();
    await h.vm.showSetting();
    h.vm.apiKey = '  personal-key  ';
    await h.vm.update();
    assert.deepEqual(h.writes, [['personal-key']]);
    assert.deepEqual(h.commits, [['closeModal']]);
    assert.equal(h.vm.apiKey, '');
    assert.equal(h.vm.active, false);
    assert.equal(h.vm.updatingSetting, false);
    assert.deepEqual(h.vm.user, { id: 1 });
});

test('a failed save keeps the modal open and allows a successful retry', async () => {
    let attempts = 0;
    const h = modal({ update: async () => {
        if (++attempts === 1) throw new Error('Server unavailable');
        return { data: { configured: true } };
    } });
    await h.vm.showSetting();
    h.vm.apiKey = 'personal-key';
    await h.vm.update();
    assert.equal(h.vm.error, 'setting.claude.save_error');
    assert.equal(h.vm.active, true);
    assert.equal(h.vm.updatingSetting, false);
    assert.equal(h.vm.apiKey, 'personal-key');
    assert.deepEqual(h.commits, []);
    await h.vm.update();
    assert.equal(h.vm.error, null);
    assert.equal(h.vm.apiKey, '');
    assert.deepEqual(h.commits, [['closeModal']]);
});

test('blank or oversized values and duplicate submissions do not send a key', async () => {
    const saving = deferred();
    const h = modal({ update: () => saving.promise });
    await h.vm.showSetting();
    for (const invalid of ['  ', 'x'.repeat(4097)]) {
        h.vm.apiKey = invalid;
        await h.vm.update();
    }
    assert.deepEqual(h.writes, []);
    h.vm.apiKey = 'personal-key';
    const pending = h.vm.update();
    await h.vm.update();
    assert.equal(h.writes.length, 1);
    saving.resolve({ data: { configured: true } });
    await pending;
});

test('closing during a read clears the secret and ignores its late response', async () => {
    const reading = deferred();
    const h = modal({ show: () => reading.promise });
    const pending = h.vm.showSetting();
    h.vm.apiKey = 'unsaved-secret';
    h.vm.closeSetting();
    reading.resolve({ data: { configured: true } });
    await pending;
    assert.equal(h.vm.apiKey, '');
    assert.equal(h.vm.configured, false);
    assert.equal(h.vm.active, false);
    assert.equal(h.vm.showingSetting, false);
});

test('changing accounts clears the draft and prevents the previous account status from applying', async () => {
    const oldRead = deferred();
    let reads = 0;
    const h = modal({ show: () => ++reads === 1 ? oldRead.promise : Promise.resolve({ data: { configured: false } }) });
    const oldPending = h.vm.showSetting();
    h.vm.apiKey = 'previous-account-draft';
    h.vm.user = { id: 2 };
    h.component.watch['user.id'].call(h.vm, 2, 1);
    await flush();
    oldRead.resolve({ data: { configured: true } });
    await oldPending;
    assert.equal(h.vm.apiKey, '');
    assert.equal(h.vm.configured, false);
    assert.equal(h.vm.showingSetting, false);
    assert.equal(h.reads.length, 2);
});

test('a previous account save cannot close the new account modal or clear its draft', async () => {
    const oldSave = deferred();
    const h = modal({ update: () => oldSave.promise });
    await h.vm.showSetting();
    h.vm.apiKey = 'previous-account-secret';
    const pending = h.vm.update();
    h.vm.user = { id: 2 };
    h.component.watch['user.id'].call(h.vm, 2, 1);
    await flush();
    h.vm.apiKey = 'new-account-draft';
    oldSave.resolve({ data: { configured: true } });
    await pending;
    assert.equal(h.vm.apiKey, 'new-account-draft');
    assert.equal(h.vm.active, true);
    assert.deepEqual(h.commits, []);
});

test('loading errors stay visible and unmounting clears the draft', async () => {
    const h = modal({ show: async () => { throw new Error('Load failed'); } });
    await h.vm.showSetting();
    assert.equal(h.vm.error, 'setting.claude.load_error');
    assert.equal(h.vm.loaded, false);
    assert.equal(h.vm.active, true);
    assert.equal(h.vm.showingSetting, false);
    h.vm.apiKey = 'unsaved-secret';
    h.component.beforeUnmount.call(h.vm);
    assert.equal(h.vm.apiKey, '');
    assert.equal(h.vm.active, false);
});

test('opening displays the API validation result and keeps the saved key hidden', async () => {
    const h = modal({ show: async () => ({ data: { configured: true, valid: true, status: 'valid' } }) });
    await h.vm.showSetting();
    assert.equal(h.vm.keyValid, true);
    assert.equal(h.vm.keyStatusMessage, 'setting.claude.valid');
    assert.equal(h.vm.apiKey, '');
    h.vm.closeSetting();
    assert.equal(h.vm.keyValid, null);
    assert.equal(h.vm.validationStatus, null);
});

test('invalid keys and an unavailable check have distinct states on opening', async () => {
    for (const [status, valid] of [['invalid', false], ['forbidden', false], ['verification_unavailable', null], ['rate_limited', null]]) {
        const h = modal({ show: async () => ({ data: { configured: true, valid, status } }) });
        await h.vm.showSetting();
        assert.equal(h.vm.keyValid, valid);
        assert.equal(h.vm.keyStatusMessage, `setting.claude.${status}`);
        assert.equal(h.vm.error, null);
        assert.equal(h.vm.configured, true);
    }
});

test('a rejected new key leaves the stored key status and modal intact', async () => {
    const h = modal({
        show: async () => ({ data: { configured: true, valid: true, status: 'valid' } }),
        update: async () => { throw { response: { status: 422, data: { status: 'invalid', valid: false } } }; },
    });
    await h.vm.showSetting();
    h.vm.apiKey = 'invalid-new-key';
    await h.vm.update();
    assert.equal(h.vm.error, 'setting.claude.invalid');
    assert.equal(h.vm.keyValid, true);
    assert.equal(h.vm.keyStatusMessage, 'setting.claude.valid');
    assert.equal(h.vm.apiKey, 'invalid-new-key');
    assert.equal(h.vm.active, true);
    assert.deepEqual(h.commits, []);
});

test('temporary verification failures allow retry and do not label the key invalid', async () => {
    let attempts = 0;
    const h = modal({ update: async () => {
        if (++attempts === 1) throw { response: { status: 503, data: { status: 'verification_unavailable', valid: null } } };
        return { data: { configured: true, valid: true, status: 'valid' } };
    } });
    await h.vm.showSetting();
    h.vm.apiKey = 'personal-key';
    await h.vm.update();
    assert.equal(h.vm.error, 'setting.claude.verification_unavailable');
    assert.equal(h.vm.canSave, true);
    await h.vm.update();
    assert.deepEqual(h.commits, [['closeModal']]);
    assert.equal(h.vm.apiKey, '');
});
