<template>
    <div class="hc-virtual-scroller-table">
        <div
            class="hc-virtual-scroller-table-content"
            ref="container"
            @scroll.passive="handleScroll"
        >
            <table
                class="hc-virtual-scroller-table-header"
                :style="{
                    width: totalWidths + 'px',
                }"
                ref="header"
            >
                <!-- Col group -->
                <colgroup>
                    <col
                        v-for="i in _columns"
                        :key="i"
                        :style="{
                            width: getColumnWidth(i - 1) + 'px',
                        }"
                    />
                </colgroup>

                <!-- Header -->
                <thead>
                    <th
                        v-for="i in _columns"
                        :key="i"
                        :class="[i <= fixed ? 'sticky' : '']"
                        :style="
                            i <= fixed
                                ? {
                                      left: totalWidthsPerColumn[i - 1] + 'px',
                                  }
                                : null
                        "
                    >
                        <slot name="header" :column="i - 1"></slot>
                    </th>
                </thead>
            </table>

            <table
                class="hc-virtual-scroller-table-body"
                :style="{
                    width: totalWidths + 'px',
                    transform:
                        'translateY(' + totalHeightsBeforeStartIndex + 'px)',
                }"
            >
                <!-- Col group -->
                <colgroup>
                    <col
                        v-for="i in _columns"
                        :key="i"
                        :style="{
                            width: getColumnWidth(i - 1) + 'px',
                        }"
                    />
                </colgroup>

                <!-- Body -->
                <tbody>
                    <!-- Top margin -->
                    <!--tr :style="{ height: topMargin + 'px' }"></tr-->

                    <!-- List of rows -->
                    <tr
                        v-for="i in visibleRowsCount"
                        :key="(startIndex + i - 1) % visibleRowsCount"
                        :style="{
                            height: getColumnHeight(startIndex + i - 1) + 'px',
                        }"
                        class="hc-virtual-scroller-table-row"
                    >
                        <!-- List of cell per row -->
                        <td
                            v-for="j in _columns"
                            :key="j"
                            :class="[
                                'hc-virtual-scroller-table-cell',
                                j <= fixed ? 'sticky' : '',
                            ]"
                            :style="
                                j <= fixed
                                    ? {
                                          left:
                                              totalWidthsPerColumn[j - 1] +
                                              'px',
                                      }
                                    : null
                            "
                        >
                            <slot
                                name="cell"
                                :index="i - 1"
                                :key="(startIndex + i - 1) % visibleRowsCount"
                                :row="startIndex + i - 1"
                                :column="j - 1"
                                :top="
                                    totalHeightsBeforeStartIndex +
                                    $refs.header.offsetHeight
                                "
                            ></slot>
                        </td>
                    </tr>

                    <!-- Bottom margin -->
                    <!--tr :style="{ height: bottomMargin + 'px' }"></tr-->
                </tbody>
            </table>
            <div
                class="hc-virtual-scroller-table-content-height"
                :style="{
                    height: totalHeights + 'px',
                }"
            ></div>
            <slot name="extra"></slot>
        </div>
    </div>
</template>

<style>
/* Container */
.hc-virtual-scroller-table {
    width: 100%;
    height: 100%;
    overflow: hidden;
    position: relative;
    background: white;
    color: #000000;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    /*scroll-behavior: smooth;*/
}

/* Table */
.hc-virtual-scroller-table-header {
    position: sticky;
    top: 0;
    z-index: 2;
    table-layout: fixed;
}

.hc-virtual-scroller-table-content {
    flex: 1;
    overflow: auto;
    position: relative;
    scroll-behavior: smooth;
}

/* Header cell */
.hc-virtual-scroller-table-header > thead > th {
    background-color: white;
    font-size: 11px;
    text-transform: uppercase;
    padding: 0;
    border: 1px solid #c0c0c0;
}
.hc-virtual-scroller-table-header > thead > th.sticky {
    position: sticky;
    z-index: 3;
}
.hc-virtual-scroller-table-header > thead > th.sticky:before {
    border: 1px solid #c0c0c0;
    bottom: -1px;
    content: "";
    display: inline-block;
    left: -1px;
    pointer-events: none;
    position: absolute;
    right: -1px;
    top: -1px;
}

.hc-virtual-scroller-table-body {
    table-layout: fixed;
}

.hc-virtual-scroller-table-content-height {
    width: 100%;
    pointer-events: none;
    position: absolute;
    top: 0;
    left: 0;
}

/* Cell */
.hc-virtual-scroller-table-cell {
    border: 1px solid #d8d8d8;
    max-width: 800px;
    padding: 0;
    position: relative;
    max-width: 160px;
    height: 100%;
    white-space: nowrap;
}
.hc-virtual-scroller-table-cell.sticky {
    position: sticky;
    z-index: 2;
    background-color: white;
}
.hc-virtual-scroller-table-cell.sticky:before {
    border: 1px solid #d5d5d5;
    bottom: -1px;
    content: "";
    display: inline-block;
    left: -1px;
    pointer-events: none;
    position: absolute;
    right: -1px;
    top: -1px;
}
</style>

<script>
export default {
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
    },

    data() {
        return {
            /**
             * Columns count
             */
            _columns: this.columns,

            /**
             * Rows count
             */
            _rows: this.rows,

            /**
             * Index of the first row to show
             */
            startIndex: 1,

            count: 0,

            /**
             * Margin from top
             */
            topMargin: 0,

            /**
             * Margin from bottom
             */
            bottomMargin: 0,

            /**
             *
             */
            containerHeight: 0,
        };
    },

    created() {
        this.scrollIsDirty = false;

        /**
         * Total heights before each row
         */
        this.totalHeightsBeforeEachRow = { 0: 0 };
    },

    mounted() {
        this.containerHeight = this.$refs.container.offsetHeight;
        this.handleScroll();
    },

    methods: {
        /**
         * Handle scroll
         */
        handleScroll() {
            if (this.scrollIsDirty) {
                return;
            }

            this.scrollIsDirty = true;

            requestAnimationFrame(() => {
                const scrollTop = this.$refs.container.scrollTop;

                // Check if startIndex does not change
                const h = this.totalHeightsBeforeEachRow[this.startIndex];
                if (
                    h > scrollTop ||
                    scrollTop >= h + this.getColumnHeight(this.startIndex)
                ) {
                    // Calculate the first index
                    // of the rows
                    // to display into the table
                    this.startIndex = Math.min(
                        this._rows - this.visibleRowsCount,
                        this.findStartIndex(Math.max(scrollTop, 0))
                    );

                    this.count =
                        this.findStartIndex(
                            this.$refs.container.scrollTop +
                                this.containerHeight,
                            this.startIndex,
                            this.startIndex + 64
                        ) -
                        this.startIndex +
                        2;
                }

                this.scrollIsDirty = false;
            });
        },

        /**
         * Find start index by scroll top value
         */
        findStartIndex(scrollTop, from = 0, to) {
            // Dichotomous search
            for (
                let low = from,
                    high = to ? Math.min(this._rows - 1, to) : this._rows - 1;
                low <= high;

            ) {
                const mid = Math.floor((low + high) / 2);

                if (this.totalHeightsBeforeRow(mid) > scrollTop) {
                    high = mid - 1;
                } else if (this.totalHeightsBeforeRow(mid + 1) <= scrollTop) {
                    low = mid + 1;
                } else {
                    return mid;
                }
            }

            return this._rows;
        },

        /**
         *
         * @param {*} row
         */
        totalHeightsBeforeRow(row) {
            if (this.totalHeightsBeforeEachRow[row] === undefined) {
                const chi = this.customHeightsIndexes.filter((i) => i < row);

                this.totalHeightsBeforeEachRow[row] =
                    this.defaultHeight * (row - chi.length) +
                    chi.reduce((carry, i) => carry + this.customHeights[i], 0);
            }

            return this.totalHeightsBeforeEachRow[row];
        },

        /**
         *
         * @param {*} i
         */
        getColumnWidth(i) {
            return this.customWidths[i]
                ? this.customWidths[i]
                : this.defaultWidth;
        },

        /**
         *
         * @param {*} i
         */
        getColumnHeight(i) {
            return this.customHeights[i]
                ? this.customHeights[i]
                : this.defaultHeight;
        },

        /**
         *
         *
        checkScrollEnd() {
            const container = this.$refs.container;
            const scrollTop = container.scrollTop;
            const scrollHeight = container.scrollHeight;
            const clientHeight = container.clientHeight;

            if (
                scrollTop + clientHeight + this.defaultHeight * 4 >=
                scrollHeight
            ) {
                this._rows++;
            }
        },
        */
    },

    watch: {
        rows() {
            this.$refs.container.scrollTop = 0;
            this._rows = this.rows;
            this.handleScroll();
        },
    },

    computed: {
        /**
         * Real visible rows count
         */
        visibleRowsCount() {
            return Math.min(this._rows - this.startIndex, this.count);
        },

        /**
         * Indexes of custom widths
         */
        customWidthsIndexes() {
            return Object.keys(this.customWidths).filter(
                (i) => i < this._columns
            );
        },

        /**
         * Indexes of custom heights
         */
        customHeightsIndexes() {
            return Object.keys(this.customHeights).filter(
                (i) => i < this._rows
            );
        },

        /**
         *
         */
        totalWidthsPerColumn() {
            const totalWidthsPerColumn = [];
            let totalWidths = 0;

            for (let i = 0; i < this._columns; ++i) {
                totalWidthsPerColumn.push(totalWidths);

                totalWidths += this.getColumnWidth(i);
            }

            return totalWidthsPerColumn;
        },

        /**
         * Total heights of the rows
         */
        totalHeights() {
            return (
                this.defaultHeight *
                    (this._rows - this.customHeightsIndexes.length) +
                this.customHeightsIndexes.reduce(
                    (carry, i) => carry + this.customHeights[i],
                    0
                )
            );
        },

        /**
         * Total widths
         */
        totalWidths() {
            return (
                this.defaultWidth *
                    (this._columns - this.customWidthsIndexes.length) +
                this.customWidthsIndexes.reduce(
                    (carry, i) => carry + this.customWidths[i],
                    0
                )
            );
        },

        /**
         *
         */
        totalHeightsBeforeStartIndex() {
            return this.totalHeightsBeforeRow(this.startIndex);
        },
    },
};
</script>
