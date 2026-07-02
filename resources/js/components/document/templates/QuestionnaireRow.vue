<template>
    <tree-layout :tabulation="5" @open="fetchSections">
        <template #header>
            <item>
                <icon class="fa fa-clipboard" color="#00b707" />
                <div
                    class="hc-item-main-content"
                    v-text="questionnaire.name"
                ></div>
                <icon
                    v-if="can('all.project.questionnaire.update')"
                    tag="a"
                    class="fa fa-cog hc-show-on-hover"
                    :size="30"
                    @mousedown.stop
                    @click.prevent.stop="edit"
                />
                <icon
                    tag="a"
                    class="fa fa-plus"
                    @click.prevent.stop="addSection"
                />
                <icon class="fa fa-caret-down">
                    <loading :loading="fetchingSections" />
                </icon>
            </item>
        </template>
        <template #body>
            <questionnaire-section-row
                v-for="section in questionnaire.sections"
                :key="section.id"
                :questionnaire="questionnaire"
                :section="section"
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
        </template>
    </tree-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import FieldTemplate from "../components/FieldTemplate.vue";
import QuestionnaireSectionRow from "./QuestionnaireSectionRow.vue";

import { FETCH_QUESTIONNAIRE_SECTIONS } from "@/actions/project/questionnaire/section";
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";
import { OPEN_MODAL } from "@/actions/modal";

export default {
    components: {
        FieldTemplate,
        QuestionnaireSectionRow,
    },

    props: {
        questionnaire: {
            type: Object,
        },
    },

    data() {
        return {
            fetchingSections: false,
        };
    },

    methods: {
        async fetchSections() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            this.fetchingSections = true;

            try {
                await store.dispatch(
                    FETCH_QUESTIONNAIRE_SECTIONS,
                    this.questionnaire.id
                );
            } finally {
                this.fetchingSections = false;
            }
        },

        edit() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            store.commit(OPEN_MODAL, "questionnaire-update");
        },

        /**
         *
         */
        addSection() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            store.commit(OPEN_MODAL, "questionnaire-section-add");
        },

        dragNewField(field) {
            this.$emit("dragging", field);
        },

        dropNewField(x, y) {
            this.$emit("dragged", x, y);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
