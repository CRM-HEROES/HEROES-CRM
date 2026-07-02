<template>
    <div
        v-tuto="{
            key: 'project.campaign.actions',
            name: $t('tutorial.project_campaign_actions.name'),
            body:
                $t('tutorial.project_campaign_actions.body.0') +
                '<br><img style=&quot;width: 100%;margin: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/campaign.action.gif&quot; />.<br>' +
                $t('tutorial.project_campaign_actions.body.1'),
        }"
        style="overflow: auto"
    >
        <campaign-template
            :field="processedProspectField"
            @dragging="dragging"
            @dragged="dragged"
        >
            <item tag="a">
                <icon class="fa fa-lock" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('campaign.action.prospect.archive')"
                ></div>
                <icon
                    class="fa fa-arrows"
                    icon-size="11"
                    :size="30"
                    color="#CCCCCC"
                />
            </item>
        </campaign-template>
        <campaign-template
            :field="notProcessedProspectField"
            @dragging="dragging"
            @dragged="dragged"
        >
            <item tag="a">
                <icon class="fa fa-unlock" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('campaign.action.prospect.do_not_archive')"
                ></div>
                <icon
                    class="fa fa-arrows"
                    icon-size="11"
                    :size="30"
                    color="#CCCCCC"
                />
            </item>
        </campaign-template>
        <campaign-template
            :field="deleteProspectField"
            @dragging="dragging"
            @dragged="dragged"
        >
            <item tag="a">
                <icon class="fa fa-trash" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('campaign.action.prospect.remove')"
                ></div>
                <icon
                    class="fa fa-arrows"
                    icon-size="11"
                    :size="30"
                    color="#CCCCCC"
                />
            </item>
        </campaign-template>
        <item tag="a" @click.prevent="actionProspectMessage">
            <icon class="fa fa-envelope" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.message.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectSms">
            <icon class="fa fa-comments" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.sms.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectEvent">
            <icon class="fa fa-calendar" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.event.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectAttachLabel">
            <icon class="fa fa-tags" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.attach_label.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectDetachLabel">
            <icon class="fa fa-tags" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.detach_label.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectAttachUser">
            <icon class="fa fa-user" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.attach_user.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectDetachUser">
            <icon class="fa fa-user" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.detach_user.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectAttachGroup">
            <icon class="fa fa-users" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.attach_group.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectDetachGroup">
            <icon class="fa fa-users" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.detach_group.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectOrder">
            <icon class="fa fa-shopping-cart" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.order.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectDocumentToFolder">
            <icon class="fa fa-folder" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.document_to_folder.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionProspectDocumentToThread">
            <icon class="fa fa-envelope" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.prospect.document_to_thread.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionOrderGenerateInvoice">
            <icon class="fa fa-shopping-cart" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.order.generate_invoice.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionOrderSignInvoice">
            <icon class="fa fa-check" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.order.sign_invoice.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
        <item tag="a" @click.prevent="actionOrderUserAction">
            <icon class="fa fa-person-digging" />
            <div
                class="hc-item-main-content"
                v-text="$t('campaign.action.order.user_action.title')"
            ></div>
            <icon class="fa fa-caret-right" />
        </item>
    </div>
</template>

<style>
#hc-campaign-actions {
    width: 240px;
    height: 100%;
    overflow: auto;
    font-size: 12px;
}
</style>

<script>
import store from "@/store";

import { OPEN_SLIDE } from "@/actions/slide";

import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            processedProspectField: {
                action: "prospect-processed",
                name: this.$t("campaign.action.prospect.archive"),
                category: "action",
                value: {},
                style: {
                    backgroundColor: "#fff4e6",
                },
            },
            notProcessedProspectField: {
                action: "prospect-not-processed",
                name: this.$t("campaign.action.prospect.do_not_archive"),
                category: "action",
                value: {},
            },
            deleteProspectField: {
                action: "prospect-delete",
                name: this.$t("campaign.action.prospect.remove"),
                category: "action",
                value: {},
                style: {
                    color: "#FFFFFF",
                    backgroundColor: "#df2900",
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
        actionProspectMessage() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-message");
        },

        /**
         *
         */
        actionProspectSms() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-sms");
        },

        /**
         *
         */
        actionProspectEvent() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-event");
        },

        /**
         *
         */
        actionProspectAttachLabel() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-attach-label");
        },

        /**
         *
         */
        actionProspectDetachLabel() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-detach-label");
        },

        /**
         *
         */
        actionProspectAttachUser() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-attach-user");
        },

        /**
         *
         */
        actionProspectDetachUser() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-detach-user");
        },

        /**
         *
         */
        actionProspectAttachGroup() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-attach-group");
        },

        /**
         *
         */
        actionProspectOrder() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-order");
        },

        /**
         *
         */
        actionProspectDetachGroup() {
            store.commit(OPEN_SLIDE, "campaign-action-prospect-detach-group");
        },

        /**
         *
         */
        actionProspectDocumentToFolder() {
            store.commit(
                OPEN_SLIDE,
                "campaign-action-prospect-document-to-folder"
            );
        },

        /**
         *
         */
        actionProspectDocumentToThread() {
            store.commit(
                OPEN_SLIDE,
                "campaign-action-prospect-document-to-thread"
            );
        },

        /**
         *
         */
        actionOrderGenerateInvoice() {
            store.commit(OPEN_SLIDE, "campaign-action-order-generate-invoice");
        },

        /**
         *
         */
        actionOrderSignInvoice() {
            store.commit(OPEN_SLIDE, "campaign-action-order-sign-invoice");
        },

        /**
         *
         */
        actionOrderUserAction() {
            store.commit(OPEN_SLIDE, "campaign-action-order-user-action");
        },
    },
};
</script>
