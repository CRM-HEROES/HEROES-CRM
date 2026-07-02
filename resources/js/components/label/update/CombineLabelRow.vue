<template>
    <item>
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <icon
            v-if="can('all.project.category.label.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    props: {
        label: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },
    },
};
</script>
