const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { File } = require('node:buffer');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

function load(relative, dependencies) {
    const filename = path.resolve(__dirname, '../../resources/js', relative);
    const { descriptor, errors } = parse(fs.readFileSync(filename, 'utf8'));
    assert.deepEqual(errors, []);
    const { code } = transformSync(descriptor.script.content, {
        babelrc: false, configFile: false, plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const module = { exports: {} };
    new Function('require', 'module', 'exports', code)(key => {
        if (key in dependencies) return dependencies[key];
        if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        if (key.endsWith('.vue')) return {};
        throw new Error('Unexpected dependency: ' + key);
    }, module, module.exports);
    return module.exports.default;
}

function composer(handler = async () => ({}), overrides = {}) {
    const calls = [];
    const commits = [];
    const service = Object.fromEntries(['send', 'sendBulk'].map(method => [method, async (...args) => {
        calls.push({ method, args });
        return handler(method, ...args);
    }]));
    const component = load('components/prospect/email/Composer.vue', {
        vuex: { mapGetters: () => ({}) },
        '@/store': { dispatch: async () => ({}), commit: (...args) => commits.push(args) },
        '@/apis/project/prospect/email': service,
    });
    const vm = {
        ...component.methods,
        initialDraft: { prospect: 12, prospects: null, to: 'lea@example.com', subject: 'Suivi', body: 'Bonjour', category: 'Suivi prospect', ...overrides },
    };
    Object.assign(vm, component.data.call(vm), { project: { slug: 'test' }, messageTemplates: [] });
    Object.defineProperty(vm, 'canSend', { get: () => component.computed.canSend.call(vm) });
    return { vm, calls, commits, component };
}

function panel(fetch = async () => ({ data: { data: [] } })) {
    const commits = [];
    const component = load('components/prospect/message/Slide.vue', {
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit: (...args) => commits.push(args) },
        '@/apis/project/prospect': { get: fetch },
        '@/utils/incoming-communications': {},
    });
    const vm = { ...component.methods };
    Object.assign(vm, component.data.call(vm), {
        prospect: { id: 12, first_name: 'Léa', email: 'lea@example.com' },
        project: { slug: 'test' }, prospectsSelected: [], slideOpen: () => true,
    });
    Object.defineProperty(vm, 'emailSelectionKey', { get: () => component.computed.emailSelectionKey.call(vm) });
    return { vm, commits, component };
}

const attachment = (name = 'document.pdf') => new File(['attachment content'], name, { type: 'application/pdf' });

test('each of the three reasons opens its form inside the slide without opening a modal', () => {
    for (let index = 0; index < 3; index++) {
        const { vm, commits } = panel();
        const reason = vm.emailReasons[index];
        vm.tab = 1;
        vm.openEmailComposer(reason);
        assert.equal(vm.tab, 3);
        assert.equal(vm.emailReturnTab, 1);
        assert.equal(vm.emailDraft.category, reason.label);
        assert.equal(vm.emailDraft.subject, reason.subject);
        assert.equal(vm.emailDraft.to, 'lea@example.com');
        assert.equal(vm.emailDraft.body, 'Bonjour Léa,\n\n');
        assert.equal(vm.emailDraft.prospect, 12);
        assert.equal(vm.emailDraft.prospects, null);
        assert.equal(vm.emailDraftKey, 1);
        assert.deepEqual(commits, []);
    }
});

test('bulk form includes only selected prospects with an email address', () => {
    const { vm } = panel();
    vm.prospect = null;
    vm.selectedProspects = [
        { id: 1, email: 'one@example.com' }, { id: 2, email: '' },
        { id: 3, email: 'three@example.com' }, { id: 4 },
    ];
    vm.openEmailComposer(vm.emailReasons[0]);
    assert.deepEqual(vm.emailDraft.prospects, [1, 3]);
    assert.equal(vm.emailDraft.to, 'one@example.com, three@example.com');
    assert.equal(vm.emailDraft.prospect, null);
});

test('bulk composer stays closed while selected recipients are loading', () => {
    const { vm } = panel();
    vm.prospect = null;
    vm.fetchingProspects = true;
    vm.selectedProspects = [{ id: 1, email: 'old@example.com' }];
    vm.openEmailComposer(vm.emailReasons[0]);
    assert.equal(vm.tab, 0);
    assert.equal(vm.emailDraft, null);
});

test('changing bulk selection refreshes recipients and clears the previous draft', () => {
    const { vm, component } = panel();
    vm.prospect = null;
    vm.tab = 3;
    vm.emailDraft = { to: 'old@example.com' };
    let requests = 0;
    vm.fetchSelectedProspects = () => { requests++; };
    component.watch.emailSelectionKey.call(vm);
    assert.equal(vm.emailDraft, null);
    assert.equal(vm.tab, 0);
    assert.equal(requests, 1);
    vm.slideOpen = () => false;
    component.watch.emailSelectionKey.call(vm);
    assert.equal(requests, 1);
});

test('a late response for a previous bulk selection cannot replace current recipients', async () => {
    const pending = [];
    const { vm } = panel((project, options) => new Promise(resolve => pending.push({ project, options, resolve })));
    vm.prospect = null;
    vm.prospectsSelected = [1];
    vm.selectedProspects = [{ id: 99, email: 'cached@example.com' }];
    const first = vm.fetchSelectedProspects();
    assert.deepEqual(vm.selectedProspects, []);
    assert.equal(vm.fetchingProspects, true);
    vm.prospectsSelected = [2];
    const second = vm.fetchSelectedProspects();
    assert.deepEqual(JSON.parse(pending[0].options.params.filters).ids, [1]);
    assert.deepEqual(JSON.parse(pending[1].options.params.filters).ids, [2]);
    const current = [{ id: 2, email: 'current@example.com' }];
    pending[1].resolve({ data: { data: current } });
    await second;
    assert.deepEqual(vm.selectedProspects, current);
    assert.equal(vm.fetchingProspects, false);
    pending[0].resolve({ data: { data: [{ id: 1, email: 'old@example.com' }] } });
    await first;
    assert.deepEqual(vm.selectedProspects, current);
    vm.openEmailComposer(vm.emailReasons[0]);
    assert.deepEqual(vm.emailDraft.prospects, [2]);
    assert.equal(vm.emailDraft.to, 'current@example.com');
});

test('selected recipients from a previous project are ignored after project navigation', async () => {
    let complete;
    const { vm } = panel(() => new Promise(resolve => { complete = resolve; }));
    vm.prospect = null;
    vm.prospectsSelected = [1];
    const pending = vm.fetchSelectedProspects();
    vm.project = { slug: 'other-project' };
    complete({ data: { data: [{ id: 1, email: 'previous-project@example.com' }] } });
    await pending;
    assert.deepEqual(vm.selectedProspects, []);
});

test('attachments may be added, removed and selected again after the picker resets', () => {
    const { vm } = composer();
    const file = attachment();
    const event = { target: { files: [file], value: file.name } };
    vm.addAttachments(event);
    assert.equal(event.target.value, '');
    assert.deepEqual(vm.attachments, [file]);
    vm.removeAttachment(file);
    assert.deepEqual(vm.attachments, []);
    vm.addAttachments(event);
    assert.deepEqual(vm.attachments, [file]);
});

test('file count includes persisted template attachments and size validation preserves the draft', () => {
    const { vm } = composer();
    const original = attachment();
    vm.attachments = [original];
    vm.templateAttachments = Array.from({ length: 9 }, (_, id) => ({ id, size: 5 }));
    vm.addAttachments({ target: { files: [attachment('excess.pdf')], value: '' } });
    assert.deepEqual(vm.attachments, [original]);
    assert.match(vm.sendError, /10 fichiers/);
    vm.templateAttachments = [];
    vm.addAttachments({ target: { files: [{ name: 'large.pdf', size: 10 * 1024 * 1024 + 1 }], value: '' } });
    assert.deepEqual(vm.attachments, [original]);
    assert.match(vm.sendError, /10 Mo/);
});

test('template selection fills the draft, replaces template files and preserves local files', () => {
    const { vm } = composer();
    const uploaded = attachment();
    const persisted = { id: 'stored-file', name: 'brochure.pdf', size: 10 };
    vm.attachments = [uploaded];
    vm.templateAttachments = [{ id: 'previous-file', size: 10 }];
    vm.plainText = body => body;
    vm.tab = 1;
    vm.setTemplate({ id: 7, subject: 'Projet {prospect.first_name}', body: 'Bonjour {prospect.first_name}', attachments: [persisted] });
    assert.equal(vm.draft.subject, 'Projet {prospect.first_name}');
    assert.equal(vm.draft.body, 'Bonjour {prospect.first_name}');
    assert.equal(vm.templateId, 7);
    assert.deepEqual(vm.templateAttachments, [persisted]);
    assert.deepEqual(vm.attachments, [uploaded]);
    assert.equal(vm.tab, 0);
    vm.removeTemplateAttachment(persisted);
    assert.deepEqual(vm.templateAttachments, []);
    assert.deepEqual(vm.attachments, [uploaded]);
});

test('single send uploads local files and only selected template IDs then clears the sent content', async () => {
    const { vm, calls } = composer();
    vm.attachments = [attachment()];
    vm.templateId = 7;
    vm.templateAttachments = [{ id: 'stored-file', name: 'brochure.pdf', size: 10 }];
    await vm.send();
    assert.equal(calls.length, 1);
    const { method, args: [project, prospect, payload] } = calls[0];
    assert.equal(method, 'send');
    assert.equal(project, 'test');
    assert.equal(prospect, 12);
    assert.equal(payload.get('to'), 'lea@example.com');
    assert.equal(payload.get('body'), 'Bonjour');
    assert.equal(payload.get('subject'), 'Suivi');
    assert.equal(payload.get('category'), 'Suivi prospect');
    assert.equal(payload.get('template_id'), '7');
    assert.deepEqual(payload.getAll('template_attachment_ids[]'), ['stored-file']);
    assert.equal(payload.get('attachments[]').name, 'document.pdf');
    assert.equal(await payload.get('attachments[]').text(), 'attachment content');
    assert.deepEqual(payload.getAll('prospects[]'), []);
    assert.equal(vm.draft.body, '');
    assert.deepEqual(vm.attachments, []);
    assert.deepEqual(vm.templateAttachments, []);
    assert.equal(vm.templateId, null);
    assert.equal(vm.sent, true);
    assert.equal(vm.sending, false);
});

test('bulk sends use the bulk endpoint with all eligible prospect IDs and attachments', async () => {
    const { vm, calls } = composer(async () => ({}), {
        prospect: null, prospects: [1, 3], to: 'one@example.com, three@example.com',
    });
    vm.attachments = [attachment()];
    await vm.send();
    assert.equal(calls.length, 1);
    assert.equal(calls[0].method, 'sendBulk');
    assert.equal(calls[0].args[0], 'test');
    const payload = calls[0].args[1];
    assert.deepEqual(payload.getAll('prospects[]'), ['1', '3']);
    assert.equal(payload.get('attachments[]').name, 'document.pdf');
});

test('sending errors keep draft text and attachments available for retry', async () => {
    const { vm, calls } = composer(async () => {
        throw { response: { data: { errors: { attachments: ['Fichier trop volumineux'] } } } };
    });
    const file = attachment();
    vm.attachments = [file];
    vm.templateId = 7;
    vm.templateAttachments = [{ id: 'stored-file', size: 10 }];
    await vm.send();
    assert.equal(calls.length, 1);
    assert.equal(vm.draft.body, 'Bonjour');
    assert.deepEqual(vm.attachments, [file]);
    assert.equal(vm.templateId, 7);
    assert.equal(vm.templateAttachments.length, 1);
    assert.equal(vm.sendError, 'Fichier trop volumineux');
    assert.equal(vm.sent, false);
    assert.equal(vm.sending, false);
});

test('duplicate submission and attachment changes are blocked until sending finishes', async () => {
    let complete;
    const { vm, calls } = composer(() => new Promise(resolve => { complete = resolve; }));
    const file = attachment();
    vm.attachments = [file];
    const pending = vm.send();
    await vm.send();
    vm.removeAttachment(file);
    vm.addAttachments({ target: { files: [attachment('second.pdf')], value: '' } });
    assert.equal(calls.length, 1);
    assert.deepEqual(vm.attachments, [file]);
    complete({});
    await pending;
    assert.equal(vm.sending, false);
});

test('blank content and empty bulk recipients prevent sending', async () => {
    const { vm, calls } = composer();
    for (const field of ['to', 'subject', 'body']) {
        const original = vm.draft[field];
        vm.draft[field] = '   ';
        assert.equal(vm.canSend, false);
        await vm.send();
        vm.draft[field] = original;
    }
    vm.draft.prospects = [];
    assert.equal(vm.canSend, false);
    await vm.send();
    assert.deepEqual(calls, []);
});
