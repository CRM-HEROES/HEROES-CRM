<template>
    <item>
        <icon class="fa fa-forward" />
        <div class="hc-item-main-content" v-text="pipeline.name"></div>
        <icon
            v-if="can('all.project.pipeline.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <icon class="fa fa-plus" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_PIPELINE } from "@/actions/project/pipeline";

export default {
    props: {
        pipeline: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "pipeline-update");
            store.commit(SET_PIPELINE, this.pipeline);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.pipeline.bgcolor,
            };
        },
    },
};
</script>
