<template>
    <campaign-template :field="field">
        <item class="hc-campaign-action-row">
            <icon class="fa fa-list" icon-size="11" :size="30" :style="style" />
            <div class="hc-item-main-content" v-text="action.name"></div>
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
.hc-campaign-action-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: {
                name: this.action.name,
                rule: { withActions: [this.action.id] },
                category: "rule",
                style: {
                    color: this.action.color,
                    backgroundColor: this.action.bgcolor,
                },
            },
        };
    },

    props: {
        action: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.action.bgcolor,
            };
        },
    },
};
</script>
