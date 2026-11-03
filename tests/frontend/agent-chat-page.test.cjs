const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../..');
const read = relative => fs.readFileSync(path.join(root, relative), 'utf8');

function load(code, dependencies) {
    const { code: commonjs } = transformSync(code, {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const moduleObject = { exports: {} };
    new Function('require', 'module', 'exports', commonjs)(
        name => {
            if (!(name in dependencies)) throw new Error(`Unexpected dependency: ${name}`);
            return dependencies[name];
        },
        moduleObject,
        moduleObject.exports
    );

    return moduleObject.exports;
}

const items = load(read('resources/js/components/agent/chat/items.js'), {});
const { buildItems, newReports } = items;

// The lines of a conversation

test('a document sits right after the last message of the run that produced it', () => {
    const messages = [
        { role: 'user', kind: 'text', text: 'Rapport ?', run_id: null },
        { role: 'agent', kind: 'text', text: 'Je regarde.', run_id: 5 },
        { role: 'agent', kind: 'tool', tool: 'team_performance', run_id: 5 },
        { role: 'agent', kind: 'text', text: 'Voici le rapport.', run_id: 5 },
        { role: 'user', kind: 'text', text: 'Merci', run_id: null },
        { role: 'agent', kind: 'text', text: 'Avec plaisir', run_id: 6 },
    ];
    const reports = [{ id: 11, run_id: 5, title: 'Activité' }, { id: 12, run_id: 6, title: 'Autre' }];

    const lines = buildItems(messages, reports);

    assert.deepEqual(lines.map(l => l.kind), ['user', 'agent', 'tool', 'agent', 'report', 'user', 'agent', 'report']);
    assert.deepEqual(lines[4], { kind: 'report', id: 11, title: 'Activité' });
    assert.deepEqual(lines[7], { kind: 'report', id: 12, title: 'Autre' });
});

test('system notices and unknown documents are handled', () => {
    const lines = buildItems([{ role: 'system', kind: 'system', text: 'Action exécutée' }, { role: 'agent', kind: 'text', text: 'ok', run_id: 1 }], [{ id: 1, run_id: 99, title: 'Orphelin' }]);

    assert.deepEqual(lines, [{ kind: 'system', text: 'Action exécutée' }, { kind: 'agent', text: 'ok' }]);
    assert.deepEqual(buildItems([], []), []);
});

test('only the documents that were not there before are new', () => {
    assert.deepEqual(newReports([{ id: 1 }], [{ id: 1 }, { id: 2 }, { id: 3 }]).map(r => r.id), [2, 3]);
    assert.deepEqual(newReports([], []), []);
});

// The page

function page({ agents, project = { id: 7, slug: 'alpha', name: 'Alpha' }, api = {}, storage = {} } = {}) {
    const commits = [];
    const dispatches = [];
    const calls = { ask: [], conversation: [], conversations: [], archive: [], report: [], approve: [], reject: [], get: [] };
    const state = { agents: agents ?? [{ id: 1, name: 'Lyra', project: null }, { id: 2, name: 'Max', project: { id: 7 } }, { id: 3, name: 'Ailleurs', project: { id: 99 } }] };

    const apis = {
        conversations: async slug => { calls.conversations.push(slug); return { data: [{ id: 40, title: 'Ancienne', agent: { id: 1 } }] }; },
        conversation: async id => { calls.conversation.push(id); return { data: { id, title: 'Titre', agent: { id: 1, name: 'Lyra' }, messages: [{ role: 'agent', kind: 'text', text: 'Bonjour', run_id: 3 }], reports: [], pending_actions: [], run: { id: 3, finished: true } } }; },
        ask: async (...args) => { calls.ask.push(args); return { data: { conversation_id: 50, run_id: 60 } }; },
        // The network takes a moment: the question is shown before the answer
        run: async () => { await new Promise(r => setImmediate(r)); return { data: { finished: true, waiting_seconds: 0 } }; },
        report: async id => { calls.report.push(id); return { data: { id, title: 'Rapport', content: '# Titre' } }; },
        archiveConversation: async id => { calls.archive.push(id); return { data: {} }; },
        approve: async id => { calls.approve.push(id); return { data: {} }; },
        reject: async id => { calls.reject.push(id); return { data: {} }; },
        get: async id => { calls.get.push(id); return { data: { id, name: 'Lyra', instructions: 'Aide-moi' } }; },
        ...api,
    };

    const stub = {};
    const exports = load(parse(read('resources/js/components/agent/chat/AgentPage.vue')).descriptor.script.content, {
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit: (...args) => commits.push(args), dispatch: async (...args) => { dispatches.push(args); } },
        '@/apis/agent': apis,
        '@/actions/modal': { OPEN_MODAL: 'openModal' },
        './AgentBar.vue': stub, './AgentInfo.vue': stub, './ChatPanel.vue': stub, './ConversationHistory.vue': stub, './ReportPanel.vue': stub,
        './items': items,
    });
    const component = exports.default;

    global.localStorage = { getItem: key => storage[key] ?? null, setItem: (key, value) => { storage[key] = value; } };
    global.window = { innerWidth: 1400 };
    global.confirm = () => true;

    const vm = { ...component.data(), project, agents: state.agents, $t: (key, params) => key + (params ? JSON.stringify(params) : '') };
    for (const [name, method] of Object.entries(component.methods)) vm[name] = method.bind(vm);
    for (const name of ['projectAgents', 'selectedAgent', 'items', 'openedReportId']) {
        Object.defineProperty(vm, name, { get: () => component.computed[name].call(vm), configurable: true });
    }
    // The poll runs at once instead of after a delay
    vm.schedule = fn => { Promise.resolve().then(fn); };
    vm.$nextTick = fn => fn && fn();

    return { vm, calls, commits, dispatches, component, storage };
}

const settle = async (n = 12) => { for (let i = 0; i < n; i++) await new Promise(r => setImmediate(r)); };

test('the page lists the agents of the project only, selects the first and loads the history of the project', async () => {
    const h = page();
    await h.vm.start();

    assert.deepEqual(h.vm.projectAgents.map(a => a.name), ['Lyra', 'Max']);
    assert.equal(h.vm.selectedAgentId, 1);
    assert.deepEqual(h.calls.conversations, ['alpha']);
    assert.equal(h.vm.conversations.length, 1);
    assert.deepEqual(h.dispatches, [['FETCH_AGENTS']]);
});

test('the history is open by default and its state is remembered', () => {
    const h = page();
    h.component.created.call(Object.assign(h.vm, { start: async () => {} }));
    assert.equal(h.vm.historyCollapsed, false);
    assert.equal(h.vm.canvasOpen, true);

    h.vm.toggleHistory();
    assert.equal(h.vm.historyCollapsed, true);
    assert.equal(h.storage['hc-agent-history-collapsed'], '1');

    const again = page({ storage: { 'hc-agent-history-collapsed': '1' } });
    again.component.created.call(Object.assign(again.vm, { start: async () => {} }));
    assert.equal(again.vm.historyCollapsed, true);
});

test('on a narrow screen the document panel starts closed', () => {
    const h = page();
    global.window = { innerWidth: 800 };
    h.component.created.call(Object.assign(h.vm, { start: async () => {} }));

    assert.equal(h.vm.canvasOpen, false);
});

test('a message is shown at once, sent to the selected agent for this project, and the answer replaces it', async () => {
    const h = page();
    await h.vm.start();

    await h.vm.send('Rapport de la semaine');
    // The question is already there, before the answer
    assert.deepEqual(h.vm.messages.map(m => m.text), ['Rapport de la semaine']);
    assert.equal(h.vm.busy, true);

    await settle();

    assert.deepEqual(h.calls.ask, [[1, 'alpha', 'Rapport de la semaine', null]]);
    assert.equal(h.vm.conversationId, 50);
    assert.equal(h.vm.busy, false);
    assert.equal(h.vm.conversationTitle, 'Titre');
    assert.deepEqual(h.vm.messages.map(m => m.text), ['Bonjour']);
    // The history shows the new conversation
    assert.equal(h.calls.conversations.length, 2);
});

test('a second message continues the same conversation', async () => {
    const h = page();
    await h.vm.start();
    await h.vm.send('Un');
    await settle();

    await h.vm.send('Deux');
    await settle();

    assert.deepEqual(h.calls.ask.map(a => a[3]), [null, 50]);
});

test('nothing is sent while the agent answers, when empty, or without an agent', async () => {
    const h = page({ agents: [] });
    await h.vm.start();
    await h.vm.send('Salut');
    assert.deepEqual(h.calls.ask, []);

    const g = page();
    await g.vm.start();
    await g.vm.send('   ');
    g.vm.busy = true;
    await g.vm.send('Pendant');
    assert.deepEqual(g.calls.ask, []);
});

test('a refusal of the server is shown and the question can be sent again', async () => {
    let attempts = 0;
    const h = page({ api: { ask: async (...args) => {
        attempts++;
        if (attempts === 1) { const e = new Error('x'); e.response = { data: { message: "L'agent est encore en train de répondre." } }; throw e; }
        h.calls.ask.push(args);
        return { data: { conversation_id: 50, run_id: 60 } };
    } } });
    await h.vm.start();

    await h.vm.send('Question');
    assert.equal(h.vm.busy, false);
    assert.equal(h.vm.error, "L'agent est encore en train de répondre.");

    await h.vm.send('Question');
    await settle();
    assert.equal(h.calls.ask.length, 1);
    assert.equal(h.vm.error, '');
});

test('a run nobody picks up warns after 20 seconds and gives up after two minutes', async () => {
    let waiting = 5;
    const h = page({ api: { run: async () => ({ data: { finished: false, waiting_seconds: waiting } }) } });
    await h.vm.start();
    h.vm.schedule = () => {};
    await h.vm.send('Question');
    await settle();

    assert.equal(h.vm.stalled, false);
    assert.equal(h.vm.busy, true);

    waiting = 30;
    await h.vm.follow(60, h.vm.token);
    assert.equal(h.vm.stalled, true);
    assert.equal(h.vm.busy, true);

    waiting = 150;
    await h.vm.follow(60, h.vm.token);
    assert.equal(h.vm.busy, false);
    assert.equal(h.vm.error, 'agent.not_started');
});

test('the answer of a conversation left in the meantime is ignored', async () => {
    let release;
    const gate = new Promise(r => { release = r; });
    const h = page({ api: { run: async () => { await gate; return { data: { finished: true, waiting_seconds: 0, error: 'vieille erreur' } }; } } });
    await h.vm.start();
    await h.vm.send('Question');
    await settle(2);

    // The user starts another conversation while the first one is being answered
    h.vm.newConversation();
    release();
    await settle();

    assert.equal(h.vm.error, '');
    assert.deepEqual(h.vm.messages, []);
    assert.equal(h.vm.conversationId, null);
});

test('choosing another agent starts a new conversation', async () => {
    const h = page();
    await h.vm.start();
    await h.vm.send('Un');
    await settle();
    assert.equal(h.vm.conversationId, 50);

    h.vm.selectAgent(2);
    assert.equal(h.vm.selectedAgentId, 2);
    assert.equal(h.vm.conversationId, null);
    assert.deepEqual(h.vm.messages, []);

    // The same agent again changes nothing
    h.vm.conversationId = 7;
    h.vm.selectAgent(2);
    assert.equal(h.vm.conversationId, 7);
});

test('opening a past conversation shows it and selects its agent', async () => {
    const h = page();
    await h.vm.start();
    h.vm.selectedAgentId = 2;

    await h.vm.openConversation(40);

    assert.deepEqual(h.calls.conversation, [40]);
    assert.equal(h.vm.conversationId, 40);
    assert.equal(h.vm.selectedAgentId, 1);
    assert.equal(h.vm.conversationTitle, 'Titre');
    assert.equal(h.vm.busy, false);
});

test('opening a conversation whose answer is still being written follows it', async () => {
    let polls = 0;
    const h = page({ api: {
        conversation: async id => ({ data: { id, title: 'En cours', agent: { id: 1 }, messages: [], reports: [], pending_actions: [], run: { id: 3, finished: false } } }),
        run: async () => { polls++; await new Promise(r => setImmediate(r)); return { data: { finished: true, waiting_seconds: 0 } }; },
    } });
    await h.vm.start();

    await h.vm.openConversation(41);
    assert.equal(h.vm.busy, true);
    await settle();

    assert.equal(polls, 1);
    assert.equal(h.vm.busy, false);
});

test('a document written by the agent opens by itself, beside the conversation', async () => {
    const h = page({ api: { conversation: async id => ({ data: { id, title: 'T', agent: { id: 1 }, messages: [{ role: 'agent', kind: 'text', text: 'Voilà', run_id: 3 }], reports: [{ id: 9, run_id: 3, title: 'Rapport' }], pending_actions: [], run: { id: 3, finished: true } } }) } });
    // A narrow screen: the panel is closed until a document arrives
    global.window = { innerWidth: 800 };
    await h.vm.start();
    assert.equal(h.vm.canvasOpen, false);

    await h.vm.send('Rapport');
    await settle(20);

    assert.deepEqual(h.calls.report, [9]);
    assert.equal(h.vm.canvasOpen, true);
    assert.equal(h.vm.openedReport.title, 'Rapport');
    assert.equal(h.vm.openedReportId, 9);
    assert.deepEqual(h.vm.items.map(i => i.kind), ['agent', 'report']);
});

test('a document already in a conversation that is opened does not pop up again', async () => {
    const h = page({ api: { conversation: async id => ({ data: { id, title: 'T', agent: { id: 1 }, messages: [], reports: [{ id: 9, run_id: 3, title: 'Rapport' }], pending_actions: [], run: { id: 3, finished: true } } }) } });
    await h.vm.start();

    await h.vm.openConversation(40);

    // Opening a past conversation shows its documents as cards; none is forced open
    assert.deepEqual(h.calls.report, []);
    assert.equal(h.vm.reports.length, 1);
});

test('opening a document twice reads it once; closing the panel forgets it', async () => {
    const h = page();
    await h.vm.openReportById(9);
    await h.vm.openReportById(9);
    assert.deepEqual(h.calls.report, [9]);

    h.vm.closeCanvas();
    assert.equal(h.vm.canvasOpen, false);
    assert.equal(h.vm.openedReport, null);
});

test('a document that cannot be read shows the reason', async () => {
    const h = page({ api: { report: async () => { const e = new Error('x'); e.response = { data: { message: 'Introuvable' } }; throw e; } } });
    await h.vm.openReportById(9);

    assert.equal(h.vm.reportError, 'Introuvable');
    assert.equal(h.vm.reportLoading, false);
});

test('deleting a conversation asks first, archives it, and leaves it if it was open', async () => {
    const h = page();
    await h.vm.start();
    await h.vm.openConversation(40);

    global.confirm = () => false;
    await h.vm.removeConversation({ id: 40, title: 'Ancienne' });
    assert.deepEqual(h.calls.archive, []);

    global.confirm = () => true;
    await h.vm.removeConversation({ id: 40, title: 'Ancienne' });
    assert.deepEqual(h.calls.archive, [40]);
    assert.equal(h.vm.conversationId, null);
    assert.deepEqual(h.vm.messages, []);
});

test('confirming or refusing an action goes through the server then refreshes the conversation', async () => {
    const h = page();
    await h.vm.start();
    await h.vm.openConversation(40);

    await h.vm.approve({ id: 5 });
    await h.vm.reject({ id: 6 });

    assert.deepEqual(h.calls.approve, [5]);
    assert.deepEqual(h.calls.reject, [6]);
    assert.deepEqual(h.vm.deciding, { 5: false, 6: false });
    assert.equal(h.calls.conversation.length, 3);
});

test('a refused decision shows the reason', async () => {
    const h = page({ api: { approve: async () => { const e = new Error('x'); e.response = { data: { message: 'Cette action a déjà été traitée ou a expiré.' } }; throw e; } } });
    await h.vm.start();
    await h.vm.openConversation(40);

    await h.vm.approve({ id: 5 });

    assert.equal(h.vm.error, 'Cette action a déjà été traitée ou a expiré.');
});

test('the information of an agent slides in, reads the whole agent and can edit it', async () => {
    const h = page();
    await h.vm.start();

    await h.vm.showInfo(1);
    assert.equal(h.vm.infoOpen, true);
    assert.deepEqual(h.calls.get, [1]);
    assert.equal(h.vm.infoAgent.instructions, 'Aide-moi');

    h.vm.editAgent(h.vm.infoAgent);
    assert.equal(h.vm.infoOpen, false);
    assert.deepEqual(h.commits.slice(-2).map(c => c[0]), ['SET_AGENT_EDITING', 'openModal']);
    assert.equal(h.commits.slice(-1)[0][1], 'agent-form');
});

test('the agent bar can start the creation of an agent', () => {
    const h = page();
    h.vm.createAgent();

    assert.deepEqual(h.commits, [['SET_AGENT_EDITING', null], ['openModal', 'agent-form']]);
});

test('leaving the page stops following the answer', async () => {
    const h = page();
    await h.vm.start();
    const before = h.vm.token;
    h.component.beforeUnmount.call(h.vm);

    assert.equal(h.vm.token, before + 1);
    assert.equal(h.vm.timer, null);
});

// Where the page lives

test('the Claude menu of the header opens the page of the agents instead of claude.ai', () => {
    const header = read('resources/js/components/layouts/Header.vue');
    const start = header.indexOf('<!-- Claude -->');
    const menu = header.slice(start, header.indexOf('<svg', start));

    assert.match(menu, /label="Claude"/);
    assert.match(menu, /name: 'agent'/);
    assert.match(menu, /project: project\.slug/);
    assert.doesNotMatch(menu, /href=|target="_blank"/);
});

test('the route of the page exists in the project', () => {
    const router = read('resources/js/router/index.js');

    assert.match(router, /const AgentPage = \(\) => import\("@\/components\/agent\/chat\/AgentPage\.vue"\)/);
    assert.match(router, /name: "agent",\s+path: "agent",\s+component: AgentPage/);
});

test('the three columns and the agent menu are in the page', () => {
    const template = parse(read('resources/js/components/agent/chat/AgentPage.vue')).descriptor.template.content;

    for (const tag of ['<agent-bar', '<conversation-history', '<chat-panel', '<report-panel', '<agent-info']) {
        assert.ok(template.includes(tag), tag);
    }
    // The menu of the agents comes first, above the columns
    assert.ok(template.indexOf('<agent-bar') < template.indexOf('<conversation-history'));
    assert.ok(template.indexOf('<conversation-history') < template.indexOf('<chat-panel'));
    assert.ok(template.indexOf('<chat-panel') < template.indexOf('<report-panel'));
});
