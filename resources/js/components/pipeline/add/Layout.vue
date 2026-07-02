<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storePipeline"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="pipeline.name"
                            :style="{
                                color: pipeline.color,
                                backgroundColor: pipeline.bgcolor,
                            }"
                            required
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
                            :list="pipelineLabels"
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
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingPipeline" />
            </form>
        </template>

        <!--template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                    </item>
                    <search v-model="userKeyword" />
                    <item-list
                        class="hc-flex-1"
                        padding="5px"
                        style="max-height: 400px"
                    >
                        <user-row
                            v-for="user in filteredUsers"
                            :key="user.id"
                            :user="user"
                            v-model="pipelineUsers"
                        />
                    </item-list>
                </div>
            </template-->
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_PIPELINE } from "@/actions/project/pipeline";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_PIPELINE_LABEL } from "@/actions/project/pipeline/label";

// import UserRow from "./UserRow.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        LabelRow,
    },

    data() {
        return {
            pipeline: this.newPipeline(),
            addingPipeline: false,
            /*userKeyword: "",
            pipelineUsers: [],*/
            tab: 0,
            pipelineLabels: [],
            labelKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        newPipeline() {
            return {
                name: "",
                color: "#000000",
                bgcolor: "#FFFFFF",
            };
        },

        /**
         *
         */
        async storePipeline() {
            this.addingPipeline = true;

            try {
                const pipeline = await store.dispatch(
                    ADD_PIPELINE,
                    this.pipeline
                );
                store.dispatch(BULK_ADD_PIPELINE_LABEL, {
                    labels: this.pipelineLabels.map((l) => l.id),
                    pipelines: [pipeline.id],
                });
                // store.commit(OPEN_SLIDE, "pipeline-manage-labels");

                /*if (this.pipelineUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_PIPELINE, {
                        users: this.pipelineUsers,
                        pipelines: [pipeline.id],
                    });
                }*/
            } finally {
                this.addingPipeline = false;
                this.pipeline = this.newPipeline();
                this.pipelineUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },

        addLabel(label) {
            this.pipelineLabels = [...this.pipelineLabels, label];
        },

        removeLabel(label) {
            this.pipelineLabels = this.pipelineLabels.filter(
                (l) => l.id != label.id
            );
        },

        updateLabelsOrder() {},
    },

    computed: {
        ...mapGetters(["categories"]),

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
                        !this.pipelineLabels.find((l) => l.id == label.id)
                );
        },
    },
};
</script>
