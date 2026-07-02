<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="pipeline"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="p"
                        ><input
                            ref="pipelineName"
                            required
                            :placeholder="p.label + ' ...'"
                            :style="{
                                color: pipeline.color,
                                backgroundColor: pipeline.bgcolor,
                            }"
                            v-model="pipeline.name"
                    /></v-field>
                    <v-field
                        :label="'Ajouter un filtre dans le pipeline'"
                        required
                        v-slot="{ label }"
                    >
                        <search-dropdown
                            :items="[labels]"
                            :max-result="10"
                            @item-selected="addLabel"
                            style="margin-bottom: -1px"
                        ></search-dropdown>
                        <draggable
                            tag="item-list"
                            :list="pipeline.labels"
                            class="hc-flex-1"
                            style="
                                overflow-y: auto;
                                overflow-x: hidden;
                                height: auto;
                                min-height: 150px;
                                border: none;
                                padding: 0;
                                padding-top: 8px;
                            "
                            item-key="id"
                            @end="updateLabelsOrder"
                        >
                            <template #item="{ element }">
                                <label-row
                                    :label="element"
                                    @remove="removeLabel(element)"
                                />
                            </template> </draggable
                    ></v-field>
                </item-list>
                <buttons>
                    <button
                        v-if="can('all.project.pipeline.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading :loading="updatingPipeline || removingPipeline" />
            </form>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import PipelineLabelService from "@/apis/project/pipeline/label";

// Actions
import { UPDATE_PIPELINE, REMOVE_PIPELINE } from "@/actions/project/pipeline";
import {
    ADD_PIPELINE_LABEL,
    REMOVE_PIPELINE_LABEL,
} from "@/actions/project/pipeline/label";
import { CLOSE_MODAL } from "@/actions/modal";

import LabelRow from "./LabelRow.vue";

export default {
    components: {
        LabelRow,
    },

    data() {
        return {
            updatingPipeline: false,
            removingPipeline: false,
            fetchingPipeline: false,
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingPipeline = true;

            try {
                await store.dispatch(UPDATE_PIPELINE, {
                    id: this.pipeline.id,
                    name: this.pipeline.name,
                });
            } finally {
                this.updatingPipeline = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingPipeline = true;

                try {
                    await store.dispatch(REMOVE_PIPELINE, this.pipeline.id);
                } finally {
                    this.removingPipeline = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        async addLabel(label) {
            try {
                await store.dispatch(ADD_PIPELINE_LABEL, label);
                this.updateLabelsOrder();
            } finally {
            }
        },

        async removeLabel(label) {
            try {
                await store.dispatch(REMOVE_PIPELINE_LABEL, label);
                this.updateLabelsOrder();
            } finally {
            }
        },

        updateLabelsOrder() {
            const orders = this.pipeline.labels.map((label, i) => ({
                label: label.id,
                order: i,
            }));

            PipelineLabelService.orders(this.project.slug, this.pipeline.id, {
                orders,
            });
        },
    },

    watch: {
        tab(newValue) {
            if (newValue == 1) {
                this.fetchPipelineUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "pipeline", "categories", "can"]),

        /**
         *
         */
        labels() {
            return this.categories
                .reduce(
                    (carry, category) => [
                        ...carry,
                        ...category.labels.map((label) => ({
                            ...label,
                            category,
                        })),
                    ],
                    []
                )
                .filter(
                    (label) =>
                        !this.pipeline.labels.find((l) => l.id == label.id)
                );
        },
    },
};
</script>
