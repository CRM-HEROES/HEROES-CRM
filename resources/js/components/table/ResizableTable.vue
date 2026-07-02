<template>
    <virtual-table
        :columns="columns"
        :rows="rows"
        :default-width="defaultWidth"
        :default-height="defaultHeight"
        :custom-widths="customWidths"
        :custom-heights="customHeights"
        :fixed="fixed"
        :class="[
            'hc-resizable-virtual-scroller-table',
            columnToResize ? 'resizing' : '',
        ]"
    >
        <!-- Header -->
        <template #header="{ column }">
            <mouse-tracker v-slot="{ x: x1 }">
                <span
                    :id="column"
                    @mousedown="beginDrag($event, column, x1)"
                    @mousemove="makeResize(x1)"
                    @mouseup="endResize(x1)"
                    @drop.prevent="endDrag($event, x1, column)"
                    @dragover.prevent="makeDrag($event, x1, column)"
                    @dragleave.prevent
                    @dragenter.prevent
                    draggable="true"
                    :class="[
                        columnToResize && columnToResize.column === column
                            ? 'resizing'
                            : '',
                        columnToDrag && columnToDrag.pos === column
                            ? 'dropping' + (columnToDrag.after ? ' after' : '')
                            : '',
                    ]"
                >
                    <slot name="header" :column="column"></slot>
                    <mouse-tracker
                        v-if="
                            resizableColumns.length == 0 ||
                            resizableColumns.indexOf(column) >= 0
                        "
                        v-slot="{ x: x2 }"
                    >
                        <span
                            :class="[
                                'hc-resizable-virtual-scroller-table-resize',
                            ]"
                            @mousedown.prevent.stop="
                                beginResize($event, column, x2)
                            "
                        ></span>
                    </mouse-tracker>
                </span>
            </mouse-tracker>
        </template>

        <!-- Body -->
        <template #cell="{ row, column, top }">
            <slot name="cell" :row="row" :column="column" :top="top"></slot>
        </template>

        <!-- Extra -->
        <template #extra>
            <slot name="extra"></slot>
        </template>
    </virtual-table>
</template>

<style>
.hc-resizable-virtual-scroller-table.resizing {
    cursor: col-resize;
    user-select: none;
}

.hc-resizable-virtual-scroller-table th > span {
    width: 100%;
    height: 100%;
    position: relative;
    display: inline-block;
}

.hc-resizable-virtual-scroller-table th > span.dropping:before {
    content: "";
    display: inline-block;
    height: 4000%;
    left: -3px;
    position: absolute;
    top: 0;
    width: 5px;
    background-color: #00000033;
    z-index: 5;
}

.hc-resizable-virtual-scroller-table th > span.dropping.after:before {
    left: unset;
    right: -3px;
}

.hc-resizable-virtual-scroller-table th > span.resizing:hover {
    z-index: 5;
}

.hc-resizable-virtual-scroller-table-resize {
    height: 100%;
    width: 0px;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 5;
    cursor: col-resize;
}

.hc-resizable-virtual-scroller-table-resize:before {
    content: "";
    display: inline-block;
    height: 100%;
    left: -4px;
    position: absolute;
    top: 0;
    width: 7px;
}

.resizing > .hc-resizable-virtual-scroller-table-resize:after,
.hc-resizable-virtual-scroller-table-resize:hover:after {
    content: "";
    display: inline-block;
    height: 50%;
    left: -5px;
    position: absolute;
    top: 25%;
    width: 11px;
    border-left: 3px solid #000c;
    border-right: 3px solid #000c;
}

.hc-resizable-virtual-scroller-table-resize:hover:before {
    /*background-color: blue;*/
}

.resizing > .hc-resizable-virtual-scroller-table-resize:before {
    background-color: #00000033;
    height: 4000%;
    left: -2px;
    width: 5px;
}

.dropping > .hc-resizable-virtual-scroller-table-resize {
    pointer-events: none;
}
</style>

<script>
import { markRaw } from "vue";

import VirtualTable from "./Table.vue";
import MouseTracker from "./MouseTracker.vue";

export default {
    components: {
        VirtualTable,
        MouseTracker,
    },

    props: {
        /**
         * Columns count
         */
        columns: Number,

        /**
         * Rows count
         */
        rows: Number,

        /**
         * Default width
         */
        defaultWidth: {
            type: Number,
            default: 100,
        },

        /**
         * Default height
         */
        defaultHeight: {
            type: Number,
            default: 24,
        },

        /**
         * Custom widths
         */
        customWidths: {
            type: Object,
            default: {},
        },

        /**
         * Custom heights
         */
        customHeights: {
            type: Object,
            default: {},
        },

        fixed: {
            type: Number,
            default: 0,
        },

        resizableColumns: {
            type: Array,
            default: [],
        },

        draggableColumns: {
            type: Array,
            default: [],
        },
    },

    data() {
        return {
            columnToResize: null,
            columnToDrag: null,
        };
    },

    methods: {
        beginResize(event, column, x) {
            this.columnToResize = {
                target: markRaw(event.target),
                column,
                x,
            };
            event.target.style.pointerEvents = "none";
        },

        makeResize(x) {
            if (!this.columnToResize) {
                return;
            }

            this.columnToResize.target.style.right =
                this.columnToResize.x - x + "px";
        },

        endResize(x) {
            if (!this.columnToResize) {
                return;
            }

            const width = Math.max(
                0,
                this.columnToResize.target.parentNode.offsetWidth +
                    x -
                    this.columnToResize.x
            );

            this.$emit("resize", this.columnToResize.column, width);
            this.columnToResize.target.style.right = 0;
            this.columnToResize.target.style.pointerEvents = "all";
            this.columnToResize = null;
        },

        beginDrag(event, column, x) {
            this.columnToDrag = {
                target: markRaw(event.target),
                column,
                to: column,
                pos: null,
                after: false,
                x,
            };
            // event.target.style.pointerEvents = "none";
        },

        makeDrag(event, x, column) {
            if (!this.columnToDrag) {
                return;
            }

            this.columnToDrag.pos = column;
            if (event.offsetX >= event.target.offsetWidth / 2) {
                this.columnToDrag.after = true;
            } else {
                this.columnToDrag.after = false;
            }
        },

        endDrag(event, x, column) {
            if (!this.columnToDrag) {
                return;
            }

            if (this.columnToDrag.column == column) {
            } else if (event.offsetX >= event.target.offsetWidth / 2) {
                this.$emit(
                    "move",
                    this.columnToDrag.column,
                    column < this.columnToDrag.column ? column + 1 : column
                );
            } else {
                this.$emit(
                    "move",
                    this.columnToDrag.column,
                    column < this.columnToDrag.column ? column : column - 1
                );
            }
            // this.$emit("resize", this.columnToResize.column, width);
            // this.columnToDrag.target.style.transform = "translateX(0)";
            // this.columnToDrag.target.style.pointerEvents = "all";
            this.columnToDrag = null;
        },
    },
};
</script>
