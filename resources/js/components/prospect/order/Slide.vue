<template>
    <slide
        :name="name"
        @open="fetchOrders"
        :title="$t('prospect.order.title', { prospect: prospectFullName })"
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-shopping-cart"
        style="width: 460px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <!-- List of orders -->
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="orderKeyword" />
                    <item-list
                        class="hc-flex-1"
                        padding="12px"
                        style="position: relative"
                    >
                        <order-row
                            v-for="c in filteredOrders"
                            :key="c.id"
                            :order="c"
                            @click="setOrder(c)"
                        />
                        <loading :loading="fetchingOrders" />
                    </item-list>
                    <buttons v-if="can('all.prospect.order.add')">
                        <a
                            @click.prevent="addOrder"
                            v-text="$t('new_order')"
                        ></a>
                        <loading :loading="addingOrder" />
                    </buttons>
                </div>
            </template>

            <template #2>
                <frame-layout :count="2" :tab="frameTab" class="hc-flex-1">
                    <template #1 v-if="prospectOrder">
                        <div class="hc-flex-column" style="height: 100%">
                            <!-- Current order name -->
                            <item @click="setOrder(null)" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="prospectOrder.name"
                                ></div>
                                <tag
                                    tag="a"
                                    :text="status.name"
                                    :color="status.color"
                                    :bgcolor="status.bgcolor"
                                    style="margin: 0 5px 0 0"
                                    @click.prevent.stop="orderTab = 5"
                                />
                            </item>

                            <!-- Order tab -->
                            <tab
                                :count="6"
                                :tab="orderTab"
                                @tab:change="(tab) => (orderTab = tab)"
                            >
                                <template #1>{{
                                    $t("prospect.order.tabs.products.title")
                                }}</template>
                                <template #2>{{
                                    $t("prospect.order.tabs.fields.title")
                                }}</template>
                                <template #3>{{
                                    $t("prospect.order.tabs.steps.title")
                                }}</template>
                                <template #4>{{
                                    $t("prospect.order.tabs.documents.title")
                                }}</template>
                                <template #5>{{
                                    $t("prospect.order.tabs.commissions.title")
                                }}</template>
                                <template #6>{{
                                    $t("prospect.order.tabs.status.title")
                                }}</template>
                            </tab>

                            <tab-layout
                                :count="6"
                                :tab="orderTab"
                                class="hc-flex-1"
                            >
                                <template #1>
                                    <product @product-selected="orderTab = 3" />
                                </template>
                                <template #2>
                                    <field
                                        @order-updated="orderTab = 0"
                                        :prospect-order="prospectOrder"
                                    />
                                </template>
                                <template #3>
                                    <step />
                                </template>
                                <template #4>
                                    <document />
                                </template>
                                <template #5>
                                    <commission />
                                </template>
                                <template #6>
                                    <status />
                                </template>
                            </tab-layout>
                        </div>
                    </template>

                    <template #2>
                        <select-prospect
                            @back="tab = 0"
                            @prospect-selected="setOrderProspect"
                        />
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SET_PROSPECT } from "@/actions/project/prospect";
import {
    FETCH_PROSPECT_ORDERS,
    SHOW_PROSPECT_ORDER,
    SET_PROSPECT_ORDER,
    ADD_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";
import { FETCH_ORDER_STEPS } from "@/actions/project/order/step";
import { FETCH_PRODUCTS } from "@/actions/project/product";
import { FETCH_PROSPECT_ORDER_COMMISSIONS } from "@/actions/project/prospect/order/commission";

// Components
import OrderRow from "./OrderRow.vue";
import Product from "./Product.vue";
import Commission from "./Commission.vue";
import Status from "./Status.vue";
import Step from "./Step.vue";
import Document from "./Document.vue";
import Field from "./Field.vue";
import SelectProspect from "../select/Select.vue";

export default {
    components: {
        OrderRow,
        Product,
        Commission,
        Status,
        Step,
        Document,
        Field,
        SelectProspect,
    },

    data() {
        return {
            name: "prospect-manage-orders",
            addingOrder: false,
            fetchingOrders: false,
            fetchingProducts: false,
            orderKeyword: "",

            tab: 0,
            frameTab: 0,
            orderTab: this.prospectOrderTab,
        };
    },

    methods: {
        /**
         *
         */
        async addOrder() {
            this.addingOrder = true;

            try {
                const order = await store.dispatch(ADD_PROSPECT_ORDER, {});
                store.commit(SET_PROSPECT_ORDER, order);
            } finally {
                this.addingOrder = false;
            }
        },

        /**
         *
         */
        async fetchOrders() {
            if (this.prospect) {
                this.fetchingOrders = true;

                try {
                    await store.dispatch(FETCH_PROSPECT_ORDERS);

                    if (this.prospectOrders.length == 0) {
                        hcConfirm(
                            "Voulez-vous créer un devis pour ce prospect ?",
                            this.addOrder
                        );
                    }
                } finally {
                    this.fetchingOrders = false;
                }
            } else {
                this.tab = 1;
                this.frameTab = 1;
            }
        },

        /**
         *
         * @param {*} order
         */
        async setOrder(pi) {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            this.orderTab = this.prospectOrderTab;
            store.commit(SET_PROSPECT_ORDER, pi);
            if (pi) {
                this.frameTab = 0;
                await store.dispatch(SHOW_PROSPECT_ORDER, pi.id);
            }
            /*if (!pi) {
                return;
            }
            store.commit(SET_PROSPECT_ORDER, order);*/
        },

        setOrderProspect(prospect) {
            store.commit(SET_PROSPECT, prospect);
            this.tab = 0;
        },
    },

    watch: {
        prospectOrder(newValue, oldValue) {
            if (newValue) {
                this.tab = 1;
                this.frameTab = 0;
                store.dispatch(FETCH_PROSPECT_ORDER_COMMISSIONS);
                // store.dispatch(FETCH_ORDER_STEPS);
            } else {
                this.tab = 0;
            }
        },

        prospect(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                this.fetchOrders();
            }
        },

        async tab(newValue) {
            if (newValue == 1) {
                this.fetchingProducts = true;

                try {
                    await store.dispatch(FETCH_PRODUCTS);
                } finally {
                    this.fetchingProducts = false;
                }
            }
        },

        prospectOrderTab(newValue) {
            this.orderTab = newValue;
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospect",
            "prospectOrders",
            "prospectOrder",
            "prospectFullName",
            "slideOpen",
            "orderStatuses",
            "prospectOrderTab",
            "can",
        ]),

        /**
         *
         */
        filteredOrders() {
            const keyword = removeStringAccent(this.orderKeyword);

            if (keyword.length == 0) {
                return this.prospectOrders;
            }

            return this.prospectOrders.filter(
                (order) =>
                    order.products.filter(
                        (p) => removeStringAccent(p.name).indexOf(keyword) >= 0
                    ).length > 0
            );
        },

        /**
         *
         */
        status() {
            const status = this.orderStatuses.find(
                (status) => status.id == this.prospectOrder.status_id
            );

            return status
                ? status
                : {
                      color: "#333333",
                      bgcolor: "#eeeeee",
                      name: "Modifier l'état",
                  };
        },
    },
};
</script>
