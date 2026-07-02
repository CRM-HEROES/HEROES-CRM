<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <icon
            v-if="can('all.project.category.label.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
            :size="30"
        />
        <checkbox
            :model-value="value"
            @change="change"
            :disabled="prospect.processed_at"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import {
    ADD_PROSPECT_LABEL,
    REMOVE_PROSPECT_LABEL,
} from "@/actions/project/prospect/label";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        label: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked
                    ? ADD_PROSPECT_LABEL
                    : REMOVE_PROSPECT_LABEL,
                this.label
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["prospect", "can"]),

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
