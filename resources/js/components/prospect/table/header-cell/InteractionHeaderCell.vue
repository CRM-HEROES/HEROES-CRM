<template>
    <label class="hc-relation-header-cell-label">
        <div class="hc-relation-header-cell-options">
            <span
                :class="[
                    'fa',
                    sortedBy && sortedDesc ? 'fa-caret-up' : 'fa-caret-down',
                ]"
                @click.prevent.stop="sortBy"
            >
                <loading :loading="sorting" />
            </span>
            <span class="fa fa-search"></span>
        </div>

        <span v-text="column.name"></span>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    SET_PROSPECTS_SORT_ORDER,
    SET_PROSPECTS_SORT_BY,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

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
            sorting: false,
        };
    },

    methods: {
        /**
         * Sort prospects list
         * by interaction.created_at field
         */
        async sortBy() {
            this.sorting = true;

            try {
                // Toggle sort order
                await store.commit(
                    SET_PROSPECTS_SORT_ORDER,
                    this.sortedDesc ? "asc" : "desc"
                );

                // Sort by the current field (interactions_created_at)
                await store.commit(
                    SET_PROSPECTS_SORT_BY,
                    "interactions_created_at"
                );

                // Update prospects list
                await store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.sorting = false;
            }
        },
    },

    computed: {
        ...mapGetters([
            "categories",
            "prospectsParams",
            "prospectsParamExists",
            "prospectsSortBy",
            "prospectsSortOrder",
        ]),

        /**
         * Check if current prospects list
         * is sorted by the current field
         */
        sortedBy() {
            return this.prospectsSortBy == "interactions_created_at";
        },

        /**
         * Get sort order
         */
        sortedDesc() {
            return this.prospectsSortOrder == "desc";
        },
    },
};
</script>
