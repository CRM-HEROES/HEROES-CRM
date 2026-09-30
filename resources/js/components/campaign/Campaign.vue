<template>
    <div id="hc-campaign">
        <div
            v-if="mobile"
            id="hc-campaign-mobile"
            v-text="$t('campaign.mobile')"
        ></div>
        <template v-else>
            <rules
                @dragging="dragNewField"
                @dragged="dropNewField"
                @focus-campaign="(campaign) => focusCampaign(campaign)"
            />

            <div
                id="hc-campaign-view-wrapper"
                ref="campaigns"
                :class="[positionDragging ? 'dragging' : '']"
                :style="{
                    backgroundPosition: `top ${centerY % 20}px left ${
                        centerX % 20
                    }px`,
                }"
                @dblclick="createEmptyRule"
            >
                <div
                    id="hc-campaign-view"
                    :style="{
                        left: centerX + 'px',
                        top: centerY + 'px',
                    }"
                >
                    <rule
                        v-for="rule in campaignRules"
                        :key="rule.id"
                        :rule="rule"
                        @create-campaign="createCampaignFromRule"
                    />
                    <operator
                        v-for="operator in campaignOperators"
                        :key="operator.id"
                        :operator="operator"
                        @mouseover="allowCreateCampaignFromRule = false"
                        @mouseout="allowCreateCampaignFromRule = true"
                    />
                    <campaign
                        v-for="campaign in campaigns"
                        :key="campaign.id"
                        :campaign="campaign"
                        @mouseover="allowCreateCampaignFromRule = false"
                        @mouseout="allowCreateCampaignFromRule = true"
                        @create-action="createActionFromCampaign"
                    />
                    <!--action
                        v-for="action in campaignActions"
                        :key="action.id"
                        :action="action"
                    /-->
                    <campaign-item
                        v-for="action in campaignActions"
                        :key="action.id"
                        :item="action"
                        @mouseover="allowCreateActionFromCampaign = false"
                        @mouseout="allowCreateActionFromCampaign = true"
                    />
                </div>
            </div>

            <actions @dragging="dragNewField" @dragged="dropNewField" />

            <matching-slide />

            <action-prospect-message-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-sms-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-event-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-attach-label-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-detach-label-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-attach-user-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-detach-user-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-attach-group-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-detach-group-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-order-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-document-to-folder-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-prospect-document-to-thread-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />

            <action-order-generate-invoice-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-order-sign-invoice-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
            <action-order-user-action-slide
                @dragging="dragNewField"
                @dragged="dropNewField"
            />

            <!-- New Field -->
            <template v-if="newField">
                <rule v-if="newField.category == 'rule'" :rule="newField" />
                <operator
                    v-else-if="newField.category == 'operator'"
                    :operator="newField"
                />
                <campaign
                    v-else-if="newField.category == 'campaign'"
                    :campaign="newField"
                />
                <action
                    v-else-if="newField.category == 'action'"
                    :action="newField"
                />
            </template>
        </template>
    </div>
</template>

<style>
#hc-campaign {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 100%;
    background-color: white;
    position: relative;
}

#hc-campaign-mobile {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    font-size: 20px;
    padding: 30px;
    text-align: center;
}

#hc-campaign > .hc-movable {
    position: fixed;
}

#hc-campaign-templates {
    width: 240px;
    height: 100%;
    overflow: auto;
    border-right: 1px solid #dddddd;
}

#hc-campaign-view-wrapper {
    flex: 1;
    background-color: #fdfdfe;
    position: relative;
    overflow: auto;
    border-right: 1px solid #dddddd;
    user-select: none;
    overflow: hidden;
    position: relative;
    background-color: #fdfcfe;
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAVCAYAAACpF6WWAAAACXBIWXMAAAsSAAALEgHS3X78AAABLUlEQVQ4jWL8//8/A1UBAwMDAAAA//9iorqJDAwMAAAAAP//or6hDAwMAAAAAP//or6hDAwMAAAAAP//ItrQly9eOBClkIGBAQAAAP//IsWl+4lSxcDAAAAAAP//or73GRgYAAAAAP//or6hDAwMAAAAAP//or6hDAwMAAAAAP//or6hDAwMAAAAAP//wshR0FgmOlIYGBgcxSUkDsB5DAwMAAAAAP//YgAZSgx+8fz5f6LU/v/PAAAAAP//or73GRgYAAAAAP//or6hDAwMAAAAAP//or6hDAwMAAAAAP//or6hDAwMAAAAAP//IsVQR6JUMTAwAAAAAP//on4hzcDAAAAAAP//or73GRgYAAAAAP//or6hDAwMAAAAAP//or6hDAwMAAAAAP//AwC5G1GPf9uAZAAAAABJRU5ErkJggg==);
}

#hc-campaign-view {
    width: 0;
    height: 0;
    position: absolute;
}

#hc-campaign .hc-movable {
    z-index: 10;
}
</style>

<script>
import store from "@/store";
import {
    ADD_CAMPAIGN_RULE,
    REMOVE_CAMPAIGN_RULE,
    ADD_CAMPAIGN_OPERATOR,
    ADD_CAMPAIGN_ACTION,
    REMOVE_CAMPAIGN_ACTION,
    ADD_CAMPAIGN,
    REMOVE_CAMPAIGN,
    ADD_CAMPAIGN_CAMPAIGN_ACTION,
    REMOVE_CAMPAIGN_CAMPAIGN_ACTION,
    ADD_CAMPAIGN_RULE_CAMPAIGN,
    REMOVE_CAMPAIGN_RULE_CAMPAIGN,
} from "@/actions/project/campaign";
import { mapGetters } from "vuex";

import CampaignItem from "./components/Item.vue";
import Rules from "./Rules.vue";
import Action from "./components/Action.vue";
import Campaign from "./components/Campaign.vue";
import Operator from "./components/Operator.vue";
import Rule from "./components/Rule.vue";
import CampaignTemplate from "./components/Template.vue";

import Actions from "./Actions.vue";

import MatchingSlide from "./slides/matching/Slide.vue";

// Prospect actions
import ActionProspectMessageSlide from "./actions/prospect/message/Slide.vue";
import ActionProspectSmsSlide from "./actions/prospect/sms/Slide.vue";
import ActionProspectEventSlide from "./actions/prospect/event/Slide.vue";
import ActionProspectAttachLabelSlide from "./actions/prospect/attach-label/Slide.vue";
import ActionProspectDetachLabelSlide from "./actions/prospect/detach-label/Slide.vue";
import ActionProspectAttachUserSlide from "./actions/prospect/attach-user/Slide.vue";
import ActionProspectDetachUserSlide from "./actions/prospect/detach-user/Slide.vue";
import ActionProspectAttachGroupSlide from "./actions/prospect/attach-group/Slide.vue";
import ActionProspectDetachGroupSlide from "./actions/prospect/detach-group/Slide.vue";
import ActionProspectOrderSlide from "./actions/prospect/order/Slide.vue";
import ActionProspectDocumentToFolderSlide from "./actions/prospect/document-to-folder/Slide.vue";
import ActionProspectDocumentToThreadSlide from "./actions/prospect/document-to-thread/Slide.vue";

// Order actions
import ActionOrderGenerateInvoiceSlide from "./actions/order/generate-invoice/Slide.vue";
import ActionOrderSignInvoiceSlide from "./actions/order/sign-invoice/Slide.vue";
import ActionOrderUserActionSlide from "./actions/order/user-action/Slide.vue";

import { useDrag } from "@/composables/mouse";

export default {
    components: {
        CampaignItem,
        Rules,
        Actions,
        Action,
        Campaign,
        Operator,
        Rule,
        CampaignTemplate,

        MatchingSlide,

        ActionProspectMessageSlide,
        ActionProspectSmsSlide,
        ActionProspectEventSlide,
        ActionProspectAttachLabelSlide,
        ActionProspectDetachLabelSlide,
        ActionProspectAttachUserSlide,
        ActionProspectDetachUserSlide,
        ActionProspectAttachGroupSlide,
        ActionProspectDetachGroupSlide,
        ActionProspectDocumentToFolderSlide,
        ActionProspectDocumentToThreadSlide,

        ActionProspectOrderSlide,
        ActionOrderGenerateInvoiceSlide,
        ActionOrderSignInvoiceSlide,
        ActionOrderUserActionSlide,
    },

    data() {
        return {
            drag: null,
            centerX: 500,
            centerY: 400,
            currentCenterX: 0,
            currentCenterY: 0,

            newField: null,

            // test
            newRuleField: {
                category: "rule",
                name: "RDV en cours",
                type: "filter",
                rule: { withCategory_1: 12 },
                style: { left: 0, top: 0 },
            },
            newOperatorField: {
                category: "operator",
                and: true,
                style: {},
            },
            newActionField: {
                category: "action",
                name: "Envoi email",
                action: "email",
                value: "{}",
                value: "12",
                style: {},
            },
            newCampaignField: {
                category: "campaign",
                name: "Campaign",
                description: "Envoi d'email à chaque nouveau prospect",
                active: false,
                for: "prospect",
                frequency: "once",
                date: "2020-10-15 15:30:00",
                style: {},
            },

            allowCreateCampaignFromRule: true,
            allowCreateActionFromCampaign: true,
        };
    },

    mounted() {
        if (!this.mobile) {
            const campaigns = this.$refs.campaigns;
            this.drag = useDrag(campaigns);
        }
    },

    methods: {
        /**
         *
         * @param {*} x
         * @param {*} y
         */
        setCenter(x, y) {
            this.centerX = x;
            this.centerY = y;
        },

        /**
         *
         * @param {*} campaign
         */
        focusCampaign(campaign) {
            this.setCenter(
                this.$refs.campaigns.offsetWidth / 2 -
                    parseInt(campaign.style.left) -
                    parseInt(campaign.style.width) / 2,
                this.$refs.campaigns.offsetHeight / 2 -
                    parseInt(campaign.style.top) -
                    80
            );
        },

        /**
         *
         * @param {*} field
         */
        dragNewField(field) {
            this.newField = field;
        },

        /**
         * Field to drop new position
         */
        fieldToDropPosition(field, x, y) {
            // Recalculate field position
            // relative to the pdf area
            const rect = this.$refs.campaigns.getBoundingClientRect();
            let left = x - rect.x;
            let top = y - rect.y;

            // Do not add field outside of the area
            if (left >= 0 && top >= 0) {
                left = left - this.centerX; // / this.documentZoom;
                top = top - this.centerY; // / this.documentZoom;

                return this.positionField(field, left, top);
            }

            return null;
        },

        /**
         * Drop field in pdf area
         */
        async dropNewField(x, y) {
            if (!this.newField) {
                return;
            }

            const field = this.fieldToDropPosition(this.newField, x, y);
            if (!field) {
                return;
            }

            if (this.newField.category == "rule") {
                await this.addRuleField(field);
            } else if (this.newField.category == "operator") {
                await this.addOperatorField(field);
            } else if (this.newField.category == "action") {
                await this.addActionField(field);
            } else if (this.newField.category == "campaign") {
                await this.addCampaignField(field);
            }

            this.newField = null;
        },

        /**
         *
         * Add rule field in area
         * @param {*} field
         * @param {*} x x position
         * @param {*} y y position
         */
        positionField(field, x, y) {
            return {
                ...field,
                // Random ID
                id: -parseInt(Math.random() * 100000),
                // Position
                style: {
                    ...field.style,
                    left: x + "px",
                    top: y + "px",
                },
            };
        },

        /**
         *
         * Add rule field in area
         * @param {*} field
         * @param {*} x x position
         * @param {*} y y position
         */
        async addRuleField(field) {
            await store.dispatch(ADD_CAMPAIGN_RULE, field);
        },

        /**
         *
         * Add rule field in area
         * @param {*} field
         * @param {*} x x position
         * @param {*} y y position
         */
        async addOperatorField(field) {
            await store.dispatch(ADD_CAMPAIGN_OPERATOR, field);
        },

        /**
         *
         * Add action field in area
         * @param {*} field
         * @param {*} x x position
         * @param {*} y y position
         */
        async addActionField(field) {
            await store.dispatch(ADD_CAMPAIGN_ACTION, field);
        },

        /**
         *
         * Add campaign field in area
         * @param {*} field
         * @param {*} x x position
         * @param {*} y y position
         */
        async addCampaignField(field) {
            await store.dispatch(ADD_CAMPAIGN, field);
        },

        /**
         *
         * @param {*} campaign
         * @param {*} x
         * @param {*} y
         */
        async createField(campaign, x, y) {
            const rect = this.$refs.campaigns.getBoundingClientRect();
            let left = x - rect.x;
            let top = y - rect.y;

            // Do not add field outside of the area
            if (left >= 0 && top >= 0) {
                left = left - this.centerX; // / this.documentZoom;
                top = top - this.centerY; // / this.documentZoom;

                let field = this.positionField(
                    {
                        action: "empty",
                        value: {},
                        style: {
                            width: "300px",
                        },
                    },
                    left,
                    top
                );

                // Fake action and relation
                store.commit(ADD_CAMPAIGN_ACTION, field);
                store.commit(ADD_CAMPAIGN_CAMPAIGN_ACTION, {
                    campaign: campaign,
                    action: field,
                });

                // Real action and relation
                const newField = await store.dispatch(
                    ADD_CAMPAIGN_ACTION,
                    field
                );
                await store.dispatch(ADD_CAMPAIGN_CAMPAIGN_ACTION, {
                    campaign: campaign,
                    action: newField,
                });

                // REmove fake action and relation
                store.commit(REMOVE_CAMPAIGN_ACTION, field.id);
                store.commit(REMOVE_CAMPAIGN_CAMPAIGN_ACTION, {
                    campaign: campaign,
                    action: field,
                });
            }
        },

        /**
         *
         * @param {*} e
         */
        async createEmptyRule(e) {
            let field = this.fieldToDropPosition(
                {
                    rule: {},
                    style: {
                        color: "#000000",
                        "background-color": "#FFFFFF",
                        width: "200px",
                    },
                },
                e.clientX,
                e.clientY
            );

            if (!field) {
                return;
            }

            // Fake action and relation
            store.commit(ADD_CAMPAIGN_RULE, field);

            // Real action and relation
            const newField = await store.dispatch(ADD_CAMPAIGN_RULE, field);

            // Remove fake action and relation
            store.commit(REMOVE_CAMPAIGN_RULE, field.id);
        },

        /**
         *
         * @param {*} campaign
         * @param {*} x
         * @param {*} y
         */
        async createActionFromCampaign(campaign, x, y) {
            if (!this.allowCreateActionFromCampaign) {
                return;
            }

            let field = this.fieldToDropPosition(
                {
                    action: "empty",
                    value: {},
                    style: {
                        width: "300px",
                    },
                },
                x,
                y
            );

            if (!field) {
                return;
            }

            // Fake action and relation
            store.commit(ADD_CAMPAIGN_ACTION, field);
            store.commit(ADD_CAMPAIGN_CAMPAIGN_ACTION, {
                campaign: campaign,
                action: field,
            });

            // Real action and relation
            const newField = await store.dispatch(ADD_CAMPAIGN_ACTION, field);
            await store.dispatch(ADD_CAMPAIGN_CAMPAIGN_ACTION, {
                campaign: campaign,
                action: newField,
            });

            // REmove fake action and relation
            store.commit(REMOVE_CAMPAIGN_ACTION, field.id);
            store.commit(REMOVE_CAMPAIGN_CAMPAIGN_ACTION, {
                campaign: campaign,
                action: field,
            });
        },

        /**
         *
         * @param {*} campaign
         * @param {*} x
         * @param {*} y
         */
        async createCampaignFromRule(rule, x, y) {
            if (!this.allowCreateCampaignFromRule) {
                return;
            }

            let field = this.fieldToDropPosition(
                {
                    name: "Campaign",
                    for: "prospect",
                    frequency: "once",
                    date: dateToString(new Date()),
                    style: {
                        width: "230px",
                    },
                },
                x,
                y
            );

            if (!field) {
                return;
            }

            // Fake campaign and relation
            store.commit(ADD_CAMPAIGN, field);
            store.commit(ADD_CAMPAIGN_RULE_CAMPAIGN, {
                rule: rule,
                campaign: field,
            });

            // Real campaign and relation
            const newField = await store.dispatch(ADD_CAMPAIGN, field);
            await store.dispatch(ADD_CAMPAIGN_RULE_CAMPAIGN, {
                rule: rule,
                campaign: newField,
            });

            // Remove fake campaign and relation
            store.commit(REMOVE_CAMPAIGN, field.id);
            store.commit(REMOVE_CAMPAIGN_RULE_CAMPAIGN, {
                rule: rule,
                campaign: field,
            });
        },
    },

    computed: {
        /**
         */
        positionDragging() {
            return this.drag && this.drag.dragging;
        },

        /**
         */
        dragPosition() {
            if (!this.drag || !this.drag.position) {
                return null;
            }

            return this.drag.position;
        },

        mobile() {
            return deviceType() != "desktop";
        },

        // Store
        ...mapGetters([
            "campaignRules",
            "campaignOperators",
            "campaignActions",
            "campaigns",
        ]),
    },

    watch: {
        /**
         */
        positionDragging(newValue) {
            if (newValue) {
                this.currentCenterX = this.centerX;
                this.currentCenterY = this.centerY;
            }
        },

        /**
         */
        dragPosition(newValue) {
            if (!newValue) {
                return;
            }

            let deltaX = this.drag.position.x - this.drag.origin.x;
            let deltaY = this.drag.position.y - this.drag.origin.y;

            /*
            deltaX /= this.zoom;
            deltaY /= this.zoom;
*/
            this.setCenter(
                this.currentCenterX + deltaX,
                this.currentCenterY + deltaY
            );
        },
    },
};
</script>
