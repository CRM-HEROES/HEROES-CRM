<template>
    <div class="hc-stat-chart-bloc">
        <div class="hc-stat-chart-content">
            <div class="hc-stat-chart-header">
                <div class="hc-stat-chart-title" v-text="title"></div>
                <slot name="options"></slot>
                <div class="hc-stat-chart-remove" @click="removeBlocChart">
                    &times;
                </div>
            </div>
            <div class="hc-stat-chart-body">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hc-stat-chart-bloc {
    display: flex;
    flex-direction: column;
    height: 360px;
    padding: 4px;
}

.hc-stat-chart-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 12px;
    border-radius: 8px;
    background-color: white;
    /* border: 1px solid #eee; */
    box-shadow: 0 0 16px -4px #7b858f11;
    overflow: hidden;
}

.hc-stat-chart-header {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 32px;
    align-items: center;
}

.hc-stat-chart-title {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    flex: 1;
    text-align: left;
}

.hc-stat-chart-remove {
    font-size: 20px;
    color: #333;
    cursor: pointer;
    width: 32px;
    height: 32px;
    line-height: 32px;
    text-align: center;
}

.hc-stat-chart-body {
    display: flex;
    flex-direction: column;
    width: 100%;
    flex: 1;
    overflow: hidden;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

export default {
    props: {
        title: {
            type: String,
        },
        index: {
            type: String,
        },
    },

    methods: {
        async removeBlocChart() {
            try {
                await ApiService.delete(
                    `project/${this.project.slug}/stat/${this.index}`
                );
                this.$emit("chart-removed", this.index);
            } finally {
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
