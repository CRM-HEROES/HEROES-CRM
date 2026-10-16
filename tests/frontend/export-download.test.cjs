const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/components/prospect/table/modals/export/Modal.vue');
const { descriptor } = parse(fs.readFileSync(filename, 'utf8'));
const { code } = transformSync(descriptor.script.content, {
    babelrc: false, configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});

function modal(handler, timer = callback => callback()) {
    const requests = [], commits = [], notices = [], downloads = [];
    const module = { exports: {} };
    const dependencies = {
        axios: { get: async (...args) => { requests.push(args); return handler(...args); } },
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit: value => commits.push(value) },
        '@/apis/common': { API_URL: '/api' },
        '@/actions/modal': { CLOSE_MODAL: 'close' },
    };

    new Function('require', 'module', 'exports', 'flashInfo', 'flashError', 'document', 'setTimeout', code)(
        key => dependencies[key], module, module.exports,
        value => notices.push(['info', value]), value => notices.push(['error', value]),
        {
            body: { appendChild() {} },
            createElement: () => ({ click() { downloads.push(this.href); }, remove() {} }),
        }, timer
    );
    const component = module.exports.default;
    const vm = {
        ...component.data(), ...component.methods,
        project: { slug: 'test', name: 'Prospects' },
        prospectsParams: { keyword: 'A&B + C' },
    };
    vm.prospectExport.email = 'name+export@example.com';

    return { vm, component, requests, commits, notices, downloads };
}

test('email keeps its own delivery flow and recipient', async () => {
    const m = modal(() => ({ data: { message: 'queued' } }));
    await m.vm.exportProspects();
    assert.equal(m.requests.length, 1);
    assert.equal(m.requests[0][1].params.email, 'name+export@example.com');
    assert.deepEqual(JSON.parse(m.requests[0][1].params.filters), m.vm.prospectsParams);
    assert.deepEqual(m.notices, [['info', 'queued']]);
    assert.deepEqual(m.downloads, []);
    assert.deepEqual(m.commits, ['close']);
});

test('download waits through queued and processing states and then starts once', async () => {
    const statuses = ['queued', 'processing', 'ready'];
    const m = modal(url => url.endsWith('/create')
        ? { status: 202, data: { message: 'Preparing', status_url: '/status', download_url: '/download' } }
        : { data: { status: statuses.shift() } });
    m.vm.prospectExport.mode = 'download';
    await m.vm.exportProspects();
    assert.equal(m.requests.length, 4);
    assert.equal(m.requests[0][1].params.count, 0);
    assert.equal('email' in m.requests[0][1].params, false);
    assert.deepEqual(m.downloads, ['/download']);
    assert.deepEqual(m.commits, ['close']);
    assert.equal(m.vm.exporting, false);
});

for (const status of ['failed', 'missing']) {
    test(`${status} exports show an error and keep the modal open`, async () => {
        const m = modal(url => url.endsWith('/create')
            ? { status: 202, data: { status_url: '/status', download_url: '/download' } }
            : { data: { status } });
        m.vm.prospectExport.mode = 'download';
        await m.vm.exportProspects();
        assert.deepEqual(m.downloads, []);
        assert.deepEqual(m.commits, []);
        assert.match(m.notices[0][1], /échoué/);
        assert.equal(m.vm.exporting, false);
    });
}

test('unmounting stops polling and automatic downloading', async () => {
    let m;
    m = modal(() => ({ data: { status_url: '/status', download_url: '/download' } }), callback => {
        m.component.beforeUnmount.call(m.vm);
        callback();
    });
    m.vm.prospectExport.mode = 'download';
    await m.vm.exportProspects();
    assert.equal(m.requests.length, 1);
    assert.deepEqual(m.downloads, []);
});

test('duplicate submissions are ignored while preparing', async () => {
    const m = modal(() => { throw new Error('unexpected request'); });
    m.vm.exporting = true;
    await m.vm.exportProspects();
    assert.deepEqual(m.requests, []);
});
