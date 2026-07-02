<template>
    <div class="hc-prospect-profile-order" @click.stop="edit">
        <div class="hc-prospect-profile-order-products">
            <div
                class="hc-prospect-profile-order-product"
                v-for="product in order.products"
            >
                <div
                    class="hc-prospect-profile-order-product-image hc-flex-column"
                >
                    <div
                        v-if="product.images && product.images.length > 0"
                        class="hc-prospect-profile-order-product-image-content"
                    >
                        <img :src="product.images[0].thumbnail" />
                    </div>
                    <i v-else class="fa fa-shopping-cart"></i>
                </div>

                <div class="hc-prospect-profile-order-product-info">
                    <span
                        class="hc-prospect-profile-order-product-name"
                        v-text="product.name"
                    ></span>
                    <span
                        class="hc-prospect-profile-order-product-price"
                        v-text="
                            'PU : ' +
                            product.price +
                            ' ' +
                            product.currency +
                            ', Total : ' +
                            product.price * product.pivot.quantity +
                            ' ' +
                            product.currency
                        "
                    ></span>
                </div>

                <div class="hc-prospect-profile-order-product-count">
                    &times;{{ product.pivot.quantity }}
                </div>
            </div>
        </div>

        <div
            class="hc-prospect-profile-order-steps"
            v-if="orderSteps.length > 0"
        >
            <div class="hc-prospect-profile-order-steps-title">
                <span
                    v-if="order.status"
                    class="hc-prospect-profile-order-status"
                    :style="{
                        color: order.status.color,
                        backgroundColor: order.status.bgcolor,
                    }"
                    v-text="order.status.name"
                ></span>
            </div>

            <table>
                <tr>
                    <template v-if="order.steps">
                        <template
                            v-for="orderStep in orderSteps"
                            :key="orderStep.id"
                        >
                            <td
                                v-if="
                                    order.steps.find(
                                        (step) => step.id == orderStep.id
                                    )
                                "
                                :style="{
                                    width: 100 / orderSteps.length + '%',
                                }"
                                class="validated"
                            >
                                <div
                                    class="hc-prospect-profile-order-step-bar"
                                ></div>
                                <div
                                    class="hc-prospect-profile-order-step-name"
                                    v-tooltip="orderStep.name"
                                    v-text="orderStep.name"
                                ></div>
                            </td>
                        </template>
                        <template
                            v-for="orderStep in orderSteps"
                            :key="orderStep.id"
                        >
                            <td
                                v-if="
                                    !order.steps.find(
                                        (step) => step.id == orderStep.id
                                    )
                                "
                                :style="{
                                    width: 100 / orderSteps.length + '%',
                                }"
                            >
                                <div
                                    class="hc-prospect-profile-order-step-bar"
                                ></div>
                                <div
                                    class="hc-prospect-profile-order-step-name"
                                    v-tooltip="orderStep.name"
                                    v-text="orderStep.name"
                                ></div>
                            </td>
                        </template>
                    </template>
                </tr>
            </table>

            <div
                v-if="order.steps"
                class="hc-prospect-profile-order-steps-percent"
                v-text="
                    ((100 * order.steps.length) / orderSteps.length).toFixed(
                        2
                    ) + '%'
                "
            ></div>
        </div>

        <div class="hc-prospect-profile-order-footer">
            <span class="hc-prospect-profile-order-total">
                Total :
                <span>{{
                    order.total_including_tax.toFixed(2) + " " + order.currency
                }}</span>
            </span>
        </div>
    </div>
</template>

<style>
.hc-prospect-profile-order {
    position: relative;
    cursor: pointer;
}

.hc-prospect-profile-order-date {
    width: 100%;
    margin-bottom: 5px;
    font-size: 12px;
    color: #777;
}

.hc-prospect-profile-order-products {
    width: 100%;
    border: 1px solid #eee;
    border-radius: 10px 10px 0 0;
    overflow: hidden;
}

.hc-prospect-profile-order-product {
    position: relative;
    border-bottom: 1px solid #eee;
    margin-bottom: -1px;
    width: 100%;
    padding: 10px 50px 10px 50px;
}

.hc-prospect-profile-order-product-image {
    width: 40px;
    height: 40px;
    position: absolute;
    top: 10px;
    left: 5px;
    border-radius: 10px;
    text-align: center;
    line-height: 40px;
    font-size: 15px;
}

.hc-prospect-profile-order-product-image > i {
    height: 40px;
    line-height: 40px;
}

.hc-prospect-profile-order-product-image-content {
    text-align: center;
    width: 100%;
    padding-top: 70%;
}

.hc-prospect-profile-order-product-image-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-profile-order-product-info {
    width: 100%;
    display: flex;
    flex-direction: column;
}

.hc-prospect-profile-order-product-name {
    width: 100%;
    display: inline-block;
    font-weight: bold;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-prospect-profile-order-product-price {
    width: 100%;
    display: inline-block;
    font-size: 12px;
    color: #777;
}

.hc-prospect-profile-order-product-count {
    position: absolute;
    top: 20px;
    right: 15px;
    font-weight: bold;
    font-size: 15px;
}

.hc-prospect-profile-order-steps {
    width: 100%;
    position: relative;
    padding: 10px 70px 10px 10px;
    border-radius: 0 0 10px 10px;
    border: 1px solid #eee;
    margin-top: -1px;
}

.hc-prospect-profile-order-steps-title {
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 7px;
}

.hc-prospect-profile-order-status {
    display: inline-block;
    padding: 0 5px;
    font-weight: normal;
    line-height: 20px;
    border-radius: 5px;
}

.hc-prospect-profile-order-steps > table {
    width: 100%;
    table-layout: fixed;
}

.hc-prospect-profile-order-steps > table td {
    padding: 1px;
}

.hc-prospect-profile-order-step-bar {
    width: 100%;
    border-top: 4px solid #ddd;
    border-radius: 2px;
}

.hc-prospect-profile-order-steps
    > table
    td.validated
    .hc-prospect-profile-order-step-bar {
    border-color: #7939b8;
}

.hc-prospect-profile-order-step-name {
    width: 100%;
    text-align: center;
    padding-top: 10px;
    color: #777;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-prospect-profile-order-steps
    > table
    > td.validated
    .hc-prospect-profile-order-step-name {
    font-weight: bold;
    color: black;
}

.hc-prospect-profile-order-steps-percent {
    position: absolute;
    right: 15px;
    top: 32px;
    font-size: 15px;
}

.hc-prospect-profile-order-footer {
    width: 100%;
}

.hc-prospect-profile-order-footer * {
    vertical-align: top;
}

.hc-prospect-profile-order-invoice {
    margin-left: 10px;
    padding: 7px 12px;
}

.hc-prospect-profile-order-total {
    line-height: 38px;
}

.hc-prospect-profile-order-total > span {
    font-weight: bold;
    font-size: 15px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SLIDE } from "@/actions/slide";
import {
    SET_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";

export default {
    props: {
        order: {
            type: Object,
        },
        prospect: {
            type: Object,
        },
    },

    methods: {
        /**
         * Edit a prospect order
         */
        edit() {
            store.commit(SET_PROSPECT_ORDER, this.order);

            // Open event edit slide
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },
    },

    computed: {
        ...mapGetters(["orderStatuses", "orderSteps"]),

        total() {
            return 0;
        },

        /**
         *
         */
        status() {
            const status = this.orderStatuses.find(
                (status) => status.id == this.order.status_id
            );

            return status;
        },
    },
};
</script>
