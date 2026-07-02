<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-user-md" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="questionnaire.name"></div>
        <icon
            v-if="can('all.project.questionnaire.update')"
            tag="a"
            class="fa fa-cog"
            :size="28"
            :icon-size="9"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";

export default {
    props: {
        questionnaire: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_SLIDE, "manage-questionnaires");
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.questionnaire.bgcolor,
            };
        },
    },
};
</script>
