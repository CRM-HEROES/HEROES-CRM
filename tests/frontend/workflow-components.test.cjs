const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { parse, compileTemplate } = require('@vue/compiler-sfc');
const { parseSync } = require('@babel/core');

const root = path.resolve(__dirname, '../..');
const directory = path.join(root, 'resources/js/components/workflow');
const components = fs.readdirSync(directory).filter((file) => file.endsWith('.vue'));
const lang = JSON.parse(fs.readFileSync(path.join(root, 'resources/js/lang/fr.json'), 'utf8'));

function translation(key) {
    return key.split('.').reduce((value, part) => (value === undefined ? undefined : value[part]), lang);
}

test('the workflow components exist', () => {
    assert.deepEqual(
        components.sort(),
        ['ConditionGroup.vue', 'ExecutionSteps.vue', 'FieldInput.vue', 'NodeEditor.vue', 'ProspectWorkflowsSlide.vue', 'Workflow.vue', 'WorkflowBuilder.vue', 'WorkflowMonitor.vue']
    );
});

for (const file of components) {
    const source = fs.readFileSync(path.join(directory, file), 'utf8');

    test(`${file} is a valid component (template and script compile)`, () => {
        const { descriptor, errors } = parse(source, { filename: file });
        assert.deepEqual(errors.map(String), []);

        const template = compileTemplate({ source: descriptor.template.content, filename: file, id: file });
        assert.deepEqual(template.errors.map(String), []);

        // The script must be valid modern JavaScript (imports, spread, optional chaining...)
        assert.doesNotThrow(() => parseSync(descriptor.script.content, { babelrc: false, configFile: false, sourceType: 'module', filename: file + '.js' }));
    });

    test(`${file} only uses translations that exist`, () => {
        const missing = [];
        const dynamic = [];

        // $t('workflow.x'), t('x') (builder helper, prefixed by workflow.), t(`x.${y}`)
        const patterns = [
            { regex: /\$t\(\s*(["'`])((?:(?!\1).)+)\1/g, prefix: '' },
            { regex: /[^$\w.]t\(\s*(["'`])((?:(?!\1).)+)\1/g, prefix: 'workflow.' },
        ];

        for (const { regex, prefix } of patterns) {
            for (const [, , key] of source.matchAll(regex)) {
                const full = prefix + key;

                if (full.includes('${') || /['"`]\s*\+/.test(full) || full.endsWith('.')) {
                    dynamic.push(full);
                } else if (full.startsWith('workflow.') || full.startsWith('header.')) {
                    if (translation(full) === undefined) {
                        missing.push(full);
                    }
                }
            }
        }

        // $t('workflow.x.' + y): the group must exist
        for (const [, , group] of source.matchAll(/\$t\(\s*(["'])(workflow\.[\w.]+?)\.\1\s*\+/g)) {
            assert.ok(translation(group) && typeof translation(group) === 'object', `Missing group ${group}`);
        }

        assert.deepEqual(missing, []);
    });
}

test('every dynamic translation group used by the builder exists', () => {
    const groups = [
        'status', 'executions', 'save', 'action', 'confirm', 'done', 'error', 'node', 'node_help', 'trigger', 'event',
        'category', 'action_type', 'field', 'on_error', 'no_provider', 'wait', 'weekday', 'condition',
        'tab', 'exec_status', 'step_status', 'monitor',
    ];

    for (const group of groups) {
        assert.equal(typeof lang.workflow[group], 'object', `workflow.${group}`);
    }

    // Branches: the labels of the output ports
    assert.equal(lang.workflow.yes, 'Oui');
    assert.equal(lang.workflow.no, 'Non');
});

test('every refusal of the graph model has a message', () => {
    // The errors returned by connect() in workflow-graph.js
    for (const error of ['unknown_node', 'self_link', 'into_trigger', 'from_end', 'bad_output', 'cycle']) {
        assert.ok(lang.workflow.error[error], `workflow.error.${error}`);
    }
});

test('the permissions of the workflows are in both permission lists', () => {
    for (const file of ['permissions.js', 'role-permissions.js']) {
        const source = fs.readFileSync(path.join(root, 'resources/js/utils', file), 'utf8');
        const block = source.slice(source.indexOf('key: "workflow"'));

        for (const key of ['view', 'add', 'update', 'delete', 'execute']) {
            assert.ok(block.slice(0, 1500).includes(`key: "${key}"`), `${file}: ${key}`);
        }
    }
});

test('the menu and the routes are wired', () => {
    const header = fs.readFileSync(path.join(root, 'resources/js/components/layouts/Header.vue'), 'utf8');
    const router = fs.readFileSync(path.join(root, 'resources/js/router/index.js'), 'utf8');

    assert.ok(header.includes("can('all.project.workflow.view')"));
    assert.ok(header.includes("name: 'workflow'"));
    assert.ok(router.includes('name: "workflow"') && router.includes('name: "workflow.show"'));
    assert.ok(router.includes('components/workflow/Workflow.vue'));
});

test('the prospect page opens the automations of the prospect, for who may see them', () => {
    const read = (file) => fs.readFileSync(path.join(root, 'resources/js', file), 'utf8');
    const slide = read('components/workflow/ProspectWorkflowsSlide.vue');
    const profile = read('components/prospect/profile/Profile.vue');
    const main = read('components/layouts/Main.vue');

    // The icon opens the slide of that name, which is mounted in the main layout
    assert.ok(slide.includes('name="prospect-manage-workflows"'));
    assert.ok(profile.includes('OPEN_SLIDE, "prospect-manage-workflows"'));
    assert.ok(main.includes('components/workflow/ProspectWorkflowsSlide.vue'));

    // ... and both the icon and the slide need the permission of the follow-up
    assert.ok(profile.includes("can('all.project.workflow.execution.view')"));
    assert.ok(main.includes("can('all.project.workflow.execution.view')"));

    for (const file of ['utils/permissions.js', 'utils/role-permissions.js']) {
        assert.ok(read(file).includes('key: "execution"'), file);
    }
});

test('the follow-up calls the endpoints of the API', () => {
    const api = fs.readFileSync(path.join(root, 'resources/js/apis/project/workflow.js'), 'utf8');

    for (const route of ['workflow-execution`', 'workflow-execution/${execution}`', 'workflow-execution/${execution}/cancel', 'workflow-stats', 'prospect/${prospect}/workflow-execution']) {
        assert.ok(api.includes(route), route);
    }
});
