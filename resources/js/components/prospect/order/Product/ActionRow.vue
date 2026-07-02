<template>
    <item class="hc-order-product-commission-header">
        <icon class="fa fa-money-bill" :style="style" />
        <div class="hc-flex-column hc-item-main-content">
            <div
                class="hc-order-product-commission-name"
                v-text="action.name"
            ></div>
            <div
                class="hc-order-product-commission-user-name"
                v-text="user.name"
            ></div>
        </div>
        <div
            class="hc-order-product-commission-user-commission-value"
            v-text="commissionValue"
        ></div>
        <icon
            tag="a"
            :class="paid ? 'fa fa-check-circle' : 'fa fa-check'"
            :color="paid ? '#7939b8' : '#CCCCCC'"
        />
    </item>
</template>

<style>
.hc-order-product-commission {
    width: 100%;
}

.hc-order-product-commission-header {
    padding: 5px 0 !important;
}

.hc-order-product-commission-user-name {
    color: #777777;
}

.hc-order-product-commission-user-commission-value {
    border: 1px solid #7939b8;
    color: #7939b8;
    padding: 0px 3px;
    border-radius: 3px;
    font-size: 11px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

export default {
    props: {
        commission: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters([
            "prospectOrder",
            "users",
            "orderActions",
            "products",
            "can",
        ]),

        action() {
            return this.orderActions.find(
                (oa) => oa.id == this.commission.action_id
            );
        },

        user() {
            return this.users.find((u) => u.id == this.commission.user_id);
        },

        product() {
            return this.products.find(
                (p) => p.id == this.commission.product_id
            );
        },

        commissionValue() {
            return (
                "+ " +
                Math.round(
                    (this.commission
                        ? this.commission.type == "fix"
                            ? this.commission.value
                            : (this.commission.including_tax
                                  ? this.product.price_including_tax
                                  : this.product.price_excluding_tax) *
                              this.commission.value
                        : 0) * 100
                ) /
                    100 +
                this.prospectOrder.currency
            );
        },

        style() {
            return {
                color: this.action.bgcolor,
            };
        },
    },
};
</script>
