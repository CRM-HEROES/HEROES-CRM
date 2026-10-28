const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { File } = require('node:buffer');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

function load(relative, dependencies = {}) {
    let source = fs.readFileSync(path.resolve(__dirname, '../../resources/js', relative), 'utf8');
    if (relative.endsWith('.vue')) source = parse(source).descriptor.script.content;
    const { code } = transformSync(source, {
        babelrc: false, configFile: false, plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const module = { exports: {} };
    new Function('require', 'module', 'exports', 'File', code)(key => {
        if (key in dependencies) return dependencies[key];
        if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        if (key.endsWith('.vue')) return {};
        throw new Error('Unexpected dependency: ' + key);
    }, module, module.exports, File);
    return module.exports.default;
}

test('template upload serializes subject and binary files without stored paths', async () => {
    const calls = [];
    const service = load('apis/project/message-template.js', {
        '@/apis/api.service': { post: async (...args) => calls.push(args) },
    });
    const file = new File(['offer bytes'], 'offre.pdf', { type: 'application/pdf' });
    await service.create('test', { name: 'Offre', subject: 'Notre offre', body: 'Bonjour', attachments: [file] });
    assert.equal(calls[0][0], 'project/test/message-template');
    const payload = calls[0][1];
    assert.equal(payload.get('subject'), 'Notre offre');
    assert.equal(await payload.get('attachments[]').text(), 'offer bytes');
    assert.equal(payload.get('attachments[]').name, 'offre.pdf');
    assert.equal(payload.has('_method'), false);
});

test('template update retains selected IDs, uploads new files and explicitly encodes removing every file', async () => {
    const calls = [];
    const service = load('apis/project/message-template.js', {
        '@/apis/api.service': { post: async (...args) => calls.push(args) },
    });
    const file = new File(['new'], 'new.txt');
    await service.update('test', 5, {
        id: 5, attachments: [{ id: 'saved-id', name: 'old.txt', path: 'private/path' }, file],
    });
    assert.equal(calls[0][1].get('_method'), 'PUT');
    assert.deepEqual(calls[0][1].getAll('retained_attachment_ids[]'), ['saved-id']);
    assert.equal(calls[0][1].has('path'), false);
    assert.equal(calls[0][1].get('attachments[]').name, 'new.txt');

    await service.update('test', 5, { attachments: [] });
    assert.equal(calls[1][1].get('retained_attachment_ids'), '');
    await service.update('test', 5, { order: 2 });
    assert.equal(calls[2][1].has('retained_attachment_ids'), false);
    assert.equal(calls[2][1].get('order'), '2');
});

test('attachment picker preserves selection when file count or size exceeds limits', () => {
    const component = load('components/message-template/Attachments.vue');
    const selected = [{ id: 'saved-id', name: 'saved.pdf' }];
    const emitted = [];
    const vm = {
        ...component.methods, attachments: selected, disabled: false, error: '',
        $emit: (...args) => emitted.push(args),
    };
    for (const files of [
        Array.from({ length: 10 }, () => new File(['x'], 'x.txt')),
        [{ name: 'large.pdf', size: 10 * 1024 * 1024 + 1 }],
    ]) {
        const event = { target: { files, value: 'selected' } };
        vm.add(event);
        assert.equal(event.target.value, '');
        assert.ok(vm.error);
        assert.equal(emitted.length, 0);
        assert.equal(vm.attachments, selected);
    }
    const file = new File(['valid'], 'valid.txt');
    vm.add({ target: { files: [file], value: '' } });
    assert.deepEqual(emitted[0], ['update:attachments', [...selected, file]]);
    assert.equal(vm.error, '');
    vm.remove(0);
    assert.deepEqual(emitted[1], ['update:attachments', []]);
});

test('failed template creation keeps form and attachments available for retry', async () => {
    const file = new File(['valid'], 'valid.txt');
    const component = load('components/message-template/add/Layout.vue', {
        vuex: { mapGetters: () => ({}) },
        '@/store': {
            dispatch: async () => { throw { response: { data: { message: 'Envoi impossible' } } }; },
            commit: () => assert.fail('Failed request must not close the form'),
        },
    });
    const vm = { ...component.methods };
    Object.assign(vm, component.data.call(vm));
    const draft = { name: 'Modèle', body: 'Bonjour', subject: 'Offre', attachments: [file] };
    vm.messageTemplate = draft;
    await vm.storeMessageTemplate();
    assert.equal(vm.messageTemplate, draft);
    assert.equal(vm.saveError, 'Envoi impossible');
    assert.equal(vm.addingMessageTemplate, false);
});

test('template store replaces temporary files with metadata returned by the server', async () => {
    const saved = { id: 5, name: 'Offre', attachments: [{ id: 'persisted', name: 'offer.pdf' }] };
    const commits = [];
    const store = load('store/project/message-template.js', {
        '@/apis/project/message-template': { update: async () => ({ data: saved }) },
    });
    const result = await store.actions.UPDATE_MESSAGE_TEMPLATE({
        state: { project: { slug: 'test' } }, commit: (...args) => commits.push(args),
    }, { id: 5, attachments: [new File(['offer'], 'offer.pdf')] });
    assert.equal(result, saved);
    assert.deepEqual(commits, [['UPDATE_MESSAGE_TEMPLATE', saved], ['SET_MESSAGE_TEMPLATE', saved]]);
});
