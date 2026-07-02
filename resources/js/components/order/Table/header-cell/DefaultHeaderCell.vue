<template>
    <label class="hc-default-header-cell-label">
        <div class="hc-default-header-cell-options">
            <span
                :class="[
                    'fa',
                    sortedBy && sortedAsc ? 'fa-caret-down' : 'fa-caret-up',
                ]"
                @click.prevent.stop="sortBy"
            ></span>
            <span
                v-if="can('all.project.field.update')"
                class="fa fa-cog"
                @click.prevent.stop="edit"
            ></span>
        </div>

        <input
            :placeholder="column.name + ' ...'"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
            @change="search"
            :value="ordersParams[searchKey]"
        />
        <span v-text="column.name"></span>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    ADD_ORDER_PARAMS,
    REMOVE_ORDER_PARAMS,
    SET_ORDERS_SORT_ORDER,
    SET_ORDERS_SORT_BY,
    FETCH_ORDERS,
} from "@/actions/project/order";

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

    methods: {
        /**
         * Sort orders list by
         * the current field
         */
        async sortBy() {
            // Sort by the current field (column.key)
            await store.commit(SET_ORDERS_SORT_BY, this.column.key);

            // Toggle sort order
            await store.commit(
                SET_ORDERS_SORT_ORDER,
                this.sortedAsc ? "desc" : "asc"
            );

            // Update orders list
            store.dispatch(FETCH_ORDERS);
        },

        /**
         * Perform a orders search
         * from the current field
         * using the entered keyword
         * @param {*} e
         */
        async search(e) {
            const value = e.target.value;

            // Perform search when keyword is given
            if (value) {
                await store.commit(ADD_ORDER_PARAMS, {
                    key: this.searchKey,
                    value: value,
                    multiple: false,
                });
                // else cancel search
            } else {
                await store.commit(REMOVE_ORDER_PARAMS, {
                    key: this.searchKey,
                });
            }

            // Update orders list
            store.dispatch(FETCH_ORDERS);
        },

        /**
         * Edit current field
         */
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },
    },

    computed: {
        ...mapGetters([
            "ordersParams",
            "fields",
            "ordersSortOrder",
            "ordersSortBy",
            "can",
        ]),

        /**
         * Check if current orders list
         * is sorted by the current field
         */
        sortedBy() {
            return this.ordersSortBy == this.column.key;
        },

        /**
         * Get sort order
         */
        sortedAsc() {
            return this.ordersSortOrder == "asc";
        },

        /**
         * Gest the order param
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
                    f.for == "order" &&
                    (this.column.category == "meta"
                        ? f.meta && f.slug == this.column.id
                        : !f.meta && f.slug == this.column.id)
            );
        },
    },
};
</script>
