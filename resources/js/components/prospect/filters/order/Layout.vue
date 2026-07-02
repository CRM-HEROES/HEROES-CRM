<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <item-list padding="12px" style="height: 100%; overflow: auto">
                <item tag="label">
                    <icon class="fa fa-filter" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.order.with_orders')"
                    ></div>
                    <icon
                        class="fa fa-thumbs-down"
                        :style="{ color: exclude ? '#DD0000' : '#CCCCCC' }"
                        @click.prevent.stop="exclude = !exclude"
                    />
                    <checkbox
                        v-model="orderParamExists"
                        style="margin-right: 5px"
                    />
                </item>
                <item
                    class="hc-prospect-filter-order-item"
                    @click="(tab = 1), (frameTab = 0)"
                >
                    <icon class="fa fa-gift" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-order-item-title"
                            v-text="
                                $t('prospect.table.filters.order.with_products')
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-order-item-value"
                            v-text="
                                filterProductsName
                                    ? filterProductsName
                                    : $t('none') + ' ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item
                    class="hc-prospect-filter-order-item"
                    @click="(tab = 1), (frameTab = 1)"
                >
                    <icon class="fa fa-person-digging" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-order-item-title"
                            v-text="
                                $t('prospect.table.filters.order.with_actions')
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-order-item-value"
                            v-text="
                                filterActionsName
                                    ? filterActionsName
                                    : $t('none') + ' ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item
                    class="hc-prospect-filter-order-item"
                    @click="(tab = 1), (frameTab = 2)"
                >
                    <icon class="fa fa-step-forward" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-order-item-title"
                            v-text="
                                $t('prospect.table.filters.order.with_steps')
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-order-item-value"
                            v-text="
                                filterStepsName
                                    ? filterStepsName
                                    : $t('none') + ' ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item
                    class="hc-prospect-filter-order-item"
                    @click="(tab = 1), (frameTab = 3)"
                >
                    <icon class="fa fa-step-forward" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-order-item-title"
                            v-text="
                                $t('prospect.table.filters.order.with_statuses')
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-order-item-value"
                            v-text="
                                filterStatusesesName
                                    ? filterStatusesesName
                                    : $t('none') + ' ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item
                    class="hc-prospect-filter-order-item"
                    @click="(tab = 1), (frameTab = 4)"
                >
                    <icon class="fa fa-file-pdf" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-order-item-title"
                            v-text="
                                $t(
                                    'prospect.table.filters.order.with_documents'
                                )
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-order-item-value"
                            v-text="
                                filterDocumentsName
                                    ? filterDocumentsName
                                    : $t('none') + ' ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item
                    class="hc-prospect-filter-order-item"
                    @click="(tab = 1), (frameTab = 5)"
                >
                    <icon class="fa fa-user" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-order-item-title"
                            v-text="
                                $t('prospect.table.filters.order.created_by')
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-order-item-value"
                            v-text="
                                filterCreatorsName
                                    ? filterCreatorsName
                                    : $t('none') + ' ...'
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>
            </item-list>
        </template>

        <template #2>
            <frame-layout :count="6" :tab="frameTab" class="hc-flex-1">
                <template #1>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.order.select_products'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="productKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <product-row
                                v-for="product in filteredProducts"
                                :key="product.id"
                                :product="product"
                                v-model="filterProducts"
                            />
                            <loading :loading="fetchingProducts" />
                        </item-list>
                    </div>
                </template>

                <template #2>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.order.select_actions'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="actionKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <action-row
                                v-for="action in filteredActions"
                                :key="action.id"
                                :action="action"
                                v-model="filterActions"
                            />
                            <loading :loading="fetchingActions" />
                        </item-list>
                    </div>
                </template>

                <template #3>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.order.select_steps'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="stepKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <step-row
                                v-for="step in filteredSteps"
                                :key="step.id"
                                :step="step"
                                v-model="filterSteps"
                            />
                            <loading :loading="fetchingSteps" />
                        </item-list>
                    </div>
                </template>

                <template #4>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.order.select_statuses'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="statusKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <status-row
                                v-for="status in filteredStatuses"
                                :key="status.id"
                                :status="status"
                                v-model="filterStatuses"
                            />
                            <loading :loading="fetchingStatus" />
                        </item-list>
                    </div>
                </template>

                <template #5>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.order.select_documents'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="documentKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <document-row
                                v-for="document in filteredDocuments"
                                :key="document.id"
                                :document="document"
                                v-model="filterDocuments"
                            />
                            <loading :loading="fetchingDocuments" />
                        </item-list>
                    </div>
                </template>

                <template #6>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item
                            @click="tab = 0"
                            style="border-bottom: 1px solid #eee"
                        >
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.order.created_by'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="userKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <user-row
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :user="user"
                                v-model="filterCreators"
                            />
                            <loading :loading="fetchingUsers" />
                        </item-list>
                    </div>
                </template>
            </frame-layout>
        </template>
    </tab-layout>
</template>

<style>
.hc-prospect-filter-order-item {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-prospect-filter-order-item-title {
    color: #000000;
}
.hc-prospect-filter-order-item-value {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}

.hc-prospect-filter-order-item-date {
    width: 90px;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 0 0 0 5px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { FETCH_DOCUMENTS } from "@/actions/project/document";
import { FETCH_ORDER_ACTIONS } from "@/actions/project/order/action";
import { FETCH_ORDER_STEPS } from "@/actions/project/order/step";
import { FETCH_ORDER_STATUSES } from "@/actions/project/order/status";
import { FETCH_PRODUCTS } from "@/actions/project/product";
import { FETCH_USERS } from "@/actions/project/user";
import {
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

// Components
import ActionRow from "./ActionRow.vue";
import DocumentRow from "./DocumentRow.vue";
import ProductRow from "./ProductRow.vue";
import StatusRow from "./StatusRow.vue";
import StepRow from "./StepRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        ActionRow,
        DocumentRow,
        ProductRow,
        StatusRow,
        StepRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            frameTab: 0,

            productKeyword: "",
            actionKeyword: "",
            statusKeyword: "",
            stepKeyword: "",
            documentKeyword: "",
            userKeyword: "",

            exclude: false,

            fetchingProducts: false,
            fetchingActions: false,
            fetchingStatus: false,
            fetchingSteps: false,
            fetchingDocuments: false,
            fetchingUsers: false,
        };
    },

    watch: {
        async tab(newValue) {
            if (newValue == 1) {
                // Products
                if (this.frameTab == 0) {
                    if (this.products.length == 0) {
                        this.fetchingProducts = true;
                    }

                    try {
                        await store.dispatch(FETCH_PRODUCTS);
                    } finally {
                        this.fetchingProducts = false;
                    }
                    // Order actions
                } else if (this.frameTab == 1) {
                    if (this.orderActions.length == 0) {
                        this.fetchingActions = true;
                    }

                    try {
                        await store.dispatch(FETCH_ORDER_ACTIONS);
                    } finally {
                        this.fetchingActions = false;
                    }
                    // Order steps
                } else if (this.frameTab == 2) {
                    if (this.orderSteps.length == 0) {
                        this.fetchingSteps = true;
                    }

                    try {
                        await store.dispatch(FETCH_ORDER_STEPS);
                    } finally {
                        this.fetchingSteps = false;
                    }
                    // Order Status
                } else if (this.frameTab == 3) {
                    if (this.orderStatuses.length == 0) {
                        this.fetchingStatus = true;
                    }

                    try {
                        await store.dispatch(FETCH_ORDER_STATUSES);
                    } finally {
                        this.fetchingStatus = false;
                    }
                    // Documents
                } else if (this.frameTab == 4) {
                    if (this.documents.length == 0) {
                        this.fetchingDocuments = true;
                    }

                    try {
                        await store.dispatch(FETCH_DOCUMENTS);
                    } finally {
                        this.fetchingDocuments = false;
                    }
                    // Creators
                } else if (this.frameTab == 5) {
                    if (this.users.length == 0) {
                        this.fetchingUsers = true;
                    }

                    try {
                        // await store.dispatch(FETCH_USERS);
                    } finally {
                        this.fetchingUsers = false;
                    }
                }
            }
        },

        exclude(value) {
            if (
                this.prospectsParamExists(
                    value ? this.withKeyOrders : this.withoutKeyOrders
                )
            ) {
                this.orderParams = this.prospectsParamValue(
                    value ? this.withKeyOrders : this.withoutKeyOrders
                );
            }
        },
    },

    computed: {
        ...mapGetters([
            "products",
            "orderActions",
            "orderStatuses",
            "orderSteps",
            "documents",
            "users",
            "prospectsParamValue",
            "prospectsParamExists",
        ]),

        /**
         *
         */
        withKeyOrders() {
            return "withOrders";
        },

        /**
         *
         */
        withoutKeyOrders() {
            return "withoutOrders";
        },

        /**
         *
         */
        filterKey() {
            return this.exclude ? this.withoutKeyOrders : this.withKeyOrders;
        },

        /**
         * Extract order params
         * from prospects query params
         *
         * Order params format
         * "products:id1,id2,...;users:id1,id2,...;confirmed:1;done:0;...""
         */
        orderParams: {
            get() {
                // Check if order params exists
                if (!this.prospectsParamExists(this.filterKey)) {
                    return null;
                }

                // Extract order params
                // from string to object
                // {
                //    products: "id1,id2,...",
                //    users: "id1,id2,...",
                //    confirmed: "1",
                //    done: "0",
                // }
                return this.prospectsParamValue(this.filterKey);
            },
            set(value) {
                // Remove order params to
                // prospect params
                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: this.withKeyOrders,
                });
                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: this.withoutKeyOrders,
                });

                if (value !== null) {
                    // Add order params to
                    // prospect params
                    store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.filterKey,
                        value: value,
                    });
                }

                store.dispatch(FETCH_PROSPECTS);
            },
        },

        orderParamExists: {
            get() {
                return this.prospectsParamExists(this.filterKey);
            },
            set(value) {
                this.orderParams = value ? {} : null;
            },
        },

        /**
         *
         */
        filterProducts: {
            get() {
                const orderParams = this.orderParams;

                if (!orderParams || !orderParams.withProducts) {
                    return [];
                }

                return orderParams.withProducts;
            },
            set(value) {
                const orderParams = this.orderParams || {};
                if (value.length > 0) {
                    orderParams.withProducts = value;
                } else if (orderParams.withProducts) {
                    delete orderParams.withProducts;
                }
                this.orderParams =
                    Object.keys(orderParams).length == 0 ? null : orderParams;
            },
        },

        /**
         *
         */
        filterActions: {
            get() {
                const orderParams = this.orderParams;

                if (!orderParams || !orderParams.withActions) {
                    return [];
                }

                return orderParams.withActions;
            },
            set(value) {
                const orderParams = this.orderParams || {};
                if (value.length > 0) {
                    orderParams.withActions = value;
                } else if (orderParams.withActions) {
                    delete orderParams.withActions;
                }
                this.orderParams =
                    Object.keys(orderParams).length == 0 ? null : orderParams;
            },
        },

        /**
         *
         */
        filterSteps: {
            get() {
                const orderParams = this.orderParams;

                if (!orderParams || !orderParams.withSteps) {
                    return [];
                }

                return orderParams.withSteps;
            },
            set(value) {
                const orderParams = this.orderParams || {};
                if (value.length > 0) {
                    orderParams.withSteps = value;
                } else if (orderParams.withSteps) {
                    delete orderParams.withSteps;
                }
                this.orderParams =
                    Object.keys(orderParams).length == 0 ? null : orderParams;
            },
        },

        /**
         *
         */
        filterStatuses: {
            get() {
                const orderParams = this.orderParams;

                if (!orderParams || !orderParams.withStatuses) {
                    return [];
                }

                return orderParams.withStatuses;
            },
            set(value) {
                const orderParams = this.orderParams || {};
                if (value.length > 0) {
                    orderParams.withStatuses = value;
                } else if (orderParams.withStatuses) {
                    delete orderParams.withStatuses;
                }
                this.orderParams =
                    Object.keys(orderParams).length == 0 ? null : orderParams;
            },
        },

        /**
         *
         */
        filterDocuments: {
            get() {
                const orderParams = this.orderParams;

                if (!orderParams || !orderParams.withDocuments) {
                    return [];
                }

                return orderParams.withDocuments;
            },
            set(value) {
                const orderParams = this.orderParams || {};
                if (value.length > 0) {
                    orderParams.withDocuments = value;
                } else if (orderParams.withDocuments) {
                    delete orderParams.withDocuments;
                }
                this.orderParams =
                    Object.keys(orderParams).length == 0 ? null : orderParams;
            },
        },

        /**
         *
         */
        filterCreators: {
            get() {
                const orderParams = this.orderParams;

                if (
                    !orderParams ||
                    !orderParams.withCreators ||
                    !orderParams.withCreators.ids
                ) {
                    return [];
                }

                return orderParams.withCreators.ids;
            },
            set(value) {
                const orderParams = this.orderParams || {};
                if (value.length > 0) {
                    if (!orderParams.withCreators) {
                        orderParams.withCreators = {};
                    }

                    orderParams.withCreators.ids = value;
                } else if (
                    orderParams.withCreators &&
                    orderParams.withCreators.ids
                ) {
                    delete orderParams.withCreators.ids;

                    if (Object.keys(orderParams.withCreators).length == 0) {
                        delete orderParams.withCreators;
                    }
                }
                this.orderParams =
                    Object.keys(orderParams).length == 0 ? null : orderParams;
            },
        },

        /**
         *
         */
        filterProductsName() {
            return this.filterProducts
                .map((fu) => this.products.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterActionsName() {
            return this.filterActions
                .map((fu) => this.orderActions.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterStepsName() {
            return this.filterSteps
                .map((fu) => this.orderSteps.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterStatusesesName() {
            return this.filterStatuses
                .map((fu) => this.orderStatuses.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterDocumentsName() {
            return this.filterDocuments
                .map((fu) => this.documents.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterCreatorsName() {
            return this.filterCreators
                .map((fu) => this.users.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filteredProducts() {
            const keyword = removeStringAccent(this.productKeyword);

            return this.products.filter(
                (product) =>
                    removeStringAccent(product.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredActions() {
            const keyword = removeStringAccent(this.actionKeyword);

            return this.orderActions.filter(
                (orderAction) =>
                    removeStringAccent(orderAction.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredSteps() {
            const keyword = removeStringAccent(this.stepKeyword);

            return this.orderSteps.filter(
                (orderStep) =>
                    removeStringAccent(orderStep.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredStatuses() {
            const keyword = removeStringAccent(this.statusKeyword);

            return this.orderStatuses.filter(
                (orderStatus) =>
                    removeStringAccent(orderStatus.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredDocuments() {
            const keyword = removeStringAccent(this.documentKeyword);

            return this.documents.filter(
                (document) =>
                    document.for == "order" &&
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0
                )
                .sort((user1, user2) =>
                    user1.pivot &&
                    user2.pivot &&
                    user1.pivot.relevance_order_action >
                        user2.pivot.relevance_order_action
                        ? -1
                        : 1
                );
        },
    },
};
</script>
