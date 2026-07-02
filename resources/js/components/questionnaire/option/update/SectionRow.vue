<template>
    <item>
        <icon class="fa fa-columns" />
        <div class="hc-item-main-content" v-text="section.name"></div>
        <icon
            v-if="can('all.project.questionnaire.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE_SECTION } from "@/actions/project/questionnaire/section";

export default {
    props: {
        section: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "questionnaire-section-update");
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
