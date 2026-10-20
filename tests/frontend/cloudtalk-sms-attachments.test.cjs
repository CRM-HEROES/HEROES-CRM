const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { File } = require('node:buffer');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

function load(relative, dependencies) {
    const filename = path.resolve(__dirname, '../../resources/js', relative);
    let source = fs.readFileSync(filename, 'utf8');
    if (relative.endsWith('.vue')) source = parse(source).descriptor.script.content;
    const { code } = transformSync(source, {
        babelrc: false, configFile: false, plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const module = { exports: {} };
    new Function('require', 'module', 'exports', code)(key => {
        if (key in dependencies) return dependencies[key];
        if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        if (key.endsWith('.vue')) return {};
        throw new Error('Unexpected dependency: ' + key);
    }, module, module.exports);
    return module.exports;
}

function panel(handler = async () => ({})) {
    const calls = [];
    const component = load('components/prospect/sms/Slide.vue', {
        vuex: { mapGetters: () => ({}) },
        '@/store': { dispatch: async (action, params) => { calls.push({ action, params }); return handler(action, params); } },
        '@/apis/project/prospect': {},
        '@/utils/incoming-communications': {},
    }).default;
    const vm = { ...component.methods };
    Object.assign(vm, component.data.call(vm), {
        project: { slug: 'test' }, prospect: { id: 1 }, prospectsSelected: [],
        canSendCloudtalkSms: true, $t: key => key,
        slideOpen: () => false,
    });
    vm.sms = { source: 'cloudtalk', message: '' };
    Object.defineProperty(vm, 'canSendSms', { get: () => component.computed.canSendSms.call(vm) });
    return { vm, component, calls };
}

function image(name = 'photo.png') {
    return new File(['image content'], name, { type: 'image/png' });
}

test('picker adds and removes images, and resets the input for selecting the same file again', () => {
    const { vm } = panel();
    const file = image();
    const event = { target: { files: [file], value: 'photo.png' } };
    assert.equal(vm.canSendSms, false);
    vm.addAttachments(event);
    assert.equal(event.target.value, '');
    assert.deepEqual(vm.attachments, [file]);
    assert.equal(vm.canSendSms, true);
    vm.removeAttachment(file);
    assert.equal(vm.canSendSms, false);
});

test('unsupported formats, more than five images and excessive total size keep the existing draft', () => {
    const { vm } = panel();
    const draft = image();
    vm.attachments = [draft];
    for (const files of [
        [new File(['document'], 'document.pdf', { type: 'application/pdf' })],
        Array.from({ length: 5 }, () => image()),
        [{ name: 'large.png', type: 'image/png', size: 5 * 1024 * 1024 }],
    ]) {
        vm.addAttachments({ target: { files, value: '' } });
        assert.deepEqual(vm.attachments, [draft]);
        assert.equal(vm.sendError, 'prospect.sms.attachments_limits');
    }
});

test('successful image-only sending clears attachments after the request', async () => {
    const h = panel();
    const file = image();
    h.vm.attachments = [file];
    await h.vm.sendSMS();
    assert.deepEqual(h.calls, [{ action: 'ADD_PROSPECT_SMS', params: { source: 'cloudtalk', message: '', attachments: [file] } }]);
    assert.deepEqual(h.vm.attachments, []);
    assert.equal(h.vm.addingSms, false);
});

test('failed upload retains the text and files and shows the server validation error', async () => {
    const h = panel(async () => { throw { response: { data: { errors: { attachments: ['Too large'] } } } }; });
    const file = image();
    h.vm.sms.message = 'Texte';
    h.vm.attachments = [file];
    await h.vm.sendSMS();
    assert.equal(h.vm.sms.message, 'Texte');
    assert.deepEqual(h.vm.attachments, [file]);
    assert.equal(h.vm.sendError, 'Too large');
    assert.equal(h.vm.addingSms, false);
});

test('double submission is blocked until the upload finishes', async () => {
    let complete;
    const h = panel(() => new Promise(resolve => { complete = resolve; }));
    h.vm.attachments = [image()];
    const pending = h.vm.sendSMS();
    await h.vm.sendSMS();
    assert.equal(h.calls.length, 1);
    complete({});
    await pending;
});

test('bulk sending includes recipients and preserves the stored image links in its history', async () => {
    const attachments = [{ url: '/image/1', file_name: 'photo.png' }];
    const h = panel(async () => ({ count: 2, attachments }));
    h.vm.prospect = null;
    h.vm.prospectsSelected = [1, 2];
    h.vm.attachments = [image()];
    await h.vm.sendSMS();
    assert.equal(h.calls[0].action, 'BULK_PROSPECT_SMS');
    assert.deepEqual(h.calls[0].params.prospects, [1, 2]);
    assert.deepEqual(h.vm.bulkSms[0].attachments, attachments);
});

test('operator and prospect changes clear attachments while refreshing the same prospect preserves them', async () => {
    const h = panel();
    const file = image();
    h.vm.attachments = [file];
    await h.component.watch.prospect.call(h.vm, { id: 1 }, { id: 1 });
    assert.deepEqual(h.vm.attachments, [file]);
    await h.component.watch.prospect.call(h.vm, { id: 2 }, { id: 1 });
    assert.deepEqual(h.vm.attachments, []);
    h.vm.attachments = [file];
    h.component.watch['sms.source'].call(h.vm);
    assert.deepEqual(h.vm.attachments, []);
});

test('API uses multipart for individual and bulk uploads and keeps plain SMS as JSON', async () => {
    const calls = [];
    const api = load('apis/project/prospect/sms.js', {
        '@/apis/api.service': { post: async (...args) => { calls.push(args); } },
    }).default;
    const file = image();
    await api.create('test', 1, { source: 'cloudtalk', message: '', attachments: [file] });
    await api.bulk('test', { source: 'cloudtalk', message: 'Photo', attachments: [file], prospects: [1, 2] });
    const plain = { source: 'cloudtalk', message: 'Bonjour' };
    await api.create('test', 1, plain);
    assert.ok(calls[0][1] instanceof FormData);
    assert.equal(calls[0][1].get('attachments[]').name, 'photo.png');
    assert.equal(await calls[0][1].get('attachments[]').text(), 'image content');
    assert.equal(calls[0][1].get('message'), '');
    assert.deepEqual(calls[1][1].getAll('prospects[]'), ['1', '2']);
    assert.equal(calls[2][1], plain);
});
