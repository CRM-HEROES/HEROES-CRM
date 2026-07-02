<template>
    <label
        :class="[
            'hc-default-header-cell-label',
            showOptions ? 'show-options' : '',
        ]"
    >
        <div
            class="hc-default-header-cell-options"
            @click.stop="showOptions = !showOptions"
        >
            <span
                :class="[
                    'fa',
                    sortedBy && sortedAsc ? 'fa-caret-down' : 'fa-caret-up',
                ]"
                @click.prevent="sortBy"
            >
                <span>Trier</span>
            </span>
        </div>

        <input
            :placeholder="column.name + ' ...'"
            @change="search"
            :value="usersParams[searchKey]"
            @blur="hideOptions()"
        />
        <span v-text="column.name" v-tooltip.top="column.name"></span>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    ADD_GLOBAL_USER_PARAMS,
    REMOVE_GLOBAL_USER_PARAMS,
    SET_GLOBAL_USERS_SORT_ORDER,
    SET_GLOBAL_USERS_SORT_BY,
    FETCH_GLOBAL_USERS,
} from "@/actions/user";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_FIELD } from "@/actions/project/field";

export default {
    props: {
        /**
         * Column
         */
        column: {
            type: Object,
        },
    },

    data() {
        return {
            showOptions: false,
        };
    },

    methods: {
        /**
         * Sort users list by
         * the current field
         */
        async sortBy() {
            // Toggle sort order
            await store.commit(
                SET_GLOBAL_USERS_SORT_ORDER,
                this.sortedAsc ? "desc" : "asc"
            );

            // Sort by the current field (column.key)
            await store.commit(SET_GLOBAL_USERS_SORT_BY, this.column.key);

            // Update users list
            store.dispatch(FETCH_GLOBAL_USERS);
        },

        /**
         * Perform a users search
         * from the current field
         * using the entered keyword
         * @param {*} e
         */
        async search(e) {
            const value = e.target.value;

            // Perform search when keyword is given
            if (value) {
                await store.commit(ADD_GLOBAL_USER_PARAMS, {
                    key: this.searchKey,
                    value: value,
                    multiple: false,
                });
                // else cancel search
            } else {
                await store.commit(REMOVE_GLOBAL_USER_PARAMS, {
                    key: this.searchKey,
                });
            }

            // Update users list
            store.dispatch(FETCH_GLOBAL_USERS);
        },

        /**
         * Edit current field
         */
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },

        /**
         */
        hideOptions() {
            setTimeout(() => {
                this.showOptions = false;
            }, 200);
        },
    },

    computed: {
        ...mapGetters([
            "usersParams",
            "fields",
            "globalUsersSortOrder",
            "globalUsersSortBy",
        ]),

        /**
         * Check if current users list
         * is sorted by the current field
         */
        sortedBy() {
            return this.globalUsersSortBy == this.column.key;
        },

        /**
         * Get sort order
         */
        sortedAsc() {
            return this.globalUsersSortOrder == "asc";
        },

        /**
         * Gest the user param
         * for the current field search
         */
        searchKey() {
            return this.column.category == "meta"
                ? "meta_" + this.column.id
                : "field_" + this.column.id;
        },

        /**
         * Current field
         */
        field() {
            return this.fields.find(
                (f) =>
                    f.for == "user" &&
                    (this.column.category == "meta"
                        ? f.meta && f.slug == this.column.id
                        : !f.meta && f.slug == this.column.id)
            );
        },
    },
};
</script>
