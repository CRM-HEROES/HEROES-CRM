<template>
    <label class="hc-thread-header-cell-label">
        <div class="hc-thread-header-cell-options">
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

            <span
                v-if="can('all.project.thread.update')"
                class="fa fa-cog"
                @click.prevent.stop="edit"
            ></span>

            <span
                class="fa fa-users"
                @click.prevent.stop="filterThread"
            ></span>
        </div>

        <input
            :placeholder="column.name + ' ...'"
            @change="search"
            :value="prospectsParams[searchKey]"
        />
        <span v-text="column.name"></span>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    SET_PROSPECTS_SORT_ORDER,
    SET_PROSPECTS_SORT_BY,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_THREAD } from "@/actions/project/thread";
import { OPEN_SLIDE } from "@/actions/slide";

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
         * by message.created_at field
         */
        async sortBy() {
            this.sorting = true;

            try {
                // Toggle sort order
                await store.commit(
                    SET_PROSPECTS_SORT_ORDER,
                    this.sortedDesc ? "asc" : "desc"
                );

                // Sort by the current field (messages_created_at)
                await store.commit(
                    SET_PROSPECTS_SORT_BY,
                    "messages_created_at"
                );

                // Update prospects list
                await store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.sorting = false;
            }
        },

        /**
         * Perform a prospects search
         * from the current field
         * using the entered keyword
         * @param {*} e
         */
        async search(e) {
            const value = e.target.value;

            // Perform search when keyword is given
            if (value) {
                await store.commit(ADD_PROSPECT_PARAMS, {
                    key: this.searchKey,
                    value: value,
                    multiple: false,
                });
                // else cancel search
            } else {
                await store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: this.searchKey,
                });
            }

            // Update prospects list
            store.dispatch(FETCH_PROSPECTS);
        },

        /**
         * ONly for thread column
         * Allow user to update the thread informations
         */
        edit() {
            store.commit(OPEN_MODAL, "thread-update");
            store.commit(
                SET_THREAD,
                this.threads.find((c) => c.id == this.column.id)
            );
        },
    },

    computed: {
        ...mapGetters([
            "threads",
            "prospectsParams",
            "prospectsParamExists",
            "prospectsSortBy",
            "prospectsSortOrder",
            "can",
        ]),

        /**
         * Check if current prospects list
         * is sorted by the current field
         */
        sortedBy() {
            return this.prospectsSortBy == "messages_created_at";
        },

        /**
         * Get sort order
         */
        sortedDesc() {
            return this.prospectsSortOrder == "desc";
        },

        /**
         * Gest the prospect param
         * for the current field search
         */
        searchKey() {
            return "thread_" + this.column.id;
        },

        /**
         * Filter by Thread
         */
        filterThread() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-thread");
        },
    },
};
</script>
