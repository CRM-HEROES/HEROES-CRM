<template>
    <campaign-template :field="field">
        <item class="hc-campaign-rule-group-row">
            <icon class="fa fa-users" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="group.name"></div>
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
.hc-campaign-rule-group-row .hc-item-main-content {
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
                action: "prospect-detach-group",
                name: "Detacher du groupe " + this.group.name,
                category: "action",
                value: {
                    group: this.group.id,
                },
            },
        };
    },

    props: {
        group: {
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
