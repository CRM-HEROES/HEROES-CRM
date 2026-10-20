const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
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
    const requests = [], events = [];
    const { markCommunicationsRead } = load('utils/incoming-communications.js', {
        '@/apis/project/incoming-communication': { read: async (...args) => {
            requests.push(args); return { data: { count: 4 } };
        } },
        '@/utils/event-bus': { emit: (...args) => events.push(args) },
    });
    assert.equal(await markCommunicationsRead('test', 2, 'sms', [{ id: 1 }, { id: '1' }, { id: 3 }]), true);
    assert.deepEqual(requests, [['test', 2, 'sms', [1, 3]]]);
    assert.deepEqual(events, [['incoming-communications-read', { projectSlug: 'test', count: 4 }]]);
});

test('failed or empty receipts do not clear the badge or prevent reading', async () => {
    const events = [];
    const { markCommunicationsRead } = load('utils/incoming-communications.js', {
        '@/apis/project/incoming-communication': { read: async () => { throw new Error('offline'); } },
        '@/utils/event-bus': { emit: (...args) => events.push(args) },
    });
    assert.equal(await markCommunicationsRead('test', 2, 'sms', []), false);
    assert.equal(await markCommunicationsRead('test', 2, 'sms', [{ id: 1 }]), false);
    assert.deepEqual(events, []);
});

test('long conversations are marked in batches without marking unseen arrivals', async () => {
    const requests = [];
    const { markCommunicationsRead } = load('utils/incoming-communications.js', {
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

test('SMS are consulted only when their conversation is visible', async () => {
    const h = panel('sms');
    h.vm.tab = 0;
    await h.vm.markDisplayedSmsRead();
    assert.deepEqual(h.calls, []);
    h.vm.tab = 1;
    await h.vm.markDisplayedSmsRead();
    assert.deepEqual(h.calls, [['test', 2, 'sms', [{ id: 1, prospect_id: 2 }]]]);
});

test('email receipts contain only the displayed prospect and thread', async () => {
    const h = panel('email');
    await h.vm.markDisplayedMessagesRead();
    assert.deepEqual(h.calls, [['test', 2, 'email', [{ id: 3, prospect_id: 2, thread_id: 8 }]]]);
});

test('background panels and a changed prospect do not mark messages read', async () => {
    for (const kind of ['sms', 'email']) {
        const h = panel(kind);
        const method = kind === 'sms' ? 'markDisplayedSmsRead' : 'markDisplayedMessagesRead';
        h.vm.slideOpen = () => false;
        await h.vm[method]();
        assert.deepEqual(h.calls, []);
        h.vm.slideOpen = () => true;
        h.vm.$nextTick = async () => { h.vm.prospect = { id: 99 }; };
        await h.vm[method]();
        assert.deepEqual(h.calls, []);
    }
});

test('a voicemail is marked only when playback starts, once per recording', async () => {
    const h = panel('voicemail');
    h.vm.$refs = { audio: { play: () => Promise.resolve() } };
    h.vm.toggleAudio();
    assert.deepEqual(h.calls, []);
    await h.vm.markVoicemailRead();
    await h.vm.markVoicemailRead();
    assert.equal(h.calls.length, 1);
    assert.deepEqual(h.calls[0], ['test', 2, 'voicemail', [h.vm.interaction]]);
});

test('outgoing voicemails and ordinary call recordings are not marked as incoming voicemail', async () => {
    const h = panel('voicemail');
    h.vm.interaction.from_user = true;
    await h.vm.markVoicemailRead();
    h.vm.interaction.from_user = false;
    h.vm.callOutcome = '';
    await h.vm.markVoicemailRead();
    assert.deepEqual(h.calls, []);
});
