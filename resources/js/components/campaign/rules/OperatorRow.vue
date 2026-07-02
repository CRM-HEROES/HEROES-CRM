<template>
    <campaign-template :field="field">
        <item class="hc-campaign-operator-row">
            <icon class="fa fa-project-diagram" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="operator.name"></div>
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
.hc-campaign-operator-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import CampaignTemplate from "../components/Template.vue";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: { ...this.operator, category: "operator", style: {} },
        };
    },

    props: {
        operator: {
            type: Object,
        },
    },
};
</script>
