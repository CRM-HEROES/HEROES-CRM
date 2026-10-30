const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const sourceRoot = path.resolve(__dirname, '../../resources/js');
const compiled = new Map();

function load(filename, dependencies = {}) {
    if (!compiled.has(filename)) {
        let source = fs.readFileSync(path.join(sourceRoot, filename), 'utf8');
        if (filename.endsWith('.vue')) source = parse(source).descriptor.script.content;
        compiled.set(filename, transformSync(source, {
            babelrc: false, configFile: false,
            plugins: ['@babel/plugin-transform-modules-commonjs'],
        }).code);
    }
    const module = { exports: {} };
    new Function('require', 'module', 'exports', compiled.get(filename))(specifier => {
        if (specifier in dependencies) return dependencies[specifier];
        if (specifier.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        if (specifier === 'vuex') return { mapGetters: () => ({}) };
        if (specifier === '@/utils/prospect-label-history') return load('utils/prospect-label-history.js');
        return {};
    }, module, module.exports);
    return module.exports.default || module.exports;
}

function deferred() {
    let resolve, reject;
    const promise = new Promise((done, fail) => { resolve = done; reject = fail; });
    return { promise, resolve, reject };
}

const label = (id, category_id = 1) => ({
    id, category_id, name: `Label ${id}`,
    pivot: { created_at: '2026-10-01T10:00:00Z', creator_id: 1 },
});
const revision = (id, old_value = null, new_value = `Label ${id}`) => ({
    id, key: 'category->1', field: 'Status', old_value, new_value, user_id: 1,
    created_at: `2026-10-${String(id).padStart(2, '0')}T10:00:00Z`,
});
const page = (data, current_page = 1, last_page = 1) => ({ data, current_page, last_page });

function harness({ labels = [label(1)], revisions = [], get, update, destroy } = {}) {
    const requests = [], commits = [], dispatches = [];
    const prospect = { id: 7, labels, revisions: page(revisions) };
    const state = { project: {
        slug: 'test', prospect, prospects: [prospect], categories: [{ id: 1, unique: true }],
    } };
    const labelStore = load('store/project/prospect/label.js', {
        '@/apis/project/prospect/label': {
            update: async (...args) => { requests.push(['update', ...args]); return update?.(...args); },
            destroy: async (...args) => { requests.push(['destroy', ...args]); return destroy?.(...args); },
        },
    });
    const revisionStore = load('store/project/prospect/revision.js', {
        '@/apis/project/prospect/revision': {
            get: async (...args) => {
                requests.push(['get', ...args]);
                return { data: get ? await get(...args) : page(revisions) };
            },
        },
    });
    const actions = { ...labelStore.actions, ...revisionStore.actions };
    const mutations = { ...labelStore.mutations, ...revisionStore.mutations };
    const context = {
        state,
        commit(name, value) { commits.push([name, value]); mutations[name](state, value); },
        dispatch(name, params) { dispatches.push([name, params]); return actions[name](context, params); },
    };
    return { state, requests, commits, dispatches, run: (name, params) => context.dispatch(name, params) };
}

const bloc = load('components/prospect/profile/blocs/history/Bloc.vue');
function history(harness, extra = {}) {
    return bloc.computed.histories.call({
        prospectEvents: [], prospectFiles: [], prospectGroups: [], prospectInteractions: [],
        prospectLinks: [], prospectMessages: [], prospectOrders: [], prospectSms: [], prospectUsers: [],
        prospectLabels: harness.state.project.prospect.labels,
        prospectRevisions: harness.state.project.prospect.revisions,
        ...extra,
    });
}

test('a unique category replacement adds a revision above the original assignment', async () => {
    const response = deferred();
    const original = label(1), replacement = label(2);
    const attached = { ...replacement, pivot: { created_at: '2026-10-02T10:00:00Z', creator_id: 1 } };
    const baseline = revision(1), change = revision(2, 'Label 1', 'Label 2');
    const h = harness({ labels: [original], get: () => response.promise, update: () => ({ data: { label: attached } }) });
    const pending = h.run('ADD_PROSPECT_LABEL', replacement);
    await new Promise(setImmediate);
    assert.deepEqual(h.state.project.prospect.labels, [original]);
    assert.deepEqual(history(h).map(item => item.type), ['label']);
    response.resolve(page([change, baseline]));
    await pending;
    assert.deepEqual(h.state.project.prospect.labels, [attached]);
    assert.deepEqual(history(h).map(item => [item.type, item.id]), [['revision', 2], ['revision', 1]]);
    assert.deepEqual(h.requests, [['update', 'test', 7, 2], ['get', 'test', 7, { params: { page: 1 } }]]);
});

test('removing and reattaching the same label preserves all three distinct events', async () => {
    const baseline = revision(1), removal = revision(2, 'Label 1', null), addition = revision(3, null, 'Label 1');
    let response = [removal, baseline];
    const attached = { ...label(1), pivot: { created_at: '2026-10-03T10:00:00Z', creator_id: 1 } };
    const h = harness({ revisions: [baseline], get: () => page(response), update: () => ({ data: { label: attached } }) });
    await h.run('REMOVE_PROSPECT_LABEL', label(1));
    assert.deepEqual(h.state.project.prospect.labels, []);
    assert.deepEqual(history(h).map(item => item.id), [2, 1]);
    response = [addition, removal, baseline];
    await h.run('ADD_PROSPECT_LABEL', label(1));
    assert.deepEqual(h.state.project.prospect.labels, [attached]);
    assert.deepEqual(history(h).map(item => item.id), [3, 2, 1]);
    assert.ok(history(h).every(item => item.type === 'revision'));
});

test('unrevised categories keep their original assignment and unrelated entries remain visible', () => {
    const h = harness({ labels: [label(1), label(9, 2)], revisions: [revision(1)] });
    const entries = history(h, { prospectMessages: [{ id: 100, created_at: '2026-10-04T10:00:00Z' }] });
    assert.deepEqual(entries.map(item => [item.type, item.id]), [['message', 100], ['label', 9], ['revision', 1]]);
});

test('an imported assignment after older revisions remains visible until its own revision exists', () => {
    const imported = { ...label(9), pivot: { created_at: '2026-10-04T13:00:00+03:00', creator_id: 1 } };
    const h = harness({ labels: [imported], revisions: [revision(1)] });
    assert.deepEqual(history(h).map(item => [item.type, item.id]), [['label', 9], ['revision', 1]]);
    h.state.project.prospect.revisions.data.unshift(revision(4, null, 'Label 9'));
    assert.deepEqual(history(h).map(item => [item.type, item.id]), [['revision', 4], ['revision', 1]]);
});

test('all mode collects 30 revisions without clearing history while later pages load', async () => {
    const laterPage = deferred();
    const rows = Array.from({ length: 30 }, (_, i) => revision(30 - i));
    const original = revision(1);
    const h = harness({ revisions: [original], get: (_project, _prospect, params) =>
        params.params.page === 1 ? page(rows.slice(0, 25), 1, 2) : laterPage.promise,
    });
    const params = Object.freeze({ all: true });
    const pending = h.run('FETCH_PROSPECT_REVISIONS', params);
    await new Promise(setImmediate);
    assert.deepEqual(h.state.project.prospect.revisions.data, [original]);
    assert.equal(h.commits.length, 0);
    laterPage.resolve(page(rows.slice(25), 2, 2));
    const result = await pending;
    assert.deepEqual(result.data, rows);
    assert.deepEqual(h.state.project.prospect.revisions.data, rows);
    assert.deepEqual(h.requests, [['get', 'test', 7, { params: { page: 1 } }], ['get', 'test', 7, { params: { page: 2 } }]]);
    assert.deepEqual(params, { all: true });
    assert.equal(h.commits.length, 1);
});

test('ordinary revision callers retain pagination and their original query', async () => {
    const response = page([revision(1)], 2, 3);
    const h = harness({ get: () => response });
    const params = Object.freeze({ params: { page: 2 } });
    assert.equal(await h.run('FETCH_PROSPECT_REVISIONS', params), response);
    assert.equal(h.requests[0][3], params);
    assert.equal(h.state.project.prospect.revisions, response);
});

test('a later paginated request cannot truncate the full timeline already in shared state', async () => {
    const rows = Array.from({ length: 30 }, (_, i) => revision(30 - i));
    const response = page(rows.slice(0, 25), 1, 2);
    const h = harness({ revisions: rows, get: () => response });
    assert.equal(await h.run('FETCH_PROSPECT_REVISIONS'), response);
    assert.deepEqual(history(h).map(item => item.id), rows.map(item => item.id));
    assert.equal(h.state.project.prospect.revisions.data.length, 30);
});

test('a paginated request cannot cancel an in-progress full timeline load', async () => {
    const full = deferred();
    const rows = Array.from({ length: 30 }, (_, i) => revision(30 - i));
    const h = harness({ get: (_project, _prospect, params) => {
        if (!params) return page(rows.slice(0, 25), 1, 2);
        return params.params.page === 1 ? full.promise : page(rows.slice(25), 2, 2);
    } });
    const pending = h.run('FETCH_PROSPECT_REVISIONS', { all: true });
    await h.run('FETCH_PROSPECT_REVISIONS');
    full.resolve(page(rows.slice(0, 25), 1, 2));
    await pending;
    assert.equal(h.state.project.prospect.revisions.data.length, 30);
    assert.deepEqual(h.requests.map(request => request[3]), [{ params: { page: 1 } }, undefined, { params: { page: 2 } }]);
});

test('failed label writes leave active labels and every historical entry intact', async () => {
    for (const action of ['ADD_PROSPECT_LABEL', 'REMOVE_PROSPECT_LABEL']) {
        const failed = async () => { throw new Error('Write failed'); };
        const h = harness({ revisions: [revision(1)], update: failed, destroy: failed });
        await assert.rejects(h.run(action, label(2)), /Write failed/);
        assert.deepEqual(h.state.project.prospect.labels, [label(1)]);
        assert.deepEqual(history(h).map(item => item.id), [1]);
        assert.deepEqual(h.commits, []);
        assert.equal(h.requests.length, 1);
    }
});

test('a failed history refresh keeps old revisions and applies the successful label write', async () => {
    const original = revision(1);
    const h = harness({ revisions: [original], get: () => { throw new Error('Read failed'); } });
    await assert.rejects(h.run('ADD_PROSPECT_LABEL', label(2)), /Read failed/);
    assert.deepEqual(h.state.project.prospect.labels, [label(2)]);
    assert.deepEqual(h.state.project.prospect.revisions.data, [original]);
    assert.deepEqual(history(h).map(item => [item.type, item.id]), [['label', 2], ['revision', 1]]);
});

test('same-second revisions of another status or author do not hide an assignment', () => {
    const original = { ...label(1), pivot: { created_at: '2026-10-01T10:00:00Z', creator_id: 2 } };
    const otherStatus = { ...revision(1, null, 'Label 2'), user_id: 2 };
    const otherAuthor = revision(1);
    for (const change of [otherStatus, otherAuthor]) {
        const h = harness({ labels: [original], revisions: [change] });
        assert.equal(history(h).filter(item => item.type === 'label').length, 1);
    }
});

test('a selection snapshot hides only the assignment added by that revision', () => {
    const first = label(1), second = label(2);
    const change = revision(1, 'Label 1', 'Label 1, Label 2');
    const h = harness({ labels: [first, second], revisions: [change] });
    assert.deepEqual(history(h).filter(item => item.type === 'label').map(item => item.id), [1]);
});

test('a removal or no-op snapshot cannot hide an original assignment', () => {
    for (const change of [revision(1, 'Label 1, Label 2', 'Label 1'), revision(1, 'Label 1', 'Label 1')]) {
        const h = harness({ revisions: [change] });
        assert.equal(history(h).filter(item => item.type === 'label').length, 1);
    }
});

test('a failure on a later page preserves the complete previously loaded history', async () => {
    const original = [revision(2), revision(1)];
    const h = harness({ revisions: original, get: (_project, _prospect, params) => {
        if (params.params.page === 2) throw new Error('Page failed');
        return page([revision(3)], 1, 2);
    } });
    await assert.rejects(h.run('FETCH_PROSPECT_REVISIONS', { all: true }), /Page failed/);
    assert.deepEqual(h.state.project.prospect.revisions.data, original);
    assert.deepEqual(h.commits, []);
});

test('switching prospect or project before a write returns prevents later history fetches and commits', async () => {
    for (const change of ['prospect', 'project']) {
        const write = deferred();
        const h = harness({ update: () => write.promise });
        const pending = h.run('ADD_PROSPECT_LABEL', label(2));
        if (change === 'prospect') h.state.project.prospect = { id: 9, labels: [], revisions: page([]) };
        else h.state.project.slug = 'other';
        write.resolve();
        await pending;
        assert.equal(h.requests.length, 1);
        assert.deepEqual(h.commits, []);
    }
});

test('switching target while history loads discards both old revisions and the old label mutation', async () => {
    for (const change of ['prospect', 'project']) {
        const response = deferred();
        const h = harness({ get: () => response.promise });
        const pending = h.run('ADD_PROSPECT_LABEL', label(2));
        await new Promise(setImmediate);
        if (change === 'prospect') h.state.project.prospect = { id: 9, labels: [], revisions: page([]) };
        else h.state.project.slug = 'other';
        response.resolve(page([revision(2), revision(1)], 1, 2));
        await pending;
        assert.equal(h.requests.length, 2);
        assert.deepEqual(h.commits, []);
    }
});

test('closing a prospect while history loads safely discards the pending response', async () => {
    const response = deferred();
    const h = harness({ get: () => response.promise });
    const pending = h.run('ADD_PROSPECT_LABEL', label(2));
    await new Promise(setImmediate);
    h.state.project.prospect = null;
    response.resolve(page([revision(2), revision(1)]));
    await pending;
    assert.equal(h.state.project.prospect, null);
    assert.deepEqual(h.commits, []);
});

test('a delayed revision response cannot overwrite a newer refresh for the same prospect', async () => {
    const oldResponse = deferred(), newResponse = deferred();
    let attempt = 0;
    const h = harness({ get: () => (++attempt === 1 ? oldResponse.promise : newResponse.promise) });
    const oldRequest = h.run('FETCH_PROSPECT_REVISIONS', { all: true });
    const newRequest = h.run('FETCH_PROSPECT_REVISIONS', { all: true });
    newResponse.resolve(page([revision(2), revision(1)]));
    await newRequest;
    oldResponse.resolve(page([revision(1)]));
    await oldRequest;
    assert.deepEqual(h.state.project.prospect.revisions.data.map(item => item.id), [2, 1]);
    assert.equal(h.commits.length, 1);
});

test('profile history requests all revisions on creation', async () => {
    const dispatches = [];
    const component = load('components/prospect/profile/blocs/history/Bloc.vue', {
        '@/store': { dispatch: async (...args) => dispatches.push(args) },
    });
    await component.methods.fetchRevisions();
    assert.deepEqual(dispatches, [['FETCH_PROSPECT_REVISIONS', { all: true }]]);
});

test('all mode sends real page query parameters through the production HTTP services', async () => {
    const axios = require('axios');
    const urls = [], configs = [];
    const api = load('apis/api.service.js', {
        axios: { get: async (url, config) => {
            urls.push(axios.getUri({ url, ...config }));
            configs.push(config);
            return { data: page([revision(config.params.page)], config.params.page, 2) };
        } },
        '@/apis/common': { API_URL: '/api' },
    });
    const service = load('apis/project/prospect/revision.js', { '@/apis/api.service': api });
    const store = load('store/project/prospect/revision.js', { '@/apis/project/prospect/revision': service });
    const state = { project: { slug: 'test', prospect: { id: 7 } } };
    const params = Object.freeze({ all: true, headers: { 'X-Test': 'revision' }, params: { filter: 'kept' } });
    const result = await store.actions.FETCH_PROSPECT_REVISIONS({ state, commit() {} }, params);
    assert.deepEqual(urls, [
        '/api/project/test/prospect/7/revision?filter=kept&page=1',
        '/api/project/test/prospect/7/revision?filter=kept&page=2',
    ]);
    assert.ok(configs.every(config => !('all' in config) && config.headers['X-Test'] === 'revision'));
    assert.deepEqual(result.data.map(item => item.id), [1, 2]);
    assert.deepEqual(params.params, { filter: 'kept' });
});
