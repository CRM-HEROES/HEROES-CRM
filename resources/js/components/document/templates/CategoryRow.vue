<template>
    <tree-layout :tabulation="5" @open="fetchLabels">
        <template #header>
            <item>
                <icon class="fa fa-tags" :color="category.bgcolor" />
                <div class="hc-item-main-content" v-text="category.name"></div>
                <field-template
                    type="html"
                    :content="'{prospect.category.' + category.id + '}'"
                    @dragging="dragNewField"
                    @dragged="dropNewField"
                >
                    <icon
                        class="fa fa-arrows"
                        icon-size="11"
                        :size="30"
                        color="#CCCCCC"
                    />
                </field-template>
                <icon
                    tag="a"
                    class="fa fa-plus"
                    @click.prevent.stop="addLabel"
                />
                <icon class="fa fa-caret-down">
                    <loading :loading="fetchingLabels" />
                </icon>
            </item>
        </template>
        <template #body>
            <label-row
                v-for="label in category.labels"
                :key="label.id"
                :label="label"
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
        </template>
    </tree-layout>
</template>

<script>
import store from "@/store";

import FieldTemplate from "../components/FieldTemplate.vue";
import LabelRow from "./LabelRow.vue";

import { FETCH_LABELS } from "@/actions/project/label";
import { SET_CATEGORY } from "@/actions/project/category";
import { OPEN_MODAL } from "@/actions/modal";

export default {
    components: {
        FieldTemplate,
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

        dragNewField(field) {
            this.$emit("dragging", field);
        },

        dropNewField(x, y) {
            this.$emit("dragged", x, y);
        },
    },
};
</script>
