<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="categoryKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <category-row
                v-for="category in filteredCategories"
                :key="category.id"
                :category="category"
                :value="isCategoryChecked(category)"
            />
        </item-list>
        <buttons>
            <a
                v-if="can('all.project.category.add')"
                @click.prevent="addCategory"
                v-text="$t('add')"
            ></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_CATEGORY,
    BULK_REMOVE_USER_CATEGORY,
    ADD_USER_CATEGORY,
    REMOVE_USER_CATEGORY,
} from "@/actions/project/user/category";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        CategoryRow,
    },

    data() {
        return {
            categoryKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} category
         */
        isCategoryChecked(category) {
            return (
                // this.can("all") ||
                this.userCategories.findIndex((l) => l.id == category.id) >= 0
            );
        },

        /**
         *
         */
        addCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },
    },

    computed: {
        ...mapGetters(["categories", "user", "userCategories", "can"]),

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    removeStringAccent(category.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.userCategories.length == this.categories.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.categories.forEach((f) => {
                        store.commit(ADD_USER_CATEGORY, f);
                    });
                    store.dispatch(BULK_ADD_USER_CATEGORY, {
                        users: [this.user.id],
                        categories: this.categories.map((f) => f.id),
                    });
                } else {
                    this.categories.forEach((f) => {
                        store.commit(REMOVE_USER_CATEGORY, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_CATEGORY, {
                        users: [this.user.id],
                        categories: this.categories.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
