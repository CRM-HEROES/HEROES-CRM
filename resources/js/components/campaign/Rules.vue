<template>
    <div id="hc-campaign-templates">
        <tree-layout
            class="hc-campaign-rules-bloc"
            :tabulation="5"
            :open="true"
        >
            <template #header>
                <item>
                    <icon class="fa fa-bullhorn" color="#333333" />
                    <div
                        class="hc-item-main-content hc-campaign-rules-bloc-title"
                        v-text="$t('campaign.campaign.title')"
                    ></div>
                    <icon class="fa fa-caret-down" />
                </item>
            </template>
            <template #body>
                <campaign-template
                    :field="newCampaign"
                    @dragging="dragging"
                    @dragged="dragged"
                >
                    <item
                        v-tuto="{
                            key: 'project.campaign.campaign.new',
                            name: $t(
                                'tutorial.project_campaign_campaign_new.name'
                            ),
                            body:
                                $t(
                                    'tutorial.project_campaign_campaign_new.body.0'
                                ) +
                                '<br><img style=&quot;width: 100%;margin-top: 10px;border-radius: 5px;&quot; src=&quot;/images/tutorial/campaign.campaign.new.gif&quot; />',
                        }"
                    >
                        <icon class="fa fa-plus" icon-size="11" :size="30" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('campaign.campaign.new')"
                        ></div>
                        <icon
                            class="fa fa-arrows"
                            icon-size="11"
                            :size="30"
                            color="#CCCCCC"
                        />
                    </item>
                </campaign-template>
                <campaign-row
                    v-for="campaign in campaigns"
                    :key="campaign.id"
                    :campaign="campaign"
                    @click="focusCampaign(campaign)"
                />
            </template>
        </tree-layout>

        <tree-layout
            class="hc-campaign-rules-bloc"
            :tabulation="5"
            :open="true"
            v-tuto="{
                key: 'project.campaign.operators',
                name: $t('tutorial.project_campaign_operators.name'),
                body:
                    $t('tutorial.project_campaign_operators.body.0') +
                    '<br><img style=&quot;width: 100%;margin-top: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/campaign.operator.gif&quot; />',
            }"
        >
            <template #header>
                <item>
                    <icon class="fa fa-project-diagram" color="#333333" />
                    <div
                        class="hc-item-main-content hc-campaign-rules-bloc-title"
                        v-text="$t('campaign.operator.title')"
                    ></div>
                    <icon class="fa fa-caret-down" />
                </item>
            </template>
            <template #body>
                <operator-row
                    :operator="{
                        name: $t('campaign.operator.and'),
                        and: true,
                    }"
                    @dragging="dragging"
                    @dragged="dragged"
                />
                <operator-row
                    :operator="{
                        name: $t('campaign.operator.or'),
                        and: false,
                    }"
                    @dragging="dragging"
                    @dragged="dragged"
                />
            </template>
        </tree-layout>

        <tree-layout
            class="hc-campaign-rules-bloc"
            :tabulation="5"
            :open="true"
            v-tuto="{
                key: 'project.campaign.rules',
                name: $t('tutorial.project_campaign_rules.name'),
                body:
                    $t('tutorial.project_campaign_rules.body.0') +
                    '<br><img style=&quot;width: 100%;margin-top: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/campaign.rule.gif&quot; />',
            }"
        >
            <template #header>
                <item>
                    <icon class="fa fa-user" color="#333333" />
                    <div
                        class="hc-item-main-content hc-campaign-rules-bloc-title"
                        v-text="$t('campaign.rule.prospect.title')"
                    ></div>
                    <icon class="fa fa-caret-down" />
                </item>
            </template>
            <template #body>
                <label-tree-layout @dragging="dragging" @dragged="dragged" />
                <menu-tree-layout @dragging="dragging" @dragged="dragged" />
                <sms-tree-layout @dragging="dragging" @dragged="dragged" />
                <interaction-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
            </template>
        </tree-layout>

        <tree-layout
            class="hc-campaign-rules-bloc"
            :tabulation="5"
            :open="false"
        >
            <template #header>
                <item>
                    <icon class="fa fa-shopping-cart" color="#333333" />
                    <div
                        class="hc-item-main-content hc-campaign-rules-bloc-title"
                        v-text="$t('campaign.rule.order.title')"
                    ></div>
                    <icon class="fa fa-caret-down" />
                </item>
            </template>
            <template #body>
                <event-label-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
                <order-action-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
                <order-product-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
                <order-status-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
                <order-step-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
            </template>
        </tree-layout>

        <tree-layout
            class="hc-campaign-rules-bloc"
            :tabulation="5"
            :open="false"
        >
            <template #header>
                <item>
                    <icon class="fa fa-calendar" color="#333333" />
                    <div
                        class="hc-item-main-content hc-campaign-rules-bloc-title"
                        v-text="$t('campaign.rule.appointment.title')"
                    ></div>
                    <icon class="fa fa-caret-down" />
                </item>
            </template>
            <template #body>
                <event-date-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
                <event-label-tree-layout
                    @dragging="dragging"
                    @dragged="dragged"
                />
            </template>
        </tree-layout>
    </div>
</template>

<style>
.hc-campaign-rules-bloc {
    width: 100%;
    border-bottom: 1px solid #e5e5e5;
}

.hc-campaign-rules-bloc-title {
    color: #000000;
    font-weight: 500;
    font-size: 11px;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

import { OPEN_MODAL } from "@/actions/modal";

import CampaignRow from "./rules/CampaignRow.vue";
import OperatorRow from "./rules/OperatorRow.vue";
import CampaignTemplate from "./components/Template.vue";

import LabelTreeLayout from "./rules/prospect/label/TreeLayout.vue";
import MenuTreeLayout from "./rules/prospect/menu/TreeLayout.vue";
import SmsTreeLayout from "./rules/prospect/sms/TreeLayout.vue";
import InteractionTreeLayout from "./rules/prospect/interaction/TreeLayout.vue";

import OrderActionTreeLayout from "./rules/order/action/TreeLayout.vue";
import OrderProductTreeLayout from "./rules/order/product/TreeLayout.vue";
import OrderStatusTreeLayout from "./rules/order/status/TreeLayout.vue";
import OrderStepTreeLayout from "./rules/order/step/TreeLayout.vue";

import EventLabelTreeLayout from "./rules/event/label/TreeLayout.vue";
import EventDateTreeLayout from "./rules/event/date/TreeLayout.vue";

export default {
    components: {
        CampaignRow,
        OperatorRow,
        CampaignTemplate,
        LabelTreeLayout,
        MenuTreeLayout,
        SmsTreeLayout,
        InteractionTreeLayout,

        OrderActionTreeLayout,
        OrderProductTreeLayout,
        OrderStatusTreeLayout,
        OrderStepTreeLayout,

        EventLabelTreeLayout,
        EventDateTreeLayout,
    },

    data() {
        let date = new Date();
        date.setHours(date.getHours() + 1);
        date.setMinutes(0);
        date.setSeconds(0);

        return {
            newCampaign: {
                category: "campaign",
                name: this.$t("campaign.campaign.title"),
                description: "",
                active: false,
                for: "prospect",
                frequency: "once",
                date: dateToString(date),
                style: {
                    width: "200px",
                },
            },
        };
    },

    methods: {
        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
        },

        /**
         *
         */
        createCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        focusCampaign(campaign) {
            this.$emit("focus-campaign", campaign);
        },
    },

    computed: {
        // Store
        ...mapGetters(["menus", "campaigns"]),
    },
};
</script>
