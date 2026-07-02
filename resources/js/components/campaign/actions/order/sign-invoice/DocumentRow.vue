<template>
    <campaign-template :field="field">
        <item class="hc-campaign-rule-document-row">
            <icon class="fa fa-file-pdf" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="document.name"></div>
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
.hc-campaign-rule-document-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import store from "@/store";

import CampaignTemplate from "@/components/campaign/components/Template.vue";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_DOCUMENT } from "@/actions/project/document";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: {
                action: "prospect-attach-document",
                name: this.$t("campaign.action.order.sign_invoice.name", {
                    invoice: this.document.name,
                }),
                category: "action",
                value: {
                    document: this.document.id,
                },
            },
        };
    },

    props: {
        document: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "document-update");
            store.commit(SET_DOCUMENT, this.document);
        },
    },
};
</script>
