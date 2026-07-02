<template>
    <field-template type="prospect-option" :content="option.id">
        <item class="hc-document-option">
            <icon class="fa fa-circle" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="option.name"></div>
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

<style>
.hc-document-option .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import FieldTemplate from "../components/FieldTemplate.vue";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";
import { SET_QUESTIONNAIRE_SECTION } from "@/actions/project/questionnaire/section";
import { SET_QUESTIONNAIRE_QUESTION } from "@/actions/project/questionnaire/question";
import { SET_QUESTIONNAIRE_OPTION } from "@/actions/project/questionnaire/option";

export default {
    components: {
        FieldTemplate,
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
        option: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
            store.commit(SET_QUESTIONNAIRE_QUESTION, this.question);
            store.commit(SET_QUESTIONNAIRE_OPTION, this.option);
            store.commit(OPEN_MODAL, "questionnaire-option-update");
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
