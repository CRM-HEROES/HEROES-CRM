<template>
    <tree-layout :tabulation="5" @open="fetchLabels">
        <template #header>
            <item class="hc-campaign-rule-category-row">
                <icon class="fa fa-tags" color="#333333" :style="style" />
                <div class="hc-item-main-content" v-text="category.name"></div>
                <icon
                    tag="a"
                    class="fa fa-plus"
                    :size="26"
                    @click.prevent.stop="addLabel"
                />
                <icon class="fa fa-caret-down" :size="26">
                    <loading :loading="fetchingLabels" />
                </icon>
            </item>
        </template>
        <template #body>
            <label-row
                v-for="label in category.labels"
                :key="label.id"
                :category="category"
                :label="label"
                @dragging="dragging"
                @dragged="dragged"
            />
        </template>
    </tree-layout>
</template>

<style>
.hc-campaign-rule-category-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import store from "@/store";

import LabelRow from "./LabelRow.vue";

import { FETCH_LABELS } from "@/actions/project/label";
import { SET_CATEGORY } from "@/actions/project/category";
import { OPEN_MODAL } from "@/actions/modal";

export default {
    components: {
        LabelRow,
    },

    props: {
        category: {
            type: Object,
        },
    },

    data() {
        return {
            fetchingLabels: false,
        };
    },

    methods: {
        async fetchLabels() {
            this.fetchingLabels = true;

            try {
                await store.dispatch(FETCH_LABELS, this.category.id);
            } finally {
                this.fetchingLabels = false;
            }
        },

        /**
         *
         */
        addLabel() {
            store.commit(SET_CATEGORY, this.category);
            store.commit(OPEN_MODAL, "label-add");
        },

        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.category.bgcolor,
            };
        },
    },
};
</script>
