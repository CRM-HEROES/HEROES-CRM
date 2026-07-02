<template>
    <campaign-template :field="field">
        <item class="hc-campaign-product-row">
            <icon class="fa fa-list" icon-size="11" :size="30" :style="style" />
            <div class="hc-item-main-content" v-text="product.name"></div>
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
.hc-campaign-product-row .hc-item-main-content {
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
                name: this.product.name,
                rule: { withProducts: [this.product.id] },
                category: "rule",
                style: {
                    color: this.product.color,
                    backgroundColor: this.product.bgcolor,
                },
            },
        };
    },

    props: {
        product: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.product.bgcolor,
            };
        },
    },
};
</script>
