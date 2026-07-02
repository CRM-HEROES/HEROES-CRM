<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="categoryKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-category">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.categories.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeCategoryStyle"
                    :title="$t('user.table.filters.categories.without')"
                    @click.prevent="toggleExcludeCategory"
                />
                <checkbox
                    :model-value="isCheckedCategory"
                    @change="changeCategory"
                />
            </item>
            <category-row
                v-for="category in filteredCategories"
                :key="category.id"
                :category="category"
            />
        </item-list>
        <buttons v-if="can('all.project.category.add')">
            <a @click.prevent="addCategory" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        CategoryRow,
    },

    data() {
        return {
            tab: 0,
            categoryKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        changeCategory(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyCategory,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyCategory,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeCategory() {
            // Add or remove with param
            store.commit(
                this.isExcludedCategory ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyCategory,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedCategory ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyCategory,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["categories", "user", "usersParamValue", "can"]),

        /**
         *
         */
        withKeyCategory() {
            return "withCategories";
        },

        /**
         *
         */
        withoutKeyCategory() {
            return "withoutCategories";
        },

        /**
         *
         */
        isCheckedCategory() {
            return (
                this.usersParamValue(this.withKeyCategory) === "" ||
                this.usersParamValue(this.withoutKeyCategory) === ""
            );
        },

        /**
         *
         */
        isExcludedCategory() {
            return this.usersParamValue(this.withoutKeyCategory) === "";
        },

        /**
         *
         */
        excludeCategoryStyle() {
            return {
                color: this.isExcludedCategory ? "#CC0000" : "#CCCCCC",
            };
        },

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
    },
};
</script>
