const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../..');
function transform(source) {
    return transformSync(source, {
        babelrc: false, configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    }).code;
}

function deferred() {
    let resolve, reject;
    const promise = new Promise((done, fail) => { resolve = done; reject = fail; });
    return { promise, resolve, reject };
}

function response(systemTime = new Date().toISOString()) {
    return { data: {
        collected_at: new Date().toISOString(),
        system: { collected_at: systemTime, host: {
            available: true, cpu: { usage_percent: 25 }, memory: { usage_percent: 40 }, disk: { usage_percent: 70 },
            network: { rx_bytes_per_second: 1000, tx_bytes_per_second: 2000 },
        } },
        http: { available: true, requests_per_second: 3, p95_approximate: true, p95_response_ms: 100 },
    } };
}

function dashboard(handler = async () => response()) {
    const file = path.join(root, 'resources/js/components/server-monitoring/Dashboard.vue');
    const { descriptor } = parse(fs.readFileSync(file, 'utf8'));
    const requests = [], timers = new Map(), events = new Map(), redirects = [];
    let timerId = 0;
    const document = {
        hidden: false,
        addEventListener: (name, callback) => events.set(name, callback),
        removeEventListener: name => events.delete(name),
    };
    const axios = {
        get: (...args) => { requests.push(args); return handler(...args); },
        isCancel: error => error?.code === 'ERR_CANCELED',
    };
    const module = { exports: {} };
    new Function('require', 'module', 'exports', 'document', 'setTimeout', 'clearTimeout', 'performance', transform(descriptor.script.content))(
        name => name === 'axios' ? axios : {}, module, module.exports, document,
        (callback, delay) => { timers.set(++timerId, { callback, delay }); return timerId; },
        id => timers.delete(id), { now: () => 0 },
    );
    const component = module.exports.default;
    const vm = {
        ...component.data(),
        $store: { state: { auth: { user: { is_super_admin: true } } } },
        $router: { replace: route => redirects.push(route) },
    };
    for (const [name, method] of Object.entries(component.methods)) vm[name] = method.bind(vm);
    for (const [name, getter] of Object.entries(component.computed)) Object.defineProperty(vm, name, { get: getter.bind(vm) });
    return { vm, component, document, events, requests, timers, redirects };
}

test('monitoring Vue templates compile', () => {
    for (const component of ['Dashboard', 'MetricChart', 'MetricGauge']) {
        const filename = path.join(root, `resources/js/components/server-monitoring/${component}.vue`);
        const { descriptor, errors } = parse(fs.readFileSync(filename, 'utf8'));
        assert.deepEqual(errors, []);
        assert.deepEqual(compileTemplate({ source: descriptor.template.content, filename, id: component }).errors, []);
    }
});

test('polling never overlaps and schedules the next refresh after ten seconds', async () => {
    const request = deferred();
    const h = dashboard(() => request.promise);
    const pending = h.vm.refresh();
    await h.vm.refresh();
    assert.equal(h.requests.length, 1);
    assert.equal(h.requests[0][0], '/api/server-monitoring');
    assert.equal(h.requests[0][1].timeout, 10000);
    request.resolve(response());
    await pending;
    assert.equal(h.vm.loading, false);
    assert.equal(h.timers.size, 1);
    assert.equal([...h.timers.values()][0].delay, 10000);
    assert.equal(h.vm.samples[0].rx, .008);
    assert.equal(h.vm.samples[0].cpu, 25);
});

test('cached host samples keep the collector timestamp and are not plotted twice', async () => {
    const collected = new Date(Date.now() - 10000).toISOString();
    const h = dashboard(async () => response(collected));
    await h.vm.refresh();
    await h.vm.refresh();
    assert.equal(h.vm.samples.length, 2);
    assert.equal(h.vm.hostSamples.length, 1);
    assert.equal(h.vm.hostSamples[0].time, collected);
});

test('unavailable data stays null, real zero stays visible and P95 overflow is explicit', async () => {
    const h = dashboard(async () => ({data: {collected_at: new Date().toISOString(), system: {host: {available: false}}, http: {available: false}}}));
    await h.vm.refresh();
    assert.equal(h.vm.samples[0].cpu, null);
    assert.equal(h.vm.samples[0].rps, null);
    assert.equal(h.vm.number(0), '0');
    assert.equal(h.vm.bytes(null), '—');
    assert.equal(h.vm.bytes(.5), '0,5 o');
    h.vm.snapshot.http = {available: true, p95_approximate: true, p95_overflow: true};
    assert.equal(h.vm.httpKpis[2].value, '> 30 000 ms');
    assert.match(h.vm.httpKpis[2].label, /approximative/);
});

test('401 and 403 erase cached metrics and stop retrying before redirecting', async () => {
    for (const status of [401, 403]) {
        const h = dashboard(async () => { throw {response: {status}}; });
        h.vm.snapshot = response().data;
        h.vm.samples = [{time: new Date().toISOString()}];
        await h.vm.refresh();
        assert.equal(h.vm.snapshot, null);
        assert.deepEqual(h.vm.samples, []);
        assert.equal(h.timers.size, 0);
        assert.equal(h.redirects[0].name, 'dashboard');
        await h.vm.refresh();
        assert.equal(h.requests.length, 1);
    }
});

test('hidden tabs abort pending requests and ignore their late response', async () => {
    const request = deferred();
    const h = dashboard(() => request.promise);
    const pending = h.vm.refresh();
    h.document.hidden = true;
    h.vm.visibilityChanged();
    assert.equal(h.requests[0][1].signal.aborted, true);
    request.resolve(response());
    await pending;
    assert.equal(h.vm.snapshot, null);
    assert.equal(h.vm.paused, true);
    assert.equal(h.timers.size, 0);
    h.document.hidden = false;
    h.vm.visibilityChanged();
    assert.equal(h.requests.length, 2);
});

test('unmount removes listeners and timers and blocks late responses', async () => {
    const request = deferred();
    const h = dashboard(() => request.promise);
    h.component.mounted.call(h.vm);
    assert.equal(h.events.size, 1);
    h.component.beforeUnmount.call(h.vm);
    assert.equal(h.events.size, 0);
    assert.equal(h.requests[0][1].signal.aborted, true);
    request.resolve(response());
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(h.vm.snapshot, null);
    assert.equal(h.timers.size, 0);
});

test('non Super Admin users cannot initiate polling', async () => {
    const h = dashboard();
    h.vm.$store.state.auth.user.is_super_admin = false;
    h.component.mounted.call(h.vm);
    await h.vm.refresh();
    assert.equal(h.requests.length, 0);
});

function routerGuard(user, authenticated = true) {
    const module = { exports: {} };
    let guard;
    const state = { auth: { user: authenticated ? user : null, authenticated } };
    const store = {
        state,
        commit: (name, value) => {
            if (name === 'auth/SET_USER') state.auth.user = value;
            if (name === 'auth/SET_AUTHENTICATED') state.auth.authenticated = value;
        },
    };
    const router = { beforeEach: callback => { guard = callback; }, afterEach: () => {} };
    const dependencies = {
        'vue-router': { createWebHistory: () => ({}), createRouter: () => router },
        '@/store': store,
        '@/router/auth.js': [], '@/router/registration.js': [],
    };
    new Function('require', 'module', 'exports', 'document', 'axios', transform(fs.readFileSync(path.join(root, 'resources/js/router/index.js'), 'utf8')))(
        name => {
            if (name in dependencies) return dependencies[name];
            if (name.startsWith('@/actions/')) return new Proxy({}, {get: (_, key) => key});
            throw new Error(`Unexpected dependency ${name}`);
        }, module, module.exports, {}, {get: async () => ({data: user})},
    );
    return guard;
}

test('router denies direct access and preserves a Super Admin deep link after authentication', async () => {
    const to = {name: 'server-monitoring', fullPath: '/server-monitoring', params: {}, meta: {middleware: 'auth', superAdmin: true}};
    const denied = [];
    await routerGuard({id: 1, is_super_admin: false})(to, {}, value => denied.push(value));
    assert.deepEqual(denied, [{name: 'dashboard'}]);
    const resumed = [];
    await routerGuard({id: 1, is_super_admin: true}, false)(to, {}, value => resumed.push(value));
    assert.deepEqual(resumed, [{path: '/server-monitoring', replace: true}]);
});
