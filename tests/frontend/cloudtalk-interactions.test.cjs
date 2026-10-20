const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/components/prospect/interaction/Slide.vue');
const { descriptor } = parse(fs.readFileSync(filename, 'utf8'));
const { code } = transformSync(descriptor.script.content, {
    babelrc: false, configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});

function slide(handler = async () => ({})) {
    const calls = [], notices = [], timers = [];
    const store = {
        dispatch: async (action, params) => {
            calls.push({ action, params });
            return handler(action, params);
        },
        commit() {},
    };
    const dependencies = {
        vuex: { mapGetters: () => ({}) },
        '@/store': store,
        '@/apis/api.service': {},
        '@/apis/project/prospect': {},
        '@/utils/event-bus': {},
        '@/utils/kavkom-phone': { state: { status: 'registered' } },
    };
    const module = { exports: {} };
    new Function('require', 'module', 'exports', 'flashError', 'setTimeout', 'clearTimeout', code)(
        key => {
            if (key in dependencies) return dependencies[key];
            if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
            if (key.endsWith('.vue')) return {};
            throw new Error(`Unexpected dependency: ${key}`);
        },
        module, module.exports, value => notices.push(value),
        callback => { timers.push(callback); return timers.length; }, () => {}
    );
    const component = module.exports.default;
    const vm = { ...component.methods };
    Object.assign(vm, component.data.call(vm), {
        project: { slug: 'test' },
        user: {},
        interactionProspect: { id: 2, mobile_phone_number: '+33612345678' },
        cloudtalkLine: { id: 7 },
        prospectsSelected: [],
        leftSlideOpen: () => true,
        fetchCloudtalkCallContext: async () => {},
        setActiveInteractionProspect(prospect) { this.interactionProspect = prospect; },
    });
    vm.interaction = {
        ...vm.newInteraction(), source: 'cloudtalk',
        number: '+33612345678', data: { call_id: '143223459' },
    };
    const writes = () => calls.filter(({ action }) => [
        'ADD_PROSPECT_INTERACTION', 'UPDATE_PROSPECT_INTERACTION',
    ].includes(action));

    return { vm, component, calls, notices, timers, writes };
}

test('shared add and update methods skip CloudTalk with and without a saved interaction id', async () => {
    const h = slide();
    for (const id of [undefined, 55]) {
        h.vm.interaction.id = id;
        const interaction = h.vm.interaction;
        assert.equal(await h.vm.addInteraction(), interaction);
        assert.equal(await h.vm.updateInteraction(), interaction);
    }
    assert.deepEqual(h.calls, []);
});

test('other operators still create and update interactions', async () => {
    const h = slide(async (_, params) => ({ ...params, id: 55 }));
    for (const source of ['telephone', 'aircall', 'ringover', 'kavkom']) {
        h.vm.interaction = { ...h.vm.newInteraction(), source };
        await h.vm.addInteraction();
        await h.vm.updateInteraction();
        assert.equal(h.vm.interaction.id, 55);
    }
    assert.deepEqual(h.calls.map(({ action }) => action), Array(4).fill([
        'ADD_PROSPECT_INTERACTION', 'UPDATE_PROSPECT_INTERACTION',
    ]).flat());
});

test('starting CloudTalk calls keeps their state local and sends no interaction id', async () => {
    const h = slide(async () => ({ call_id: '143223459', responseData: { status: 200 } }));
    await h.vm.interactionViaCloudtalk('+33612345678');
    assert.equal(h.calls.length, 1);
    assert.equal(h.calls[0].action, 'MAKE_CLOUDTALK_CALL');
    assert.deepEqual(h.calls[0].params, { number: '+33612345678', line_id: 7 });
    assert.equal(h.vm.interaction.source, 'cloudtalk');
    assert.equal(h.vm.interaction.status, 'initiated');
    assert.equal(h.vm.interaction.id, undefined);

    h.vm.interaction.id = 55;
    await h.vm.makeCloudtalkCall();
    assert.equal('interaction_id' in h.calls.at(-1).params, false);
    assert.deepEqual(h.writes(), []);
});

test('failed CloudTalk calls do not create a failed interaction from the browser', async () => {
    const h = slide(async () => { throw { response: { status: 403, data: { message: 'Agent non connecté' } } }; });
    await h.vm.makeCloudtalkCall();
    assert.equal(h.vm.interaction.status, 'failed');
    assert.equal(h.vm.cloudtalkLoginRequired, true);
    assert.equal(h.notices[0].body, 'Agent non connecté');
    assert.deepEqual(h.writes(), []);
});

test('iframe call events update local state without persisting interactions', async () => {
    const h = slide();
    const call = { call_id: '143223459', number: '+33612345678', direction: 'outgoing' };
    h.vm.prepareCloudtalkInteractionFromIframe(call);
    for (const method of [
        'cloudtalkCallRinging', 'cloudtalkCallOutgoing', 'cloudtalkCallAnswered',
        'cloudtalkCallHangup', 'cloudtalkCallEnded',
    ]) {
        await h.vm[method](call);
    }
    assert.equal(h.vm.interaction.status, 'ended');
    assert.deepEqual(h.writes(), []);
    assert.equal(h.timers.length, 1);
});

test('resolving an incoming prospect does not create a browser interaction', async () => {
    const h = slide(async () => ({ number: '+33612345678', prospect: { id: 2 } }));
    await h.component.methods.fetchCloudtalkCallContext.call(h.vm, { number: '+33612345678' });
    assert.equal(h.vm.cloudtalkLookup.prospect.id, 2);
    assert.deepEqual(h.calls.map(({ action }) => action), ['LOOKUP_CLOUDTALK_CALL']);
    assert.deepEqual(h.writes(), []);
});

test('creating a prospect during a CloudTalk call does not also create an interaction', async () => {
    const h = slide(async () => ({ id: 2 }));
    await h.vm.createCloudtalkProspect();
    assert.deepEqual(h.calls.map(({ action }) => action), ['ADD_PROSPECT']);
    assert.deepEqual(h.writes(), []);
});

test('history sync only reads history and refreshes the webhook interactions', async () => {
    for (const id of [undefined, 55]) {
        const h = slide(async action => action === 'FETCH_CLOUDTALK_CALL_HISTORY'
            ? { call: { id: '143223459', direction: 'outgoing', number: '+33612345678', recorded: true } }
            : []);
        h.vm.interaction.id = id;
        h.vm.isCurrentCloudtalkInteractionEvent = () => true;
        await h.vm.syncCloudtalkCallHistory();
        assert.deepEqual(h.calls.map(({ action }) => action), [
            'FETCH_CLOUDTALK_CALL_HISTORY', 'FETCH_PROSPECT_INTERACTIONS',
        ]);
        assert.equal(h.vm.interaction.data.recorded, true);
        assert.deepEqual(h.writes(), []);
    }
});

test('delayed history for another prospect does not refresh the current prospect', async () => {
    const h = slide(async () => ({ call: { id: '143223459', direction: 'outgoing' } }));
    const oldInteraction = { ...h.vm.interaction, prospect_id: 3 };
    h.vm.isCurrentCloudtalkInteractionEvent = () => false;
    await h.vm.syncCloudtalkCallHistory({}, 1, oldInteraction, 3);
    assert.deepEqual(h.calls.map(({ action }) => action), ['FETCH_CLOUDTALK_CALL_HISTORY']);
    assert.deepEqual(h.writes(), []);
});
