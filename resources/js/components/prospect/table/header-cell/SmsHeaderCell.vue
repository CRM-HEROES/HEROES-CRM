<template>
    <label class="hc-sms-header-cell-label">
        <div class="hc-sms-header-cell-options">
            <span
                :class="[
                    'fa',
                    sortedBy && sortedDesc ? 'fa-caret-up' : 'fa-caret-down',
                ]"
                @click.prevent.stop="sortBy"
            >
                <loading :loading="sorting" />
            </span>
        </div>

        <input
            :placeholder="column.name + ' ...'"
            @change="search"
            v-model.lazy="searchQuery"
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
                await store.commit(SET_PROSPECTS_SORT_BY, "sms_created_at");

                // Update prospects list
                await store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.sorting = false;
            }
        },
    },

    computed: {
        ...mapGetters([
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
            return this.prospectsSortBy == "sms_created_at";
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
            return "withSms";
        },

        /**
         *
         */
        searchQuery: {
            get() {
                const params = this.prospectsParams[this.searchKey];
                if (params && params.query) {
                    return params.query;
                }

                return "";
            },
            async set(value) {
                // Perform search when keyword is given
                if (value) {
                    const params = this.prospectsParams[this.searchKey]
                        ? this.prospectsParams[this.searchKey]
                        : {};
                    params.query = value;
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.searchKey,
                        value: params,
                        multiple: false,
                    });
                    // else cancel search
                } else {
                    const params = this.prospectsParams[this.searchKey];
                    if (params && params.query) {
                        delete params.query;

                        if (Object.keys(params).length == 0) {
                            await store.commit(REMOVE_PROSPECT_PARAMS, {
                                key: this.searchKey,
                            });
                        } else {
                            await store.commit(ADD_PROSPECT_PARAMS, {
                                key: this.searchKey,
                                value: params,
                                multiple: false,
                            });
                        }
                    }
                }

                // Update prospects list
                store.dispatch(FETCH_PROSPECTS);
            },
        },
    },
};
</script>
