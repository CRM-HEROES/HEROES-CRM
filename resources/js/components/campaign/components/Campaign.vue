<template>
    <movable
        :classes="[
            'hc-campaign',
            isNew ? 'is-new' : '',
            selected ? 'selected' : '',
            relating ? 'relating' : '',
            removing ? 'removing' : '',
            campaign.processing ? 'processing' : '',
            campaign.pending ? 'pending' : '',
        ]"
        :style="campaign.style"
        :zoom="campaignZoom"
        :rotatable="false"
        @change="updateStyle"
        @changing="change"
    >
        <!--
            Field content
        -->
        <div
            class="hc-campaign-content"
            :style="contentStyle"
            ref="content"
            @mouseup="addRelation"
        >
            <div class="hc-campaign-name">
                <input v-model="campaign.name" @change.stop="update" />
            </div>
            <div class="hc-campaign-body" @mousedown.stop>
                <item class="hc-campaign-active" tag="label">
                    <div
                        class="hc-flex-1"
                        v-text="$t('campaign.campaign.enabled')"
                    ></div>
                    <checkbox v-model="campaign.active" @change.stop="update" />
                </item>

                <label class="hc-campaign-item-value-row">
                    <div
                        class="hc-campaign-item-value-row-label"
                        v-text="'Pour:'"
                    ></div>
                    <div class="hc-campaign-item-value-row-select">
                        <select
                            class="hc-campaign-frequency"
                            v-model="campaign.for"
                            @change.stop="update"
                        >
                            <option
                                value="prospect"
                                v-text="$t('campaign.campaign.for.prospect')"
                            ></option>
                            <option
                                value="order"
                                v-text="$t('campaign.campaign.for.order')"
                            ></option>
                            <option
                                value="event"
                                v-text="$t('campaign.campaign.for.appointment')"
                            ></option>
                        </select>
                    </div>
                </label>

                <label class="hc-campaign-item-value-row">
                    <div
                        class="hc-campaign-item-value-row-label"
                        v-text="'Fréquence:'"
                    ></div>
                    <div class="hc-campaign-item-value-row-select">
                        <select
                            class="hc-campaign-frequency"
                            v-model="campaign.frequency"
                            @change.stop="update"
                        >
                            <option
                                value="once"
                                v-text="$t('campaign.campaign.frequency.once')"
                            ></option>
                            <option
                                value="every_fifteen_minutes"
                                v-text="
                                    $t(
                                        'campaign.campaign.frequency.every_fifteen_minutes'
                                    )
                                "
                            ></option>
                            <option
                                value="every_thirty_minutes"
                                v-text="
                                    $t(
                                        'campaign.campaign.frequency.every_thirty_minutes'
                                    )
                                "
                            ></option>
                            <option
                                value="hourly"
                                v-text="
                                    $t('campaign.campaign.frequency.hourly')
                                "
                            ></option>
                            <option
                                value="daily"
                                v-text="$t('campaign.campaign.frequency.daily')"
                            ></option>
                            <option
                                value="monthly"
                                v-text="
                                    $t('campaign.campaign.frequency.monthly')
                                "
                            ></option>
                        </select>
                    </div>
                </label>

                <label class="hc-campaign-item-value-row">
                    <div
                        class="hc-campaign-item-value-row-label"
                        v-text="'Date:'"
                    ></div>
                    <div class="hc-campaign-item-value-row-select">
                        <input
                            v-model="date"
                            type="date"
                            @change.stop="update"
                        />
                    </div>
                </label>

                <label class="hc-campaign-item-value-row">
                    <div
                        class="hc-campaign-item-value-row-label"
                        v-text="'Heure:'"
                    ></div>
                    <div class="hc-campaign-item-value-row-select">
                        <select v-model="hour" @change.stop="update">
                            <option
                                value=""
                                v-text="$t('campaign.campaign.hour')"
                            ></option>
                            <option
                                v-for="hour in selectableHours"
                                :value="hour"
                                v-text="hour"
                            ></option>
                        </select>
                    </div>
                </label>

                <item class="hc-campaign-active" tag="label">
                    <div
                        class="hc-flex-1"
                        v-text="
                            $t(
                                'campaign.campaign.re_process_processed_prospects'
                            )
                        "
                    ></div>
                    <checkbox
                        v-model="campaign.re_process_processed_prospects"
                        @change.stop="update"
                    />
                </item>

                <!--item class="hc-campaign-check" tag="label">
                    <icon class="fa fa-check" :size="20" :icon-size="10" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('campaign.campaign.check_rules')"
                    ></div>
                </item>

                <item
                    class="hc-campaign-view-prospects"
                    tag="a"
                    @click.prevent="matching"
                >
                    <icon class="fa fa-eye" :size="20" :icon-size="10" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('campaign.campaign.view_matching_prospects')"
                    ></div>
                </item-->
            </div>
        </div>

        <!--
            
        -->
        <div class="hc-campaign-relation" ref="relation"></div>

        <!--
            
        -->
        <div class="hc-campaign-relation-arrows">
            <relation
                :width="relationStyle.width"
                :height="relationStyle.height"
                v-if="relating"
            ></relation>
            <campaign-action-relation
                v-for="action in campaign.actions"
                :child="campaign"
                :parent="getAction(action)"
                :processing="action.pivot ? action.pivot.processing : false"
                :error="action.pivot ? action.pivot.error : false"
                @mousedown.stop="removeAction(action)"
            ></campaign-action-relation>
        </div>

        <!--
            Field remove
        -->
        <div class="hc-campaign-remove" @mousedown.stop="remove">&times;</div>
    </movable>
</template>

<style>
.hc-campaign {
    outline: 1px solid #7939b8;
    box-shadow: 0 2px 2px #0003;
    background-color: white;
    display: flex;
    flex-direction: column;
    border-radius: 2px;
}

.hc-campaign-content {
}

.hc-campaign.selected {
    outline: 2px solid #7939b8;
}

.hc-campaign.removing {
    transition: transform ease-out 70ms, opacity ease-out 70ms;
    transform: scale(0.85);
    opacity: 0;
}

.hc-campaign.is-new {
    box-shadow: 0 4px 8px #0005;
    transform: scale(1.005);
    z-index: 1000;
}

.hc-campaign.is-new {
    position: fixed;
}

.hc-campaign:hover {
    outline: 2px solid #7939b8;
}

.hc-campaign-remove {
    background-color: #7939b8;
    border-radius: 50%;
    color: white;
    height: 22px;
    font-size: 12px;
    left: -15px;
    line-height: 24px;
    opacity: 0;
    position: absolute;
    text-align: center;
    top: -15px;
    transform: scale(0.7);
    transition: opacity ease-out 100ms, transform ease-out 100ms;
    width: 22px;
}

.hc-campaign:hover > .hc-campaign-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-campaign.is-new .hc-movable-h-resize,
.hc-campaign.is-new .hc-movable-w-resize,
.hc-campaign.is-new .hc-movable-wh-resize,
.hc-campaign.is-new .hc-movable-rotate,
.hc-campaign.is-new .hc-movable-remove {
    display: none;
}

.hc-campaign-relation {
    position: absolute;
    top: 10px;
    cursor: pointer;
    height: 10px;
    right: -5px;
    width: 10px;
    border-radius: 50%;
    background: red;
    z-index: 1;
    background-color: white;
    border: 1px solid #7939b8;
}

.hc-campaign-relation:hover {
    background-color: #7939b8;
    border: 1px solid white;
}

.hc-campaign-relation-arrows {
    position: absolute;
    top: 15px;
    right: 0;
    width: 0;
}

.hc-campaign-name {
    text-align: center;
    font-size: 11px;
    padding: 7px !important;
    background-color: #7939b8;
}

.hc-campaign-name > input {
    background: none;
    border: none;
    text-align: center;
    width: 100%;
    color: white;
}

.hc-campaign-body {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.hc-campaign-body > .hc-item {
    min-height: 23px;
    padding: 3px 7px !important;
    background: none;
    border: none;
    border-bottom: 1px solid #eeeeee;
    font-size: 11px;
    font-weight: 600;
}

.hc-campaign-active > .hc-checkbox {
    margin-right: 0;
}

.hc-campaign-frequency {
    padding: 0 3px !important;
}

.hc-campaign-date > input {
    border: none;
}

.hc-campaign-check,
.hc-campaign-view-prospects {
    padding-left: 0 !important;
    padding-right: 0 !important;
}

@keyframes campaignprocessing {
    from {
        opacity: 0.7;
        width: 0;
        height: 0;
        top: 0;
        left: 0;
    }
    to {
        opacity: 0;
        width: 50px;
        height: 50px;
        top: -25px;
        left: -25px;
    }
}

.processing .hc-campaign-relation-arrows:before,
.pending .hc-campaign-relation-arrows:before {
    content: "";
    display: inline-block;
    position: absolute;
    border-radius: 50%;
    background-color: #49a563;
    animation: campaignprocessing 1s infinite;
}

.pending .hc-campaign-relation-arrows:before {
    background-color: #fdad00;
}

.hc-campaign-item-value-row {
    width: 100%;
    display: flex;
    flex-direction: row;
    position: relative;
    min-height: 30px;
    align-items: center;
    padding: 2px 4px;
}

.hc-campaign-item-value-row-label {
    font-weight: 600;
    flex: 1;
    padding-left: 4px;
    font-size: 11px;
}

.hc-campaign-item-value-row-select {
    position: relative;
    padding-right: 20px;
}

.hc-campaign-item-value-row-select:after {
    border-left: 4px solid transparent;
    border-radius: 2px;
    border-right: 4px solid transparent;
    border-top: 5px solid #888888;
    content: "";
    height: 0;
    pointer-events: none;
    position: absolute;
    right: 7px;
    top: 12px;
    width: 0;
}

.hc-campaign-item-value-row-select > * {
    -webkit-appearance: none;
    color: #0ea0cf;
    border: none;
    padding: 0 8px;
    height: 100%;
    width: 100%;
    overflow: hidden;
    text-align: right;
    max-width: 140px;
    height: 26px;
}

.hc-campaign-item-value-row-select > *:focus {
    text-align: left;
}

.hc-campaign-item-value-row-select > select {
    border-radius: 4px;
}

.hc-campaign-item-value-row-select > select > option {
    color: #000000;
    background-color: white;
}
</style>

<script>
import store from "@/store";

import Movable from "@/components/Movable.vue";
import Relation from "@/components/campaign/components/Relation.vue";
import CampaignActionRelation from "@/components/campaign/components/CampaignActionRelation.vue";

import CampaignService from "@/apis/project/campaign";
import {
    UPDATE_CAMPAIGN,
    SET_CAMPAIGN,
    REMOVE_CAMPAIGN,
    REMOVE_CAMPAIGN_CAMPAIGN_ACTION,
    ADD_CAMPAIGN_RULE_CAMPAIGN,
    ADD_CAMPAIGN_OPERATOR_CAMPAIGN,
} from "@/actions/project/campaign";

import { OPEN_SLIDE } from "@/actions/slide";
import { mapGetters } from "vuex";

import { useDrag } from "@/composables/mouse";

export default {
    components: {
        Movable,
        Relation,
        CampaignActionRelation,
    },

    props: {
        /**
         *
         */
        campaign: {
            type: Object,
        },

        /**
         * Is campaign selected
         */
        selected: {
            type: Boolean,
            default: false,
        },

        /**
         * Is campaign new
         */
        isNew: {
            type: Boolean,
            default: false,
        },
    },

    mounted() {
        const relation = this.$refs.relation;
        this.drag = useDrag(relation);

        if (!this.campaign.style.width) {
            const content = this.$refs.content;
            this.campaign.style.width = content.offsetWidth + "px";
        }
    },

    data() {
        return {
            drag: null,
            relationStyle: {
                width: 0,
                height: 0,
            },

            currentX: 0,
            currentY: 0,

            relating: false,
            removing: false,
        };
    },

    methods: {
        /**
         * Remove current campaign
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removing = true;
                try {
                    await store.dispatch(REMOVE_CAMPAIGN, this.campaign.id);
                } finally {
                    this.removing = false;
                }
            });
        },

        /**
         * Get a given associated action
         */
        getAction(action) {
            return this.campaignActions.find((o) => o.id == action.id);
        },

        /**
         * Remove action
         * from the current campaign
         */
        async removeAction(action) {
            await store.dispatch(REMOVE_CAMPAIGN_CAMPAIGN_ACTION, {
                campaign: this.campaign,
                action: action,
            });
        },

        /**
         * Associate
         * rule or operator
         * to the current campaign
         */
        async addRelation() {
            // Rule
            if (this.campaignRule) {
                await store.dispatch(ADD_CAMPAIGN_RULE_CAMPAIGN, {
                    campaign: this.campaign,
                    rule: this.campaignRule,
                });
                // Operator
            } else if (this.campaignOperator) {
                await store.dispatch(ADD_CAMPAIGN_OPERATOR_CAMPAIGN, {
                    campaign: this.campaign,
                    operator: this.campaignOperator,
                });
            }
        },

        /**
         *
         */
        change(style) {
            this.campaign.style = {
                ...this.campaign.style,
                ...style,
            };
        },

        /**
         * Update campaign
         * infos
         */
        update() {
            store.dispatch(UPDATE_CAMPAIGN, this.campaign);
        },

        /**
         * Update campaign
         * css styles
         */
        updateStyle(style) {
            store.dispatch(UPDATE_CAMPAIGN, {
                id: this.campaign.id,
                style,
            });
        },

        /**
         * Check if all rules
         * are valid
         * for a campaign
         */
        async checkRules() {
            this.checkingRules = true;
            try {
                const { data } = await CampaignService.checkRules(
                    this.project.slug,
                    this.campaign.id
                );
            } finally {
                this.checkingRules = false;
            }

            // TO DO
            // show invalid rules relation
        },

        /**
         * Show matching prospect
         * for a the current campaign
         */
        matching() {
            store.commit(SET_CAMPAIGN, this.campaign);
            store.commit(OPEN_SLIDE, "campaign-matching-prospects");
        },
    },

    computed: {
        /**
         * Content style
         */
        contentStyle() {
            return Object.fromEntries(
                Object.entries(this.campaign.style).filter(
                    ([key]) =>
                        key != "left" &&
                        key != "top" &&
                        key != "width" &&
                        key != "height" &&
                        key != "transform"
                )
            );
        },

        /**
         *
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

        /**
         * Selectable event hours
         */
        selectableHours() {
            let eh = [];

            for (var i = 0; i < 24; ++i) {
                for (var j = 0; j < 4; ++j) {
                    eh.push(
                        (i < 10 ? "0" : "") +
                            i +
                            ":" +
                            (j == 0 ? "0" : "") +
                            j * 15
                    );
                }
            }

            return eh;
        },

        /**
         * Campaign date
         */
        date: {
            get: function () {
                return this.campaign.date
                    ? this.campaign.date.substring(0, 10)
                    : "";
            },
            set: function (value) {
                let hour = this.campaign.date
                    ? this.campaign.date.substring(10)
                    : null;

                this.campaign.date = value + (hour ? hour : " 08:00") + ":00";
            },
        },

        /**
         * Campaign hour
         */
        hour: {
            get: function () {
                return this.campaign.date
                    ? this.campaign.date.substring(11, 16)
                    : "";
            },
            set: function (value) {
                if (!value) {
                    return;
                }

                if (this.campaign.date) {
                    return;
                }

                this.campaign.date =
                    this.campaign.date.substring(0, 11) +
                    value +
                    this.prospectEvent.started_at.substring(16);
            },
        },

        // Store
        ...mapGetters([
            "campaignZoom",
            "campaignOperators",
            "campaignRule",
            "campaignOperator",
            "campaignActions",
        ]),
    },

    watch: {
        /**
         */
        positionDragging(newValue) {
            if (newValue) {
                this.relating = true;
                const rect = this.$refs.relation.getBoundingClientRect();
                this.currentX = rect.left;
                this.currentY = rect.top;

                store.commit(SET_CAMPAIGN, this.campaign);
            } else {
                if (this.drag.position) {
                    this.$emit(
                        "create-action",
                        this.campaign,
                        this.drag.position.x,
                        this.drag.position.y
                    );
                }
                this.relating = false;
                store.commit(SET_CAMPAIGN, null);
            }
        },

        /**
         */
        dragPosition(newValue) {
            if (!newValue) {
                return;
            }

            let deltaX = this.drag.position.x - this.currentX;
            let deltaY = this.drag.position.y - this.currentY;

            this.relationStyle.width = deltaX;
            this.relationStyle.height = deltaY;
        },
    },
};
</script>
