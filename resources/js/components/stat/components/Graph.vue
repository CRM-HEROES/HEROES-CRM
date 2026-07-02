<template>
    <div class="hc-stat-graph" :style="{ backgroundColor: backgroundColor }">
        <div class="hc-stat-graph-view">
            <chart
                :stat-key="statKey"
                :from="from"
                :to="to"
                :border-color="borderColor"
                :fill-color="fillColor"
            />
        </div>
        <div class="hc-stat-graph-infos">
            <label class="hc-stat-graph-label" v-text="title"> </label>
            <div class="hc-stat-graph-last-value-container">
                <div class="hc-stat-graph-last-date" v-text="to"></div>
                <div class="hc-stat-graph-last-value"></div>
            </div>
        </div>
    </div>
</template>

<style>
.hc-stat-graph {
    display: flex;
    flex-direction: column;
    border-radius: 4px;
    padding: 7px;
}

.hc-stat-graph-view {
    flex: 1;
    padding: 5px;
    height: 0;
}

.hc-stat-graph-infos {
    display: flex;
    flex-direction: column;
    padding-top: 5px;
}

.hc-stat-graph-label {
    font-weight: bold;
    font-size: 13px;
}

.hc-stat-graph-last-value-container {
    display: flex;
    flex-direction: row;
    opacity: 0.7;
    font-size: 11px;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

import Chart from "./Chart.vue";

export default {
    components: {
        Chart,
    },

    props: {
        title: {
            type: String,
        },
        statKey: {
            type: String,
        },
        from: {
            type: String,
        },
        to: {
            type: String,
        },
        borderColor: {
            type: String,
            default: "#000000",
        },
        fillColor: {
            type: String,
            default: "#00000022",
        },
        backgroundColor: {
            type: String,
            default: "#FFFFFF",
        },
    },
};
</script>
