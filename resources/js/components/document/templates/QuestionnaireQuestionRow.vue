<template>
    <tree-layout :tabulation="5" @open="fetchOptions">
        <template #header>
            <template
                v-if="question.type == 'checkbox' || question.type == 'radio'"
            >
                <item>
                    <icon :class="icon" color="#18b4e5" />
                    <div
                        class="hc-item-main-content"
                        :title="question.name"
                        v-text="question.name"
                    ></div>
                    <icon
                        v-if="can('all.project.questionnaire.update')"
                        tag="a"
                        class="fa fa-cog hc-show-on-hover"
                        :size="30"
                        @mousedown.stop
                        @click.prevent.stop="edit"
                    />
                    <field-template
                        type="html"
                        :content="'{prospect.question.' + question.id + '}'"
                        @dragging="dragNewField"
                        @dragged="dropNewField"
                    >
                        <icon
                            class="fa fa-arrows"
                            icon-size="11"
                            :size="30"
                            color="#CCCCCC"
                        />
                    </field-template>
                    <icon
                        tag="a"
                        class="fa fa-plus hc-show-on-hover"
                        @click.prevent.stop="addOption"
                    />
                    <icon class="fa fa-caret-down hc-show-on-hover">
                        <loading :loading="fetchingOptions" />
                    </icon>
                </item>
            </template>

            <template v-else>
                <field-template
                    type="html"
                    :content="'{prospect.question.' + question.id + '}'"
                    @dragging="dragNewField"
                    @dragged="dropNewField"
                >
                    <item>
                        <icon :class="icon" color="#18b4e5" />
                        <div
                            class="hc-item-main-content"
                            v-text="question.name"
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
                            class="fa fa-arrows"
                            icon-size="11"
                            :size="30"
                            color="#CCCCCC"
                        />
                    </item>
                </field-template>
            </template>
        </template>
        <template #body>
            <questionnaire-option-row
                v-for="option in question.options"
                :key="option.id"
                :questionnaire="questionnaire"
                :section="section"
                :question="question"
                :option="option"
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
import QuestionnaireOptionRow from "./QuestionnaireOptionRow.vue";

import { FETCH_QUESTIONNAIRE_OPTIONS } from "@/actions/project/questionnaire/option";
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";
import { SET_QUESTIONNAIRE_SECTION } from "@/actions/project/questionnaire/section";
import { SET_QUESTIONNAIRE_QUESTION } from "@/actions/project/questionnaire/question";
import { OPEN_MODAL } from "@/actions/modal";

export default {
    components: {
        FieldTemplate,
        QuestionnaireOptionRow,
    },

    props: {
        questionnaire: {
            type: Object,
        },
        section: {
            type: Object,
        },
        question: {
            type: Object,
        },
    },

    data() {
        return {
            fetchingOptions: false,
        };
    },

    methods: {
        async fetchOptions() {
            this.fetchingOptions = true;

            try {
                await store.dispatch(FETCH_QUESTIONNAIRE_OPTIONS, {
                    questionnaire: this.questionnaire.id,
                    section: this.section.id,
                    question: this.question.id,
                });
            } finally {
                this.fetchingOptions = false;
            }
        },

        edit() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
            store.commit(SET_QUESTIONNAIRE_QUESTION, this.question);
            store.commit(OPEN_MODAL, "questionnaire-question-update");
        },

        /**
         *
         */
        addOption() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
            store.commit(SET_QUESTIONNAIRE_QUESTION, this.question);
            store.commit(OPEN_MODAL, "questionnaire-option-add");
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

        /**
         *
         */
        icon() {
            if (this.question.type == "checkbox") {
                return "fa fa-check-square";
            } else if (this.question.type == "radio") {
                return "fa fa-record-vinyl";
            } else if (this.question.type == "text") {
                return "fa fa-font";
            } else if (this.question.type == "textarea") {
                return "fa fa-align-left";
            } else if (this.question.type == "date") {
                return "fa fa-calendar";
            } else if (this.question.type == "image") {
                return "fa fa-image";
            } else if (this.question.type == "file") {
                return "fa fa-file";
            } else {
                return "fa fa-question";
            }
        },
    },
};
</script>
