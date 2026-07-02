<template>
    <slide
        :name="name"
        :title="$t('campaign.action.order.user_action.title')"
        icon="fa fa-user"
        style="width: 300px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <item-list padding="12px" style="height: auto">
                        <item
                            class="hc-campaign-action-message-item"
                            @click="(frameTab = 0), (tab = 1)"
                        >
                            <icon class="fa fa-person-digging" :size="36" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-campaign-action-message-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.order.user_action.action'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-campaign-action-message-item-value"
                                    v-text="
                                        orderAction
                                            ? orderAction.name
                                            : $t(
                                                  'campaign.action.order.user_action.select_action'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <item
                            class="hc-campaign-action-message-item"
                            @click="(frameTab = 1), (tab = 1)"
                        >
                            <icon class="fa fa-envelope" :size="36" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-campaign-action-message-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.order.user_action.user'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-campaign-action-message-item-value"
                                    v-text="
                                        user
                                            ? user.name
                                            : $t(
                                                  'campaign.action.order.user_action.select_user'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                    </item-list>
                    <buttons>
                        <campaign-template
                            tag="button"
                            :field="action"
                            @dragging="dragging"
                            @dragged="dragged"
                            v-text="$t('add')"
                            :disabled="
                                !action.value.orderAction || !action.value.user
                            "
                        ></campaign-template>
                    </buttons>
                </div>
            </template>

            <!-- List of users -->
            <template #2>
                <frame-layout :count="2" :tab="frameTab" class="hc-flex-1">
                    <template #1>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'campaign.action.order.user_action.action'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="orderActionKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <orderAction-row
                                    v-for="c in filteredOrderActions"
                                    :key="c.id"
                                    :orderAction="c"
                                    @click="
                                        (action.value.orderAction = c.id),
                                            (tab = 0)
                                    "
                                />
                            </item-list>
                            <buttons>
                                <a
                                    @click.prevent="addOrderAction"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'campaign.action.order.user_action.user'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="userKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <user-row
                                    v-for="c in filteredUsers"
                                    :key="c.id"
                                    :user="c"
                                    @click="
                                        (action.value.user = c.id), (tab = 0)
                                    "
                                />
                            </item-list>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-campaign-action-message-item {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-campaign-action-message-item-title {
    color: #000000;
}

.hc-campaign-action-message-item-value {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import UserRow from "./UserRow.vue";
import OrderActionRow from "./OrderActionRow.vue";
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        UserRow,
        OrderActionRow,
        CampaignTemplate,
    },

    data() {
        return {
            name: "campaign-action-order-user-action",
            tab: 0,
            frameTab: 0,
            userKeyword: "",
            orderActionKeyword: "",
            action: {
                action: "prospect-message",
                name: this.$t("campaign.action.order.user_action.name"),
                value: {
                    user: null,
                    orderAction: null,
                },
                category: "action",
                style: {
                    width: "200px",
                },
            },
        };
    },

    methods: {
        /**
         *
         */
        addOrderAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },

        /**
         *
         */
        addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },

        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },

    watch: {
        orderAction() {
            if (this.orderAction) {
                this.action.name = this.actionName;
            }
        },

        user() {
            if (this.orderAction) {
                this.action.name = this.actionName;
                this.action.style.color = this.orderAction.color;
                this.action.style.backgroundColor = this.orderAction.bgcolor;
            }
        },
    },

    computed: {
        ...mapGetters(["users", "roles", "orderActions"]),

        allUsers() {
            return [
                {
                    id: "order-creator",
                    name: this.$t(
                        "campaign.action.order.user_action.order_creator"
                    ),
                },
                {
                    id: "prospect-creator",
                    name: this.$t(
                        "campaign.action.order.user_action.prospect_creator"
                    ),
                },
                {
                    id: "prospect-affected-user",
                    name: this.$t(
                        "campaign.action.order.user_action.prospect_affected_user"
                    ),
                },
                ...this.users,
                ...this.roles.map((role) => ({
                    id: "role." + role.id,
                    name: this.$t("campaign.action.order.user_action.role", {
                        role: role.name,
                    }),
                })),
            ];
        },

        user() {
            return this.allUsers.find(
                (user) => user.id === this.action.value.user
            );
        },

        orderAction() {
            return this.orderActions.find(
                (orderAction) =>
                    orderAction.id === this.action.value.orderAction
            );
        },

        actionName() {
            if (!this.orderAction) {
                return;
            }

            if (!this.user) {
                return;
            }

            return this.$t("campaign.action.order.user_action.name", {
                action: this.orderAction.name,
                user: this.user.name,
            });
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.allUsers.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredOrderActions() {
            const keyword = removeStringAccent(this.orderActionKeyword);

            return this.orderActions.filter(
                (orderAction) =>
                    removeStringAccent(orderAction.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
