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
import { SET_GROUP } from "@/actions/project/group";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: {
                action: "prospect-attach-group",
                name: "Affecter au groupe " + this.group.name,
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
            store.commit(OPEN_MODAL, "group-update");
            store.commit(SET_GROUP, this.group);
        },
    },
};
</script>
