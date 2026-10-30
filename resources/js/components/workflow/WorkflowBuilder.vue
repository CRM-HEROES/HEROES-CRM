<template>
    <div class="hc-wfb">
        <!-- Toolbar -->
        <header class="hc-wfb-toolbar">
            <input
                v-model="name"
                class="hc-wfb-name"
                type="text"
                maxlength="150"
                :disabled="!canUpdate"
                @change="rename"
            />
            <span class="hc-workflow-badge" :class="status" v-text="$t('workflow.status.' + status)"></span>
            <span v-if="version" class="hc-wfb-muted">{{ $t("workflow.version", { n: version.number }) }}</span>
            <span v-if="unpublished" class="hc-wfb-warn" v-text="$t('workflow.unpublished')"></span>
            <span class="hc-wfb-muted hc-wfb-save" :class="saveState" v-text="$t('workflow.save.' + saveState)"></span>

            <span class="hc-wfb-spacer"></span>

            <button
                v-if="primary"
                type="button"
                class="hc-wfb-primary"
                :disabled="busy || (primary.write && !canUpdate)"
                @click="primary.run"
                v-text="$t(primary.label)"
            ></button>
            <button
                v-if="canExecute && status === 'active' && !unpublished"
                type="button"
                class="hc-wfb-soft"
                :disabled="busy"
                @click="pause"
                v-text="$t('workflow.action.pause')"
            ></button>

            <div class="hc-wfb-more">
                <button type="button" class="hc-wfb-soft hc-wfb-more-toggle" :title="$t('workflow.more')" @click.stop="moreOpen = !moreOpen">⋯</button>
                <div v-if="moreOpen" class="hc-wfb-more-menu" @click.stop>
                    <button type="button" :disabled="busy" @click="moreOpen = false; check()" v-text="$t('workflow.action.validate')"></button>
                    <button type="button" :disabled="readOnly" @click="moreOpen = false; arrange()" v-text="$t('workflow.action.arrange')"></button>
                    <button
                        v-if="canUpdate && status !== 'archived' && status !== 'active'"
                        type="button"
                        :disabled="busy"
                        @click="moreOpen = false; publish(false)"
                        v-text="$t('workflow.action.publish')"
                    ></button>
                    <button
                        v-if="canExecute && status === 'active' && unpublished"
                        type="button"
                        :disabled="busy"
                        @click="moreOpen = false; pause()"
                        v-text="$t('workflow.action.pause')"
                    ></button>
                    <button
                        v-if="canExecute && status !== 'archived'"
                        type="button"
                        :disabled="busy"
                        @click="moreOpen = false; archive()"
                        v-text="$t('workflow.action.archive')"
                    ></button>
                    <button
                        v-if="canDelete"
                        type="button"
                        class="hc-wfb-danger"
                        :disabled="busy"
                        @click="moreOpen = false; destroy()"
                        v-text="$t('workflow.action.delete')"
                    ></button>
                </div>
            </div>
        </header>

        <!-- Problems -->
        <div v-if="errors.length" class="hc-wfb-errors" role="alert">
            <strong v-text="$t('workflow.problems')"></strong>
            <ul>
                <li v-for="error in errors" :key="error.message">
                    <a v-if="error.node && nodeExists(error.node)" href="#" class="hc-wfb-error-link" @click.prevent="goTo(error.node)" v-text="error.message"></a>
                    <span v-else v-text="error.message"></span>
                </li>
            </ul>
        </div>
        <div v-else-if="checked" class="hc-wfb-ok" v-text="$t('workflow.valid')"></div>

        <div class="hc-wfb-body">
            <!-- Palette -->
            <nav class="hc-wfb-palette">
                <!-- The trigger is the first step: one click to change it -->
                <div class="hc-wfb-section" v-text="$t('workflow.section.trigger')"></div>
                <button type="button" class="hc-wfb-palette-item type-trigger" :class="{ on: selectedId === triggerId }" @click="selectedId = triggerId">
                    <span class="hc-wfb-palette-dot"></span>
                    <span v-text="$t('workflow.section.trigger_change')"></span>
                </button>

                <template v-for="section in sections(false)" :key="section.key">
                    <div class="hc-wfb-section" v-text="$t('workflow.section.' + section.key)"></div>
                    <p class="hc-wfb-section-help" v-text="$t('workflow.section.' + section.key + '_help')"></p>
                    <button
                        v-for="type in section.types"
                        :key="type"
                        type="button"
                        class="hc-wfb-palette-item"
                        :class="'type-' + type"
                        :draggable="!readOnly"
                        :disabled="readOnly"
                        :title="$t('workflow.node_help.' + type)"
                        @click="addFromPalette(type)"
                        @dragstart="paletteDragStart($event, type)"
                    >
                        <span class="hc-wfb-palette-dot"></span>
                        <span v-text="$t('workflow.node.' + type)"></span>
                    </button>
                </template>
                <p class="hc-wfb-palette-hint" v-text="$t('workflow.palette_hint')"></p>
            </nav>

            <!-- Canvas -->
            <div
                ref="canvas"
                class="hc-wfb-canvas"
                @click="canvasClick"
                @dragover.prevent
                @drop.prevent="paletteDrop"
            >
                <div v-if="connecting" class="hc-wfb-connecting">
                    {{ $t("workflow.connecting") }}
                    <button type="button" @click.stop="cancelConnect" v-text="$t('workflow.cancel')"></button>
                </div>

                <div class="hc-wfb-surface" :style="{ width: size.width + 'px', height: size.height + 'px' }">
                    <svg class="hc-wfb-links" :width="size.width" :height="size.height">
                        <defs>
                            <marker id="hc-wfb-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" fill="#8a94a6" />
                            </marker>
                        </defs>
                        <g v-for="link in links" :key="link.key">
                            <path :d="link.path" class="hc-wfb-link" :class="link.branch" marker-end="url(#hc-wfb-arrow)" />
                            <g
                                v-if="!readOnly"
                                class="hc-wfb-link-remove"
                                :transform="`translate(${link.middle.x}, ${link.middle.y})`"
                                @click.stop="removeLink(link)"
                            >
                                <title>{{ $t("workflow.remove_link") }}</title>
                                <circle r="8" />
                                <text y="4" text-anchor="middle">×</text>
                            </g>
                            <g
                                v-if="!readOnly"
                                class="hc-wfb-link-insert"
                                :transform="`translate(${link.middle.x + 22}, ${link.middle.y})`"
                                @click.stop="openMenu(link.from, link.branch, link.middle.x + 22, link.middle.y + 12, true)"
                            >
                                <title>{{ $t("workflow.add_menu.insert") }}</title>
                                <circle r="8" />
                                <text y="4" text-anchor="middle">+</text>
                            </g>
                        </g>
                    </svg>

                    <div
                        v-for="node in view.nodes"
                        :key="node.id"
                        class="hc-wfb-node"
                        :class="[
                            'type-' + node.type,
                            { selected: selectedId === node.id, orphan: orphanIds.includes(node.id), invalid: errorNodes.includes(node.id), target: connecting && connecting.from !== node.id && node.type !== 'trigger' },
                        ]"
                        :style="{ left: node.position.x + 'px', top: node.position.y + 'px' }"
                        @click.stop="nodeClick(node)"
                    >
                        <span
                            v-if="node.type !== 'trigger'"
                            class="hc-wfb-port in"
                            :title="$t('workflow.input')"
                            @click.stop="nodeClick(node)"
                        ></span>

                        <div class="hc-wfb-node-icon" v-text="icon(node.type)"></div>
                        <div class="hc-wfb-node-main" @mousedown="dragStart($event, node)">
                            <span class="hc-wfb-node-type" v-text="$t('workflow.node.' + node.type)"></span>
                            <span class="hc-wfb-node-body" :title="describe(node)" v-text="describe(node)"></span>
                        </div>
                        <span v-if="!readOnly && node.type !== 'trigger'" class="hc-wfb-node-remove" :title="$t('workflow.remove_node')" @mousedown.stop @click.stop="removeNode(node)">×</span>

                        <template v-for="branch in outputsOf(node)" :key="String(branch)">
                            <!-- Open output: the "+" adds the next step, already linked -->
                            <button
                                v-if="!hasLink(node, branch)"
                                type="button"
                                class="hc-wfb-add"
                                :class="branch"
                                :style="{ left: portLeft(branch) }"
                                :disabled="readOnly"
                                :title="$t('workflow.add_menu.title')"
                                @click.stop="openMenu(node.id, branch, node.position.x + portCenter(branch), menuY(node), false)"
                            >
                                +
                                <span v-if="branch" class="hc-wfb-add-label" v-text="$t('workflow.' + branch)"></span>
                            </button>
                            <!-- Linked output: a small dot, to link it elsewhere -->
                            <button
                                v-else
                                type="button"
                                class="hc-wfb-port out"
                                :class="[branch, { active: connecting && connecting.from === node.id && connecting.branch === branch }]"
                                :style="{ left: portLeft(branch) }"
                                :disabled="readOnly"
                                :title="$t('workflow.output')"
                                @click.stop="startConnect(node, branch)"
                            >
                                <span v-if="branch" v-text="$t('workflow.' + branch)"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Menu of the "+" -->
                    <div v-if="menu" class="hc-wfb-menu" :style="{ left: menu.x + 'px', top: menu.y + 'px' }" @click.stop>
                        <template v-for="section in sections(menu.insert)" :key="section.key">
                            <div class="hc-wfb-menu-title" v-text="$t('workflow.section.' + section.key)"></div>
                            <button
                                v-for="type in section.types"
                                :key="type"
                                type="button"
                                class="hc-wfb-menu-item"
                                :class="'type-' + type"
                                @click="addAfter(type)"
                            >
                                <span class="hc-wfb-menu-icon" v-text="icon(type)"></span>
                                <span>
                                    <strong v-text="$t('workflow.node.' + type)"></strong>
                                    <small v-text="$t('workflow.node_help.' + type)"></small>
                                </span>
                            </button>
                        </template>
                        <button v-if="!menu.insert" type="button" class="hc-wfb-menu-item link" @click="linkExisting">
                            <span class="hc-wfb-menu-icon">↪</span>
                            <span>
                                <strong v-text="$t('workflow.add_menu.link')"></strong>
                                <small v-text="$t('workflow.add_menu.link_help')"></small>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Configuration of the selected step -->
            <node-editor
                v-if="selectedNode"
                :key="selectedNode.id"
                :node="selectedNode"
                :graph="graph"
                :catalog="catalog"
                :read-only="readOnly"
                @update="updateNode"
                @link="linkFromPanel"
                @remove="removeNode(selectedNode)"
                @close="selectedId = null"
            />
        </div>
    </div>
</template>

<style>
.hc-wfb {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-height: 0;
}

.hc-wfb-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #fff;
    border-bottom: 1px solid #e3e6ef;
}

.hc-wfb-toolbar button {
    padding: 7px 14px;
    color: #2b2f3a;
    background: #fff;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, box-shadow 0.15s;
}

.hc-wfb-toolbar button:hover:not(:disabled) {
    background: #f3f4f9;
}

.hc-wfb-toolbar button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.hc-wfb-name {
    width: 260px;
    padding: 6px 10px;
    border: 1px solid transparent;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
}

.hc-wfb-name:hover,
.hc-wfb-name:focus {
    border-color: #d5d9e4;
    outline: 0;
}

.hc-wfb-muted {
    color: #7a7f90;
    font-size: 12px;
}

.hc-wfb-warn {
    padding: 2px 8px;
    color: #8a5a00;
    background: #fff7dd;
    border-radius: 10px;
    font-size: 12px;
}

.hc-wfb-save.error {
    color: #c0341d;
}

.hc-wfb-spacer {
    flex: 1;
}

.hc-wfb-toolbar .hc-wfb-primary {
    color: #fff;
    background: #4b5ce8;
    border-color: #4b5ce8;
    box-shadow: 0 2px 6px rgba(75, 92, 232, 0.35);
}

.hc-wfb-toolbar .hc-wfb-primary:hover:not(:disabled) {
    background: #3a49c9;
}

.hc-wfb-more {
    position: relative;
}

.hc-wfb-more-toggle {
    padding: 7px 12px;
    font-size: 16px;
    line-height: 1;
}

.hc-wfb-more-menu {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    z-index: 20;
    display: flex;
    flex-direction: column;
    min-width: 190px;
    padding: 6px;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(30, 35, 60, 0.15);
}

.hc-wfb-toolbar .hc-wfb-more-menu button {
    text-align: left;
    border: 0;
    font-weight: 500;
}

.hc-wfb-toolbar .hc-wfb-danger {
    color: #c0341d;
}

.hc-wfb-errors {
    padding: 8px 14px;
    background: #fdecea;
    color: #8a1f11;
    font-size: 13px;
}

.hc-wfb-errors ul {
    margin: 4px 0 0 18px;
    padding: 0;
}

.hc-wfb-ok {
    padding: 6px 14px;
    background: #e6f6ec;
    color: #14633a;
    font-size: 13px;
}

.hc-wfb-body {
    display: flex;
    flex: 1;
    min-height: 0;
}

.hc-wfb-palette {
    display: flex;
    flex: none;
    flex-direction: column;
    gap: 6px;
    width: 170px;
    padding: 14px 12px;
    overflow: auto;
    background: #fff;
    border-right: 1px solid #e3e6ef;
}

.hc-wfb-palette-title {
    color: #7a7f90;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.hc-wfb-palette-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    text-align: left;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-radius: 8px;
    font-size: 13px;
    cursor: grab;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.hc-wfb-palette-item:hover {
    border-color: #aab2f5;
    box-shadow: 0 2px 8px rgba(75, 92, 232, 0.12);
}

.hc-wfb-section {
    margin-top: 10px;
    color: #3a49c9;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.hc-wfb-section-help {
    margin: -2px 0 2px;
    color: #8a8fa0;
    font-size: 11px;
    line-height: 1.3;
}

.hc-wfb-palette-item.on {
    background: #eceefd;
    border-color: #aab2f5;
}

.hc-wfb-palette-hint {
    color: #8a8fa0;
    font-size: 11px;
}

.type-trigger .hc-wfb-palette-dot {
    background: #2e9d5c;
}

.hc-wfb-palette-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #8a94a6;
}

.type-condition .hc-wfb-palette-dot {
    background: #e8a317;
}
.type-action .hc-wfb-palette-dot {
    background: #4b5ce8;
}
.type-wait .hc-wfb-palette-dot,
.type-wait_event .hc-wfb-palette-dot {
    background: #8e5bd6;
}
.type-end .hc-wfb-palette-dot {
    background: #666;
}

.hc-wfb-canvas {
    position: relative;
    flex: 1;
    min-width: 0;
    overflow: auto;
    background-color: #fbfbfe;
    background-image: radial-gradient(#d9dbe6 1px, transparent 1px);
    background-size: 20px 20px;
}

.hc-wfb-connecting {
    position: sticky;
    top: 0;
    left: 0;
    z-index: 5;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px;
    background: #fff7dd;
    color: #6b4b00;
    font-size: 13px;
}

.hc-wfb-surface {
    position: relative;
}

.hc-wfb-links {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
}

.hc-wfb-link {
    fill: none;
    stroke: #aab0c0;
    stroke-width: 2;
}

.hc-wfb-link.yes {
    stroke: #2e9d5c;
}

.hc-wfb-link.no {
    stroke: #d05a45;
}

.hc-wfb-link-remove {
    cursor: pointer;
    pointer-events: all;
    opacity: 0.35;
}

.hc-wfb-link-remove:hover {
    opacity: 1;
}

.hc-wfb-link-remove circle {
    fill: #fff;
    stroke: #8a94a6;
}

.hc-wfb-link-remove text {
    font-size: 13px;
    fill: #555;
}

.hc-wfb-node {
    position: absolute;
    display: flex;
    align-items: center;
    gap: 10px;
    width: 230px;
    height: 76px;
    box-sizing: border-box;
    padding: 0 12px;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(30, 35, 60, 0.08);
    cursor: pointer;
    user-select: none;
    transition: box-shadow 0.15s, border-color 0.15s;
}

.hc-wfb-node:hover {
    box-shadow: 0 4px 14px rgba(30, 35, 60, 0.14);
}

.hc-wfb-node-icon {
    display: flex;
    flex: none;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    color: #fff;
    background: #8a94a6;
    border-radius: 10px;
    font-size: 18px;
}

.hc-wfb-node.type-trigger .hc-wfb-node-icon {
    background: #2e9d5c;
}
.hc-wfb-node.type-condition .hc-wfb-node-icon {
    background: #e8a317;
}
.hc-wfb-node.type-action .hc-wfb-node-icon {
    background: #4b5ce8;
}
.hc-wfb-node.type-wait .hc-wfb-node-icon,
.hc-wfb-node.type-wait_event .hc-wfb-node-icon {
    background: #8e5bd6;
}
.hc-wfb-node.type-end .hc-wfb-node-icon {
    background: #555b6b;
}

.hc-wfb-node.selected {
    border-color: #4b5ce8;
    box-shadow: 0 0 0 3px rgba(75, 92, 232, 0.22);
}

.hc-wfb-node.orphan {
    border-style: dashed;
    border-color: #d05a45;
}

.hc-wfb-node.invalid {
    border-color: #d05a45;
    box-shadow: 0 0 0 3px rgba(208, 90, 69, 0.2);
}

.hc-wfb-error-link {
    color: #8a1f11;
    text-decoration: underline;
    cursor: pointer;
}

.hc-wfb-node.target {
    outline: 2px dashed #4b5ce8;
}

.hc-wfb-node-main {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    cursor: move;
}

.hc-wfb-node-type {
    color: #7a7f90;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.hc-wfb-node-body {
    display: -webkit-box;
    overflow: hidden;
    color: #2b2f3a;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.25;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.hc-wfb-node-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    display: none;
    width: 20px;
    height: 20px;
    color: #8a8fa0;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-radius: 50%;
    font-size: 14px;
    line-height: 18px;
    text-align: center;
}

.hc-wfb-node:hover .hc-wfb-node-remove {
    display: block;
}

.hc-wfb-node-remove:hover {
    color: #c0341d;
    border-color: #f1c3bb;
}

.hc-wfb-port {
    position: absolute;
    width: 14px;
    height: 14px;
    padding: 0;
    background: #fff;
    border: 2px solid #8a94a6;
    border-radius: 50%;
}

.hc-wfb-port.in {
    top: -8px;
    left: calc(50% - 7px);
}

.hc-wfb-port.out {
    bottom: -8px;
    cursor: crosshair;
}

.hc-wfb-port.out:not(.yes):not(.no) {
    left: calc(50% - 7px);
}

.hc-wfb-port.out.active {
    background: #4b5ce8;
    border-color: #4b5ce8;
}

.hc-wfb-port.out.yes {
    border-color: #2e9d5c;
}

.hc-wfb-port.out.no {
    border-color: #d05a45;
}

.hc-wfb-port.out span {
    position: absolute;
    top: 14px;
    left: -6px;
    color: #555;
    font-size: 10px;
}

.hc-wfb-add {
    position: absolute;
    bottom: -14px;
    width: 28px;
    height: 28px;
    padding: 0;
    color: #fff;
    background: #4b5ce8;
    border: 3px solid #fbfbfe;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(75, 92, 232, 0.4);
    font-size: 18px;
    font-weight: 700;
    line-height: 20px;
    cursor: pointer;
    transition: transform 0.12s;
}

.hc-wfb-add:hover:not(:disabled) {
    transform: scale(1.15);
}

.hc-wfb-add:disabled {
    display: none;
}

.hc-wfb-add.yes {
    background: #2e9d5c;
    box-shadow: 0 2px 6px rgba(46, 157, 92, 0.4);
}

.hc-wfb-add.no {
    background: #d05a45;
    box-shadow: 0 2px 6px rgba(208, 90, 69, 0.4);
}

.hc-wfb-add-label {
    position: absolute;
    top: 26px;
    left: 0;
    width: 22px;
    color: #555;
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
    text-align: center;
}

.hc-wfb-link-insert {
    cursor: pointer;
    pointer-events: all;
    opacity: 0.35;
}

.hc-wfb-link-insert:hover {
    opacity: 1;
}

.hc-wfb-link-insert circle {
    fill: #4b5ce8;
    stroke: #4b5ce8;
}

.hc-wfb-link-insert text {
    font-size: 13px;
    font-weight: 700;
    fill: #fff;
}

.hc-wfb-menu {
    position: absolute;
    z-index: 30;
    display: flex;
    flex-direction: column;
    width: 280px;
    padding: 6px;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(30, 35, 60, 0.2);
}

.hc-wfb-menu-title {
    padding: 6px 8px;
    color: #7a7f90;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.hc-wfb-menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    text-align: left;
    background: transparent;
    border: 0;
    border-radius: 8px;
    cursor: pointer;
}

.hc-wfb-menu-item:hover {
    background: #f1f2fd;
}

.hc-wfb-menu-item strong {
    display: block;
    color: #2b2f3a;
    font-size: 13px;
}

.hc-wfb-menu-item small {
    display: block;
    color: #7a7f90;
    font-size: 11px;
    line-height: 1.3;
}

.hc-wfb-menu-icon {
    display: flex;
    flex: none;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #fff;
    background: #8a94a6;
    border-radius: 8px;
    font-size: 16px;
}

.hc-wfb-menu-item.type-condition .hc-wfb-menu-icon {
    background: #e8a317;
}
.hc-wfb-menu-item.type-action .hc-wfb-menu-icon {
    background: #4b5ce8;
}
.hc-wfb-menu-item.type-wait .hc-wfb-menu-icon,
.hc-wfb-menu-item.type-wait_event .hc-wfb-menu-icon {
    background: #8e5bd6;
}
.hc-wfb-menu-item.type-end .hc-wfb-menu-icon {
    background: #555b6b;
}
.hc-wfb-menu-item.link .hc-wfb-menu-icon {
    color: #555b6b;
    background: #eceef4;
}
</style>

<script>
import { mapGetters } from "vuex";
import WorkflowService from "@/apis/project/workflow";
import NodeEditor from "./NodeEditor.vue";
import { slug } from "@/utils/workflow-i18n";
import {
    NODE_HEIGHT,
    NODE_WIDTH,
    addNode,
    bounds,
    connect,
    appendNode,
    createNode,
    disconnect,
    findNode,
    layout,
    links,
    moveNode,
    normalize,
    orphans,
    outputs,
    removeNode,
    updateNode,
} from "@/utils/workflow-graph";

const SAVE_DELAY = 1000;

/**
 * The problems of an answer: { message, node } (the step they are about, if known)
 */
function problemsOf(data) {
    return data.problems || (data.errors || []).map((message) => ({ message, node: null }));
}

export default {
    components: { NodeEditor },

    props: {
        workflow: { type: Object, required: true },
        catalog: { type: Object, required: true },
    },

    emits: ["changed", "deleted"],

    data() {
        return {
            graph: normalize(this.workflow.graph),
            name: this.workflow.name,
            status: this.workflow.status,
            version: this.workflow.version,
            unpublished: this.workflow.unpublished_changes,
            errors: [],
            checked: false,
            saveState: "saved",
            busy: false,
            selectedId: null,
            connecting: null,
            menu: null,
            moreOpen: false,
            live: null,
            saveTimer: null,
            saving: Promise.resolve(),
        };
    },

    computed: {
        ...mapGetters(["project", "can"]),

        canUpdate() {
            return this.can("all.project.workflow.update");
        },
        canExecute() {
            return this.can("all.project.workflow.execute");
        },
        canDelete() {
            return this.can("all.project.workflow.delete");
        },
        readOnly() {
            return !this.canUpdate || this.status === "archived";
        },
        /**
         * The one button to go further: turn the workflow on, or publish the changes
         */
        primary() {
            if (this.status === 'archived') return null;

            if (this.status === 'active') {
                return this.canExecute && this.unpublished ? { label: 'workflow.action.publish_now', write: false, run: () => this.publish(false) } : null;
            }

            if (this.status === 'paused' && !this.unpublished && this.canExecute) {
                return { label: 'workflow.action.activate', write: false, run: () => this.activate() };
            }

            if (this.canExecute) {
                return { label: 'workflow.action.turn_on', write: true, run: () => this.publish(true) };
            }

            return this.canUpdate ? { label: 'workflow.action.publish', write: true, run: () => this.publish(false) } : null;
        },
        triggerId() {
            const trigger = this.graph.nodes.find((node) => node.type === 'trigger');

            return trigger ? trigger.id : null;
        },
        paletteTypes() {
            return ["condition", "action", "wait", "wait_event", "end"];
        },
        /**
         * The graph with the node being dragged where the mouse is
         */
        view() {
            return this.live ? moveNode(this.graph, this.live.id, this.live.x, this.live.y) : this.graph;
        },
        links() {
            return links(this.view);
        },
        size() {
            return bounds(this.view);
        },
        errorNodes() {
            return this.errors.map((error) => error.node).filter(Boolean);
        },
        orphanIds() {
            return orphans(this.graph);
        },
        selectedNode() {
            return this.selectedId ? findNode(this.graph, this.selectedId) : null;
        },
    },

    mounted() {
        document.addEventListener('click', this.closeMenus);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.closeMenus);
        this.stopDrag();
        clearTimeout(this.saveTimer);

        // Do not lose the last changes
        if (this.saveState === "pending") {
            this.saveNow();
        }
    },

    methods: {
        outputsOf: outputs,

        menuY(node) {
            return node.position.y + NODE_HEIGHT + 30;
        },

        /**
         * What can be added, by what it is for. An end cannot be put before another step.
         */
        sections(inserting) {
            return [
                { key: 'actions', types: ['action'] },
                { key: 'rules', types: ['condition', 'wait', 'wait_event', ...(inserting ? [] : ['end'])] },
            ];
        },

        nodeExists(id) {
            return !!findNode(this.graph, id);
        },

        /**
         * From a problem: show the step and open its settings
         */
        goTo(id) {
            this.selectedId = id;

            const node = findNode(this.graph, id);
            const canvas = this.$refs.canvas;

            if (node && canvas) {
                canvas.scrollTo({ left: Math.max(0, node.position.x - 60), top: Math.max(0, node.position.y - 80), behavior: 'smooth' });
            }
        },

        /**
         * A refused publication only lists messages: ask again for the steps they are about
         */
        async refreshProblems() {
            try {
                const { data } = await WorkflowService.validate(this.project.slug, this.workflow.id, { graph: this.graph });

                this.errors = problemsOf(data);
            } catch (e) {
                // the messages are already shown
            }
        },

        closeMenus() {
            this.menu = null;
            this.moreOpen = false;
        },

        icon(type) {
            return { trigger: '⚡', condition: '⑂', action: '✉', wait: '⏱', wait_event: '⏳', end: '■' }[type] || '•';
        },

        hasLink(node, branch) {
            return this.graph.edges.some((edge) => edge.from === node.id && (edge.branch || null) === (branch || null));
        },

        portCenter(branch) {
            return branch === 'yes' ? NODE_WIDTH * 0.3 : branch === 'no' ? NODE_WIDTH * 0.7 : NODE_WIDTH / 2;
        },

        openMenu(from, branch, x, y, insert) {
            if (this.readOnly) return;

            this.menu = { from, branch: branch || null, x: Math.max(0, x - 140), y, insert };
        },

        /**
         * A step added from the "+": created, linked, and open for configuration
         */
        addAfter(type) {
            const { from, branch } = this.menu;
            const { graph, node, error } = appendNode(this.graph, from, branch, type);

            this.menu = null;

            if (error) {
                flashWarning({ title: this.$t("workflow.title"), body: this.$t('workflow.error.' + error) });
                return;
            }

            this.edit(graph);
            this.selectedId = node.id;
        },

        linkExisting() {
            const { from, branch } = this.menu;

            this.menu = null;
            this.connecting = { from, branch };
        },

        portLeft(branch) {
            return branch === "yes" ? `${NODE_WIDTH * 0.3 - 8}px` : branch === "no" ? `${NODE_WIDTH * 0.7 - 8}px` : undefined;
        },

        /**
         * One line that says what a step does
         */
        describe(node) {
            const c = node.config || {};
            const t = (key, params) => this.$t(`workflow.${key}`, params);

            switch (node.type) {
                case "trigger":
                    return t(`trigger.${slug(c.trigger)}`);
                case "action":
                    return c.action
                        ? this.$te(`workflow.action_type.${slug(c.action)}`)
                            ? t(`action_type.${slug(c.action)}`)
                            : c.action
                        : t("not_configured");
                case "condition": {
                    const count = (c.items || []).length;
                    return count ? t("condition.summary", { count }) : t("not_configured");
                }
                case "wait":
                    return this.describeWait(c);
                case "wait_event":
                    return `${t(`event.${c.event}`)} (${this.describeDuration(c)})`;
                default:
                    return node.name || "";
            }
        },

        describeDuration(c) {
            return [
                c.days ? `${c.days} ${this.$t("workflow.wait.days")}` : null,
                c.hours ? `${c.hours} ${this.$t("workflow.wait.hours")}` : null,
                c.minutes ? `${c.minutes} ${this.$t("workflow.wait.minutes")}` : null,
            ]
                .filter(Boolean)
                .join(" ");
        },

        describeWait(c) {
            if (c.until_date) {
                return this.$t("workflow.wait.until_date_summary", { date: c.until_date });
            }

            if (c.until_time) {
                return this.$t("workflow.wait.until_time_summary", { time: c.until_time });
            }

            return this.describeDuration(c) || this.$t("workflow.not_configured");
        },

        // Editing the graph

        edit(graph) {
            this.graph = graph;
        },

        addFromPalette(type) {
            if (this.readOnly) {
                return;
            }

            // Under the lowest step, aligned with the selected one
            const lowest = Math.max(0, ...this.graph.nodes.map((n) => n.position.y));
            const anchor = this.selectedNode || { position: { x: 80 } };

            this.place(type, { x: anchor.position.x, y: lowest + NODE_HEIGHT + 80 });
        },

        place(type, position) {
            const node = createNode(this.graph, type, position);

            this.edit(addNode(this.graph, node));
            this.selectedId = node.id;
        },

        paletteDragStart(event, type) {
            event.dataTransfer.setData("text/plain", type);
            event.dataTransfer.effectAllowed = "copy";
        },

        paletteDrop(event) {
            const type = event.dataTransfer.getData("text/plain");

            if (this.readOnly || !this.paletteTypes.includes(type)) {
                return;
            }

            const rect = this.$refs.canvas.getBoundingClientRect();

            this.place(type, {
                x: event.clientX - rect.left + this.$refs.canvas.scrollLeft - NODE_WIDTH / 2,
                y: event.clientY - rect.top + this.$refs.canvas.scrollTop - 20,
            });
        },

        removeNode(node) {
            if (this.readOnly) {
                return;
            }

            this.edit(removeNode(this.graph, node.id));

            if (this.selectedId === node.id) {
                this.selectedId = null;
            }
        },

        updateNode(patch) {
            this.edit(updateNode(this.graph, this.selectedId, patch));
        },

        arrange() {
            this.edit(layout(this.graph));
        },

        // Links

        startConnect(node, branch) {
            if (this.readOnly) {
                return;
            }

            this.connecting = { from: node.id, branch };
        },

        cancelConnect() {
            this.connecting = null;
        },

        nodeClick(node) {
            this.menu = null;

            if (this.connecting) {
                this.completeConnect(node);
                return;
            }

            this.selectedId = node.id;
        },

        canvasClick() {
            this.menu = null;

            if (this.connecting) {
                this.cancelConnect();
            } else {
                this.selectedId = null;
            }
        },

        completeConnect(node) {
            const { from, branch } = this.connecting;
            this.connecting = null;

            this.link(from, node.id, branch);
        },

        link(from, to, branch) {
            const { graph, error } = connect(this.graph, from, to, branch);

            if (error) {
                flashWarning({ title: this.$t("workflow.title"), body: this.$t(`workflow.error.${error}`) });
                return;
            }

            this.edit(graph);
        },

        /**
         * From the panel of a step: link an output to a step, or remove it (to = null)
         */
        linkFromPanel({ branch, to }) {
            if (to) {
                this.link(this.selectedId, to, branch);
            } else {
                this.edit(disconnect(this.graph, this.selectedId, branch));
            }
        },

        removeLink(link) {
            this.edit(disconnect(this.graph, link.from, link.branch));
        },

        // Moving a step

        dragStart(event, node) {
            if (this.readOnly || event.button !== 0 || this.connecting) {
                return;
            }

            this.drag = { id: node.id, mx: event.clientX, my: event.clientY, x: node.position.x, y: node.position.y, moved: false };
            window.addEventListener("mousemove", this.dragMove);
            window.addEventListener("mouseup", this.dragEnd);
        },

        dragMove(event) {
            const d = this.drag;

            if (!d) {
                return;
            }

            const x = Math.max(0, d.x + event.clientX - d.mx);
            const y = Math.max(0, d.y + event.clientY - d.my);

            d.moved = d.moved || Math.abs(x - d.x) + Math.abs(y - d.y) > 3;
            this.live = { id: d.id, x, y };
        },

        dragEnd() {
            const live = this.live;

            this.stopDrag();

            if (live) {
                this.edit(moveNode(this.graph, live.id, live.x, live.y));
            }
        },

        stopDrag() {
            window.removeEventListener("mousemove", this.dragMove);
            window.removeEventListener("mouseup", this.dragEnd);
            this.drag = null;
            this.live = null;
        },

        // Saving the draft

        scheduleSave() {
            if (this.readOnly) {
                return;
            }

            this.saveState = "pending";
            clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => this.saveNow(), SAVE_DELAY);
        },

        /**
         * Saves the draft, one request at a time and in order.
         * The answer says what is still wrong with it.
         */
        saveNow() {
            clearTimeout(this.saveTimer);

            this.saving = this.saving.then(async () => {
                this.saveState = "saving";

                try {
                    const { data } = await WorkflowService.update(this.project.slug, this.workflow.id, {
                        draft_graph: this.graph,
                    });

                    this.unpublished = data.unpublished_changes;
                    this.errors = this.checked ? problemsOf(data) : [];

                    // Edited again while saving: the next save is already planned
                    if (this.saveState === "saving") {
                        this.saveState = "saved";
                    }
                } catch (e) {
                    this.saveState = "error";
                }
            });

            return this.saving;
        },

        async rename() {
            const name = this.name.trim();

            if (!name) {
                this.name = this.workflow.name;
                return;
            }

            try {
                await WorkflowService.update(this.project.slug, this.workflow.id, { name });
                this.$emit("changed");
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.save") });
            }
        },

        // Checking, publishing, life cycle

        async check() {
            this.busy = true;

            try {
                await this.saveNow();
                const { data } = await WorkflowService.validate(this.project.slug, this.workflow.id, { graph: this.graph });
                this.errors = problemsOf(data);
                this.checked = true;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                this.busy = false;
            }
        },

        async publish(activate) {
            await this.run(async () => {
                await this.saveNow();

                return WorkflowService.publish(this.project.slug, this.workflow.id, { activate });
            }, "published");
        },

        activate() {
            return this.run(() => WorkflowService.activate(this.project.slug, this.workflow.id), "activated");
        },

        pause() {
            return this.run(() => WorkflowService.pause(this.project.slug, this.workflow.id), "paused");
        },

        archive() {
            if (!window.confirm(this.$t("workflow.confirm.archive"))) {
                return;
            }

            return this.run(() => WorkflowService.archive(this.project.slug, this.workflow.id), "archived");
        },

        async destroy() {
            if (!window.confirm(this.$t("workflow.confirm.delete"))) {
                return;
            }

            this.busy = true;

            try {
                await WorkflowService.destroy(this.project.slug, this.workflow.id);
                this.$emit("deleted");
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.delete") });
                this.busy = false;
            }
        },

        /**
         * Runs a request that changes the state of the workflow and applies the
         * answer. A refusal (422) lists what must be fixed first.
         */
        async run(request, done) {
            this.busy = true;

            try {
                const { data } = await request();

                this.status = data.status;
                this.version = data.version;
                this.unpublished = data.unpublished_changes;
                this.errors = [];
                this.checked = false;

                flash({ title: this.$t("workflow.title"), body: this.$t(`workflow.done.${done}`) });
                this.$emit("changed");
            } catch (e) {
                const response = e.response;

                if (response && response.status === 422 && Array.isArray(response.data.errors)) {
                    this.errors = problemsOf(response.data);
                    this.checked = true;
                    this.refreshProblems();
                } else {
                    flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.save") });
                }
            } finally {
                this.busy = false;
            }
        },
    },

    watch: {
        graph: {
            deep: true,
            handler() {
                this.scheduleSave();
            },
        },
    },
};
</script>
