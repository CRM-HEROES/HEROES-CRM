<template>
    <modal name="commission-update" :title="$t('commission.title')">
        <form class="hc-flex-column" @submit.prevent="updateCommission">
            <tab-layout :count="4" :tab="tab" class="hc-flex-1">
                <template #1>
                    <div class="hc-flex-column" style="height: 100%">
                        <item tag="label">
                            <icon class="fa fa-gift" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('commission.tab.product.title')"
                            ></div>
                            <checkbox
                                style="padding-right: 5px"
                                v-model="selectAllProducts"
                            />
                        </item>
                        <search v-model="productKeyword" />
                        <item-list class="hc-flex-1" padding="5px">
                            <product-row
                                v-for="product in filteredProducts"
                                :key="product.id"
                                :product="product"
                                v-model="selectedProducts"
                            />
                        </item-list>
                    </div>
                </template>
                <template #2>
                    <div class="hc-flex-column" style="height: 100%">
                        <item tag="label">
                            <icon class="fa fa-person-digging" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('commission.tab.action.title')"
                            ></div>
                            <checkbox
                                style="padding-right: 5px"
                                v-model="selectAllActions"
                            />
                        </item>
                        <search v-model="actionKeyword" />
                        <item-list class="hc-flex-1" padding="5px">
                            <action-row
                                v-for="action in filteredActions"
                                :key="action.id"
                                :action="action"
                                v-model="selectedActions"
                            />
                        </item-list>
                    </div>
                </template>
                <template #3>
                    <div class="hc-flex-column" style="height: 100%">
                        <item tag="label">
                            <icon class="fa fa-user" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('commission.tab.user.title')"
                            ></div>
                            <checkbox
                                style="padding-right: 5px"
                                v-model="selectAllUsers"
                            />
                        </item>
                        <search v-model="userKeyword" />
                        <item-list class="hc-flex-1" padding="5px">
                            <user-row
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :user="user"
                                v-model="selectedUsers"
                            />
                        </item-list>
                    </div>
                </template>
                <template #4>
                    <item-list padding="12px" class="hc-flex-1">
                        <v-field
                            :label="$t('commission.tab.commission.types.title')"
                            required
                            v-slot="{ label }"
                        >
                            <select v-model="commissionType">
                                <option
                                    value="percentage"
                                    v-text="
                                        $t(
                                            'commission.tab.commission.types.percentage'
                                        )
                                    "
                                ></option>
                                <option
                                    value="fix"
                                    v-text="
                                        $t(
                                            'commission.tab.commission.types.fix'
                                        )
                                    "
                                ></option>
                            </select>
                        </v-field>
                        <v-field
                            :label="
                                $t('commission.tab.commission.commission.title')
                            "
                            required
                            v-slot="{ label }"
                            ><input
                                required
                                type="number"
                                :placeholder="label + ' ...'"
                                v-model="commissionValue"
                        /></v-field>
                        <v-field
                            :label="
                                $t(
                                    'commission.tab.commission.including_tax.title'
                                )
                            "
                            required
                            v-slot="{ label }"
                            v-if="commissionType == 'percentage'"
                        >
                            <select v-model="commissionIncludingTax">
                                <option
                                    value="1"
                                    v-text="
                                        $t(
                                            'commission.tab.commission.including_tax.incl_tax'
                                        )
                                    "
                                ></option>
                                <option
                                    value="0"
                                    v-text="
                                        $t(
                                            'commission.tab.commission.including_tax.excl_tax'
                                        )
                                    "
                                ></option>
                            </select>
                        </v-field>
                        <item>
                            <icon class="fa fa-gift" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    selectedProducts.length +
                                    ' ' +
                                    $t(
                                        'commission.tab.commission.selected_products'
                                    )
                                "
                            ></div>
                        </item>
                        <item>
                            <icon class="fa fa-person-digging" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    selectedActions.length +
                                    ' ' +
                                    $t(
                                        'commission.tab.commission.selected_actions'
                                    )
                                "
                            ></div>
                        </item>
                        <item>
                            <icon class="fa fa-user" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    selectedUsers.length +
                                    ' ' +
                                    $t(
                                        'commission.tab.commission.selected_users'
                                    )
                                "
                            ></div>
                        </item>
                    </item-list>
                </template>
            </tab-layout>
            <buttons>
                <a
                    v-if="tab > 0"
                    @click.prevent="tab--"
                    v-text="$t('previous')"
                ></a>
                <a
                    v-if="tab < 3"
                    @click.prevent="tab++"
                    v-text="$t('next')"
                ></a>
                <button
                    v-if="tab == 3"
                    @click.prevent="removeCommission"
                    class="hc-button-danger"
                    :disabled="
                        selectedUsers.length == 0 ||
                        selectAllActions.length == 0 ||
                        selectAllProducts.length == 0
                    "
                    v-text="$t('delete')"
                ></button>
                <button
                    v-if="tab == 3"
                    :disabled="
                        selectedUsers.length == 0 ||
                        selectAllActions.length == 0 ||
                        selectAllProducts.length == 0
                    "
                    v-text="$t('update')"
                ></button>
            </buttons>
            <loading :loading="updatingCommission || removingCommission" />
        </form>
    </modal>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

import { BULK_UPDATE_COMMISSION } from "@/actions/project/user/commission";
import { CLOSE_MODAL } from "@/actions/modal";

// Components
import ActionRow from "./ActionRow.vue";
import ProductRow from "./ProductRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        ActionRow,
        ProductRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,

            actionKeyword: "",
            userKeyword: "",
            productKeyword: "",

            selectedActions: [],
            selectedUsers: [],
            selectedProducts: [],

            commissionType: "percentage",
            commissionValue: "",
            commissionIncludingTax: 1,

            updatingCommission: false,
            removingCommission: false,
        };
    },

    methods: {
        async updateCommission() {
            this.updatingCommission = true;

            try {
                await store.dispatch(BULK_UPDATE_COMMISSION, {
                    actions: this.selectedActions,
                    users: this.selectedUsers,
                    products: this.selectedProducts,
                    type: this.commissionType,
                    value: this.commissionValue / 100,
                    including_tax: this.commissionIncludingTax,
                    action: "attach",
                });
            } finally {
                this.updatingCommission = false;
                this.tab = 0;
                store.commit(CLOSE_MODAL);
            }
        },

        async removeCommission() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingCommission = true;

                try {
                    await store.dispatch(BULK_UPDATE_COMMISSION, {
                        actions: this.selectedActions,
                        users: this.selectedUsers,
                        products: this.selectedProducts,
                        action: "detach",
                    });
                } finally {
                    this.removingCommission = false;
                    this.tab = 0;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    computed: {
        ...mapGetters(["orderActions", "users", "products"]),

        /**
         *
         */
        selectAllActions: {
            get() {
                return (
                    this.filteredActions.length == this.selectedActions.length
                );
            },
            set(value) {
                this.selectedActions = value
                    ? this.filteredActions.map((action) => action.id)
                    : [];
            },
        },

        /**
         *
         */
        selectAllUsers: {
            get() {
                return this.filteredUsers.length == this.selectedUsers.length;
            },
            set(value) {
                this.selectedUsers = value
                    ? this.filteredUsers.map((user) => user.id)
                    : [];
            },
        },

        /**
         *
         */
        selectAllProducts: {
            get() {
                return (
                    this.filteredProducts.length == this.selectedProducts.length
                );
            },
            set(value) {
                this.selectedProducts = value
                    ? this.filteredProducts.map((product) => product.id)
                    : [];
            },
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
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
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
    },
};
</script>
