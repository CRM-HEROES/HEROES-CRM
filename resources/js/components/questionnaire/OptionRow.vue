<template>
    <item>
        <icon class="fa fa-check-square" />
        <div class="hc-item-main-content" v-text="option.name"></div>
        <icon
            v-if="can('all.project.questionnaire.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <!--icon class="fa fa-caret-right" /-->
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE_OPTION } from "@/actions/project/questionnaire/option";

export default {
    props: {
        option: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "questionnaire-option-update");
            store.commit(SET_QUESTIONNAIRE_OPTION, this.option);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
