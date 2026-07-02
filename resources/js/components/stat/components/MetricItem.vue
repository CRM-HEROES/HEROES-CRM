<template>
    <div class="hc-stat-metric-item" :style="style">
        <icon class="fa fa-eye" tag="a" color="#FFFFFF" :size="30" />
        <span v-text="metric.name"></span>
    </div>
</template>

<style>
.hc-stat-metric-item {
    display: flex;
    flex-direction: row;
    border-radius: 3px;
    color: white;
    padding-right: 10px;
    text-decoration: none;
    align-items: center;
    cursor: pointer;
    font-size: 12px;
}

.hc-stat-metric-item:hover {
    text-decoration: none;
    color: white;
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
        metric: {
            type: Object,
        },
    },

    computed: {
        style() {
            return {
                backgroundColor: this.metric.color,
            };
        },
    },
};
</script>
