const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const filename = path.resolve(__dirname, '../../resources/js/components/settings/Cloudtalk.vue');
const { descriptor } = parse(fs.readFileSync(filename, 'utf8'));
const { code } = transformSync(descriptor.script.content, {
    babelrc: false, configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});

const prepared = () => ({ data: {
    status_url: 'https://crm.test/api/project/project-a/sms/export/export-id/status',
    download_url: 'https://crm.test/api/project/project-a/sms/export/export-id/download',
    message: 'Export SMS en attente…',
} });

function modal(post = async () => prepared(), get = async () => ({ data: { status: 'ready' } })) {
    const requests = [], statusRequests = [], downloads = [], timers = new Map();
    let nextTimer = 0;
    const module = { exports: {} };
    new Function('require', 'module', 'exports', 'document', 'URL', 'setTimeout', 'clearTimeout', code)(
        key => {
            if (key === 'vuex') return { mapGetters: () => ({}) };
            if (key === 'axios') return { get: (...args) => { statusRequests.push(args); return get(...args); } };
            return { post: (...args) => { requests.push(args); return post(...args); } };
        }, module, module.exports,
        {
            body: { appendChild() {} },
            createElement: () => ({
                click() { downloads.push({ filename: this.download, href: this.href }); },
                remove() {},
            }),
        },
        { createObjectURL() { throw new Error('SMS exports must not use a browser blob.'); } },
        (callback, delay) => { const id = ++nextTimer; timers.set(id, { callback, delay }); return id; },
        id => timers.delete(id)
    );
    const component = module.exports.default;
    const vm = { ...component.data(), ...component.methods, projectSlug: 'project-a', canCloudtalkExport: true };
    vm.smsDateFrom = '2026-10-01';
    vm.smsDateTo = '2026-10-23';
    vm.exportTimezone = 'Indian/Antananarivo';
    Object.defineProperty(vm, 'canDownloadSms', { get: () => component.computed.canDownloadSms.call(vm) });
    return {
        vm, component, requests, statusRequests, downloads, timers,
        advance() {
            assert.equal(timers.size, 1);
            const [id, timer] = timers.entries().next().value;
            assert.equal(timer.delay, 3000);
            timers.delete(id);
            timer.callback();
        },
    };
}

async function flush() {
    for (let i = 0; i < 8; i++) await Promise.resolve();
}

function reset(m, trigger) {
    if (trigger === 'projectSlug') {
        m.vm.projectSlug = 'project-b';
        m.component.watch.projectSlug.call(m.vm);
    } else if (trigger === 'user') {
        m.component.watch.user.call(m.vm, { id: 2 }, { id: 1 });
    } else if (trigger === 'canCloudtalkExport') {
        m.vm.canCloudtalkExport = false;
        m.component.watch.canCloudtalkExport.call(m.vm, false);
    } else if (trigger === 'beforeUnmount') {
        m.component.beforeUnmount.call(m.vm);
    } else {
        m.vm.resetExport();
    }
}

test('CloudTalk template compiles with its SMS date filters', () => {
    const result = compileTemplate({ source: descriptor.template.content, filename, id: 'cloudtalk' });
    assert.deepEqual(result.errors, []);
    assert.match(descriptor.template.content, /<template #3>Exportation SMS<\/template>/);
    assert.match(descriptor.template.content, /v-model="smsDateFrom"/);
    assert.match(descriptor.template.content, /v-model="smsDateTo"/);
});

test('SMS export prepares all messages within the selected dates and downloads the ready file directly', async () => {
    const m = modal();
    await m.vm.downloadSmsExport();
    assert.deepEqual(m.requests, [['project/project-a/sms/export', {
        date_from: '2026-10-01', date_to: '2026-10-23', timezone: 'Indian/Antananarivo', count: 0,
    }]]);
    assert.deepEqual(m.statusRequests, [[prepared().data.status_url]]);
    assert.deepEqual(m.downloads, [{ filename: 'sms-2026-10-01-2026-10-23.csv', href: prepared().data.download_url }]);
    assert.equal(m.vm.downloadingSms, false);
    assert.equal(m.vm.smsProgress, '');
    assert.equal(m.timers.size, 0);
});

test('SMS export defaults to all messages and polls queued and processing status every three seconds', async () => {
    const statuses = ['queued', 'processing', 'ready'];
    const m = modal(undefined, async () => ({ data: { status: statuses.shift() } }));
    const download = m.vm.downloadSmsExport();
    await flush();
    assert.equal(m.requests[0][1].count, 0);
    assert.equal(m.vm.downloadingSms, true);
    assert.match(m.vm.smsProgress, /attente/);
    assert.deepEqual(m.downloads, []);
    await m.vm.downloadSmsExport();
    assert.equal(m.requests.length, 1);
    m.advance();
    await flush();
    assert.match(m.vm.smsProgress, /en cours/);
    assert.deepEqual(m.downloads, []);
    m.advance();
    await download;
    assert.equal(m.statusRequests.length, 3);
    assert.equal(m.downloads.length, 1);
    assert.equal(m.timers.size, 0);
});

test('SMS export rejects invalid dates, duplicate submissions and unauthorized requests', async () => {
    for (const values of [
        { smsDateFrom: '' }, { smsDateFrom: '2026-02-30' },
        { smsDateFrom: '2026-11-01' }, { downloadingSms: true },
        { canCloudtalkExport: false }, { projectSlug: '' },
    ]) {
        const m = modal();
        Object.assign(m.vm, values);
        await m.vm.downloadSmsExport();
        assert.deepEqual(m.requests, []);
    }
});

for (const trigger of ['projectSlug', 'user', 'canCloudtalkExport', 'resetExport', 'beforeUnmount']) {
    test(`SMS preparation response is ignored after ${trigger}`, async () => {
        let resolve;
        const m = modal(() => new Promise(done => { resolve = done; }));
        const download = m.vm.downloadSmsExport();
        reset(m, trigger);
        resolve(prepared());
        await download;
        assert.deepEqual(m.downloads, []);
        assert.deepEqual(m.statusRequests, []);
        assert.equal(m.vm.downloadedSmsName, '');
    });

    test(`SMS polling timer is cancelled and its promise settles after ${trigger}`, async () => {
        const m = modal(undefined, async () => ({ data: { status: 'queued' } }));
        const download = m.vm.downloadSmsExport();
        await flush();
        assert.equal(m.timers.size, 1);
        reset(m, trigger);
        await download;
        assert.equal(m.timers.size, 0);
        assert.equal(m.statusRequests.length, 1);
        assert.deepEqual(m.downloads, []);
    });

    test(`SMS ready response is ignored after ${trigger} while polling`, async () => {
        let resolve;
        const m = modal(undefined, () => new Promise(done => { resolve = done; }));
        const download = m.vm.downloadSmsExport();
        await flush();
        reset(m, trigger);
        resolve({ data: { status: 'ready' } });
        await download;
        assert.deepEqual(m.downloads, []);
        assert.equal(m.timers.size, 0);
    });
}

for (const status of ['failed', 'missing']) {
    test(`${status} SMS export displays the server error and permits retry`, async () => {
        let ready = false;
        const m = modal(undefined, async () => ({ data: ready
            ? { status: 'ready' }
            : { status, message: 'Export indisponible, veuillez réessayer.' } }));
        await m.vm.downloadSmsExport();
        assert.equal(m.vm.smsError, 'Export indisponible, veuillez réessayer.');
        assert.equal(m.vm.downloadingSms, false);
        assert.deepEqual(m.downloads, []);
        ready = true;
        await m.vm.downloadSmsExport();
        assert.equal(m.vm.smsError, '');
        assert.equal(m.downloads.length, 1);
    });
}

test('SMS export refuses preparation responses without valid status and download URLs', async () => {
    for (const data of [{}, { status_url: 'status' }, { status_url: '', download_url: 'file' }]) {
        const m = modal(async () => ({ data }));
        await m.vm.downloadSmsExport();
        assert.match(m.vm.smsError, /invalide/);
        assert.deepEqual(m.downloads, []);
        assert.deepEqual(m.statusRequests, []);
        assert.equal(m.vm.downloadingSms, false);
    }
});

test('JSON validation errors are displayed without downloading', async () => {
    const m = modal(async () => {
        throw { response: { data: { errors: { date_from: ['Date invalide.'] } } } };
    });
    await m.vm.downloadSmsExport();
    assert.equal(m.vm.smsError, 'Date invalide.');
    assert.equal(m.vm.downloadingSms, false);
    assert.deepEqual(m.downloads, []);
});

test('status request failures stop polling and display an error', async () => {
    const m = modal(undefined, async () => {
        throw { response: { data: { message: 'Accès refusé.' } } };
    });
    await m.vm.downloadSmsExport();
    assert.equal(m.vm.smsError, 'Accès refusé.');
    assert.equal(m.vm.downloadingSms, false);
    assert.equal(m.timers.size, 0);
    assert.deepEqual(m.downloads, []);
});

test('late failed requests do not overwrite a reset form', async () => {
    let reject;
    const m = modal(() => new Promise((resolve, fail) => { reject = fail; }));
    const download = m.vm.downloadSmsExport();
    m.vm.resetExport();
    reject(new Error('offline'));
    await download;
    assert.equal(m.vm.smsError, '');
});
