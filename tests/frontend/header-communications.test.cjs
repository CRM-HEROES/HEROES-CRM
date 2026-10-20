const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { baseParse } = require('@vue/compiler-dom');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/components/layouts/Header.vue');
const { descriptor } = parse(fs.readFileSync(filename, 'utf8'));
const { code } = transformSync(descriptor.script.content, {
    babelrc: false, configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});

function header(handler = async () => ({ data: 6 })) {
    const commits = [], dispatches = [], requests = [];
    const module = { exports: {} };
    const dependencies = {
        vuex: { mapGetters: () => ({}), mapActions: () => ({}) },
        '@/store': {
            commit: (...args) => commits.push(args),
            dispatch: (...args) => { dispatches.push(args); return Promise.resolve(); },
        },
        '@/apis/project/prospect': {
            count: (...args) => { requests.push(args); return handler(...args); },
        },
    };
    new Function('require', 'module', 'exports', code)(key => {
        if (key in dependencies) return dependencies[key];
        if (key.startsWith('@/actions/')) return new Proxy({}, { get: (_, name) => name });
        if (key.endsWith('.vue')) return {};
        throw new Error(`Unexpected dependency: ${key}`);
    }, module, module.exports);
    const component = module.exports.default;
    const vm = {
        ...component.data(), ...component.methods,
        project: { slug: 'test' }, user: { id: 1 },
    };
    Object.defineProperty(vm, 'incomingCommunicationsFilters', {
        get: () => component.computed.incomingCommunicationsFilters.call(vm),
    });
    return { vm, component, commits, dispatches, requests };
}

test('header link uses the same incoming filter as the prospect list', () => {
    const h = header();
    const root = baseParse(descriptor.template.content);
    function findMenu(node) {
        if (node.tag === 'header-menu' && node.children.some(child => child.tag === 'img'
            && child.props.some(prop => prop.name === 'src' && prop.value.content === '/images/comunication.png'))) {
            return node;
        }
        for (const child of node.children || []) {
            const result = findMenu(child);
            if (result) return result;
        }
    }
    const menu = findMenu(root);
    const destination = menu.props.find(prop => prop.type === 7 && prop.arg?.content === 'to').exp.content;
    const route = new Function('project', 'incomingCommunicationsFilters', `return (${destination})`)(
        h.vm.project, h.vm.incomingCommunicationsFilters
    );
    assert.equal(route.name, 'prospect');
    assert.deepEqual(route.params, { project: 'test' });
    assert.deepEqual(JSON.parse(route.query.filters), { incomingCommunications: true });
});

test('click clears old filters and fetches prospects with received communications', async () => {
    const h = header();
    let countRefreshes = 0;
    h.vm.fetchIncomingCommunicationsCount = () => { countRefreshes++; };
    h.vm.showIncomingCommunications();
    assert.deepEqual(h.commits, [
        ['INIT_PROSPECT_PARAMS'],
        ['SET_PROSPECT_PARAMS', { incomingCommunications: true }],
    ]);
    assert.deepEqual(h.dispatches, [['FETCH_PROSPECTS']]);
    assert.equal(countRefreshes, 1);
});

test('badge counts the prospects using the same filter', async () => {
    const h = header();
    await h.vm.fetchIncomingCommunicationsCount();
    assert.equal(h.vm.incomingCommunicationsCount, 6);
    assert.deepEqual(h.requests, [['test', {
        params: { filters: JSON.stringify({ incomingCommunications: true }) },
    }]]);
});

test('missing project makes no count request', async () => {
    const h = header();
    h.vm.project = null;
    await h.vm.fetchIncomingCommunicationsCount();
    assert.deepEqual(h.requests, []);
});

test('a delayed count cannot replace the badge of another project', async () => {
    let resolve;
    const h = header(() => new Promise(done => { resolve = done; }));
    const pending = h.vm.fetchIncomingCommunicationsCount();
    h.vm.project = { slug: 'other' };
    h.vm.incomingCommunicationsCount = 3;
    resolve({ data: 99 });
    await pending;
    assert.equal(h.vm.incomingCommunicationsCount, 3);
});

test('project changes reset the badge and request a fresh count', () => {
    const h = header();
    let refreshes = 0;
    h.vm.incomingCommunicationsCount = 99;
    h.vm.fetchIncomingCommunicationsCount = () => { refreshes++; };
    h.component.watch['project.slug'].handler.call(h.vm);
    assert.equal(h.vm.incomingCommunicationsCount, 0);
    assert.equal(refreshes, 1);
    assert.equal(h.component.watch['project.slug'].immediate, true);
});
