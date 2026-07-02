<template>
    <item
        class="hc-campaign-campaign-row"
        tag="router-link"
        :to="{
            name: 'campaign.show',
            params: {
                campaign: this.campaign.id,
            },
        }"
    >
        <icon class="fa fa-bullhorn" icon-size="11" :size="30" :style="style" />
        <div class="hc-item-main-content" v-text="campaign.name"></div>
        <icon class="fa fa-cog hc-show-on-hover" icon-size="11" :size="30" />
    </item>
</template>

<style>
.hc-campaign-campaign-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import CampaignTemplate from "../components/Template.vue";

export default {
    components: {
        CampaignTemplate,
    },

    props: {
        campaign: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.campaign.bgcolor,
            };
        },
    },
};
</script>
