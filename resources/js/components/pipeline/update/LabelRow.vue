<template>
    <item tag="label" @click.prevent="edit">
        <icon class="fa fa-tag" :style="style" size="30" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <icon
            class="fa fa-times icon-red"
            size="30"
            @click.stop="$emit('remove', label)"
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
            if (!this.can("all.project.category.label.update")) {
                return;
            }

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
