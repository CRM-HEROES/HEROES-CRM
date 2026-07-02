<template>
    <bloc icon="fa fa-forward icon-green" :name="pipeline.name">
        <template #options>
            <span
                v-text="progression + '%'"
                :style="{
                    fontWeight: 700,
                    fontSize: '14px',
                    color: progressionStyle,
                }"
            ></span>
        </template>
        <template #body>
            <draggable
                tag="div"
                :disabled="false"
                :list="pipeline.labels"
                item-key="id"
                style="
                    padding: 10px 10px;
                    float: left;
                    width: 100%;
                    white-space: nowrap;
                "
                :class="[prospectPipelineLabels(pipeline) ? '' : 'no-selected']"
                group="prospect-profile-pipeline"
                @end="pipelineMoved"
            >
                <template #item="{ element }">
                    <pipeline-row
                        :pipeline="pipeline"
                        :label="element"
                        :active="
                            prospectPipelineLabels(pipeline) &&
                            prospectPipelineLabels(pipeline).id == element.id
                                ? 'active'
                                : ''
                        "
                    />
                </template>
            </draggable>
        </template>
    </bloc>
</template>

<style></style>

<script>
import PipelineLabelService from "@/apis/project/pipeline/label";
import { mapGetters } from "vuex";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import PipelineRow from "./PipelineRow.vue";

export default {
    components: {
        Bloc,
        PipelineRow,
    },

    props: {
        bloc: {
            type: Object,
        },
        pipeline: {
            type: Object,
        },
    },

    data() {
        return {};
    },

    methods: {
        pipelineMoved() {
            const orders = this.pipeline.labels.map((label, i) => ({
                label: label.id,
                order: i,
            }));

            PipelineLabelService.orders(this.project.slug, this.pipeline.id, {
                orders,
            });
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectPipelineLabels"]),

        progression() {
            const stage = this.prospectPipelineLabels(this.pipeline);

            if (!stage) {
                return 0;
            }

            return (
                ((this.pipeline.labels.findIndex(
                    (label) => label.id == stage.id
                ) +
                    1) *
                    100) /
                this.pipeline.labels.length
            ).toFixed(2);
        },

        progressionStyle() {
            if (this.progression < 33) {
                return "red";
            }

            if (this.progression < 66) {
                return "#f68800";
            }

            if (this.progression < 100) {
                return "#abad00";
            }

            return "#489f1f";
        },
    },
};
</script>
