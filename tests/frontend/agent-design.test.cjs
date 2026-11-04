const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../..');
const read = relative => fs.readFileSync(path.join(root, relative), 'utf8');
const AGENT_DIR = 'resources/js/components/agent';

function listVue(dir) {
    return fs.readdirSync(path.join(root, dir), { withFileTypes: true }).flatMap(entry =>
        entry.isDirectory() ? listVue(path.join(dir, entry.name)) : entry.name.endsWith('.vue') ? [path.join(dir, entry.name)] : []
    );
}

function component(relative, dependencies = {}) {
    const { code } = transformSync(parse(read(relative)).descriptor.script.content, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const moduleObject = { exports: {} };
    new Function('require', 'module', 'exports', code)(name => {
        if (name.endsWith('AgentButton.vue')) return {};
        if (!(name in dependencies)) throw new Error(`Unexpected dependency: ${name}`);
        return dependencies[name];
    }, moduleObject, moduleObject.exports);

    return moduleObject.exports.default;
}

function card(action) {
    const def = component(`${AGENT_DIR}/ActionCard.vue`);
    const vm = { action, working: false, $t: (key, params) => (params && typeof params === 'object' ? `${key}${JSON.stringify(params)}` : key) };
    for (const [name, method] of Object.entries(def.methods)) vm[name] = method.bind(vm);
    for (const name of Object.keys(def.computed)) Object.defineProperty(vm, name, { get: () => def.computed[name].call(vm), configurable: true });

    return vm;
}

// The request for authorisation

const BULK = {
    id: 1, agent: 'Lyra', tool: 'prospect_set_labels', risk: 'write', status: 'pending', running: false,
    preview: { action: 'Ajouter le(s) label(s) : NRP', prospects: 11749, skipped_processed: 3, sample: ['Jean Dupont #12', 'Marie Martin #13'] },
    expires_at: '2026-11-05T10:00:00Z',
};

test('a bulk action shows what it does, how many prospects, and a sample', () => {
    const vm = card(BULK);

    assert.equal(vm.title, 'Ajouter le(s) label(s) : NRP');
    assert.equal(vm.count, 11749);
    assert.deepEqual(vm.sample, ['Jean Dupont #12', 'Marie Martin #13']);
    assert.equal(vm.skipped, 3);
    assert.equal(vm.destructive, false);
    assert.equal(vm.icon, 'fa fa-pen');
    // What has its own place is not repeated in the list of details
    assert.deepEqual(vm.details, []);
    assert.equal(vm.eyebrow, 'agent.perm.asks{"agent":"Lyra"}');
});

test('a single action lists its details in readable form', () => {
    const vm = card({
        ...BULK, tool: 'appointment_create', preview: {
            action: 'Créer un rendez-vous', prospect: 'Jean Dupont #12', name: 'Rappeler', quand: '2026-12-01 10:00', affecté_à: 7,
            values: { city: 'Nice', meta: { source: 'Facebook' } }, champs: ['email', 'phone'], urgent: true, vide: '', rien: null,
        },
    });

    const details = Object.fromEntries(vm.details.map(d => [d.key, d.value]));

    assert.equal(vm.count, null);
    assert.equal(details.prospect, 'Jean Dupont #12');
    assert.equal(details.quand, '2026-12-01 10:00');
    assert.equal(details['affecté_à'], '7');
    assert.equal(details.values, 'city : Nice\nmeta : source : Facebook');
    assert.equal(details['champs'], 'email, phone');
    assert.equal(details.urgent, 'agent.perm.yes');
    // Empty values are not shown
    assert.equal('vide' in details, false);
    assert.equal('rien' in details, false);
});

test('without a title from the server, the name of the tool is used', () => {
    assert.equal(card({ ...BULK, preview: {} }).title, 'agent.tools.prospect_set_labels');
    assert.equal(card({ ...BULK, preview: null }).title, 'agent.tools.prospect_set_labels');
    assert.equal(card({ ...BULK, preview: { action: '' } }).title, 'agent.tools.prospect_set_labels');
});

test('the risk sets the icon and a deletion asks for a stronger confirmation', () => {
    assert.equal(card({ ...BULK, risk: 'communication' }).icon, 'fa fa-paper-plane');
    const deletion = card({ ...BULK, risk: 'destructive' });
    assert.equal(deletion.icon, 'fa fa-trash');
    assert.equal(deletion.destructive, true);

    const template = parse(read(`${AGENT_DIR}/ActionCard.vue`)).descriptor.template.content;
    assert.match(template, /:variant="destructive \? 'danger' : 'success'"/);
    assert.match(template, /agent\.perm\.approve_destructive/);
});

test('the buttons only exist while the action waits, and send the decision', () => {
    const template = parse(read(`${AGENT_DIR}/ActionCard.vue`)).descriptor.template.content;

    assert.match(template, /<footer v-if="action\.status === 'pending'"/);
    assert.match(template, /@click="\$emit\('reject'\)"/);
    assert.match(template, /@click="\$emit\('approve'\)"/);
    // While the server works the button shows it and cannot be pressed twice
    assert.match(template, /:loading="working"/);
    assert.match(template, /:disabled="working"/);
});

test('each state says what happened', () => {
    assert.equal(card({ ...BULK, status: 'executed' }).eyebrow, 'agent.perm.status.executed');
    assert.equal(card({ ...BULK, status: 'rejected' }).eyebrow, 'agent.perm.status.rejected');
    assert.equal(card({ ...BULK, status: 'failed' }).eyebrow, 'agent.perm.status.failed');
    assert.equal(card({ ...BULK, status: 'expired' }).eyebrow, 'agent.perm.status.expired');
    assert.equal(card({ ...BULK, status: 'approved' }).eyebrow, 'agent.perm.status.running');
    // An agent without a name still asks politely
    assert.match(card({ ...BULK, agent: null }).eyebrow, /L'agent/);
});

test('the progress of a long action is a percentage that never goes past 100', () => {
    assert.equal(card({ ...BULK, progress: { done: 250, total: 1000 } }).percent, 25);
    assert.equal(card({ ...BULK, progress: { done: 1200, total: 1000 } }).percent, 100);
    assert.equal(card({ ...BULK, progress: { done: 0, total: 0 } }).percent, 0);
    assert.equal(card({ ...BULK, progress: null }).percent, 0);
});

test('only a real number of prospects is shown as a figure', () => {
    assert.equal(card({ ...BULK, preview: { prospects: 0 } }).count, 0);
    assert.equal(card({ ...BULK, preview: { prospects: '12' } }).count, null);
    assert.equal(card({ ...BULK, preview: { prospects: 1 } }).count, 1);
});

// One look for the whole screen

test('no agent screen uses the old buttons of the application', () => {
    for (const file of listVue(AGENT_DIR).filter(f => !f.endsWith('AgentButton.vue'))) {
        const template = parse(read(file)).descriptor.template.content;

        assert.doesNotMatch(template, /<buttons[\s>]/, `${file}: the old <buttons> wrapper`);
        assert.doesNotMatch(template, /class="is-secondary"|hc-button-danger/, `${file}: an old button class`);
    }
});

test('the actions of the screens go through the shared button', () => {
    for (const file of ['chat/ChatPanel.vue', 'chat/ConversationHistory.vue', 'chat/ReportPanel.vue', 'chat/AgentInfo.vue', 'FormModal.vue', 'ActionCard.vue']) {
        const source = read(`${AGENT_DIR}/${file}`);

        assert.match(source, /import AgButton from "(\.\.\/)?(\.\/)?ui\/AgentButton\.vue"/, file);
        assert.match(source, /<ag-button/, file);
    }
});

test('every agent screen compiles', () => {
    for (const file of listVue(AGENT_DIR)) {
        const { descriptor } = parse(read(file));
        assert.deepEqual(compileTemplate({ source: descriptor.template.content, filename: file, id: 'design' }).errors, [], file);
    }
});

test('the shared button has its variants, sizes and states', () => {
    const source = read(`${AGENT_DIR}/ui/AgentButton.vue`);
    const def = component(`${AGENT_DIR}/ui/AgentButton.vue`);

    for (const variant of ['primary', 'secondary', 'ghost', 'danger', 'success', 'ghost-danger']) {
        assert.ok(def.props.variant.validator(variant), variant);
        assert.ok(source.includes(`.hc-ag-btn.is-${variant}`), `style of ${variant}`);
    }

    assert.equal(def.props.variant.validator('rainbow'), false);
    assert.equal(def.props.size.validator('xl'), false);
    assert.equal(def.props.type.default, 'button');

    // A loading button cannot be pressed again, and says so to assistive technology
    const template = parse(source).descriptor.template.content;
    assert.match(template, /:disabled="disabled \|\| loading"/);
    assert.match(template, /:aria-busy="loading/);
    // The keyboard focus is visible
    assert.match(source, /\.hc-ag-btn:focus-visible/);
});

test('the colours of the screens come from shared variables', () => {
    const tokens = read(`${AGENT_DIR}/ui/AgentButton.vue`);

    for (const name of ['--ag-accent', '--ag-ink', '--ag-line', '--ag-danger', '--ag-success', '--ag-warn', '--ag-radius', '--ag-shadow']) {
        assert.ok(tokens.includes(`${name}:`), name);
    }

    // The window used from the profile page loads them itself
    assert.match(read(`${AGENT_DIR}/FormModal.vue`), /AgentButton\.vue/);
});

test('the French texts of the request for authorisation exist', () => {
    const fr = JSON.parse(read('resources/js/lang/fr.json'));
    const get = key => key.split('.').reduce((o, p) => (o ? o[p] : undefined), fr);

    for (const key of [
        'agent.perm.asks', 'agent.perm.approve', 'agent.perm.approve_destructive', 'agent.perm.reject',
        'agent.perm.concerned_one', 'agent.perm.concerned_many', 'agent.perm.rejected_note', 'agent.perm.expired_note',
        'agent.perm.yes', 'agent.perm.no', 'agent.perm.status.running', 'agent.perm.status.executed', 'agent.perm.status.rejected',
        'agent.perm.status.failed', 'agent.perm.status.expired', 'agent.risk.write', 'agent.risk.communication', 'agent.risk.destructive',
        'agent.running', 'agent.done', 'agent.expires', 'agent.skipped_processed', 'agent.chat.hint', 'agent.chat.document', 'agent.chat.open_document',
        'agent.preview.prospect', 'agent.preview.values', 'agent.preview.sample', 'agent.preview.workflow',
    ]) {
        assert.equal(typeof get(key), 'string', key);
    }
});
