<template>
    <campaign-template :field="field">
        <item class="hc-campaign-step-row">
            <icon class="fa fa-list" icon-size="11" :size="30" :style="style" />
            <div class="hc-item-main-content" v-text="step.name"></div>
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
.hc-campaign-step-row .hc-item-main-content {
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
                name: this.step.name,
                rule: { withSteps: [this.step.id] },
                category: "rule",
                style: {
                    color: this.step.color,
                    backgroundColor: this.step.bgcolor,
                },
            },
        };
    },

    props: {
        step: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.step.bgcolor,
            };
        },
    },
};
</script>
