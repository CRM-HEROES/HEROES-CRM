<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="pipedriveAccountKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-filter" />
                <div
                    class="hc-item-main-content"
                    v-text="
                        $t('prospect.table.filters.pipedrive_accounts.with')
                    "
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludePipedriveAccountStyle"
                    :title="
                        $t('prospect.table.filters.pipedrive_accounts.without')
                    "
                    @click.prevent="toggleExcludePipedriveAccount"
                />
                <checkbox
                    :model-value="isCheckedPipedriveAccount"
                    @change="changePipedriveAccount"
                />
            </item>
            <pipedrive-account-row
                v-for="pipedriveAccount in filteredPipedriveAccounts"
                :key="pipedriveAccount.id"
                :pipedrive-account="pipedriveAccount"
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
import PipedriveAccountRow from "./PipedriveAccountRow.vue";

export default {
    components: {
        PipedriveAccountRow,
    },

    data() {
        return {
            tab: 0,
            pipedriveAccountKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} event
         */
        changePipedriveAccount(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyPipedriveAccount,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyPipedriveAccount,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        /**
         *
         */
        toggleExcludePipedriveAccount() {
            // Add or remove with param
            store.commit(
                this.isExcludedPipedriveAccount
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyPipedriveAccount,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedPipedriveAccount
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyPipedriveAccount,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters([
            "pipedriveAccounts",
            "prospect",
            "prospectFullName",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyPipedriveAccount() {
            return "withPipedriveAccounts";
        },

        /**
         *
         */
        withoutKeyPipedriveAccount() {
            return "withoutPipedriveAccounts";
        },

        /**
         *
         */
        isCheckedPipedriveAccount() {
            return (
                this.prospectsParamValue(this.withKeyPipedriveAccount) === "" ||
                this.prospectsParamValue(this.withoutKeyPipedriveAccount) === ""
            );
        },

        /**
         *
         */
        isExcludedPipedriveAccount() {
            return (
                this.prospectsParamValue(this.withoutKeyPipedriveAccount) === ""
            );
        },

        /**
         *
         */
        excludePipedriveAccountStyle() {
            return {
                color: this.isExcludedPipedriveAccount ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredPipedriveAccounts() {
            const keyword = removeStringAccent(this.pipedriveAccountKeyword);

            return this.pipedriveAccounts.filter(
                (pipedriveAccount) =>
                    removeStringAccent(pipedriveAccount.name).indexOf(
                        keyword
                    ) >= 0
            );
        },
    },
};
</script>
