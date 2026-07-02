<template>
    <item>
        <icon class="fa fa-file-alt" />
        <div class="hc-item-main-content" v-text="template.name"></div>
        <icon
            v-if="can('all.project.event-description-template.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_EVENT_DESCRIPTION_TEMPLATE } from "@/actions/project/event-description-template";

export default {
    props: {
        template: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "event-description-template-update");
            store.commit(SET_EVENT_DESCRIPTION_TEMPLATE, this.template);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.template.bgcolor,
            };
        },
    },
};
</script>
