const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { transformSync } = require('@babel/core');

// The module is an ES module; the other front tests compile their sources the same way
const filename = path.resolve(__dirname, '../../resources/js/utils/workflow-graph.js');
const { code } = transformSync(fs.readFileSync(filename, 'utf8'), {
    babelrc: false,
    configFile: false,
    plugins: ['@babel/plugin-transform-modules-commonjs'],
});
const module_ = { exports: {} };
new Function('module', 'exports', code)(module_, module_.exports);
const g = module_.exports;

function blank() {
    return g.addNode({ nodes: [], edges: [] }, g.createNode({ nodes: [], edges: [] }, 'trigger'));
}

function add(graph, type) {
    return g.addNode(graph, g.createNode(graph, type));
}

test('a node gets a free identifier and the default configuration of its type', () => {
    let graph = blank();
    graph = add(graph, 'action');
    graph = add(graph, 'action');

    assert.deepEqual(graph.nodes.map((n) => n.id), ['trigger1', 'action1', 'action2']);
    assert.deepEqual(g.findNode(graph, 'trigger1').config, { trigger: 'prospect.created' });
    assert.deepEqual(g.createNode(graph, 'wait_event').config, { event: 'sms_received', hours: 24 });
    assert.throws(() => g.createNode(graph, 'teleport'));
});

test('there is a single trigger, and it cannot be removed', () => {
    let graph = blank();

    assert.equal(add(graph, 'trigger').nodes.length, 1);
    assert.equal(g.removeNode(graph, 'trigger1').nodes.length, 1);
});

test('functions do not change the graph they receive', () => {
    const graph = add(blank(), 'action');
    const snapshot = JSON.stringify(graph);

    g.moveNode(graph, 'action1', 500, 500);
    g.connect(graph, 'trigger1', 'action1');
    g.removeNode(graph, 'action1');
    g.updateNode(graph, 'action1', { name: 'X', config: { action: 'send_sms' } });
    g.layout(graph);

    assert.equal(JSON.stringify(graph), snapshot);
});

test('removing a node removes the links that touch it', () => {
    let graph = add(add(blank(), 'action'), 'end');
    graph = g.connect(graph, 'trigger1', 'action1').graph;
    graph = g.connect(graph, 'action1', 'end1').graph;

    graph = g.removeNode(graph, 'action1');

    assert.deepEqual(graph.edges, []);
    assert.deepEqual(graph.nodes.map((n) => n.id), ['trigger1', 'end1']);
});

test('the outputs of a node depend on its type', () => {
    assert.deepEqual(g.outputs({ type: 'action' }), [null]);
    assert.deepEqual(g.outputs({ type: 'condition' }), ['yes', 'no']);
    assert.deepEqual(g.outputs({ type: 'wait_event' }), ['yes', 'no']);
    assert.deepEqual(g.outputs({ type: 'end' }), []);
});

test('links are refused when they make no sense, and say why', () => {
    let graph = add(add(add(blank(), 'action'), 'condition'), 'end');

    assert.equal(g.connect(graph, 'action1', 'action1').error, 'self_link');
    assert.equal(g.connect(graph, 'action1', 'trigger1').error, 'into_trigger');
    assert.equal(g.connect(graph, 'end1', 'action1').error, 'from_end');
    assert.equal(g.connect(graph, 'action1', 'end1', 'yes').error, 'bad_output');   // one way out has no yes / no
    assert.equal(g.connect(graph, 'condition1', 'end1').error, 'bad_output');       // a condition needs its way
    assert.equal(g.connect(graph, 'action1', 'ghost').error, 'unknown_node');

    const ok = g.connect(graph, 'condition1', 'end1', 'yes');
    assert.equal(ok.error, null);
    assert.deepEqual(ok.graph.edges, [{ from: 'condition1', to: 'end1', branch: 'yes' }]);
});

test('a link that brings the workflow back is refused', () => {
    let graph = add(add(blank(), 'action'), 'action');
    graph = g.connect(graph, 'trigger1', 'action1').graph;
    graph = g.connect(graph, 'action1', 'action2').graph;

    assert.equal(g.connect(graph, 'action2', 'action1').error, 'cycle');
    assert.equal(g.wouldCreateCycle(graph, 'action2', 'action1'), true);
    assert.equal(g.wouldCreateCycle(graph, 'action1', 'action2'), false);
});

test('an output has one link: linking it again replaces the previous one', () => {
    let graph = add(add(blank(), 'action'), 'action');
    graph = g.connect(graph, 'trigger1', 'action1').graph;
    graph = g.connect(graph, 'trigger1', 'action2').graph;

    assert.deepEqual(graph.edges, [{ from: 'trigger1', to: 'action2' }]);

    graph = add(graph, 'condition');
    graph = g.connect(graph, 'condition1', 'action1', 'yes').graph;
    graph = g.connect(graph, 'condition1', 'action2', 'no').graph;
    graph = g.connect(graph, 'condition1', 'action2', 'yes').graph; // replaces the "yes" only

    assert.deepEqual(graph.edges.filter((e) => e.from === 'condition1'), [
        { from: 'condition1', to: 'action2', branch: 'no' },
        { from: 'condition1', to: 'action2', branch: 'yes' },
    ]);
});

test('disconnecting removes only the link of that output', () => {
    let graph = add(add(add(blank(), 'condition'), 'action'), 'action');
    graph = g.connect(graph, 'condition1', 'action1', 'yes').graph;
    graph = g.connect(graph, 'condition1', 'action2', 'no').graph;

    graph = g.disconnect(graph, 'condition1', 'yes');

    assert.deepEqual(graph.edges, [{ from: 'condition1', to: 'action2', branch: 'no' }]);
});

test('orphans are the nodes no path from the trigger reaches', () => {
    let graph = add(add(blank(), 'action'), 'action');
    graph = g.connect(graph, 'trigger1', 'action1').graph;

    assert.deepEqual(g.orphans(graph), ['action2']);
    assert.deepEqual(g.orphans({ nodes: [{ id: 'a', type: 'action' }], edges: [] }), ['a']); // no trigger: everything
});

test('the links are drawn from the right port of a node to the input of the next', () => {
    let graph = add(add(blank(), 'condition'), 'end');
    graph = g.moveNode(graph, 'trigger1', 0, 0);
    graph = g.moveNode(graph, 'condition1', 0, 200);
    graph = g.moveNode(graph, 'end1', 400, 400);
    graph = g.connect(graph, 'trigger1', 'condition1').graph;
    graph = g.connect(graph, 'condition1', 'end1', 'no').graph;

    const [first, second] = g.links(graph);

    assert.equal(first.key, 'trigger1::condition1');
    assert.match(first.path, /^M 115 76 C /);                                  // bottom centre of the trigger
    assert.match(first.path, / 115 200$/);                                      // top centre of the condition
    assert.equal(second.branch, 'no');
    assert.match(second.path, new RegExp(`^M ${230 * 0.7} ${200 + 76} `));      // the "no" port
});

test('links to a node that does not exist are not drawn', () => {
    const graph = { nodes: [{ id: 'a', type: 'action', position: { x: 0, y: 0 } }], edges: [{ from: 'a', to: 'ghost' }] };

    assert.deepEqual(g.links(graph), []);
});

test('the automatic layout puts each node below the longest path leading to it', () => {
    let graph = add(add(add(add(blank(), 'condition'), 'action'), 'action'), 'end');
    graph = g.connect(graph, 'trigger1', 'condition1').graph;
    graph = g.connect(graph, 'condition1', 'action1', 'yes').graph;
    graph = g.connect(graph, 'condition1', 'end1', 'no').graph;
    graph = g.connect(graph, 'action1', 'end1').graph; // end1 is reached by two paths: it goes under action1

    const arranged = g.layout(graph);
    const y = (id) => g.findNode(arranged, id).position.y;

    assert.ok(y('trigger1') < y('condition1'));
    assert.ok(y('condition1') < y('action1'));
    assert.ok(y('action1') < y('end1'));
    // The unconnected node is below everything
    assert.ok(y('action2') > y('end1'));
});

test('the size of the canvas contains every node', () => {
    const graph = g.moveNode(add(blank(), 'action'), 'action1', 1000, 800);
    const { width, height } = g.bounds(graph);

    assert.ok(width >= 1000 + g.NODE_WIDTH);
    assert.ok(height >= 800 + g.NODE_HEIGHT);
});

test('a graph from the server is normalised', () => {
    assert.deepEqual(g.normalize(null), { nodes: [], edges: [] });
    assert.deepEqual(g.normalize({ nodes: 'x' }), { nodes: [], edges: [] });
});

test('the "+" of an open output adds a step below, already linked', () => {
    const graph = { nodes: [{ id: 'trigger', type: 'trigger', config: {}, position: { x: 80, y: 60 } }], edges: [] };

    const { graph: next, node, error } = g.appendNode(graph, 'trigger', null, 'action');

    assert.equal(error, null);
    assert.equal(next.nodes.length, 2);
    assert.deepEqual(next.edges, [{ from: 'trigger', to: node.id }]);
    assert.ok(node.position.y > 60 + g.NODE_HEIGHT);
    assert.equal(graph.nodes.length, 1);
});

test('the "+" of a yes / no output links that branch, on its side', () => {
    const graph = { nodes: [{ id: 'condition1', type: 'condition', config: {}, position: { x: 300, y: 60 } }], edges: [] };

    const yes = g.appendNode(graph, 'condition1', 'yes', 'action');
    const no = g.appendNode(yes.graph, 'condition1', 'no', 'end');

    assert.deepEqual(no.graph.edges.map((e) => [e.branch, e.to]), [['yes', yes.node.id], ['no', no.node.id]]);
    assert.ok(yes.node.position.x < no.node.position.x);
});

test('adding on a linked output inserts the step and moves what follows down', () => {
    const graph = {
        nodes: [
            { id: 'trigger', type: 'trigger', config: {}, position: { x: 80, y: 60 } },
            { id: 'action1', type: 'action', config: {}, position: { x: 80, y: 216 } },
            { id: 'end1', type: 'end', config: {}, position: { x: 80, y: 372 } },
        ],
        edges: [{ from: 'trigger', to: 'action1' }, { from: 'action1', to: 'end1' }],
    };

    const { graph: next, node, error } = g.appendNode(graph, 'trigger', null, 'wait');

    assert.equal(error, null);
    assert.deepEqual(next.edges, [{ from: 'action1', to: 'end1' }, { from: 'trigger', to: node.id }, { from: node.id, to: 'action1' }]);
    assert.ok(next.nodes.find((n) => n.id === 'action1').position.y > 216);
    assert.ok(next.nodes.find((n) => n.id === 'end1').position.y > 372);
});

test('an end step cannot be inserted before another step', () => {
    const graph = {
        nodes: [
            { id: 'trigger', type: 'trigger', config: {}, position: { x: 80, y: 60 } },
            { id: 'action1', type: 'action', config: {}, position: { x: 80, y: 216 } },
        ],
        edges: [{ from: 'trigger', to: 'action1' }],
    };

    const result = g.appendNode(graph, 'trigger', null, 'end');

    assert.equal(result.error, 'from_end');
    assert.deepEqual(result.graph.edges, graph.edges);
});
