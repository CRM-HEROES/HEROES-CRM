<template>
    <campaign-template :field="field">
        <item class="hc-campaign-status-row">
            <icon class="fa fa-list" icon-size="11" :size="30" :style="style" />
            <div class="hc-item-main-content" v-text="status.name"></div>
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
.hc-campaign-status-row .hc-item-main-content {
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
                name: this.status.name,
                rule: { withStatuses: [this.status.id] },
                category: "rule",
                style: {
                    color: this.status.color,
                    backgroundColor: this.status.bgcolor,
                },
            },
        };
    },

    props: {
        status: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.status.bgcolor,
            };
        },
    },
};
</script>
