<template>
    <div
        :class="['hc-pipeline-column']"
        :style="{
            width: width + 'px',
        }"
    >
        <div :class="['hc-pipeline-column-content']">
            <header-cell :label="label" :count="count" />
            <draggable
                tag="div"
                :list="pipelineProspects"
                class="hc-pipeline-column-body"
                item-key="id"
                group="pipeline-stage"
                @start="setProspect"
                @end="setProspectPipeline"
                :component-data="{ 'data-label': label.id }"
            >
                <template #item="{ element }">
                    <prospect :prospect="element" />
                </template>
            </draggable>
        </div>
    </div>
</template>

<style>
.hc-pipeline-column {
    padding: 4px;
    min-width: 300px;
    /* height: 100%; */
    display: inline-block;
    vertical-align: top;
    align-self: stretch;
}

.hc-pipeline-column-content {
    width: 100%;
    height: 100%;
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    background-color: #fff6;
}

.hc-pipeline-column-body {
    position: relative;
    width: 100%;
    flex: 1;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { UPDATE_PROSPECT_PIPELINE_LABEL } from "@/actions/project/prospect/pipeline";

import { SET_PROSPECT } from "../../../actions/project/prospect";

import HeaderCell from "./Header.vue";
import Prospect from "./Prospect.vue";

export default {
    components: {
        HeaderCell,
        Prospect,
    },

    props: {
        pipeline: {
            type: Object,
        },
        label: {
            type: Object,
        },
        count: {
            type: Number,
        },
        width: {
            type: Number,
            default: 280,
        },
    },

    data() {
        return {
            updatingPipeline: false,
        };
    },

    methods: {
        setProspect(e) {
            store.commit(SET_PROSPECT, this.pipelineProspects[e.oldIndex]);
        },

        setProspectPipeline(e) {
            this.updatingPipeline = true;

            const label = this.pipeline.labels.find(
                (label) => label.id == e.to.dataset.label
            );

            try {
                store.dispatch(UPDATE_PROSPECT_PIPELINE_LABEL, {
                    pipeline: this.pipeline,
                    label: label,
                });
                this.$emit(
                    "prospect-pipeline-updated",
                    this.prospect,
                    this.pipeline,
                    label
                );
            } finally {
                this.updatingPipeline = false;
                store.commit(SET_PROSPECT, null);
            }
        },
    },

    computed: {
        ...mapGetters(["project", "prospects", "prospect"]),

        pipelineProspects() {
            return this.prospects.filter((prospect) =>
                prospect.labels.find((l) => l.id == this.label.id)
            );
        },
    },
};
</script>
