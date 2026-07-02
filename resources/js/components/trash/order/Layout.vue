<template>
    <div class="hc-flex-column" style="height: 100%">
        <div class="hc-flex-column hc-flex-1" style="overflow: hidden">
            <search v-model="keyword" />
            <item-list class="hc-flex-1" padding="5px" style="overflow: auto">
                <item tag="label">
                    <icon class="fa fa-check-square" />
                    <div class="hc-item-main-content" v-text="$t('all')"></div>

                    <checkbox v-model="all" />
                </item>

                <order-action-row
                    v-for="orderAction in orderActions"
                    :key="orderAction.id"
                    :orderAction="orderAction"
                    v-model="selectedOrderActions"
                />

                <order-status-row
                    v-for="orderStatus in orderStatuses"
                    :key="orderStatus.id"
                    :orderAction="orderStatus"
                    v-model="selectedOrderStatuses"
                />

                <order-row
                    v-for="order in orders"
                    :key="order.id"
                    :order="order"
                    v-model="selectedOrders"
                />
                <loading :loading="removing" />
            </item-list>
            <buttons>
                <button
                    @click.prevent="restore"
                    v-text="$t('restore')"
                ></button>
                <button
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
            </buttons>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import TrashService from "@/apis/project/trash";
import OrderActionRow from "./OrderActionRow.vue";
import OrderStatusRow from "./OrderStatusRow.vue";
import OrderRow from "./OrderRow.vue";

export default {
    components: {
        OrderActionRow,
        OrderStatusRow,
        OrderRow,
    },

    data() {
        return {
            keyword: "",
            orderActions: [],
            orderStatuses: [],
            orders: [],
            selectedOrderActions: [],
            selectedOrderStatuses: [],
            selectedOrders: [],
            removing: false,
        };
    },

    mounted() {
        this.fetch();
    },

    methods: {
        async remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                try {
                    this.removing = true;

                    await TrashService.bulkDestroy(this.project.slug, {
                        "order-actions": this.selectedOrderActions,
                        orders: this.selectedOrders,
                    });
                    this.fetch();
                } finally {
                    this.removing = false;
                }
            });
        },

        async restore() {
            try {
                await TrashService.bulkRestore(this.project.slug, {
                    "order-actions": this.selectedOrderActions,
                    orders: this.selectedOrders,
                });
                this.fetch();
            } finally {
            }
        },

        /**
         * Fetch trashed orderActions and orders
         */
        async fetch() {
            this.fetchOrderActions();
            this.fetchOrderStatuses();
            this.fetchOrders();
        },

        /**
         * Fetch trashed orderActions
         */
        async fetchOrderActions() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.orderAction(this.project.slug, {
                params,
            });

            this.orderActions = data.data;
        },

        /**
         * Fetch trashed orderStatuses
         */
        async fetchOrderStatuses() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.orderStatus(this.project.slug, {
                params,
            });

            this.orderActions = data.data;
        },

        /**
         * Fetch trashed orders
         */
        async fetchOrders() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.order(this.project.slug, {
                params,
            });

            this.orders = data.data;
        },
    },

    watch: {
        keyword() {
            this.fetch();
        },
    },

    computed: {
        ...mapGetters(["project"]),

        all: {
            get() {
                return (
                    this.selectedOrderActions.length ==
                        this.orderActions.length &&
                    this.selectedOrderStatuses.length ==
                        this.orderStatuses.length &&
                    this.selectedOrders.length == this.orders.length
                );
            },
            set(value) {
                this.selectedOrderActions = value
                    ? this.orderActions.map((orderAction) => orderAction.id)
                    : [];
                this.selectedOrderStatuses = value
                    ? this.orderStatuses.map((orderStatus) => orderStatus.id)
                    : [];
                this.selectedOrders = value
                    ? this.orders.map((order) => order.id)
                    : [];
            },
        },
    },
};
</script>
