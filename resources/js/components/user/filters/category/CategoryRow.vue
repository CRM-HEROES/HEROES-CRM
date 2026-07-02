<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('user.table.filters.categories.without_category', {
                    category: category.name,
                })
            "
            @click.prevent="toggleExclude"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_USERS,
    ADD_USER_PARAMS,
    REMOVE_USER_PARAMS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

export default {
    props: {
        category: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_USERS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_USERS);
        },

        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },
    },

    computed: {
        ...mapGetters(["usersParamExists"]),

        /**
         *
         */
        withKey() {
            return "withCategories";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutCategories";
        },

        /**
         *
         */
        value() {
            return this.category.id;
        },

        /**
         *
         */
        isExcluded() {
            return this.usersParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isChecked() {
            return (
                this.usersParamExists(this.withKey, this.value) ||
                this.usersParamExists(this.withoutKey, this.value)
            );
        },

        /**
         *
         */
        style() {
            return {
                color: this.category.bgcolor,
            };
        },

        /**
         *
         */
        excludeStyle() {
            return {
                color: this.isExcluded ? "#CC0000" : "#CCCCCC",
            };
        },
    },
};
</script>
