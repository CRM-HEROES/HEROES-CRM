const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { transformSync } = require('@babel/core');

const load = (relative, require_) => {
    const { code } = transformSync(fs.readFileSync(path.resolve(__dirname, '../..', relative), 'utf8'), {
        babelrc: false,
        configFile: false,
        plugins: ['@babel/plugin-transform-modules-commonjs'],
    });
    const module_ = { exports: {} };
    new Function('module', 'exports', 'require', code)(module_, module_.exports, require_ || require);
    return module_.exports;
};

const g = load('resources/js/utils/workflow-graph.js');
const t = load('resources/js/utils/workflow-templates.js', (id) => (id === './workflow-graph' ? g : require(id)));
const lang = JSON.parse(fs.readFileSync(path.resolve(__dirname, '../../resources/js/lang/fr.json'), 'utf8')).workflow;

test('there are ready-made workflows, each with a name and a description', () => {
    assert.ok(t.TEMPLATE_IDS.length >= 3);

    for (const id of t.TEMPLATE_IDS) {
        assert.ok(lang.template[id].name, id);
        assert.ok(lang.template[id].description, id);
    }
});

test('every template is a sound graph: one trigger, valid links, nothing left apart', () => {
    for (const id of t.TEMPLATE_IDS) {
        const graph = t.templateGraph(id);
        const ids = graph.nodes.map((n) => n.id);

        assert.equal(new Set(ids).size, ids.length, id);
        assert.equal(graph.nodes.filter((n) => n.type === 'trigger').length, 1, id);
        assert.deepEqual(g.orphans(graph), [], id);

        // Rebuilding it link by link gives the same links: each one respects the rules of the builder
        let rebuilt = { nodes: graph.nodes, edges: [] };
        for (const edge of graph.edges) {
            const result = g.connect(rebuilt, edge.from, edge.to, edge.branch || null);
            assert.equal(result.error, null, `${id}: ${edge.from} -> ${edge.to}`);
            rebuilt = result.graph;
        }
        assert.equal(rebuilt.edges.length, graph.edges.length, id);

        // Every output that exists has its link: no step is left open, except the ends
        for (const node of graph.nodes) {
            for (const branch of g.outputs(node)) {
                const linked = graph.edges.some((e) => e.from === node.id && (e.branch || null) === branch);
                const last = node.type === 'action' && !graph.edges.some((e) => e.from === node.id);
                assert.ok(linked || last, `${id}: ${node.id} ${branch}`);
            }
        }
    }
});

test('a template gives a new graph each time, and an unknown one gives nothing', () => {
    const a = t.templateGraph('welcome_email');
    a.nodes[0].config.trigger = 'x';

    assert.equal(t.templateGraph('welcome_email').nodes[0].config.trigger, 'prospect.created');
    assert.equal(t.templateGraph('nope'), null);
});
