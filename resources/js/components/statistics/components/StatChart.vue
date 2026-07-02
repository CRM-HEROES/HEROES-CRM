<template>
    <div
        :class="[
            'stat-chart-bloc',
            statChart && statChart.options && statChart.options.columns
                ? 'w-' + statChart.options.columns
                : '',
            statChart && statChart.options && statChart.options.rows
                ? 'h-' + statChart.options.rows
                : '',
        ]"
    >
        <div class="stat-chart-bloc-content">
            <div class="stat-chart-bloc-header">
                <div
                    class="stat-chart-bloc-title"
                    v-text="title"
                    v-tooltip="title"
                ></div>
                <a
                    class="stat-chart-bloc-edit fa fa-pen"
                    @click.prevent="editChart"
                ></a>
                <a
                    class="stat-chart-bloc-remove fa fa-times"
                    @click.prevent="removeChart"
                ></a>
            </div>
            <div class="stat-chart-bloc-body">
                <slot></slot>
            </div>
            <loading :loading="removingChart" />
        </div>
    </div>
</template>

<style>
.stat-chart-bloc {
    width: 25%;
    padding: 4px;
    height: 300px;
    float: left;
}

.stat-chart-bloc.w-2 {
    width: 50%;
}

.stat-chart-bloc.w-3 {
    width: 75%;
}

.stat-chart-bloc.h-2 {
    height: 600px;
}

.stat-chart-bloc-content {
    background-color: white;
    border-radius: 8px;
    width: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid #0002;
    position: relative;
    height: 100%;
    color: black;
    text-decoration: none;
    /*gap: 8px;*/
}

.stat-chart-bloc-header {
    width: 100%;
    height: 40px;
    display: flex;
    flex-direction: row;
    /*border-bottom: 1px solid #f5f5f5;*/
}

.stat-chart-bloc-title {
    flex: 1;
    height: 40px;
    line-height: 40px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: bold;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-chart-bloc-edit,
.stat-chart-bloc-remove {
    width: 30px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    text-decoration: none;
    color: #cccccc;
    cursor: pointer;
}

.stat-chart-bloc-body {
    width: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    padding-bottom: 12px;
    flex: 1;
}

.stat-chart-bloc-sub-header {
    display: flex;
    flex-direction: row;
    padding: 0 16px;
    align-items: center;
}

.stat-chart-bloc-project {
    font-size: 12px;
    color: #6805b5;
    flex: 1;
    padding-right: 10px;
    cursor: pointer;
}

.stat-chart-bloc-sub-title {
    font-size: 12px;
    color: #555555;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    position: relative;
}

.stat-chart-bloc-sub-title > input {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 1;
    top: 0;
    left: 0;
}

.stat-chart-bloc-sub-title > input::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}

.stat-chart-bloc-sub-title > span {
    position: relative;
    display: inline-block;
    width: 100%;
    height: 100%;
    z-index: 2;
    background-color: white;
    pointer-events: none;
}

.stat-chart-bloc-total {
    font-size: 16px;
    color: #000000;
    font-weight: bold;
    padding-left: 10px;
}

.stat-chart-bloc-chart {
    max-width: 100%;
    height: auto;
    padding: 8px;
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-chart-bloc-chart > canvas {
    margin: auto;
    height: 100%;
    width: auto;
}

.stat-chart-bloc-labels {
    width: 100%;
    height: 26px;
    position: relative;
}

.stat-chart-bloc-labels-content {
    width: 100%;
    height: 100%;
    padding: 0 16px;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    overflow: hidden;
    background-color: white;
    justify-content: center;
    position: absolute;
    top: 0;
    left: 0;
}

.stat-chart-bloc-labels-content:hover {
    height: auto;
    top: unset;
    bottom: 0;
}

.stat-chart-bloc-label {
    width: 33.333%;
    height: 26px;
    line-height: 26px;
    display: flex;
    flex-direction: row;
    padding: 4px 8px;
    gap: 5px;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 12px;
}

.stat-chart-bloc-label:hover {
    background-color: #eee;
}

.stat-chart-bloc-label-color {
    font-size: 11px;
}

.stat-chart-bloc-label-name {
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-chart-bloc-label-count {
    font-size: 11px;
    color: #6805b5;
    font-weight: bold;
}

@media (max-width: 1600px) {
    .stat-chart-bloc {
        width: 33.333%;
    }

    .stat-chart-bloc.w-2 {
        width: 66.666%;
    }

    .stat-chart-bloc.w-3,
    .stat-chart-bloc.w-4 {
        width: 100%;
    }
}
@media (max-width: 1200px) {
    .stat-chart-bloc {
        width: 50%;
    }

    .stat-chart-bloc.w-2,
    .stat-chart-bloc.w-3,
    .stat-chart-bloc.w-4 {
        width: 100%;
    }
}
@media (max-width: 900px) {
    .stat-chart-bloc {
        width: 100%;
    }
}
@media (max-width: 767px) {
    .stat-chart-bloc {
        width: 100%;
    }
}
</style>

<script>
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_CHART, REMOVE_CHART } from "@/actions/project/stat/chart";

export default {
    props: {
        /**
         * Chart title
         */
        title: {
            type: String,
        },

        /**
         * Chart
         */
        statChart: {
            type: Object,
        },
    },

    data() {
        return {
            removingChart: false,
        };
    },

    methods: {
        async removeChart() {
            this.removingChart = true;

            try {
                this.project = await store.dispatch(REMOVE_CHART, {
                    slug: this.statChart.id,
                    project: this.statChart.project.slug,
                });
            } finally {
                this.removingChart = false;
            }
        },
        async editChart() {
            store.commit(OPEN_MODAL, "stat-chart-add");
            store.commit(SET_CHART, this.statChart);
        },
    },
};
</script>
