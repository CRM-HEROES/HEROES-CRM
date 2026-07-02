<template>
    <tree-layout class="hc-order-action" :open="user !== null">
        <template #header>
            <item class="hc-order-action-header" v-if="user">
                <icon class="fa fa-person-digging" :style="style" />
                <div class="hc-flex-column hc-item-main-content">
                    <div
                        class="hc-order-action-name"
                        v-text="action.name"
                    ></div>
                    <div
                        class="hc-order-action-user-name"
                        v-text="user.name"
                    ></div>
                </div>
                <icon
                    v-if="can('all.project.order.action.update')"
                    tag="a"
                    class="fa fa-cog hc-show-on-hover"
                    @click.prevent.stop="edit"
                />
                <div
                    class="hc-order-action-user-commission-value"
                    v-text="commissionValue"
                ></div>
                <icon
                    tag="a"
                    :class="paid ? 'fa fa-check-circle' : 'fa fa-check'"
                    :color="paid ? '#7939b8' : '#CCCCCC'"
                    :v-tooltip="
                        paid
                            ? $t('prospect.order.commission.mark_as_unpaid')
                            : $t('prospect.order.commission.mark_as_paid')
                    "
                    @click.prevent.stop="actionPaid(!paid)"
                />
                <icon
                    v-if="can('all.project.order.action.update')"
                    tag="a"
                    class="fa fa-pen icon-cyan"
                    @click.prevent.stop="setUser"
                />
                <icon class="fa fa-caret-down" />
            </item>
            <item class="hc-order-action-header" @click.stop="setUser" v-else>
                <icon class="fa fa-person-digging" :style="style" />
                <div class="hc-flex-column hc-item-main-content">
                    <div
                        class="hc-order-action-name"
                        v-text="action.name"
                    ></div>
                    <div
                        class="hc-order-action-user-name"
                        v-text="$t('prospect.order.commission.select_user')"
                    ></div>
                </div>
                <icon
                    v-if="can('all.project.order.action.update')"
                    tag="a"
                    class="fa fa-cog hc-show-on-hover"
                    @click.prevent.stop="edit"
                />
                <icon class="fa fa-caret-right" />
            </item>
        </template>
        <template #body>
            <item-list
                v-if="user"
                style="
                    background-color: #f7f7f8;
                    padding: 5px;
                    border-radius: 5px;
                "
            >
                <product-row
                    v-for="product in prospectOrder.products"
                    :key="product.id"
                    :product="product"
                    :action="action"
                    :user="user"
                    @edit-commission="editCommission(product)"
                />
            </item-list>
        </template>
    </tree-layout>
</template>

<style>
.hc-order-action {
    width: 100%;
}

.hc-order-action-header {
    padding: 5px 0 !important;
}

.hc-order-action-user-name {
    color: #777777;
}

.hc-order-action-user-commission-value {
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

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { ADD_PROSPECT_ORDER_ACTION } from "@/actions/project/prospect/order/action";
import { SET_ORDER_ACTION } from "@/actions/project/order/action";

// Components
import ProductRow from "./ProductRow.vue";

export default {
    components: {
        ProductRow,
    },

    props: {
        action: {
            type: Object,
        },
        user: {
            type: Object,
        },
        paid: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        /**
         *
         */
        edit() {
            store.commit(OPEN_MODAL, "order-action-update");
            store.commit(SET_ORDER_ACTION, this.action);
        },

        /**
         *
         */
        setUser() {
            this.$emit("set-user");
        },

        /**
         *
         * @param {*} product
         */
        editCommission(product) {
            this.$emit("edit-commission", this.user, this.action, product);
        },

        /**
         *
         * @param {*} user
         */
        actionPaid(paid) {
            store.dispatch(ADD_PROSPECT_ORDER_ACTION, {
                action: this.action,
                user: this.user,
                params: {
                    paid: paid,
                },
            });
        },
    },

    computed: {
        ...mapGetters(["prospectOrder", "prospectOrderCommissions", "can"]),

        commissionValue() {
            return (
                "+ " +
                Math.round(
                    this.prospectOrder.products.reduce((value, product) => {
                        const commission = this.prospectOrderCommissions.find(
                            (c) =>
                                c.product_id == product.id &&
                                c.user_id == this.user.id &&
                                c.action_id == this.action.id
                        );

                        return (
                            value +
                            (commission
                                ? commission.type == "fix"
                                    ? commission.value
                                    : (commission.including_tax
                                          ? product.price_including_tax
                                          : product.price_excluding_tax) *
                                      commission.value
                                : 0)
                        );
                    }, 0) * 100
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
