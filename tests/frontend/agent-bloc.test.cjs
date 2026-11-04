const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { transformSync } = require('@babel/core');

const root = path.resolve(__dirname, '../..');

/**
 * Loads an ES module / SFC script with its dependencies replaced.
 */
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

function sfcScript(relative) {
    return parse(fs.readFileSync(path.join(root, relative), 'utf8')).descriptor.script.content;
}

const NOOP = () => ({});

test('the agent screens compile', () => {
    for (const relative of [
        'resources/js/components/agent/FormModal.vue',
        'resources/js/components/agent/ActionCard.vue',
        'resources/js/components/project/profile/blocs/agent/Bloc.vue',
        'resources/js/components/project/profile/blocs/agent/AgentRow.vue',
        'resources/js/components/project/profile/ProfileBloc.vue',
    ]) {
        const file = path.join(root, relative);
        const { descriptor } = parse(fs.readFileSync(file, 'utf8'));
        assert.deepEqual(compileTemplate({ source: descriptor.template.content, filename: file, id: 'agent' }).errors, [], relative);
    }
});

test('the profile page knows the agent bloc', () => {
    const source = fs.readFileSync(path.join(root, 'resources/js/components/project/profile/ProfileBloc.vue'), 'utf8');
    assert.match(source, /bloc\.type == 'agent'/);
    assert.match(source, /AgentBloc/);
});

// The layout of the profile page

function profileLayout(saved) {
    const file = fs.readFileSync(path.join(root, 'resources/js/store/project/user/setting.js'), 'utf8');
    // Only the getter is needed: take it out of the module, whatever it imports
    const start = file.indexOf('projectUserSettingsProjectProfile(state, getters) {');
    const end = file.indexOf('projectUserSettingsAgendaMinimumHour');
    const body = file.slice(start, end).replace(/\/\*\*[\s\S]*$/, '').trim().replace(/,\s*$/, '');
    const getter = new Function(`return { ${body} }`)().projectUserSettingsProjectProfile;

    return getter({}, { projectUserSetting: () => saved });
}

const types = layout => layout.map(column => column.map(bloc => bloc.type));

test('the default layout shows the agents right after the phone lines', () => {
    const layout = profileLayout(undefined);
    const column = types(layout).find(c => c.includes('line'));
    assert.equal(column[column.indexOf('line') + 1], 'agent');
    assert.equal(types(layout).flat().filter(t => t === 'agent').length, 1);
});

test('a layout the user saved before gets the agents bloc after the phone lines, nothing else moves', () => {
    const saved = [[{ type: 'user' }], [{ type: 'document' }, { type: 'line' }, { type: 'api' }], [{ type: 'field' }]];
    const layout = profileLayout(saved);

    assert.deepEqual(types(layout), [['user'], ['document', 'line', 'agent', 'api'], ['field']]);
    // The saved setting itself is not modified
    assert.deepEqual(types(saved), [['user'], ['document', 'line', 'api'], ['field']]);
});

test('a saved layout without phone lines gets it at the end of the last column', () => {
    assert.deepEqual(types(profileLayout([[{ type: 'user' }], [{ type: 'field' }]])), [['user'], ['field', 'agent']]);
});

test('a layout that already has the bloc is left alone, wherever the user put it', () => {
    const saved = [[{ type: 'agent' }, { type: 'user' }], [{ type: 'line' }]];
    assert.deepEqual(types(profileLayout(saved)), [['agent', 'user'], ['line']]);
});

// The store

test('the chat window no longer exists anywhere', () => {
    assert.equal(fs.existsSync(path.join(root, 'resources/js/components/agent/AgentsModal.vue')), false);
    for (const relative of ['resources/js/components/layouts/Main.vue', 'resources/js/components/layouts/setting/Layout.vue', 'resources/js/components/project/profile/blocs/agent/AgentRow.vue']) {
        const source = fs.readFileSync(path.join(root, relative), 'utf8');
        assert.doesNotMatch(source, /AgentsModal|agents-modal|manageAgents|SET_AGENT_CHAT|"agents"\)/, relative);
    }
});

test('the store keeps the agents and the one being edited', () => {
    const calls = [];
    const agentStore = load(fs.readFileSync(path.join(root, 'resources/js/store/agent.js'), 'utf8'), {
        '@/apis/agent': { list: async () => { calls.push('list'); return { data: [{ id: 1 }, { id: 2 }] }; } },
    }).default;
    const state = JSON.parse(JSON.stringify(agentStore.state));

    assert.equal(agentStore.getters.agentsLoaded(state), false);
    agentStore.mutations.SET_AGENTS(state, [{ id: 1 }]);
    assert.equal(agentStore.getters.agentsLoaded(state), true);

    const source = { id: 5, name: 'Lyra' };
    agentStore.mutations.SET_AGENT_EDITING(state, source);
    // A copy: editing never changes the list by reference
    assert.notEqual(agentStore.getters.agentEditing(state), source);
    agentStore.mutations.SET_AGENT_EDITING(state, null);
    assert.equal(agentStore.getters.agentEditing(state), null);

});

test('fetching the agents fills the store', async () => {
    const agentStore = load(fs.readFileSync(path.join(root, 'resources/js/store/agent.js'), 'utf8'), {
        '@/apis/agent': { list: async () => ({ data: [{ id: 1 }, { id: 2 }] }) },
    }).default;
    const commits = [];
    await agentStore.actions.FETCH_AGENTS({ commit: (...args) => commits.push(args) });

    assert.deepEqual(commits, [['SET_AGENTS', [{ id: 1 }, { id: 2 }]]]);
});

// The form: three steps, nothing technical asked

const CATALOG = {
    default_model: 'claude-opus-5-5',
    models: { 'claude-opus-5-5': 'Opus' },
    autonomies: { read_only: 'a', propose: 'b', auto: 'c' },
    skills: [{ key: 'crm_exploration', label: 'Consulter', writes: false }],
};

function form({ editing = null, api = {} } = {}) {
    const commits = [];
    const dispatches = [];
    const calls = { create: [], update: [], remove: [], get: [], upload: [], removeAvatar: [] };
    const apis = {
        catalog: async () => ({ data: CATALOG }),
        get: async id => { calls.get.push(id); return { data: { id, name: 'Lyra', avatar_url: '/api/agent-avatar/actuelle', persona: 'Posée', instructions: 'Aide-moi', autonomy: 'auto', project: { id: 7, slug: 'alpha', name: 'Alpha' }, all_skills: true } }; },
        create: async body => { calls.create.push(body); return { data: { id: 9, name: body.name } }; },
        uploadAvatar: async (id, file) => { calls.upload.push([id, file]); return { data: { avatar_url: '/api/agent-avatar/x' } }; },
        removeAvatar: async id => { calls.removeAvatar.push(id); return { data: {} }; },
        update: async (id, body) => { calls.update.push([id, body]); return { data: {} }; },
        remove: async id => { calls.remove.push(id); return { data: {} }; },
        ...api,
    };
    const exports = load(sfcScript('resources/js/components/agent/FormModal.vue'), {
        vuex: { mapGetters: () => ({}) },
        '@/store': { commit: (...args) => commits.push(args), dispatch: async (...args) => { dispatches.push(args); } },
        '@/apis/agent': apis,
        '@/actions/modal': { CLOSE_MODAL: 'closeModal' },
        './ui/AgentButton.vue': {},
    });
    const component = exports.default;
    global.URL.createObjectURL = file => `blob:${file.name}`;
    global.URL.revokeObjectURL = () => {};
    const vm = { ...component.data.call({ blank: component.methods.blank }), project: { name: 'Alpha', slug: 'alpha' }, agentEditing: editing, $t: (key, params) => key + (params ? JSON.stringify(params) : '') };
    vm.blank = component.methods.blank;
    for (const [name, method] of Object.entries(component.methods)) vm[name] = method.bind(vm);
    for (const name of ['editing', 'current', 'last', 'canNext', 'canSave', 'autonomyLevels', 'scopeText']) {
        Object.defineProperty(vm, name, { get: () => component.computed[name].call(vm), configurable: true });
    }

    return { vm, calls, commits, dispatches, component };
}

test('the form asks neither for a model, nor skills, nor a project', () => {
    const source = sfcScript('resources/js/components/agent/FormModal.vue');
    const template = parse(fs.readFileSync(path.join(root, 'resources/js/components/agent/FormModal.vue'), 'utf8')).descriptor.template.content;

    assert.doesNotMatch(template, /form\.model|form\.skills|form\.scope|catalog\.models|catalog\.skills/);
    assert.doesNotMatch(source, /skills:|scope:/);
});

test('a new agent starts blank, on the first of three steps, with the safe autonomy', async () => {
    const h = form();
    await h.vm.load();

    assert.deepEqual(h.vm.steps, ['identity', 'mission', 'autonomy']);
    assert.equal(h.vm.current, 'identity');
    assert.equal(h.vm.editing, false);
    assert.equal(h.vm.form.name, '');
    // Nothing is changed in the CRM unless the user lets the agent
    assert.equal(h.vm.form.autonomy, 'propose');
    assert.equal(h.vm.canNext, false);
});

test('one cannot leave the first step without a name, then moves forward and back', async () => {
    const h = form();
    await h.vm.load();

    h.vm.next();
    assert.equal(h.vm.step, 0);
    assert.equal(h.vm.canGoTo(2), false);

    h.vm.form.name = 'Lyra';
    h.vm.next();
    assert.equal(h.vm.current, 'mission');
    h.vm.next();
    assert.equal(h.vm.current, 'autonomy');
    assert.equal(h.vm.last, true);
    // Already on the last step: next does nothing more
    h.vm.next();
    assert.equal(h.vm.step, 2);

    h.vm.back();
    h.vm.back();
    h.vm.back();
    assert.equal(h.vm.step, 0);
    h.vm.goTo(2);
    assert.equal(h.vm.current, 'autonomy');
});

test('Enter on the first steps moves on and never creates the agent', async () => {
    const h = form();
    await h.vm.load();
    h.vm.form.name = 'Lyra';

    h.vm.submit();
    assert.equal(h.vm.current, 'mission');
    h.vm.submit();
    await Promise.resolve();
    assert.equal(h.calls.create.length, 0);
    assert.equal(h.vm.current, 'autonomy');
});

test('the agent is created in the project where the user is, with the default model and every skill left to the server', async () => {
    const h = form();
    await h.vm.load();
    h.vm.form.name = ' Lyra ';
    h.vm.form.instructions = 'Analyse les performances.';
    h.vm.form.autonomy = 'auto';
    h.vm.goTo(2);

    await h.vm.submit();

    assert.equal(h.calls.create.length, 1);
    const body = h.calls.create[0];
    assert.equal(body.project, 'alpha');
    assert.equal(body.autonomy, 'auto');
    assert.equal(body.instructions, 'Analyse les performances.');
    // Nothing technical is sent: the server applies the default model and all the skills
    for (const key of ['model', 'skills', 'effort', 'all_skills', 'scope']) assert.equal(key in body, false, key);
    assert.deepEqual(h.dispatches, [['FETCH_AGENTS']]);
    assert.deepEqual(h.commits, [['closeModal']]);
    assert.equal(h.vm.saving, false);
});

test('the last step shows a recap with the project of the agent', async () => {
    const h = form();
    await h.vm.load();

    assert.equal(h.vm.scopeText, 'agent.this_project{"name":"Alpha"}');
    assert.deepEqual(h.vm.autonomyLevels.map(l => l.value), ['read_only', 'propose', 'auto']);
});

test('editing reads the whole agent, keeps its project and changes only what the form shows', async () => {
    const h = form({ editing: { id: 4, name: 'Lyra', project: { id: 7, slug: 'alpha', name: 'Alpha' } } });
    await h.vm.load();

    assert.deepEqual(h.calls.get, [4]);
    assert.equal(h.vm.editing, true);
    assert.equal(h.vm.form.instructions, 'Aide-moi');
    assert.equal(h.vm.form.autonomy, 'auto');

    h.vm.form.name = 'Lyra 2';
    h.vm.goTo(2);
    await h.vm.submit();

    assert.equal(h.calls.create.length, 0);
    assert.equal(h.calls.update[0][0], 4);
    assert.equal(h.calls.update[0][1].name, 'Lyra 2');
    for (const key of ['project', 'model', 'skills', 'all_skills']) assert.equal(key in h.calls.update[0][1], false, key);
});

test('a refused save shows the reason, keeps the window open and allows a retry', async () => {
    let attempts = 0;
    const h = form({
        api: {
            create: async () => {
                attempts++;
                if (attempts === 1) {
                    const error = new Error('refused');
                    error.response = { data: { message: 'invalid', errors: { name: ['Le nom est trop long.'] } } };
                    throw error;
                }
                return { data: {} };
            },
        },
    });
    await h.vm.load();
    h.vm.form.name = 'Lyra';
    h.vm.goTo(2);

    await h.vm.submit();
    assert.equal(h.vm.error, 'Le nom est trop long.');
    assert.deepEqual(h.commits, []);
    assert.equal(h.vm.saving, false);

    await h.vm.submit();
    assert.equal(h.vm.error, '');
    assert.deepEqual(h.commits, [['closeModal']]);
});

test('a double click on Create sends the agent once', async () => {
    const h = form();
    await h.vm.load();
    h.vm.form.name = 'Lyra';
    h.vm.goTo(2);

    await Promise.all([h.vm.submit(), h.vm.submit()]);

    assert.equal(h.calls.create.length, 1);
});

test('opening the window again starts over on the first step with a blank form', async () => {
    const h = form();
    await h.vm.load();
    h.vm.form.name = 'Lyra';
    h.vm.goTo(2);

    await h.vm.load();

    assert.equal(h.vm.step, 0);
    assert.equal(h.vm.form.name, '');
});

test('deleting asks first and does nothing when refused', async () => {
    const h = form({ editing: { id: 4, name: 'Lyra' } });
    await h.vm.load();

    global.confirm = () => false;
    await h.vm.remove();
    assert.deepEqual(h.calls.remove, []);

    global.confirm = () => true;
    await h.vm.remove();
    assert.deepEqual(h.calls.remove, [4]);
    assert.deepEqual(h.commits, [['closeModal']]);
    delete global.confirm;
});

// The picture

function picture(name = 'moi.png', { type = 'image/png', size = 50_000 } = {}) {
    return { name, type, size };
}

function choose(h, file) {
    const input = { files: file ? [file] : [], value: 'C:\\fakepath\\x' };
    h.vm.pickPhoto({ target: input });

    return input;
}

test('a photo chosen in the first step is shown, then sent after the agent is created', async () => {
    const h = form();
    await h.vm.load();
    assert.equal(h.vm.photoPreview, null);

    const input = choose(h, picture('moi.png'));
    assert.equal(h.vm.photoPreview, 'blob:moi.png');
    // The same file can be chosen again
    assert.equal(input.value, '');

    h.vm.form.name = 'Lyra';
    h.vm.goTo(2);
    await h.vm.submit();

    assert.equal(h.calls.create.length, 1);
    // The picture is not part of the JSON of the agent
    assert.equal('photo' in h.calls.create[0], false);
    assert.deepEqual(h.calls.upload, [[9, picture('moi.png')]]);
    assert.deepEqual(h.commits.filter(c => c[0] === 'closeModal'), [['closeModal']]);
});

test('no photo chosen: nothing is uploaded', async () => {
    const h = form();
    await h.vm.load();
    h.vm.form.name = 'Lyra';
    h.vm.goTo(2);
    await h.vm.submit();

    assert.deepEqual(h.calls.upload, []);
    assert.deepEqual(h.calls.removeAvatar, []);
});

test('a file that is not a picture, or too heavy, is refused before any request', async () => {
    const h = form();
    await h.vm.load();

    choose(h, picture('script.svg', { type: 'image/svg+xml' }));
    assert.equal(h.vm.error, 'agent.photo_type');
    assert.equal(h.vm.photo, null);

    choose(h, picture('trop-lourde.jpg', { type: 'image/jpeg', size: 3 * 1024 * 1024 }));
    assert.equal(h.vm.error, 'agent.photo_size');
    assert.equal(h.vm.photoPreview, null);

    // A good one clears the message
    choose(h, picture('bonne.webp', { type: 'image/webp' }));
    assert.equal(h.vm.error, '');
    assert.equal(h.vm.photoPreview, 'blob:bonne.webp');
});

test('cancelling the file dialog keeps the current choice', async () => {
    const h = form();
    await h.vm.load();
    choose(h, picture('moi.png'));

    choose(h, null);

    assert.equal(h.vm.photoPreview, 'blob:moi.png');
});

test('editing shows the current picture, replaces it only when a new one is chosen', async () => {
    const h = form({ editing: { id: 4, name: 'Lyra', avatar_url: '/api/agent-avatar/actuelle' } });
    await h.vm.load();
    assert.equal(h.vm.photoPreview, '/api/agent-avatar/actuelle');

    // Saved without touching the picture: no request about it
    h.vm.goTo(2);
    await h.vm.submit();
    assert.deepEqual(h.calls.upload, []);
    assert.deepEqual(h.calls.removeAvatar, []);

    // Replaced
    const again = form({ editing: { id: 4, name: 'Lyra', avatar_url: '/api/agent-avatar/actuelle' } });
    await again.vm.load();
    choose(again, picture('nouvelle.png'));
    again.vm.goTo(2);
    await again.vm.submit();
    assert.deepEqual(again.calls.upload, [[4, picture('nouvelle.png')]]);
});

test('removing the picture of an existing agent asks the server to delete it', async () => {
    const h = form({ editing: { id: 4, name: 'Lyra', avatar_url: '/api/agent-avatar/actuelle' } });
    await h.vm.load();

    h.vm.removePhoto();
    assert.equal(h.vm.photoPreview, null);
    h.vm.goTo(2);
    await h.vm.submit();

    assert.deepEqual(h.calls.removeAvatar, [4]);
    assert.deepEqual(h.calls.upload, []);
});

test('removing a picture that was only chosen sends nothing to the server', async () => {
    const h = form();
    await h.vm.load();
    h.vm.form.name = 'Lyra';
    choose(h, picture('moi.png'));
    h.vm.removePhoto();
    h.vm.goTo(2);
    await h.vm.submit();

    assert.deepEqual(h.calls.upload, []);
    assert.deepEqual(h.calls.removeAvatar, []);
});

test('if the picture is refused after the agent was created, the agent is kept and a retry does not create a second one', async () => {
    let attempts = 0;
    const h = form({
        api: {
            uploadAvatar: async (id, file) => {
                attempts++;
                if (attempts === 1) {
                    const error = new Error('refused');
                    error.response = { data: { message: 'x', errors: { photo: ['Cette photo ne peut pas être lue.'] } } };
                    throw error;
                }
                h.calls.upload.push([id, file]);
                return { data: {} };
            },
        },
    });
    await h.vm.load();
    h.vm.form.name = 'Lyra';
    choose(h, picture('moi.png'));
    h.vm.goTo(2);

    await h.vm.submit();

    assert.equal(h.vm.error, 'Cette photo ne peut pas être lue.');
    // The window stays open; the agent exists and is now the one edited
    assert.equal(h.commits.some(c => c[0] === 'closeModal'), false);
    assert.equal(h.commits.some(c => c[0] === 'SET_AGENT_EDITING' && c[1].id === 9), true);
    assert.equal(h.calls.create.length, 1);
    assert.equal(h.dispatches.some(d => d[0] === 'FETCH_AGENTS'), true);

    // The store now holds the agent: a second try updates it and uploads the picture
    h.vm.agentEditing = { id: 9, name: 'Lyra' };
    await h.vm.submit();

    assert.equal(h.calls.create.length, 1);
    assert.equal(h.calls.update.length, 1);
    assert.equal(h.calls.update[0][0], 9);
    assert.deepEqual(h.calls.upload, [[9, picture('moi.png')]]);
    assert.equal(h.commits.some(c => c[0] === 'closeModal'), true);
});

test('the row shows the picture of the agent, or the robot when it has none', () => {
    const source = fs.readFileSync(path.join(root, 'resources/js/components/project/profile/blocs/agent/AgentRow.vue'), 'utf8');
    assert.match(source, /v-if="agent\.avatar_url"/);
    assert.match(source, /:src="agent\.avatar_url"/);
    assert.match(source, /v-else class="fa fa-robot"/);
});
