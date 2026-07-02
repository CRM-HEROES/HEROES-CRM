<template>
    <div id="hc-pipeline">
        <div id="hc-pipeline-header">
            <select id="hc-pipeline-select" v-model="pipelineId">
                <option
                    :value="null"
                    v-text="'Choisir une pipeline ...'"
                ></option>
                <option
                    v-for="pipeline in pipelines"
                    :value="pipeline.id"
                    v-text="pipeline.name"
                ></option>
            </select>
            <icon
                v-if="pipeline"
                class="fa fa-pen icon-blue"
                tag="a"
                @click.prevent="updatePipeline"
            />
            <icon
                class="fa fa-plus icon-blue"
                tag="a"
                @click.prevent="addPipeline"
            />
        </div>
        <div id="hc-pipeline-body">
            <div id="hc-pipeline-table" ref="body">
                <draggable
                    v-if="pipeline"
                    tag="div"
                    :disabled="false"
                    :list="pipeline.labels"
                    id="hc-pipeline-columns"
                    ref="columns"
                    @end="pipelineMoved"
                >
                    <template #item="{ element }">
                        <column
                            :pipeline="pipeline"
                            :label="element"
                            :count="
                                prospectsCount[element.id]
                                    ? prospectsCount[element.id]
                                    : 0
                            "
                            @prospect-pipeline-updated="fetchProspectsCount"
                        />
                    </template>
                </draggable>
            </div>
        </div>
    </div>
</template>

<style>
#hc-pipeline {
    border-top: 1px solid silver;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    user-select: none;
    position: relative;
}

#hc-pipeline-header {
    width: 100%;
    height: 50px;
    background-color: #fff;
    border-bottom: 1px solid silver;
    display: flex;
    align-items: center;
    justify-content: right;
    padding: 10px;
}

#hc-pipeline-select {
    height: 100%;
    padding: 0 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 12px;
}

#hc-pipeline-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: hidden;
}

#hc-pipeline-table {
    width: 100%;
    height: 100%;
    overflow: auto;
    position: relative;
    background: white;
    background-color: #f8f5fa;
}

#hc-pipeline-columns {
    width: auto;
    /* font-size: 0; */
    /* white-space: nowrap; */
    padding: 4px;
    display: flex;
    flex-direction: row;
}

@media (max-width: 767px) {
    .agenda-list #hc-pipeline-table {
        flex-direction: column;
    }
    .agenda-list #hc-pipeline-columns {
        white-space: normal;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import PipelineService from "@/apis/project/pipeline";
import PipelineLabelService from "@/apis/project/pipeline/label";

import { OPEN_MODAL } from "@/actions/modal";
import {
    FETCH_PROSPECTS,
    SET_PROSPECT_PARAMS,
    SET_PROSPECTS_FIELDS,
    SET_PROSPECTS_COUNT,
} from "../../../actions/project/prospect";

import Column from "./Column.vue";

export default {
    components: {
        Column,
    },

    data() {
        return {
            loading: false,
            prospects: [],
            prospectsCount: {},
        };
    },

    created() {
        this.fetchProspects();
        this.fetchProspectsCount();

        if (!this.pipeline) {
            if (this.pipelines.length > 0) {
                this.setPipeline(this.pipelines[0]);
            } else {
                this.addPipeline();
            }
        }
    },

    methods: {
        async fetchProspects() {
            if (!this.pipeline) {
                return;
            }

            store.commit(SET_PROSPECT_PARAMS, {
                withPipelines: [this.pipeline.id],
            });
            store.commit(
                SET_PROSPECTS_FIELDS,
                `id,first_name,last_name,email,mobile_phone_number,pipeline->${this.pipeline.id}`
            );
            store.commit(SET_PROSPECTS_COUNT, 500);

            this.loading = true;

            try {
                const data = store.dispatch(FETCH_PROSPECTS);
                store.commit(SET_PROSPECTS_FIELDS, null);
            } finally {
                this.loading = false;
            }
        },

        async fetchProspectsCount() {
            if (!this.pipeline) {
                return;
            }

            try {
                const { data } = await PipelineService.count(
                    this.project.slug,
                    this.pipeline.id
                );
                this.prospectsCount = data;
            } finally {
            }
        },

        pipelineMoved() {
            const orders = this.pipeline.labels.map((label, i) => ({
                label: label.id,
                order: i,
            }));

            PipelineLabelService.orders(this.project.slug, this.pipeline.id, {
                orders,
            });
        },

        setPipeline(pipeline) {
            this.$router.push({
                name: "pipeline.show",
                params: {
                    project: this.project.slug,
                    pipeline: pipeline.id,
                },
            });
        },

        addPipeline() {
            store.commit(OPEN_MODAL, "pipeline-add");
        },

        updatePipeline() {
            store.commit(OPEN_MODAL, "pipeline-update");
        },
    },

    watch: {
        pipeline() {
            this.fetchProspects();
            this.fetchProspectsCount();
        },
    },

    computed: {
        ...mapGetters(["project", "pipelines", "pipeline"]),

        pipelineId: {
            get() {
                return this.pipeline ? this.pipeline.id : null;
            },
            set(value) {
                const pipeline = this.pipelines.find((p) => p.id == value);
                if (!pipeline) {
                    return;
                }

                this.setPipeline(pipeline);
            },
        },

        labels() {
            if (!this.pipeline || !Array.isArray(this.pipeline.labels)) {
                return [];
            }

            return [...this.pipeline.labels] /*.sort(
(l1, l2) => l1.pivot.order - l2.pivot.order
)*/;
        },
    },
};
</script>
