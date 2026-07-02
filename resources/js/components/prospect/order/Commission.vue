<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <div class="hc-flex-row">
                    <div
                        class="hc-order-commission-resume hc-flex-row hc-flex-1"
                    >
                        <div
                            class="hc-order-commission-label"
                            v-text="$t('prospect.order.commission.total')"
                        ></div>
                        <div
                            class="hc-order-commission-value"
                            v-text="commissionTotal"
                        ></div>
                    </div>
                    <div
                        class="hc-order-commission-resume hc-flex-row hc-flex-1"
                    >
                        <div
                            class="hc-order-commission-label"
                            v-text="$t('prospect.order.commission.remain')"
                        ></div>
                        <div
                            class="hc-order-commission-value"
                            v-text="commissionLeft"
                        ></div>
                    </div>
                    <div
                        class="hc-order-commission-resume hc-flex-row hc-flex-1"
                    >
                        <div
                            class="hc-order-commission-label"
                            v-text="$t('prospect.order.commission.paid')"
                        ></div>
                        <div
                            class="hc-order-commission-value"
                            style="color: #7939b8"
                        >
                            <i
                                class="fa fa-check"
                                style="margin-right: 3px"
                            ></i>
                            <span v-text="commissionPaid"></span>
                        </div>
                    </div>
                </div>
                <search v-model="actionKeyword" />
                <div
                    padding="12px"
                    class="hc-flex-1"
                    style="overflow: auto; padding: 10px"
                >
                    <action-row
                        v-for="action in filteredActions"
                        :key="action.id"
                        :action="action"
                        :user="getActionUser(action)"
                        :paid="getActionPaid(action)"
                        @set-user="actionByUser = action"
                        @edit-commission="editCommission"
                    />
                </div>
                <buttons>
                    <a
                        @click.prevent="addAction"
                        v-if="can('all.project.order.action.add')"
                        v-text="$t('add')"
                    ></a>
                    <a
                        v-if="can('all.user.commission.add')"
                        @click.prevent="manageCommissions"
                        v-text="$t('prospect.order.tabs.commissions.manage')"
                    ></a>
                </buttons>
            </div>
        </template>
        <template #2>
            <frame-layout :count="2" :tab="commissionTab">
                <template #1>
                    <div
                        class="hc-flex-column"
                        style="height: 100%"
                        v-if="actionByUser"
                    >
                        <item @click="actionByUser = null" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    'Utilisateur pour &quot;' +
                                    actionByUser.name +
                                    '&quot;'
                                "
                            ></div>
                        </item>
                        <search v-model="userKeyword" />
                        <item-list padding="12px" class="hc-flex-1">
                            <item tag="a" @click.prevent="removeActionUser()">
                                <icon class="fa fa-times icon-red" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('none')"
                                ></div>
                            </item>
                            <user-row
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :user="user"
                                @click="setActionUser(user)"
                        /></item-list>
                    </div>
                </template>
                <template #2>
                    <form
                        class="hc-flex-column"
                        style="height: 100%"
                        v-if="commission"
                        @submit.prevent="updateCommission"
                    >
                        <item
                            @click="(commission = null), (tab = 0)"
                            style="border-bottom: 1px solid #eee"
                        >
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    commission.action.name +
                                    ' > ' +
                                    commission.user.name +
                                    ' > ' +
                                    commission.product.name
                                "
                            ></div>
                        </item>
                        <item-list padding="12px" class="hc-flex-1">
                            <h-field
                                :label="$t('type')"
                                required
                                v-slot="{ label }"
                            >
                                <select v-model="commission.type">
                                    <option
                                        value="percentage"
                                        v-text="
                                            $t(
                                                'prospect.order.commission.in_percent'
                                            )
                                        "
                                    ></option>
                                    <option
                                        value="fix"
                                        v-text="
                                            'Fixe en ' +
                                            commission.product.currency
                                        "
                                    ></option>
                                </select>
                            </h-field>
                            <h-field
                                label="Commission"
                                required
                                v-slot="{ label }"
                                ><input
                                    required
                                    type="text"
                                    :placeholder="label + ' ...'"
                                    v-model="value"
                            /></h-field>
                            <h-field
                                label="Calculer à partir du prix du produit en "
                                required
                                v-slot="{ label }"
                                v-if="commission.type == 'percentage'"
                            >
                                <select v-model="commission.including_tax">
                                    <option
                                        value="1"
                                        v-text="
                                            $t(
                                                'prospect.order.commission.tax_included'
                                            )
                                        "
                                    ></option>
                                    <option
                                        value="0"
                                        v-text="
                                            $t(
                                                'prospect.order.commission.tax_excluded'
                                            )
                                        "
                                    ></option>
                                </select>
                            </h-field>
                        </item-list>
                        <buttons v-if="can('all.user.commission.add')">
                            <button
                                class="hc-button-danger"
                                v-text="$t('delete')"
                                @click.prevent="removeCommission"
                            ></button>
                            <button v-text="$t('update')"></button>
                        </buttons>
                        <loading :loading="updatingCommission" />
                    </form>
                </template>
            </frame-layout>
        </template>
    </tab-layout>
</template>

<style>
.hc-order-commission-resume {
    justify-content: space-between;
    align-items: center;
    height: 30px;
    border-bottom: 1px solid #eee;
    border-right: 1px solid #eee;
}

.hc-order-commission-resume:last-child {
    border-right: none;
}

.hc-order-commission-label {
    padding: 0 5px 0 7px;
    color: #777777;
}

.hc-order-commission-value {
    padding: 0 7px 0 5px;
    font-weight: bold;
}

.dark .hc-order-commission-resume {
    border-bottom-color: #444444;
    border-right-color: #444444;
}

.dark .hc-order-commission-label {
    color: #bbbbbb;
}

.dark .hc-order-commission-value {
    color: #cccccc;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import CommissionService from "@/apis/project/user/commission";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import {
    ADD_PROSPECT_ORDER_ACTION,
    REMOVE_PROSPECT_ORDER_ACTION,
} from "@/actions/project/prospect/order/action";
import {
    UPDATE_COMMISSION,
    REMOVE_COMMISSION,
} from "@/actions/project/user/commission";
import { FETCH_PROSPECT_ORDER_COMMISSIONS } from "@/actions/project/prospect/order/commission";

// Components
import ActionRow from "./Commission/ActionRow.vue";
import UserRow from "./Commission/UserRow.vue";

export default {
    components: {
        ActionRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            commissionTab: 0,
            actionKeyword: "",
            userKeyword: "",
            actionByUser: null,
            commission: null,
            updatingCommission: false,
        };
    },

    methods: {
        /**
         *
         */
        addAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },

        /**
         *
         * @param {*} user
         */
        getActionUser(action) {
            if (!this.prospectOrder.actions) {
                return null;
            }

            const ac = this.prospectOrder.actions.find(
                (a) => a.id == action.id
            );

            if (!ac || !ac.pivot || !ac.pivot.user) {
                return null;
            }

            return ac.pivot.user;
        },

        /**
         *
         * @param {*} user
         */
        getActionPaid(action) {
            if (!this.prospectOrder.actions) {
                return false;
            }

            const ac = this.prospectOrder.actions.find(
                (a) => a.id == action.id
            );

            if (!ac || !ac.pivot || !ac.pivot.paid) {
                return false;
            }

            return ac.pivot.paid;
        },

        /**
         *
         * @param {*} user
         */
        async setActionUser(user) {
            try {
                await store.dispatch(ADD_PROSPECT_ORDER_ACTION, {
                    action: this.actionByUser,
                    user: user,
                    params: {},
                });

                const action = this.actionByUser;
                this.actionByUser = null;

                // Check if user has commission
                // on the first product of the order
                if (this.prospectOrder.products.length > 0) {
                    const { data } = await CommissionService.get(
                        this.project.slug,
                        {
                            params: {
                                filters: JSON.stringify({
                                    actions: [action.id],
                                    products: [
                                        this.prospectOrder.products[0].id,
                                    ],
                                    users: [user.id],
                                }),
                            },
                        }
                    );

                    // If not, define the commission
                    if (data.length == 0) {
                        this.editCommission(
                            user,
                            action,
                            this.prospectOrder.products[0]
                        );
                    }
                }
            } finally {
            }
        },

        /**
         *
         * @param {*} user
         */
        removeActionUser() {
            store.dispatch(REMOVE_PROSPECT_ORDER_ACTION, this.actionByUser.id);

            this.actionByUser = null;
        },

        /**
         *
         */
        manageCommissions() {
            store.commit(OPEN_MODAL, "commission-update");
        },

        /**
         *
         */
        newCommission(user, action, product) {
            return {
                user,
                action,
                product,
                type: "percentage",
                value: "",
                including_tax: 1,
            };
        },

        /**
         *
         * @param {*} user
         * @param {*} action
         * @param {*} product
         */
        editCommission(user, action, product) {
            this.commission = this.newCommission(user, action, product);

            const c = this.prospectOrderCommissions.find(
                (poc) =>
                    poc.user_id == user.id &&
                    poc.action_id == action.id &&
                    poc.product_id == product.id
            );

            if (c != undefined) {
                this.commission = {
                    ...this.commission,
                    ...c,
                };
            }
        },

        /**
         *
         */
        async updateCommission() {
            this.updatingCommission = true;

            try {
                await store.dispatch(UPDATE_COMMISSION, {
                    user: this.commission.user,
                    action: this.commission.action,
                    product: this.commission.product,
                    params: {
                        type: this.commission.type,
                        value: this.commission.value,
                        including_tax: this.commission.including_tax,
                    },
                });
                store.dispatch(FETCH_PROSPECT_ORDER_COMMISSIONS);
            } finally {
                this.updatingCommission = false;
                this.commission = null;
            }
        },

        /**
         *
         */
        async removeCommission() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.updatingCommission = true;

                try {
                    await store.dispatch(REMOVE_COMMISSION, {
                        user: this.commission.user,
                        action: this.commission.action,
                        product: this.commission.product,
                    });
                    store.dispatch(FETCH_PROSPECT_ORDER_COMMISSIONS);
                } finally {
                    this.updatingCommission = false;
                    this.commission = null;
                }
            });
        },
    },

    watch: {
        actionByUser(newValue) {
            if (newValue) {
                this.tab = 1;
                this.commissionTab = 0;
            } else {
                this.tab = 0;
            }
        },
        commission(newValue) {
            if (newValue) {
                this.tab = 1;
                this.commissionTab = 1;
            } else {
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "orderActions",
            "users",
            "prospectOrder",
            "prospectOrderCommissions",
            "can",
        ]),

        commissionTotal() {
            return (
                Math.round(
                    this.commissions.reduce(
                        (value, action) => value + action.value,
                        0
                    ) * 100
                ) /
                    100 +
                this.prospectOrder.currency
            );
        },

        commissionPaid() {
            return (
                Math.round(
                    this.commissions
                        .filter((commission) => commission.action.pivot.paid)
                        .reduce((value, action) => value + action.value, 0) *
                        100
                ) /
                    100 +
                this.prospectOrder.currency
            );
        },

        commissionLeft() {
            return (
                Math.round(
                    this.commissions
                        .filter((commission) => !commission.action.pivot.paid)
                        .reduce((value, action) => value + action.value, 0) *
                        100
                ) /
                    100 +
                this.prospectOrder.currency
            );
        },

        commissions() {
            if (!this.prospectOrder.actions) {
                return [];
            }

            return this.prospectOrder.actions.map((action) => {
                return {
                    action: action,
                    value: this.prospectOrder.products.reduce(
                        (value, product) => {
                            const commission =
                                this.prospectOrderCommissions.find(
                                    (c) =>
                                        c.product_id == product.id &&
                                        c.user_id == action.pivot.user_id &&
                                        c.action_id == action.id
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
                        },
                        0
                    ),
                };
            });
        },

        /**
         *
         */
        filteredActions() {
            const keyword = removeStringAccent(this.actionKeyword);

            return this.orderActions.filter(
                (action) =>
                    removeStringAccent(action.name).indexOf(keyword) >= 0
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

        /**
         *
         */
        value: {
            get() {
                return this.commission.type == "percentage"
                    ? this.commission.value * 100
                    : this.commission.value;
            },
            set(value) {
                this.commission.value =
                    this.commission.type == "percentage"
                        ? parseFloat(value) / 100
                        : value;
            },
        },
    },
};
</script>
