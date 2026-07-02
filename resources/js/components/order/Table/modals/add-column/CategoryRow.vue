<template>
    <column-row
        :name="category.name"
        icon="fa fa-tags"
        :icon-color="category.bgcolor"
        :column-id="'category->' + category.id"
        :header-id="'hc-orders-table-header-category-' + category.id"
    >
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <icon class="fa fa-tags icon-blue" @click.prevent.stop="labels" />
    </column-row>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { OPEN_MODAL, CLOSE_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

import ColumnRow from "./ColumnRow.vue";

export default {
    components: { ColumnRow },

    props: {
        /**
         * Category
         */
        category: {
            type: Object,
        },
    },

    methods: {
        labels() {
            store.commit(CLOSE_MODAL);
            store.commit(OPEN_SLIDE, "categories");
            store.commit(SET_CATEGORY, this.category);
        },

        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
