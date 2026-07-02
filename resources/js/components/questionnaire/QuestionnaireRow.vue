<template>
    <item>
        <icon class="fa fa-clipboard" />
        <div class="hc-item-main-content" v-text="questionnaire.name"></div>
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
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";

export default {
    props: {
        questionnaire: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "questionnaire-update");
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
