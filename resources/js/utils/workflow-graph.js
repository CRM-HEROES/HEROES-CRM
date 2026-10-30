/**
 * Graph model of the workflow builder.
 *
 * Pure functions on a graph { nodes: [{ id, type, name, config, position }],
 * edges: [{ from, to, branch? }] }: they never change the graph they receive
 * and know nothing about Vue. The same format is what the API stores and the
 * engine executes; the rules enforced here are the ones that can be decided
 * without the server (the server validates everything again when publishing).
 */

export const NODE_WIDTH = 230;
export const NODE_HEIGHT = 76;

export const YES = "yes";
export const NO = "no";

const GAP_X = 70;
const GAP_Y = 80;
const ORIGIN_X = 80;
const ORIGIN_Y = 60;

/**
 * Starting configuration of each kind of node
 */
const DEFAULT_CONFIG = {
    trigger: () => ({ trigger: "prospect.created" }),
    condition: () => ({ op: "and", items: [] }),
    action: () => ({}),
    wait: () => ({ minutes: 30 }),
    wait_event: () => ({ event: "sms_received", hours: 24 }),
    end: () => ({}),
};

function clone(value) {
    return JSON.parse(JSON.stringify(value));
}

/**
 * Always the same shape, whatever was stored
 */
export function normalize(graph) {
    return {
        nodes: Array.isArray(graph && graph.nodes) ? clone(graph.nodes) : [],
        edges: Array.isArray(graph && graph.edges) ? clone(graph.edges) : [],
    };
}

export function findNode(graph, id) {
    return graph.nodes.find((node) => node.id === id) || null;
}

export function triggerNode(graph) {
    return graph.nodes.find((node) => node.type === "trigger") || null;
}

/**
 * Output ports of a node: [null] one way out, ["yes", "no"] two ways, [] none.
 */
export function outputs(node) {
    if (node.type === "end") {
        return [];
    }

    if (node.type === "condition" || node.type === "wait_event") {
        return [YES, NO];
    }

    return [null];
}

/**
 * A free identifier ("action3")
 */
export function nextId(graph, prefix) {
    const used = new Set(graph.nodes.map((node) => node.id));
    let i = 1;

    while (used.has(`${prefix}${i}`)) {
        i++;
    }

    return `${prefix}${i}`;
}

export function createNode(graph, type, position = { x: ORIGIN_X, y: ORIGIN_Y }, config) {
    const make = DEFAULT_CONFIG[type];

    if (!make) {
        throw new Error(`Unknown node type: ${type}`);
    }

    return {
        id: nextId(graph, type === "wait_event" ? "event" : type),
        type,
        name: null,
        config: config || make(),
        position: { x: Math.round(position.x), y: Math.round(position.y) },
    };
}

export function addNode(graph, node) {
    const next = normalize(graph);

    // One trigger per workflow
    if (node.type === "trigger" && triggerNode(next)) {
        return next;
    }

    next.nodes.push(clone(node));

    return next;
}

/**
 * The trigger cannot be removed: a workflow always has one.
 */
export function removeNode(graph, id) {
    const next = normalize(graph);
    const node = findNode(next, id);

    if (!node || node.type === "trigger") {
        return next;
    }

    next.nodes = next.nodes.filter((n) => n.id !== id);
    next.edges = next.edges.filter((edge) => edge.from !== id && edge.to !== id);

    return next;
}

export function moveNode(graph, id, x, y) {
    const next = normalize(graph);
    const node = findNode(next, id);

    if (node) {
        node.position = { x: Math.round(x), y: Math.round(y) };
    }

    return next;
}

/**
 * Change the name and / or the configuration of a node
 */
export function updateNode(graph, id, patch) {
    const next = normalize(graph);
    const node = findNode(next, id);

    if (node) {
        if ("name" in patch) {
            node.name = patch.name || null;
        }

        if ("config" in patch) {
            node.config = clone(patch.config);
        }
    }

    return next;
}

/**
 * Nodes that can be reached from $startId
 */
export function reachable(graph, startId) {
    const seen = new Set();
    const stack = [startId];

    while (stack.length) {
        const id = stack.pop();

        if (seen.has(id)) {
            continue;
        }

        seen.add(id);

        graph.edges.filter((edge) => edge.from === id).forEach((edge) => stack.push(edge.to));
    }

    return seen;
}

/**
 * Would the link from -> to bring the workflow back to a step it already went through?
 */
export function wouldCreateCycle(graph, from, to) {
    return from === to || reachable(graph, to).has(from);
}

/**
 * Nodes that no path from the trigger reaches
 */
export function orphans(graph) {
    const trigger = triggerNode(graph);

    if (!trigger) {
        return graph.nodes.map((node) => node.id);
    }

    const reached = reachable(graph, trigger.id);

    return graph.nodes.filter((node) => !reached.has(node.id)).map((node) => node.id);
}

/**
 * Link two nodes. Returns { graph, error }: error is a translation key suffix
 * (workflow.error.<error>) when the link is refused, and graph is then unchanged.
 * An output has a single link: linking it again replaces the previous one.
 */
export function connect(graph, from, to, branch = null) {
    const next = normalize(graph);
    const source = findNode(next, from);
    const target = findNode(next, to);

    if (!source || !target) {
        return { graph: next, error: "unknown_node" };
    }

    if (from === to) {
        return { graph: next, error: "self_link" };
    }

    if (target.type === "trigger") {
        return { graph: next, error: "into_trigger" };
    }

    const ports = outputs(source);

    if (!ports.length) {
        return { graph: next, error: "from_end" };
    }

    if (!ports.includes(branch)) {
        return { graph: next, error: "bad_output" };
    }

    if (wouldCreateCycle(next, from, to)) {
        return { graph: next, error: "cycle" };
    }

    next.edges = next.edges.filter((edge) => !(edge.from === from && (edge.branch || null) === branch));
    next.edges.push(branch ? { from, to, branch } : { from, to });

    return { graph: next, error: null };
}

/**
 * The "+" of an output: adds a step right after it, already linked.
 * When the output leads somewhere, the new step is inserted in between (what
 * followed is moved down to make room). Returns { graph, node, error }.
 */
export function appendNode(graph, from, branch, type) {
    const base = normalize(graph);
    const source = findNode(base, from);

    if (!source) {
        return { graph: base, node: null, error: "unknown_node" };
    }

    const existing = base.edges.find((edge) => edge.from === from && (edge.branch || null) === (branch || null));
    const node = createNode(base, type, {
        x: Math.max(0, source.position.x + (branch === YES ? -(NODE_WIDTH / 2 + GAP_X / 2) : branch === NO ? NODE_WIDTH / 2 + GAP_X / 2 : 0)),
        y: source.position.y + NODE_HEIGHT + GAP_Y,
    });

    if (existing && !outputs(node).length) {
        return { graph: base, node: null, error: "from_end" };
    }

    let next = addNode(base, node);

    if (existing) {
        // Make room for the new step, then keep the rest of the path behind it
        const shift = NODE_HEIGHT + GAP_Y;
        const moved = reachable(base, existing.to);

        next.nodes.forEach((n) => {
            if (moved.has(n.id)) {
                n.position = { x: n.position.x, y: n.position.y + shift };
            }
        });
    }

    const first = connect(next, from, node.id, branch || null);

    if (first.error) {
        return { graph: base, node: null, error: first.error };
    }

    next = first.graph;

    if (existing) {
        const second = connect(next, node.id, existing.to, outputs(node)[0]);

        if (second.error) {
            return { graph: base, node: null, error: second.error };
        }

        next = second.graph;
    }

    return { graph: next, node, error: null };
}

export function disconnect(graph, from, branch = null) {
    const next = normalize(graph);

    next.edges = next.edges.filter((edge) => !(edge.from === from && (edge.branch || null) === branch));

    return next;
}

/**
 * Where a port is drawn, in the canvas coordinates
 */
export function outputPoint(node, branch) {
    const x = node.position.x;
    const y = node.position.y + NODE_HEIGHT;

    if (branch === YES) {
        return { x: x + NODE_WIDTH * 0.3, y };
    }

    if (branch === NO) {
        return { x: x + NODE_WIDTH * 0.7, y };
    }

    return { x: x + NODE_WIDTH / 2, y };
}

export function inputPoint(node) {
    return { x: node.position.x + NODE_WIDTH / 2, y: node.position.y };
}

/**
 * SVG path of a link, from the output of a node to the input of another
 */
export function linkPath(from, to) {
    const dy = Math.max(40, Math.abs(to.y - from.y) / 2);

    return `M ${from.x} ${from.y} C ${from.x} ${from.y + dy}, ${to.x} ${to.y - dy}, ${to.x} ${to.y}`;
}

/**
 * Everything needed to draw the links: path and position of the label
 */
export function links(graph) {
    return graph.edges
        .map((edge) => {
            const source = findNode(graph, edge.from);
            const target = findNode(graph, edge.to);

            if (!source || !target) {
                return null;
            }

            const start = outputPoint(source, edge.branch || null);
            const end = inputPoint(target);

            return {
                key: `${edge.from}:${edge.branch || ""}:${edge.to}`,
                from: edge.from,
                to: edge.to,
                branch: edge.branch || null,
                path: linkPath(start, end),
                // Where the button to remove the link is drawn
                middle: { x: (start.x + end.x) / 2, y: (start.y + end.y) / 2 },
            };
        })
        .filter(Boolean);
}

/**
 * Arrange the nodes top to bottom, by the longest path from the trigger.
 * Nodes that are not connected are put in a row below.
 */
export function layout(graph) {
    const next = normalize(graph);
    const trigger = triggerNode(next);
    const level = {};

    if (trigger) {
        level[trigger.id] = 0;

        // The graph has no cycle: n passes are enough to settle the levels
        for (let pass = 0; pass < next.nodes.length; pass++) {
            next.edges.forEach((edge) => {
                if (level[edge.from] !== undefined && (level[edge.to] === undefined || level[edge.to] < level[edge.from] + 1)) {
                    level[edge.to] = level[edge.from] + 1;
                }
            });
        }
    }

    const rows = {};
    const maxLevel = Math.max(-1, ...Object.values(level));

    next.nodes.forEach((node) => {
        const row = level[node.id] !== undefined ? level[node.id] : maxLevel + 1;
        (rows[row] = rows[row] || []).push(node);
    });

    Object.entries(rows).forEach(([row, nodes]) => {
        nodes.forEach((node, column) => {
            node.position = {
                x: ORIGIN_X + column * (NODE_WIDTH + GAP_X),
                y: ORIGIN_Y + Number(row) * (NODE_HEIGHT + GAP_Y),
            };
        });
    });

    return next;
}

/**
 * Size of the canvas that contains every node
 */
export function bounds(graph) {
    let width = 0;
    let height = 0;

    graph.nodes.forEach((node) => {
        width = Math.max(width, node.position.x + NODE_WIDTH);
        height = Math.max(height, node.position.y + NODE_HEIGHT);
    });

    return { width: width + 200, height: height + 200 };
}
