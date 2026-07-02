<template>
    <movable
        :classes="[
            'hc-campaign-operator',
            isNew ? 'is-new' : '',
            selected ? 'selected' : '',
            relating ? 'relating' : '',
            removing ? 'removing' : '',
            operator.and ? 'and' : 'or',
        ]"
        :style="operator.style"
        :zoom="campaignZoom"
        :w-resizable="false"
        :h-resizable="false"
        :wh-resizable="false"
        :rotatable="false"
        @change="update"
        @changing="change"
    >
        <!--
            
        -->
        <div class="hc-campaign-operator-relation-arrows">
            <relation
                :width="relationStyle.width"
                :height="relationStyle.height"
                v-if="relating"
            ></relation>
            <operator-operator-relation
                v-for="op in operator.parent_operators"
                :child="operator"
                :parent="getOperator(op)"
                @mousedown.stop="removeOperator(op)"
            ></operator-operator-relation>
            <operator-campaign-relation
                v-for="campaign in operator.campaigns"
                :child="operator"
                :parent="getCampaign(campaign)"
                @mousedown.stop="removeCampaign(campaign)"
            ></operator-campaign-relation>
        </div>

        <!--
            Field content
        -->
        <div
            class="hc-campaign-operator-content"
            :style="contentStyle"
            @mouseup="addRelation"
        >
            <span
                v-text="
                    operator.and
                        ? $t('campaign.operator.and')
                        : $t('campaign.operator.or')
                "
            ></span>
        </div>

        <!--
            
        -->
        <div class="hc-campaign-operator-relation" ref="relation"></div>

        <!--
            Field remove
        -->
        <div class="hc-campaign-operator-remove" @mousedown.stop="remove">
            &times;
        </div>
    </movable>
</template>

<style>
.hc-campaign-operator {
    border-radius: 50%;
    font-size: 10px;
    font-weight: bold;
}

.hc-campaign-operator-content {
    position: relative;
    width: 30px;
    height: 30px;
    line-height: 30px;
    background-color: #2c84b5;
    border-radius: 50%;
    text-align: center;
    font-weight: bold;
    color: white;
}

.hc-campaign-operator.or > .hc-campaign-operator-content {
    background-color: #49a563;
}

.hc-campaign-operator.selected {
    outline: 2px solid #7939b8;
}

.hc-campaign-operator.removing {
    transition: transform ease-out 70ms, opacity ease-out 70ms;
    transform: scale(0.85);
    opacity: 0;
}

.hc-campaign-operator.is-new {
    box-shadow: 0 4px 8px #0005;
    transform: scale(1.005);
    z-index: 1000;
}

.hc-campaign-operator.is-new {
    position: fixed;
}

.hc-campaign-operator:hover {
    outline: 2px solid #7939b8;
}

.hc-campaign-operator-remove {
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

.hc-campaign-operator:hover > .hc-campaign-operator-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-campaign-operator.is-new .hc-movable-h-resize,
.hc-campaign-operator.is-new .hc-movable-w-resize,
.hc-campaign-operator.is-new .hc-movable-wh-resize,
.hc-campaign-operator.is-new .hc-movable-rotate,
.hc-campaign-operator.is-new .hc-movable-remove {
    display: none;
}

.hc-campaign-operator-relation {
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

.hc-campaign-operator-relation:hover {
    background-color: #7939b8;
    border: 1px solid white;
}

.hc-campaign-operator-relation-arrows {
    position: absolute;
    top: 15px;
    left: 30px;
}
</style>

<script>
import Movable from "@/components/Movable.vue";
import Relation from "@/components/campaign/components/Relation.vue";
import OperatorOperatorRelation from "@/components/campaign/components/OperatorOperatorRelation.vue";
import OperatorCampaignRelation from "@/components/campaign/components/OperatorCampaignRelation.vue";

import store from "@/store";
import {
    UPDATE_CAMPAIGN_OPERATOR,
    REMOVE_CAMPAIGN_OPERATOR,
    SET_CAMPAIGN_OPERATOR,
    ADD_CAMPAIGN_RULE_OPERATOR,
    ADD_CAMPAIGN_OPERATOR_OPERATOR,
    REMOVE_CAMPAIGN_OPERATOR_OPERATOR,
    REMOVE_CAMPAIGN_OPERATOR_CAMPAIGN,
} from "@/actions/project/campaign";
import { mapGetters } from "vuex";

import { useDrag } from "@/composables/mouse";

export default {
    components: {
        Movable,
        Relation,
        OperatorOperatorRelation,
        OperatorCampaignRelation,
    },

    props: {
        /**
         *
         */
        operator: {
            type: Object,
        },

        /**
         * Is operator selected
         */
        selected: {
            type: Boolean,
            default: false,
        },

        /**
         * Is operator selecting
         */
        isNew: {
            type: Boolean,
            default: false,
        },
    },

    mounted() {
        const relation = this.$refs.relation;
        this.drag = useDrag(relation);
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
         *
         */
        async remove() {
            this.removing = true;

            try {
                await store.dispatch(
                    REMOVE_CAMPAIGN_OPERATOR,
                    this.operator.id
                );
            } finally {
                this.removing = false;
            }
        },

        /**
         *
         */
        selecting(event) {
            this.$emit("selected", event, this.operator);
        },

        /**
         */
        getOperator(operator) {
            return this.campaignOperators.find((o) => o.id == operator.id);
        },

        /**
         */
        async removeOperator(operator) {
            await store.dispatch(REMOVE_CAMPAIGN_OPERATOR_OPERATOR, {
                child: this.operator,
                parent: operator,
            });
        },

        /**
         */
        getCampaign(campaign) {
            return this.campaigns.find((o) => o.id == campaign.id);
        },

        /**
         */
        async removeCampaign(campaign) {
            await store.dispatch(REMOVE_CAMPAIGN_OPERATOR_CAMPAIGN, {
                campaign: campaign,
                operator: this.operator,
            });
        },

        /**
         *
         */
        change(style) {
            this.operator.style = {
                ...this.operator.style,
                ...style,
            };
        },

        /**
         *
         * @param {*} style
         */
        update(style) {
            store.dispatch(UPDATE_CAMPAIGN_OPERATOR, {
                id: this.operator.id,
                style,
            });
        },

        async addRelation() {
            if (this.campaignRule && this.campaignRule.type != "count") {
                await store.dispatch(ADD_CAMPAIGN_RULE_OPERATOR, {
                    rule: this.campaignRule,
                    operator: this.operator,
                });
            } else if (this.campaignOperator) {
                await store.dispatch(ADD_CAMPAIGN_OPERATOR_OPERATOR, {
                    child: this.campaignOperator,
                    parent: this.operator,
                });
            }
        },
    },

    computed: {
        /**
         * Content style
         */
        contentStyle() {
            return Object.fromEntries(
                Object.entries(this.operator.style).filter(
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

        // Store
        ...mapGetters([
            "campaignZoom",
            "campaignRule",
            "campaignOperators",
            "campaignOperator",
            "campaigns",
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

                store.commit(SET_CAMPAIGN_OPERATOR, this.operator);
            } else {
                this.relating = false;
                store.commit(SET_CAMPAIGN_OPERATOR, null);
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
