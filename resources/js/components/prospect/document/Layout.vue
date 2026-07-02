<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="documentKeyword" />
                <div
                    class="hc-flex-1"
                    style="
                        padding: 10px 10px;
                        float: left;
                        width: 100%;
                        overflow: auto;
                    "
                >
                    <prospect-document-row
                        v-for="document in filteredProspectDocuments"
                        :key="document.id"
                        :document="document"
                    />
                    <order-document-row
                        v-for="document in filteredOrderDocuments"
                        :key="document.id"
                        :document="document"
                        @click.prevent="(orderDocument = document), (tab = 1)"
                    />
                </div>
                <buttons v-if="can('all.project.document.add')">
                    <a @click.prevent="addDocument" v-text="$t('add')"></a>
                </buttons>
            </div>
        </template>
        <template #2 v-if="orderDocument">
            <div class="hc-flex-column" style="height: 100%">
                <item @click="tab = 0" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="orderDocument.name"
                    ></div>
                </item>
                <search v-model="orderKeyword" />
                <div
                    class="hc-flex-1"
                    style="padding: 10px 10px; float: left; width: 100%"
                >
                    <order-row
                        v-for="order in filteredOrders"
                        :key="order.id"
                        :order="order"
                        :document="orderDocument"
                    />
                    <buttons v-if="can('all.prospect.order.add')">
                        <a
                            @click.prevent="addOrder"
                            v-text="$t('new_order')"
                        ></a>
                    </buttons>
                </div>
                <loading :loading="fetchingOrders" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_PROSPECT_ORDER,
    SET_PROSPECT_ORDER,
    FETCH_PROSPECT_ORDERS,
} from "@/actions/project/prospect/order";
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SUB_SLIDE } from "@/actions/slide";

// Components
import ProspectDocumentRow from "./ProspectDocumentRow.vue";
import OrderDocumentRow from "./ProspectDocumentRow.vue";
import OrderRow from "./OrderRow.vue";

export default {
    components: {
        ProspectDocumentRow,
        OrderDocumentRow,
        OrderRow,
    },

    data() {
        return {
            name: "prospect-manage-documents",
            tab: 0,
            documentKeyword: "",
            orderKeyword: "",
            fetchingOrders: false,
            orderDocument: null,
            addingOrder: false,
        };
    },

    methods: {
        /**
         *
         */
        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },

        /**
         *
         */
        async fetchOrders() {
            this.fetchingOrders = true;

            try {
                await store.dispatch(FETCH_PROSPECT_ORDERS);
            } finally {
                this.fetchingOrders = false;
            }
        },

        async addOrder() {
            this.addingOrder = true;

            try {
                const order = await store.dispatch(ADD_PROSPECT_ORDER, {});
                store.commit(OPEN_SUB_SLIDE, "prospect-manage-orders");
                store.commit(SET_PROSPECT_ORDER, order);
            } finally {
                this.addingOrder = false;
            }
        },
    },

    watch: {
        prospect(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                this.fetchOrders();
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "documents",
            "prospect",
            "prospectFullName",
            "prospectOrders",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        filteredProspectDocuments() {
            const keyword = removeStringAccent(this.documentKeyword);

            return this.documents.filter(
                (document) =>
                    document.for == "prospect" &&
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredOrderDocuments() {
            const keyword = removeStringAccent(this.documentKeyword);

            return this.documents.filter(
                (document) =>
                    (document.for == "order" || document.for == "invoice") &&
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredOrders() {
            const keyword = removeStringAccent(this.orderKeyword);

            return this.prospectOrders.filter(
                (order) => removeStringAccent(order.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
