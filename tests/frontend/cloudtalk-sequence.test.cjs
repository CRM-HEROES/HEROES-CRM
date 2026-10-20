const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { reactive } = require('vue');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../../resources/js');
const compiled = new Map();
const interactionPath = 'components/prospect/interaction/';
const first = { id: 1, first_name: 'Alice', last_name: 'Martin', mobile_phone_number: '+33612345671' };
const second = { id: 2, first_name: 'Bob', last_name: 'Durand', phone_number: '+33612345672' };
const callbackTrace = require('./fixtures/cloudtalk-agent-callback.json');
const deferred = () => {
    let resolve, reject;
    const promise = new Promise((yes, no) => { resolve = yes; reject = no; });
    return { promise, resolve, reject };
};

function harness(options = {}) {
    const calls = [], gets = [], commits = [], notices = [], timers = [], listeners = new Map();
    let vm;
    const bus = {
        on(name, fn) { listeners.set(name, fn); },
        off(name, fn) { if (listeners.get(name) === fn) listeners.delete(name); },
        emit(name, ...args) { return listeners.get(name)?.(...args); },
    };
    const windowListeners = new Map();
    const fakeWindow = {
        addEventListener(name, fn) { windowListeners.set(name, fn); },
        removeEventListener(name, fn) { if (windowListeners.get(name) === fn) windowListeners.delete(name); },
    };
    const store = {
        commit(...args) { commits.push(args); },
        async dispatch(action, params) {
            calls.push({ action, params });
            if (options.dispatch) {
                const result = options.dispatch(action, params);
                if (result !== undefined) return result;
            }
            if (action === 'MAKE_CLOUDTALK_CALL') return { call_id: `api-${makeCalls().length}`, responseData: {} };
            if (action === 'LOOKUP_CLOUDTALK_CALL') return { prospect: vm.cloudtalkSequenceProspect };
            return {};
        },
    };
    const dependencies = {
        vuex: { mapGetters: () => ({}) },
        '@/store': store,
        '@/utils/event-bus': bus,
        '@/utils/kavkom-phone': { state: { status: 'registered' } },
        '@/apis/project/prospect': { async get(slug, config) {
            gets.push({ slug, ...config.params });
            if (options.get) return options.get(slug, config);
            const ids = JSON.parse(config.params.filters).ids.map(String);
            return { data: { data: (options.prospects || [second, first]).filter(p => ids.includes(String(p.id))) } };
        } },
    };
    function load(filename) {
        if (!compiled.has(filename)) {
            let source = fs.readFileSync(path.join(root, filename), 'utf8');
            if (filename.endsWith('.vue')) source = parse(source).descriptor.script.content;
            compiled.set(filename, transformSync(source, {
                babelrc: false, configFile: false,
                plugins: ['@babel/plugin-transform-modules-commonjs'],
            }).code);
        }
        const module = { exports: {} };
        new Function('require', 'module', 'exports', 'flashWarning', 'flashError', 'setTimeout', 'clearTimeout', 'window', compiled.get(filename))(
            key => {
                if (key in dependencies) return dependencies[key];
                if (key === './cloudtalk-sequence') return load(interactionPath + 'cloudtalk-sequence.js');
                if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
                return {};
            }, module, module.exports, value => notices.push(value), value => notices.push(value),
            (callback, delay) => { const timer = { callback, delay, cleared: false }; timers.push(timer); return timer; },
            timer => { if (timer) timer.cleared = true; }, fakeWindow,
        );
        return module.exports;
    }
    const component = load(interactionPath + 'Slide.vue').default;
    const mixin = component.mixins[0];
    const methods = { ...mixin.methods, ...component.methods };
    vm = reactive({ ...methods });
    Object.assign(vm, mixin.data.call(vm), component.data.call(vm), {
        project: { slug: 'test' }, cloudtalkLine: { id: 7 }, user: { id: 3 },
        prospectsSelected: [1, 2], interactionProspect: null,
        can: () => true, leftSlideOpen: () => true,
        $nextTick: async () => {}, $t: key => key,
        setActiveInteractionProspect(prospect) { this.interactionProspect = prospect; },
        fetchInteractions: async () => {},
    });
    for (const [name, getter] of Object.entries(mixin.computed)) Object.defineProperty(vm, name, { get: () => getter.call(vm) });
    for (const name of Object.keys(methods)) vm[name] = vm[name].bind(vm);
    mixin.created.call(vm);

    const phone = load('components/utils/Cloudtalk.vue').default;
    const frameWindow = {};
    const forwarded = {
        'call-activity': 'displayCloudtalkPhoneFromIframe', 'ringing-call': 'cloudtalkCallRinging',
        'outgoing-call': 'cloudtalkCallOutgoing', 'answered-call': 'cloudtalkCallAnswered',
        'hangup-call': 'cloudtalkCallHangup', 'call-ended': 'cloudtalkCallEnded',
        'contact-info': 'cloudtalkCallContactInfo',
    };
    const child = { ...phone.methods, ...phone.data(), loading: false,
        $refs: { phoneFrame: { contentWindow: frameWindow } },
        $emit: (event, payload) => vm[forwarded[event]]?.(payload),
    };
    Object.defineProperty(child, 'showLoading', { get: () => phone.computed.showLoading.call(child) });
    for (const name of Object.keys(phone.methods)) child[name] = child[name].bind(child);
    phone.mounted.call(child);
    const makeCalls = () => calls.filter(c => c.action === 'MAKE_CLOUDTALK_CALL');
    const start = (ids = [1, 2]) => bus.emit('cloudtalk:start-sequence', { projectSlug: vm.project.slug, prospectIds: ids });
    const message = (event, properties = {}, overrides = {}) => windowListeners.get('message')?.({
        origin: child.origin, source: frameWindow, data: JSON.stringify({ event, properties }), ...overrides,
    });
    const end = (id = vm.currentCloudtalkCallIds()[0], event = 'call_ended') => message(event, {
        call_id: id, external_number: vm.interaction.number, direction: 'outbound',
    });
    return { vm, component, mixin, phone, child, start, end, message, makeCalls, calls, gets, commits, notices, timers,
        bus, windowListeners, load };
}

test('menu action starts an ordered sequence, preferring mobile and skipping unavailable numbers', async () => {
    const h = harness({ prospects: [second, { id: 3, mobile_phone_number: ' ' }, first] });
    const menu = h.load('components/prospect/table/CardMenus.vue').default;
    await menu.methods.bulkCloudtalkInteraction.call({ ...h.vm, prospectsSelected: [1, 3, 2, 1] });
    await new Promise(resolve => setImmediate(resolve));
    assert.deepEqual(h.vm.cloudtalkSequence.prospects.map(p => p.id), [1, 2]);
    assert.equal(h.vm.interactionProspect.id, 1);
    assert.deepEqual(h.makeCalls()[0].params, { line_id: 7, number: first.mobile_phone_number });
    assert.equal(h.vm.cloudtalkSequenceNextProspect.id, 2);
    assert.equal(h.vm.cloudtalkSequenceName(h.vm.cloudtalkSequenceNextProspect), 'Bob Durand');
    assert.equal(h.notices[0].body, 'prospect.interaction.batch.skipped');
});

test('the registered window message listener opens one choice dialog on hangup and ended', async () => {
    const h = harness();
    await h.start();
    h.message('call.dialing', { call_id: 'iframe-1', direction: 'outbound', number: first.mobile_phone_number });
    h.end('iframe-1', 'call.hangup');
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    const timerCount = h.timers.length;
    h.end('iframe-1');
    assert.equal(h.timers.length, timerCount);
    assert.equal(h.makeCalls().length, 1);
    assert.equal(h.vm.cloudtalkSequence.index, 0);
    assert.equal(h.vm.cloudtalkWaitingForEvent, false);
});

test('the pasted callback events use the selected prospect and open the choices on hangup without ended', async () => {
    const h = harness({ dispatch: action => action === 'MAKE_CLOUDTALK_CALL'
        ? { call_id: null, responseData: { status: 200 } } : undefined });
    await h.start();
    const timeout = h.vm.cloudtalkEventTimeout;
    for (const event of callbackTrace.slice(0, 3)) {
        h.message(event.event, event.properties);
        assert.equal(h.vm.interaction.number, first.mobile_phone_number);
        assert.equal(h.vm.interactionProspect.id, first.id);
        assert.equal(h.vm.interaction.from_user, true);
        assert.equal(h.vm.interaction.data.direction, 'outgoing');
        assert.equal(h.vm.cloudtalkWaitingForEvent, false);
    }
    assert.equal(timeout.cleared, true);
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    const controls = h.load(interactionPath + 'components/CloudtalkSequenceControls.vue').default;
    assert.equal(controls.computed.dialogMode.call({ sequence: h.vm.cloudtalkSequence }), 'choice');
    await h.vm.syncCloudtalkCallHistory();
    const history = h.calls.find(c => c.action === 'FETCH_CLOUDTALK_CALL_HISTORY');
    assert.equal(history.params.number, first.mobile_phone_number);
    assert.equal(history.params.direction, 'outgoing');
    await h.vm.nextCloudtalkSequenceCall();
    const secondTimeout = h.vm.cloudtalkEventTimeout;
    h.message('ended', callbackTrace[2].properties);
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, false);
    assert.equal(h.vm.cloudtalkWaitingForEvent, true);
    assert.equal(secondTimeout.cleared, false);
    for (const event of callbackTrace.slice(3)) {
        h.message(event.event, event.properties);
        assert.equal(h.vm.interaction.number, second.phone_number);
        assert.equal(h.vm.interactionProspect.id, second.id);
        assert.equal(h.vm.interaction.from_user, true);
    }
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    assert.deepEqual(h.makeCalls().map(c => c.params.number), [first.mobile_phone_number, second.phone_number]);
    assert.equal(h.calls.filter(c => c.action === 'LOOKUP_CLOUDTALK_CALL')
        .every(c => [first.mobile_phone_number, second.phone_number].includes(c.params.number)), true);
    assert.equal(h.notices.length, 0);
});

test('iframe numbers, direction and outcome cannot replace the locally requested call', async () => {
    const h = harness();
    await h.start();
    const properties = {
        call_uuid: 'callback-uuid', direction: 'inbound', status: 'missed',
        external_number: '365811021001', number: 'agent-number', customer_number: 'other-customer',
        phone_number: 'other-phone', contact_phone: 'other-contact', internal_number: 'agent-number',
    };
    h.message('ringing', properties);
    assert.equal(h.vm.interaction.status, 'ringing');
    assert.equal(h.vm.interaction.number, first.mobile_phone_number);
    assert.equal(h.vm.interactionProspect.id, first.id);
    assert.equal(h.vm.interaction.from_user, true);
    h.message('calling', properties);
    assert.equal(h.vm.interaction.status, 'answered');
    h.message('contact_info', properties);
    assert.equal(h.vm.interaction.number, first.mobile_phone_number);
    h.message('hangup', properties);
    assert.equal(h.vm.interaction.status, 'hangup');
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
});

test('call progression also works without any UUID or reliable number from the iframe', async () => {
    const h = harness({ dispatch: action => action === 'MAKE_CLOUDTALK_CALL'
        ? { call_id: null, responseData: { status: 200 } } : undefined });
    await h.start();
    for (const event of callbackTrace.slice(0, 3)) {
        h.message(event.event, { external_number: event.properties.external_number, internal_number: null });
    }
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    assert.equal(h.vm.interaction.number, first.mobile_phone_number);
    await h.vm.nextCloudtalkSequenceCall();
    h.message('ended');
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, false);
    for (const event of callbackTrace.slice(3)) {
        h.message(event.event, { external_number: event.properties.external_number, internal_number: null });
    }
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    assert.equal(h.vm.interaction.number, second.phone_number);
});

test('a late timeout from the first call cannot mark the second call disconnected', async () => {
    const h = harness();
    await h.start();
    const timeout = h.vm.cloudtalkEventTimeout;
    h.end();
    await h.vm.nextCloudtalkSequenceCall();
    timeout.callback();
    assert.equal(h.vm.cloudtalkWaitingForEvent, true);
    assert.equal(h.vm.cloudtalkLoginRequired, false);
    assert.equal(h.notices.length, 0);
});

test('a valid iframe event hides loading before the REST request completes', async () => {
    const pending = deferred();
    const h = harness({ dispatch: action => action === 'MAKE_CLOUDTALK_CALL' ? pending.promise : undefined });
    const start = h.start();
    await new Promise(resolve => setImmediate(resolve));
    h.child.loading = h.component.computed.cloudtalkLoading.call(h.vm);
    h.phone.watch.loading.call(h.child, true);
    h.child.showSlowLoadingMessage = true;
    const timer = h.child.slowLoadingTimeout;
    assert.equal(h.child.showLoading, true);
    h.message('ready');
    assert.equal(h.child.showLoading, false);
    assert.equal(h.child.loading, true);
    assert.equal(h.child.showSlowLoadingMessage, false);
    assert.equal(timer.cleared, true);
    assert.equal(h.vm.callingCloudtalk, true);
    await h.vm.makeCloudtalkCall();
    assert.equal(h.makeCalls().length, 1);
    pending.resolve({ call_id: 'api-1', responseData: {} });
    await start;
});

test('untrusted or malformed messages do not hide loading', () => {
    const h = harness();
    h.child.loading = true;
    h.phone.watch.loading.call(h.child, true);
    const timer = h.child.slowLoadingTimeout;
    h.message('ready', {}, { origin: 'https://example.com' });
    h.message('ready', {}, { source: {} });
    for (const data of ['invalid json', null, {}, { event: 42 }, { event: '  ' }]) {
        h.message('ready', {}, { data });
    }
    assert.equal(h.child.showLoading, true);
    assert.equal(timer.cleared, false);
    h.message('ready', {}, { data: { event: 'ready' } });
    assert.equal(h.child.showLoading, false);
});

test('the next loading cycle shows the indicator again and a call event clears it', () => {
    const h = harness();
    h.child.loading = true;
    h.phone.watch.loading.call(h.child, true);
    h.message('ready');
    h.child.loading = false;
    h.phone.watch.loading.call(h.child, false);
    h.child.loading = true;
    h.phone.watch.loading.call(h.child, true);
    assert.equal(h.child.showLoading, true);
    const timer = h.child.slowLoadingTimeout;
    timer.callback();
    assert.equal(h.child.showSlowLoadingMessage, true);
    h.message('call.dialing', { number: first.mobile_phone_number });
    assert.equal(h.child.showLoading, false);
    assert.equal(h.child.showSlowLoadingMessage, false);
    assert.equal(timer.cleared, true);
});

test('retry calls the same prospect; next waits for the user and stops at the last prospect', async () => {
    const h = harness();
    await h.start();
    await h.vm.nextCloudtalkSequenceCall();
    assert.equal(h.makeCalls().length, 1);
    h.end();
    await h.vm.retryCloudtalkSequenceCall();
    assert.equal(h.makeCalls()[1].params.number, first.mobile_phone_number);
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, false);
    h.end();
    await h.vm.nextCloudtalkSequenceCall();
    assert.equal(h.makeCalls()[2].params.number, second.phone_number);
    assert.equal(h.vm.interactionProspect.id, 2);
    assert.equal(h.vm.cloudtalkSequenceNextProspect, null);
    h.end();
    await h.vm.nextCloudtalkSequenceCall();
    assert.equal(h.makeCalls().length, 3);
});

test('duplicate events from the finished call cannot end a retry', async () => {
    const h = harness();
    await h.start();
    h.end();
    await h.vm.retryCloudtalkSequenceCall();
    h.end('api-1');
    h.message('call.dialing', { call_id: 'api-1', direction: 'outbound', number: first.mobile_phone_number });
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, false);
    assert.equal(h.vm.interaction.data.call_id, 'api-2');
    h.end();
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
});

test('untrusted, malformed and unrelated UUIDs do not complete the sequence', async () => {
    const h = harness();
    await h.start();
    const properties = { call_id: 'api-1', number: first.mobile_phone_number, direction: 'outbound' };
    h.message('ended', properties, { origin: 'https://example.com' });
    h.message('ended', properties, { source: {} });
    h.message('ended', properties, { data: 'invalid json' });
    h.message('ended', properties, { data: null });
    h.message('ended', {});
    h.message('calling', properties);
    h.message('ended', { ...properties, call_id: 'other-call', number: second.phone_number });
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, false);
    assert.equal(h.vm.interaction.number, first.mobile_phone_number);
    assert.equal(h.makeCalls().length, 1);
});

test('known call ids remain valid when CloudTalk reports the agent number', async () => {
    const h = harness();
    await h.start();
    h.message('call_ended', { call_id: 'api-1', number: '365811021001', direction: 'outbound' });
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    assert.equal(h.vm.interaction.number, first.mobile_phone_number);
});

test('a visible individual call is reopened and a closed stale status does not block a sequence', async () => {
    const h = harness();
    h.vm.interaction = { ...h.vm.newInteraction(), source: 'cloudtalk', status: 'answered' };
    h.vm.cloudtalkPhoneVisible = true;
    await h.start();
    assert.equal(h.makeCalls().length, 0);
    assert.equal(h.vm.cloudtalkSequence, null);
    h.vm.resetCloudtalkSlideState();
    await h.start();
    assert.equal(h.makeCalls().length, 1);
});

test('closing the slide requires confirmation; cancel retains the current call', async () => {
    const h = harness();
    await h.start();
    const generic = h.load('components/Slide.vue').default;
    const slide = { open: true, beforeClose: h.vm.beforeCloudtalkSlideClose };
    generic.methods.close.call(slide);
    assert.equal(slide.open, true);
    assert.equal(h.vm.cloudtalkSequence.stopAction, 'close');
    h.vm.cancelStopCloudtalkSequence();
    assert.equal(h.vm.cloudtalkSequence.stopAction, null);
    assert.equal(h.vm.interaction.status, 'initiated');
    assert.equal(h.makeCalls().length, 1);
    h.vm.beforeCloudtalkSlideClose();
    h.vm.confirmStopCloudtalkSequence();
    assert.equal(h.vm.cloudtalkSequence, null);
    assert.equal(h.vm.interaction.status, 'new');
    assert.equal(h.vm.cloudtalkWaitingForEvent, false);
    assert.equal(h.vm.cloudtalkPhoneVisible, false);
    assert.deepEqual(h.commits.at(-1), ['CLOSE_SLIDE', true]);
    h.end('api-1');
    h.message('ended');
    assert.equal(h.vm.cloudtalkPhoneVisible, false);
    await h.start();
    assert.equal(h.makeCalls().length, 2);
});

test('other slides close normally without a veto callback', () => {
    const generic = harness().load('components/Slide.vue').default;
    for (const beforeClose of [null, () => true]) {
        const slide = { open: true, beforeClose };
        generic.methods.close.call(slide);
        assert.equal(slide.open, false);
    }
});

test('both back buttons request confirmation and preserve the sequence until confirmed', async () => {
    const h = harness();
    await h.start();
    for (const method of ['backFromCloudtalk', 'backFromInteractionFrame']) {
        h.vm[method]();
        assert.equal(h.vm.cloudtalkSequence.stopAction, 'back');
        assert.equal(h.vm.frameTab, 5);
        h.vm.cancelStopCloudtalkSequence();
    }
    h.vm.backFromCloudtalk();
    h.vm.confirmStopCloudtalkSequence();
    assert.equal(h.vm.cloudtalkSequence, null);
    assert.equal(h.vm.frameTab, 0);
    assert.equal(h.vm.tab, 0);
});

test('global close requests are intercepted and reopening never submits another call', async () => {
    const h = harness();
    await h.start();
    h.vm.handleCloudtalkSlideClosed();
    assert.deepEqual(h.commits.at(-1), ['OPEN_LEFT_SLIDE', h.vm.name]);
    assert.equal(h.vm.cloudtalkSequence.stopAction, 'close');
    h.vm.cancelStopCloudtalkSequence();
    h.vm.openCloudtalkSequencePhone();
    await h.start([2]);
    assert.equal(h.makeCalls().length, 1);
    assert.equal(h.vm.cloudtalkSequenceProspect.id, 1);
});

test('the small window stop action requires confirmation even while loading', async () => {
    const pending = deferred();
    const h = harness({ get: () => pending.promise });
    const start = h.start();
    h.vm.requestStopCloudtalkSequence('stay');
    assert.equal(h.vm.cloudtalkSequence.loading, true);
    h.vm.confirmStopCloudtalkSequence();
    pending.resolve({ data: { data: [first] } });
    await start;
    assert.equal(h.vm.cloudtalkSequence, null);
    assert.equal(h.makeCalls().length, 0);
});

test('late REST success after stopping cannot revive the call or clear a new pending request', async () => {
    const old = deferred(), fresh = deferred();
    let attempts = 0;
    const h = harness({ dispatch: action => {
        if (action === 'MAKE_CLOUDTALK_CALL') return (++attempts === 1 ? old : fresh).promise;
    } });
    const starting = h.start();
    await new Promise(resolve => setImmediate(resolve));
    h.vm.stopCloudtalkSequence();
    const restarting = h.start();
    await new Promise(resolve => setImmediate(resolve));
    old.resolve({ call_id: 'old-api', responseData: {} });
    await starting;
    assert.equal(h.vm.callingCloudtalk, true);
    assert.equal(h.vm.interaction.status, 'new');
    assert.equal(h.vm.cloudtalkSequenceIgnoredIds.includes('old-api'), true);
    fresh.resolve({ call_id: 'fresh-api', responseData: {} });
    await restarting;
    assert.equal(h.vm.interaction.data.call_id, 'fresh-api');
    assert.equal(h.vm.callingCloudtalk, false);
});

test('closing during loading pauses new calls until the stop confirmation is cancelled', async () => {
    const pending = deferred();
    const h = harness({ get: () => pending.promise });
    const start = h.start();
    h.vm.beforeCloudtalkSlideClose();
    pending.resolve({ data: { data: [first] } });
    await start;
    assert.equal(h.vm.cloudtalkSequence.loading, false);
    assert.equal(h.vm.cloudtalkSequence.stopAction, 'close');
    assert.equal(h.makeCalls().length, 0);
    await h.vm.cancelStopCloudtalkSequence();
    assert.equal(h.makeCalls().length, 1);
    assert.equal(h.vm.cloudtalkSequence.stopAction, null);
});

test('late REST failure after stopping does not show errors or change a new call', async () => {
    const pending = deferred();
    const h = harness({ dispatch: action => action === 'MAKE_CLOUDTALK_CALL' ? pending.promise : undefined });
    const start = h.start();
    await new Promise(resolve => setImmediate(resolve));
    h.vm.stopCloudtalkSequence();
    pending.reject({ response: { status: 403, data: { message: 'old failure' } } });
    await start;
    assert.equal(h.vm.interaction.status, 'new');
    assert.equal(h.vm.cloudtalkLoginRequired, false);
    assert.equal(h.notices.length, 0);
});

test('call failure offers the three choices and retry can recover', async () => {
    let failed = false;
    const h = harness({ dispatch: action => {
        if (action === 'MAKE_CLOUDTALK_CALL' && !failed) {
            failed = true;
            return Promise.reject({ response: { status: 403, data: { message: 'Agent non connecté' } } });
        }
    } });
    await h.start();
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    assert.equal(h.vm.cloudtalkSequence.error, 'Agent non connecté');
    assert.equal(h.vm.cloudtalkLoginRequired, true);
    await h.vm.retryCloudtalkSequenceCall();
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, false);
    assert.equal(h.vm.interaction.status, 'initiated');
    assert.equal(h.vm.cloudtalkLoginRequired, false);
});

test('an end event before the REST response is not undone by that response', async () => {
    const pending = deferred();
    const h = harness({ dispatch: action => action === 'MAKE_CLOUDTALK_CALL' ? pending.promise : undefined });
    const start = h.start();
    await new Promise(resolve => setImmediate(resolve));
    h.message('call.dialing', { call_id: 'iframe-id', number: first.mobile_phone_number, direction: 'outbound' });
    h.end('iframe-id');
    pending.resolve({ call_id: 'rest-id', responseData: {} });
    await start;
    assert.equal(h.vm.cloudtalkSequence.awaitingChoice, true);
    assert.equal(h.vm.interaction.status, 'ended');
    assert.equal(h.vm.cloudtalkSequence.completedIds.includes('rest-id'), true);
});

test('large selections are fetched in bounded chunks without truncation', async () => {
    const prospects = Array.from({ length: 501 }, (_, i) => ({ ...first, id: i + 1 }));
    const h = harness({ prospects });
    await h.start(prospects.map(p => p.id));
    assert.deepEqual(h.gets.map(g => JSON.parse(g.filters).ids.length), [500, 1]);
    assert.equal(h.vm.cloudtalkSequence.prospects.length, 501);
    assert.equal(h.makeCalls().length, 1);
});

test('empty results and denied permissions never initiate calls', async () => {
    const h = harness({ prospects: [] });
    await h.start();
    assert.equal(h.vm.cloudtalkSequence, null);
    assert.equal(h.notices[0].body, 'prospect.interaction.batch.no_numbers');
    h.vm.can = () => false;
    await h.start();
    assert.equal(h.gets.length, 1);
    assert.equal(h.makeCalls().length, 0);
});

test('project changes cancel loading and cannot restore the previous project prospect', async () => {
    const pending = deferred();
    const h = harness({ get: () => pending.promise });
    const start = h.start();
    h.vm.project = { slug: 'other' };
    h.mixin.watch['project.slug'].call(h.vm, 'other', 'test');
    pending.resolve({ data: { data: [first] } });
    await start;
    assert.equal(h.vm.cloudtalkSequence, null);
    assert.equal(h.vm.interactionProspect, null);
    assert.equal(h.vm.cloudtalkPhoneVisible, false);
    assert.equal(h.makeCalls().length, 0);
});

test('history responses from a previous attempt cannot overwrite the next call', async () => {
    const pending = deferred();
    const h = harness({ dispatch: action => action === 'FETCH_CLOUDTALK_CALL_HISTORY' ? pending.promise : undefined });
    await h.start();
    h.end();
    const history = h.vm.syncCloudtalkCallHistory();
    await h.vm.nextCloudtalkSequenceCall();
    pending.resolve({ call: { id: 'api-1', number: first.mobile_phone_number, status: 'ended' } });
    await history;
    assert.equal(h.vm.interaction.number, second.phone_number);
    assert.equal(h.vm.interaction.data.call_id, 'api-2');
});

test('number lookup cannot replace the selected prospect with another contact sharing the number', async () => {
    const h = harness({ dispatch: action => action === 'LOOKUP_CLOUDTALK_CALL'
        ? { prospect: { id: 99 }, threads: [{ id: 9 }], messages: [{ id: 9 }] } : undefined });
    await h.start();
    assert.equal(h.vm.interactionProspect.id, 1);
    assert.deepEqual(h.vm.cloudtalkLookup.threads, []);
});

test('generic prospect watchers cannot change the number being dialled in a sequence', async () => {
    const h = harness();
    await h.start();
    h.component.watch.interactionProspect.call(h.vm, { ...first, phone_number: second.phone_number }, null);
    h.component.watch.currentProspect.call(h.vm, second);
    await h.vm.fetchSelectedProspects();
    assert.equal(h.vm.interaction.number, first.mobile_phone_number);
    assert.equal(h.gets.length, 1);
    assert.equal(h.timers.filter(t => t.delay === 1000).length, 0);
});

test('stopping a completed sequence removes listeners on unmount and keeps interaction writes webhook-only', async () => {
    const h = harness();
    await h.start();
    h.end();
    h.vm.stopCloudtalkSequence();
    assert.equal(h.vm.cloudtalkSequence, null);
    assert.equal(h.calls.some(c => ['ADD_PROSPECT_INTERACTION', 'UPDATE_PROSPECT_INTERACTION'].includes(c.action)), false);
    h.mixin.beforeUnmount.call(h.vm);
    h.phone.beforeUnmount.call(h.child);
    assert.equal(h.windowListeners.has('message'), false);
    assert.equal(await h.start(), undefined);
    assert.equal(h.makeCalls().length, 1);
});

test('dialog uses confirmation before stop, choice after end, and emits the matching dismiss action', () => {
    const controls = harness().load(interactionPath + 'components/CloudtalkSequenceControls.vue').default;
    const events = [];
    const vm = { sequence: null, $emit: (...args) => events.push(args) };
    Object.defineProperty(vm, 'dialogMode', { get: () => controls.computed.dialogMode.call(vm) });
    assert.equal(vm.dialogMode, null);
    vm.sequence = { awaitingChoice: true, stopAction: null };
    assert.equal(vm.dialogMode, 'choice');
    controls.methods.dismissDialog.call(vm);
    vm.sequence.stopAction = 'close';
    assert.equal(vm.dialogMode, 'confirm');
    controls.methods.dismissDialog.call(vm);
    assert.deepEqual(events, [['request-stop', 'stay'], ['cancel-stop']]);
});

test('the modified Vue templates compile and every supported locale contains the sequence labels', () => {
    for (const filename of ['components/Slide.vue', interactionPath + 'Slide.vue',
        interactionPath + 'components/CloudtalkSequenceControls.vue', 'components/prospect/table/CardMenus.vue', 'components/utils/Cloudtalk.vue']) {
        const { descriptor, errors } = parse(fs.readFileSync(path.join(root, filename), 'utf8'));
        assert.deepEqual(errors, [], filename);
        const result = compileTemplate({ source: descriptor.template.content, filename, id: filename });
        assert.deepEqual(result.errors, [], filename);
    }
    const locales = ['fr', 'en', 'es', 'it', 'pt', 'ar', 'hi', 'ru', 'zh_CN'];
    const labels = JSON.parse(fs.readFileSync(path.join(root, 'lang/fr.json'))).prospect.interaction.batch;
    for (const locale of locales) {
        const batch = JSON.parse(fs.readFileSync(path.join(root, `lang/${locale}.json`))).prospect.interaction.batch;
        for (const key of Object.keys(labels)) assert.equal(typeof batch[key], 'string', `${locale}:${key}`);
    }
});
