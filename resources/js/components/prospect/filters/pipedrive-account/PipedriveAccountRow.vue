<template>
    <item tag="label">
        <icon class="fa fa-share-alt" />
        <div class="hc-item-main-content" v-text="pipedriveAccount.name"></div>
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t(
                    'prospect.table.filters.pipedrive_accounts.without_pipedrive_account',
                    {
                        pipedriveAccount: pipedriveAccount.name,
                    }
                )
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
    FETCH_PROSPECTS,
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
} from "@/actions/project/prospect";

export default {
    props: {
        pipedriveAccount: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded ? ADD_PROSPECT_PARAMS : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded ? REMOVE_PROSPECT_PARAMS : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters(["prospectsParamExists"]),

        /**
         *
         */
        withKey() {
            return "withPipedriveAccounts";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutPipedriveAccounts";
        },

        /**
         *
         */
        value() {
            return this.pipedriveAccount.id;
        },

        /**
         *
         */
        isExcluded() {
            return this.prospectsParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isChecked() {
            return (
                this.prospectsParamExists(this.withKey, this.value) ||
                this.prospectsParamExists(this.withoutKey, this.value)
            );
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
