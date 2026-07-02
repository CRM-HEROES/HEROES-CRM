<template>
    <item tag="label">
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="category.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";
import { UPDATE_USER_BULK_CATEGORIES } from "@/actions/project/user";

export default {
    props: {
        category: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },
    },

    computed: {
        ...mapGetters(["userBulkCategories", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.category.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkCategories;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_CATEGORIES, value);
            },
        },
    },
};
</script>
