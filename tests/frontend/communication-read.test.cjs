const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { baseParse } = require('@vue/compiler-dom');
const { transformSync } = require('@babel/core');

const sourceRoot = path.resolve(__dirname, '../../resources/js');
const compiled = new Map();
function load(filename, dependencies) {
    if (!compiled.has(filename)) {
        let source = fs.readFileSync(path.join(sourceRoot, filename), 'utf8');
        if (filename.endsWith('.vue')) source = parse(source).descriptor.script.content;
        compiled.set(filename, transformSync(source, {
            babelrc: false, configFile: false,
            plugins: ['@babel/plugin-transform-modules-commonjs'],
        }).code);
    }
    const module = { exports: {} };
    new Function('require', 'module', 'exports', compiled.get(filename))(key => {
        if (key in dependencies) return dependencies[key];
        if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        return {};
    }, module, module.exports);
    return module.exports;
}

test('read receipts send only displayed ids, deduplicate and notify the badge', async () => {
    const requests = [], events = [], commits = [];
    const { markCommunicationsRead } = load('utils/incoming-communications.js', {
        '@/store': { commit: (...args) => commits.push(args) },
        '@/apis/project/incoming-communication': { read: async (...args) => {
            requests.push(args); return { data: { count: 4 } };
        } },
        '@/utils/event-bus': { emit: (...args) => events.push(args) },
    });
    assert.equal(await markCommunicationsRead('test', 2, 'sms', [{ id: 1 }, { id: '1' }, { id: 3 }]), true);
    assert.deepEqual(requests, [['test', 2, 'sms', [1, 3]]]);
    assert.deepEqual(commits, [['MARK_PROSPECT_COMMUNICATIONS_READ', {
        projectSlug: 'test', prospectId: 2, type: 'sms', ids: [1, 3],
    }]]);
    assert.deepEqual(events, [['incoming-communications-read', { projectSlug: 'test', count: 4 }]]);
});

test('failed or empty receipts do not clear the badge or prevent reading', async () => {
    const events = [], commits = [];
    const { markCommunicationsRead } = load('utils/incoming-communications.js', {
        '@/store': { commit: (...args) => commits.push(args) },
        '@/apis/project/incoming-communication': { read: async () => { throw new Error('offline'); } },
        '@/utils/event-bus': { emit: (...args) => events.push(args) },
    });
    assert.equal(await markCommunicationsRead('test', 2, 'sms', []), false);
    assert.equal(await markCommunicationsRead('test', 2, 'sms', [{ id: 1 }]), false);
    assert.deepEqual(events, []);
    assert.deepEqual(commits, []);
});

test('long conversations are marked in batches without marking unseen arrivals', async () => {
    const requests = [];
    const { markCommunicationsRead } = load('utils/incoming-communications.js', {
        '@/store': { commit() {} },
        '@/apis/project/incoming-communication': { read: async (...args) => {
            requests.push(args); return { data: { count: 1 } };
        } },
        '@/utils/event-bus': { emit() {} },
    });
    await markCommunicationsRead('test', 2, 'email', Array.from({ length: 501 }, (_, id) => ({ id: id + 1 })));
    assert.equal(requests.length, 2);
    assert.equal(requests[0][3].length, 500);
    assert.deepEqual(requests[1][3], [501]);
});

function panel(kind) {
    const calls = [];
    const filename = kind === 'voicemail'
        ? 'components/prospect/interaction/InteractionRow.vue'
        : `components/prospect/${kind === 'sms' ? 'sms' : 'message'}/Slide.vue`;
    const component = load(filename, {
        vuex: { mapGetters: () => ({}) },
        '@/utils/incoming-communications': { markCommunicationsRead: async (...args) => { calls.push(args); return true; } },
    }).default;
    const vm = {
        ...component.methods,
        project: { slug: 'test' }, prospect: { id: 2 }, messageThread: { id: 8 },
        name: 'panel', tab: 1, frameTab: 0, selectedProspectsTab: 0,
        slideOpen: () => true, $nextTick: async () => {},
        prospectSms: [{ id: 1, prospect_id: 2 }, { id: 2, prospect_id: 99 }],
        prospectMessages: [
            { id: 3, prospect_id: 2, thread_id: 8 },
            { id: 4, prospect_id: 2, thread_id: 99 },
        ],
        callOutcome: 'voicemail', interaction: { id: 5, prospect_id: 2, from_user: false },
        lastReadVoicemailId: null,
    };
    return { vm, calls };
}

test('opening or refreshing SMS does not process incoming messages', async () => {
    const h = panel('sms');
    const component = load('components/prospect/sms/Slide.vue', { vuex: { mapGetters: () => ({}) } }).default;
    h.vm.$refs = {};
    await component.watch.prospectSms.call(h.vm, h.vm.prospectSms, []);
    await component.watch.tab.call(h.vm, 1);
    assert.deepEqual(h.calls, []);
    assert.equal(component.methods.markDisplayedSmsRead, undefined);
});

test('email receipts contain only the displayed prospect and thread', async () => {
    const h = panel('email');
    await h.vm.markDisplayedMessagesRead();
    assert.deepEqual(h.calls, [['test', 2, 'email', [{ id: 3, prospect_id: 2, thread_id: 8 }]]]);
});

test('background email panels and a changed prospect do not mark messages read', async () => {
    const h = panel('email');
    h.vm.slideOpen = () => false;
    await h.vm.markDisplayedMessagesRead();
    assert.deepEqual(h.calls, []);
    h.vm.slideOpen = () => true;
    h.vm.$nextTick = async () => { h.vm.prospect = { id: 99 }; };
    await h.vm.markDisplayedMessagesRead();
    assert.deepEqual(h.calls, []);
});

test('playing a voicemail does not process it', () => {
    const h = panel('voicemail');
    h.vm.$refs = { audio: { play: () => Promise.resolve() } };
    h.vm.toggleAudio();
    assert.deepEqual(h.calls, []);
    const source = fs.readFileSync(path.join(sourceRoot, 'components/prospect/interaction/InteractionRow.vue'), 'utf8');
    assert.doesNotMatch(source, /markCommunicationsRead|markVoicemailRead/);
});

function processor(type, handler = async () => true) {
    const calls = [], historyRequests = [];
    const component = load('components/prospect/ProcessCommunications.vue', {
        '@/utils/incoming-communications': { markCommunicationsRead: async (...args) => {
            calls.push(args); return handler(...args);
        } },
        '@/apis/project/incoming-communication': {
            smsHistory: async (...args) => {
                historyRequests.push(args);
                return { data: [{ id: 1, message: 'Hello', user_name: 'Agent' }] };
            },
        },
    }).default;
    const vm = {
        ...component.data(), ...component.methods,
        projectSlug: 'test', prospect: { id: 2 }, type, disabled: false,
        showHistory: false,
        records: [
            { id: 1, prospect_id: 2, from_user: false, call_outcome: 'voicemail' },
            { id: 2, prospect_id: 2, from_user: true, call_outcome: 'voicemail' },
            { id: 3, prospect_id: 99, from_user: false, call_outcome: 'voicemail' },
        ],
    };
    Object.defineProperty(vm, 'pendingRecords', { get: () => component.computed.pendingRecords.call(vm) });
    return { vm, calls, historyRequests, component };
}

test('the process button handles only incoming messages of the displayed prospect', async () => {
    for (const type of ['sms', 'voicemail']) {
        const h = processor(type);
        if (type === 'voicemail') h.vm.records.push({ id: 4, prospect_id: 2, from_user: false, call_outcome: 'missed' });
        await h.vm.process();
        assert.deepEqual(h.calls, [['test', 2, type, [h.vm.records[0]]]]);
        assert.deepEqual(h.vm.pendingRecords, []);
    }
});

test('one SMS processing click marks only the most recent unread SMS', async () => {
    const h = processor('sms');
    h.vm.records = [
        { id: 10, prospect_id: 2, from_user: false, created_at: '2026-11-01T10:00:00Z' },
        { id: 12, prospect_id: 2, from_user: false, created_at: '2026-11-01T12:00:00Z' },
        { id: 11, prospect_id: 2, from_user: false, created_at: '2026-11-01T11:00:00Z' },
    ];
    h.vm.prospect.unread_communications = { sms: [10, 11, 12] };

    await h.vm.process();

    assert.deepEqual(h.calls[0][3].map(record => record.id), [12]);
    assert.deepEqual(h.vm.pendingRecords.map(record => record.id), [10, 11]);
});

test('messages received during processing still require a new click', async () => {
    let finish;
    const h = processor('sms', () => new Promise(resolve => { finish = resolve; }));
    const request = h.vm.process();
    h.vm.records.push({ id: 4, prospect_id: 2, is_incoming: true });
    await h.vm.process(); // A double click must not start a second request.
    finish(true);
    await request;
    assert.equal(h.calls.length, 1);
    assert.deepEqual(h.vm.pendingRecords.map(record => record.id), [4]);
});

test('failed processing leaves messages pending and allows retrying', async () => {
    let succeeds = false;
    const h = processor('sms', async () => succeeds);
    h.vm.disabled = true;
    await h.vm.process();
    assert.deepEqual(h.calls, []);
    h.vm.disabled = false;
    await h.vm.process();
    assert.equal(h.vm.error, true);
    assert.equal(h.vm.processing, false);
    assert.equal(h.vm.pendingRecords.length, 1);
    succeeds = true;
    await h.vm.process();
    assert.equal(h.vm.error, false);
    assert.equal(h.vm.pendingRecords.length, 0);
});

test('SMS processing history loads from the server and refreshes after processing', async () => {
    const h = processor('sms');
    h.vm.showHistory = true;
    await h.component.created.call(h.vm);
    assert.deepEqual(h.historyRequests, [['test', 2]]);
    assert.deepEqual(h.vm.history, [{ id: 1, message: 'Hello', user_name: 'Agent' }]);

    await h.vm.process();
    assert.equal(h.historyRequests.length, 2);
    assert.deepEqual(h.vm.history[0], { id: 1, message: 'Hello', user_name: 'Agent' });
});

test('the unread metadata identifies voicemails even when provider fields differ', async () => {
    const h = processor('voicemail');
    h.vm.prospect.unread_communications = { voicemail: ['4'] };
    h.vm.records.push({ id: 4, prospect_id: 2, data: { has_voicemail: true } });
    await h.vm.process();
    assert.deepEqual(h.calls[0][3].map(record => record.id), [4]);
});

test('SMS messages stay above the form, followed by actions and history', () => {
    const smsSource = fs.readFileSync(path.join(sourceRoot, 'components/prospect/sms/Slide.vue'), 'utf8');
    const formIndex = smsSource.indexOf('<form');
    const processIndex = smsSource.indexOf('<process-communications');
    const smsListIndex = smsSource.indexOf('class="hc-prospect-sms-list"');
    assert.ok(smsListIndex >= 0 && formIndex > smsListIndex && processIndex > formIndex);
    assert.match(smsSource.slice(processIndex), /show-history/);
    const processorTemplate = parse(fs.readFileSync(
        path.join(sourceRoot, 'components/prospect/ProcessCommunications.vue'), 'utf8'
    )).descriptor.template.content;
    assert.ok(processorTemplate.indexOf('<buttons>') < processorTemplate.indexOf('hc-process-communications-history'));
    assert.match(processorTemplate, /not_processed/);
    assert.match(processorTemplate, /processed_by/);
    assert.match(processorTemplate, /class="hc-button-warning"/);
    assert.match(processorTemplate, /class="hc-button-secondary"/);

    for (const [kind, type] of [['interaction', 'voicemail'], ['message', null]]) {
        const source = fs.readFileSync(path.join(sourceRoot, `components/prospect/${kind}/Slide.vue`), 'utf8');
        const template = baseParse(parse(source).descriptor.template.content);
        const slide = template.children.find(node => node.tag === 'slide');
        const children = slide.children.filter(node => node.type === 1);
        const footer = children.find(node => node.tag === 'process-communications');
        if (!type) {
            assert.equal(footer, undefined);
            continue;
        }
        assert.equal(children.at(-1), footer);
        assert.equal(footer.props.find(prop => prop.name === 'type').value.content, type);
        assert.ok(footer.props.some(prop => prop.name === 'if'));
    }
});

test('the SMS non-processed action closes the slide', () => {
    const commits = [];
    const component = load('components/prospect/sms/Slide.vue', {
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit: (...args) => commits.push(args) },
    }).default;
    component.methods.closeSlide();
    assert.deepEqual(commits, [['CLOSE_SLIDE']]);
});
