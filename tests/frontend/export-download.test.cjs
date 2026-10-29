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

function modal(handler, timer = callback => callback(), fixtures = {}) {
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
        fields: [{ for: 'prospect', slug: 'first_name', name: 'Prénom', meta: false }],
        categories: [],
        threads: [],
        projectUserSettingsProspectsTable: [{ key: 'first_name' }],
        can: () => true,
        canEvent: false,
        $t: key => key,
        user: { email: 'name+export@example.com' },
        ...fixtures,
    };
    for (const [key, computed] of Object.entries(component.computed)) {
        const getter = typeof computed === 'function' ? computed : computed.get;
        Object.defineProperty(vm, key, { get: () => getter.call(vm), configurable: true });
    }
    vm.prospectExport.email = vm.user.email;
    vm.resetExportFields();

    return { vm, component, requests, commits, notices, downloads };
}

test('email keeps its own delivery flow and recipient', async () => {
    const m = modal(() => ({ data: { message: 'queued' } }));
    await m.vm.exportProspects();
    assert.equal(m.requests.length, 1);
    assert.equal(m.requests[0][1].params.email, 'name+export@example.com');
    assert.equal(m.requests[0][1].params.fields, 'first_name');
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
    assert.equal(m.requests[0][1].params.fields, 'first_name');
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

test('only visible exportable table columns are preselected with pinned columns first', () => {
    const m = modal(() => {}, undefined, {
        fields: [
            { for: 'prospect', slug: 'first_name', name: 'Prénom', meta: false },
            { for: 'prospect', slug: 'last_name', name: 'Nom', meta: false },
            { for: 'prospect', slug: 'email', name: 'Email', meta: false },
            { for: 'prospect', slug: 'score', name: 'Score', meta: true },
        ],
        projectUserSettingsProspectsTable: [
            { key: 'first_name' },
            { key: 'last_name', hidden: true, fixed: true },
            { key: 'email', fixed: true },
            { key: 'meta->score', fixed: true },
            { key: 'creator' },
            { key: 'import' },
            { key: 'pipedrive-accounts' },
            { key: 'calendar->1' },
            { key: 'event->name' },
            { key: 'deleted_field' },
        ],
    });

    assert.deepEqual(m.vm.prospectExport.fields, ['email', 'meta->score', 'first_name']);
    assert.deepEqual(m.vm.selectedExportFields, ['email', 'meta->score', 'first_name']);
    assert.deepEqual(m.vm.exportColumns.slice(0, 3).map(column => column.key), ['email', 'meta->score', 'first_name']);
    assert.ok(m.vm.exportColumns.some(column => column.key === 'last_name'));
    for (const key of ['creator', 'import', 'pipedrive-accounts', 'calendar->1', 'event->name', 'deleted_field']) {
        assert.ok(!m.vm.exportColumns.some(column => column.key === key), `${key} cannot be exported`);
    }
});

test('all prospect custom fields, accessible categories and threads are offered', () => {
    const m = modal(() => {}, undefined, {
        fields: [
            { for: 'prospect', slug: 'first_name', name: 'Prénom', meta: false },
            { for: 'prospect', slug: 'score', name: 'Score', meta: true },
            { for: 'event', slug: 'venue', name: 'Salle', meta: true },
            { for: 'user', slug: 'badge', name: 'Badge', meta: true },
        ],
        categories: [
            { id: 7, name: 'Statut', for: 'prospect' },
            { id: 9, name: 'Équipe', for: 'user' },
        ],
        threads: [{ id: 8, name: 'Notes' }],
    });

    for (const column of [
        { key: 'meta->score', name: 'Score' },
        { key: 'category->7', name: 'Statut' },
        { key: 'thread->8', name: 'Notes' },
    ]) {
        assert.ok(m.vm.exportColumns.some(candidate => candidate.key === column.key && candidate.name === column.name));
    }
    assert.ok(m.vm.exportColumns.some(column => column.key === 'links'));
    assert.ok(!m.vm.exportColumns.some(column => column.key === 'meta->venue' || column.key === 'meta->badge'));
    assert.ok(!m.vm.exportColumns.some(column => column.key === 'category->9'));
    assert.deepEqual(m.vm.prospectExport.fields, ['first_name']);
});

test('appointments, calls and SMS follow the table visibility permissions', () => {
    const settings = ['first_name', 'events', 'interactions', 'sms'].map(key => ({ key }));
    const restricted = modal(() => {}, undefined, {
        can: () => false,
        projectUserSettingsProspectsTable: settings,
    });
    for (const key of ['events', 'interactions', 'sms']) {
        assert.ok(!restricted.vm.exportColumns.some(column => column.key === key));
    }
    assert.deepEqual(restricted.vm.prospectExport.fields, ['first_name']);

    const calendarAccess = modal(() => {}, undefined, { can: () => false, canEvent: true });
    assert.ok(calendarAccess.vm.exportColumns.some(column => column.key === 'events'));
    assert.ok(!calendarAccess.vm.exportColumns.some(column => column.key === 'sms' || column.key === 'interactions'));

    for (const [permission, column] of [
        ['all.prospect.event.view', 'events'],
        ['all.prospect.interaction.view', 'interactions'],
        ['all.prospect.sms.view', 'sms'],
    ]) {
        const allowed = modal(() => {}, undefined, { can: key => key === permission });
        assert.ok(allowed.vm.exportColumns.some(candidate => candidate.key === column), permission);
    }
});

for (const mode of ['email', 'download']) {
    test(`${mode} sends only selected valid columns in export order`, async () => {
        const m = modal(url => url.endsWith('/create')
            ? { data: { message: 'queued', status_url: '/status', download_url: '/download' } }
            : { data: { status: 'ready' } }, undefined, {
            fields: [
                { for: 'prospect', slug: 'first_name', name: 'Prénom', meta: false },
                { for: 'prospect', slug: 'score', name: 'Score', meta: true },
            ],
            projectUserSettingsProspectsTable: [{ key: 'meta->score' }, { key: 'first_name' }],
        });
        m.vm.prospectExport.mode = mode;
        m.vm.prospectExport.fields = ['first_name', 'deleted_field', 'meta->score', 'first_name'];

        await m.vm.exportProspects();

        assert.equal(m.requests[0][1].params.fields, 'meta->score,first_name');
        assert.deepEqual(m.commits, ['close']);
    });

    test(`${mode} refuses an empty selection before making a request`, async () => {
        const m = modal(() => { throw new Error('unexpected request'); });
        m.vm.prospectExport.mode = mode;
        m.vm.prospectExport.fields = [];

        await m.vm.exportProspects();

        assert.deepEqual(m.requests, []);
        assert.deepEqual(m.notices, [['error', 'Sélectionnez au moins une colonne à exporter.']]);
        assert.deepEqual(m.commits, []);
        assert.equal(m.vm.exporting, false);
    });
}

test('a selection containing only obsolete columns is rejected', async () => {
    const m = modal(() => { throw new Error('unexpected request'); });
    m.vm.prospectExport.fields = ['deleted_field'];

    await m.vm.exportProspects();

    assert.deepEqual(m.requests, []);
    assert.deepEqual(m.notices, [['error', 'Sélectionnez au moins une colonne à exporter.']]);
});

test('opening the modal again resets selection from the latest table settings', () => {
    const m = modal(() => {}, undefined, {
        fields: [
            { for: 'prospect', slug: 'first_name', name: 'Prénom', meta: false },
            { for: 'prospect', slug: 'email', name: 'Email', meta: false },
        ],
    });
    assert.match(descriptor.template.content, /@open\s*=\s*["']\s*resetExportFields(?:\(\))?\s*["']/);
    m.vm.prospectExport.fields = ['history'];
    m.vm.projectUserSettingsProspectsTable = [{ key: 'first_name', hidden: true }, { key: 'email' }];

    m.vm.resetExportFields();

    assert.deepEqual(m.vm.prospectExport.fields, ['email']);
    assert.deepEqual(m.vm.selectedExportFields, ['email']);
});

test('selecting all includes every available column, including hidden custom fields', () => {
    const m = modal(() => {}, undefined, {
        fields: [
            { for: 'prospect', slug: 'first_name', name: 'Prénom', meta: false },
            { for: 'prospect', slug: 'score', name: 'Score', meta: true },
        ],
        projectUserSettingsProspectsTable: [{ key: 'first_name' }, { key: 'meta->score', hidden: true }],
    });

    m.vm.selectAllExportFields();

    assert.deepEqual(m.vm.prospectExport.fields, m.vm.exportColumns.map(column => column.key));
    assert.ok(m.vm.selectedExportFields.includes('meta->score'));
    assert.ok(m.vm.selectedExportFields.includes('history'));
});
