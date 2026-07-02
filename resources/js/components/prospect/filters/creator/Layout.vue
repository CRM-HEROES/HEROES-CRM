<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="userKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-prospects-table-filter-user">
                <icon class="fa fa-filter" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.filters.creators.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeCreatorStyle"
                    :title="$t('prospect.table.filters.creators.without')"
                    @click.prevent="toggleExcludeCreator"
                />
                <checkbox
                    :model-value="isCheckedCreator"
                    @change="changeCreator"
                />
            </item>
            <creator-row
                v-for="creator in filteredCreators"
                :key="creator.id"
                :creator="creator"
            />
        </item-list>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

// Components
import CreatorRow from "./CreatorRow.vue";

export default {
    components: {
        CreatorRow,
    },

    data() {
        return {
            tab: 0,
            userKeyword: "",
        };
    },

    methods: {
        changeCreator(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyCreator,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyCreator,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        toggleExcludeCreator() {
            // Add or remove with param
            store.commit(
                this.isExcludedCreator
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyCreator,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedCreator
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyCreator,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters([
            "users",
            "prospect",
            "prospectFullName",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyCreator() {
            return "withCreators";
        },

        /**
         *
         */
        withoutKeyCreator() {
            return "withoutCreators";
        },

        /**
         *
         */
        isCheckedCreator() {
            return (
                this.prospectsParamValue(this.withKeyCreator) === "" ||
                this.prospectsParamValue(this.withoutKeyCreator) === ""
            );
        },

        /**
         *
         */
        isExcludedCreator() {
            return this.prospectsParamValue(this.withoutKeyCreator) === "";
        },

        /**
         *
         */
        excludeCreatorStyle() {
            return {
                color: this.isExcludedCreator ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredCreators() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (creator) =>
                    removeStringAccent(creator.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
