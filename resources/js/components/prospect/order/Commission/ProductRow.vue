<template>
    <item class="hc-order-action-product">
        <div class="hc-item-main-content" v-text="product.name"></div>
        <div
            class="hc-order-action-product-commission-value"
            v-if="commission"
            v-text="commissionValue"
        ></div>
        <div
            class="hc-order-action-product-commission"
            v-if="commission"
            v-text="commissionLabel"
        ></div>
        <div
            class="hc-order-action-product-commission-not-defined"
            v-else
            v-text="$t('prospect.order.tabs.commissions.not_defined')"
            @click.prevent="editCommission"
        ></div>
        <icon tag="a" class="fa fa-cog" @click.prevent="editCommission" />
    </item>
</template>

<style>
.hc-order-action-product {
    padding: 0 0 0 10px;
}

.hc-order-action-product .hc-item-main-content {
    font-size: 11px;
}

.hc-order-action-product > .hc-icon {
    width: 26px;
    height: 26px;
}

.hc-order-action-product-commission {
    background: #7939b8;
    color: white;
    padding: 0px 4px;
    border-radius: 3px;
    font-size: 11px;
}

.hc-order-action-product-commission-value {
    color: #7939b8;
    font-size: 11px;
    margin-right: 5px;
}

.hc-order-action-product-commission-not-defined {
    color: #ed5e3e;
    font-size: 11px;
    margin-right: 5px;
}

.hc-order-action-product-commission-not-defined:hover {
    text-decoration: underline;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        product: {
            type: Object,
        },
        user: {
            type: Object,
        },
        action: {
            type: Object,
        },
    },

    methods: {
        editCommission() {
            this.$emit("edit-commission");
        },
    },

    computed: {
        ...mapGetters(["prospectOrderCommissions"]),

        commission() {
            return this.prospectOrderCommissions.find(
                (c) =>
                    c.product_id == this.product.id &&
                    c.user_id == this.user.id &&
                    c.action_id == this.action.id
            );
        },

        commissionLabel() {
            return this.commission.type == "percentage"
                ? this.commission.value * 100 +
                      "%" +
                      (this.commission.including_tax ? "TTC" : "HT")
                : this.commission.value + "" + this.product.currency;
        },

        commissionValue() {
            return (
                "+ " +
                Math.round(
                    (this.commission.type == "fix"
                        ? this.commission.value
                        : (this.commission.including_tax
                              ? this.product.price_including_tax
                              : this.product.price_excluding_tax) *
                          this.commission.value) * 100
                ) /
                    100 +
                "" +
                this.product.currency
            );
        },
    },
};
</script>
