<template>
    <label class="hc-relation-header-cell-label">
        <div class="hc-relation-header-cell-options">
            <span
                :class="[
                    'fa',
                    sortedBy && sortedDesc ? 'fa-caret-up' : 'fa-caret-down',
                ]"
                @click.prevent.stop="sortBy"
            ></span>
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

    methods: {
        /**
         * Sort prospects list
         * by event.started_at field
         */
        async sortBy() {
            // Toggle sort order
            await store.commit(
                SET_PROSPECTS_SORT_ORDER,
                this.sortedDesc ? "asc" : "desc"
            );

            // Sort by the current field (events_started_at)
            await store.commit(SET_PROSPECTS_SORT_BY, "events_started_at");

            // Update prospects list
            store.dispatch(FETCH_PROSPECTS);
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
            return this.prospectsSortBy == "events_started_at";
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
