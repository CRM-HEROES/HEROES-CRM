<template>
    <campaign-template :field="field">
        <item class="hc-campaign-rule-user-row">
            <icon class="fa fa-user" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="user.name"></div>
            <icon
                class="fa fa-arrows"
                icon-size="11"
                :size="30"
                color="#CCCCCC"
            />
        </item>
    </campaign-template>
</template>

<style>
.hc-campaign-rule-user-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import store from "@/store";

import CampaignTemplate from "@/components/campaign/components/Template.vue";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FIELD } from "@/actions/project/field";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: {
                action: "prospect-attach-user",
                name: "Affecter à " + this.user.name,
                category: "action",
                value: {
                    user: this.user.id,
                },
            },
        };
    },

    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },
    },
};
</script>
