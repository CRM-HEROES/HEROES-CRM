<template>
    <movable
        :classes="[
            'hc-campaign-rule',
            isNew ? 'is-new' : '',
            selected ? 'selected' : '',
            relating ? 'relating' : '',
            removing ? 'removing' : '',
        ]"
        :style="rule.style"
        :zoom="campaignZoom"
        :rotatable="false"
        @change="update"
        @changing="change"
    >
        <!--
            Field content
        -->
        <div
            v-if="Object.keys(rule.rule).length == 0"
            class="hc-campaign-rule-search"
            @change.stop
        >
            <input
                ref="search"
                :placeholder="$t('search')"
                v-model="keyword"
                @focus="open = true"
                @blur="close"
                @mousedown.stop
            />
            <item-list v-if="hasResults" class="hc-campaign-rule-search-result">
                <available-rule
                    v-for="(rule, i) in filteredRules"
                    :key="rule.type + '-' + rule.id"
                    :id="
                        'hc-campaign-rule-search-result-' +
                        rule.type +
                        '-' +
                        rule.id
                    "
                    :rule="rule"
                    @click.stop.prevent="setRule(rule)"
                />
            </item-list>
        </div>
        <div
            v-else="rule.rule"
            class="hc-campaign-rule-content"
            :style="contentStyle"
            ref="content"
        >
            <span v-text="rule.name"></span>
        </div>

        <!--
            
        -->
        <div class="hc-campaign-relation-arrows">
            <relation
                :width="relationStyle.width"
                :height="relationStyle.height"
                v-if="relating"
            ></relation>
            <rule-operator-relation
                v-for="operator in rule.operators"
                :child="rule"
                :parent="getOperator(operator)"
                @mousedown.stop="removeOperator(operator)"
                :error="operator.pivot ? operator.pivot.error : false"
            ></rule-operator-relation>
            <rule-campaign-relation
                v-for="campaign in rule.campaigns"
                :child="rule"
                :parent="getCampaign(campaign)"
                @mousedown.stop="removeCampaign(campaign)"
                :error="campaign.pivot ? campaign.pivot.error : false"
            ></rule-campaign-relation>
        </div>

        <!--
            
        -->
        <div class="hc-campaign-relation" ref="relation"></div>

        <!--
            Field remove
        -->
        <div class="hc-campaign-rule-remove" @mousedown.stop="remove">
            &times;
        </div>
    </movable>
</template>

<style>
.hc-campaign-rule {
    outline: 1px dashed #7939b8;
    background-color: white;
    font-size: 11px;
}

.hc-campaign-rule-content {
    padding: 5px;
}

.hc-campaign-rule.selected {
    outline: 2px solid #7939b8;
}

.hc-campaign-rule.removing {
    transition: transform ease-out 70ms, opacity ease-out 70ms;
    transform: scale(0.85);
    opacity: 0;
}

.hc-campaign-rule.is-new {
    box-shadow: 0 4px 8px #0005;
    transform: scale(1.005);
    z-index: 1000;
}

.hc-campaign-rule.is-new {
    position: fixed;
}

.hc-campaign-rule:hover {
    outline: 2px solid #7939b8;
}

.hc-campaign-rule-remove {
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

.hc-campaign-rule:hover > .hc-campaign-rule-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-campaign-rule.is-new .hc-movable-h-resize,
.hc-campaign-rule.is-new .hc-movable-w-resize,
.hc-campaign-rule.is-new .hc-movable-wh-resize,
.hc-campaign-rule.is-new .hc-movable-rotate,
.hc-campaign-rule.is-new .hc-movable-remove {
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

.hc-campaign-relation-arrows {
    position: absolute;
    top: 15px;
    right: 0;
}

.hc-campaign-relation:hover {
    background-color: #7939b8;
    border: 1px solid white;
}

.hc-campaign-rule-search {
    width: 100%;
    height: 30px;
    position: relative;
}

.hc-campaign-rule-search > input {
    width: 100%;
    height: 100%;
    border: none;
    padding: 0 15px;
}

.hc-campaign-rule-search-result {
    min-width: 100%;
    width: auto;
    max-width: 300px;
    position: absolute;
    height: auto;
    top: 30px;
    border: 1px solid #ddd;
    background-color: #fff;
    max-height: 288px;
}

.hc-campaign-rule-search-result > .selected {
    background-color: #12a0f3;
}

.hc-campaign-rule-search-result > .selected * {
    color: white !important;
}
</style>

<script>
import { mapGetters } from "vuex";
import Movable from "@/components/Movable.vue";
import AvailableRule from "./AvailableRule.vue";
import Relation from "@/components/campaign/components/Relation.vue";
import RuleOperatorRelation from "@/components/campaign/components/RuleOperatorRelation.vue";
import RuleCampaignRelation from "@/components/campaign/components/RuleCampaignRelation.vue";

import store from "@/store";
import {
    UPDATE_CAMPAIGN_RULE,
    REMOVE_CAMPAIGN_RULE,
    SET_CAMPAIGN_RULE,
    REMOVE_CAMPAIGN_RULE_OPERATOR,
    REMOVE_CAMPAIGN_RULE_CAMPAIGN,
} from "@/actions/project/campaign";

import { useDrag } from "@/composables/mouse";

export default {
    components: {
        Movable,
        AvailableRule,
        Relation,
        RuleOperatorRelation,
        RuleCampaignRelation,
    },

    props: {
        /**
         *
         */
        rule: {
            type: Object,
        },

        /**
         * Is rule selected
         */
        selected: {
            type: Boolean,
            default: false,
        },

        /**
         * Is rule selecting
         */
        isNew: {
            type: Boolean,
            default: false,
        },
    },

    mounted() {
        const relation = this.$refs.relation;
        this.drag = useDrag(relation);

        if (this.$refs.content) {
            if (!this.rule.style.width) {
                this.rule.style.width = this.$refs.content.offsetWidth + "px";
            }
        }

        if (this.$refs.search) {
            this.$refs.search.focus();
        }
    },

    data() {
        return {
            open: false,
            keyword: "",
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
                store.dispatch(REMOVE_CAMPAIGN_RULE, this.rule.id);
            } finally {
                this.removing = false;
            }
        },

        /**
         *
         */
        selecting(event) {
            this.$emit("selected", event, this.rule);
        },

        /**
         */
        getOperator(operator) {
            return this.campaignOperators.find((o) => o.id == operator.id);
        },

        /**
         */
        async removeOperator(operator) {
            await store.dispatch(REMOVE_CAMPAIGN_RULE_OPERATOR, {
                rule: this.rule,
                operator: operator,
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
            await store.dispatch(REMOVE_CAMPAIGN_RULE_CAMPAIGN, {
                rule: this.rule,
                campaign: campaign,
            });
        },

        /**
         *
         */
        change(style) {
            this.rule.style = {
                ...this.rule.style,
                ...style,
            };
        },

        /**
         *
         * @param {*} style
         */
        update(style) {
            store.dispatch(UPDATE_CAMPAIGN_RULE, {
                id: this.rule.id,
                style,
            });
        },

        /**
         *
         */
        close() {
            setTimeout(() => {
                //this.open = false;
            }, 250);
        },

        setRule(rule) {
            store.dispatch(UPDATE_CAMPAIGN_RULE, {
                id: this.rule.id,
                name: rule.name,
                rule: rule.rule,
                style: {
                    ...this.rule.style,
                    ...rule.style,
                },
            });
        },
    },

    computed: {
        // Store
        ...mapGetters([
            "campaignZoom",
            "campaignOperators",
            "campaigns",
            "categories",
            "menus",
        ]),

        /**
         * Content style
         */
        contentStyle() {
            return Object.fromEntries(
                Object.entries(this.rule.style).filter(
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

        labelRules() {
            return this.categories.reduce(
                (carry, category) => [
                    ...carry,
                    ...category.labels.map((label) => ({
                        id: label.id,
                        name: category.name + " > " + label.name,
                        rule: { withLabels: label.id },
                        icon: "fa fa-tag",
                        style: {
                            color: label.color,
                            "background-color": label.bgcolor,
                        },
                    })),
                ],
                []
            );
        },

        menuRules() {
            return this.menus.map((menu) => ({
                id: menu.id,
                name: menu.name,
                rule: menu.filters,
                icon: "fa fa-list",
                style: {
                    color: menu.color,
                    "background-color": menu.bgcolor,
                },
            }));
        },

        rules() {
            return [...this.labelRules, ...this.menuRules].sort((a, b) =>
                a.name > b.name ? 1 : -1
            );
        },

        /**
         *
         */
        hasResults() {
            return this.open && this.filteredRules.length > 0;
        },

        /**
         *
         */
        filteredRules() {
            const keyword = removeStringAccent(this.keyword);

            return this.rules
                .filter(
                    (rule) =>
                        removeStringAccent(rule.name).indexOf(keyword) >= 0
                )
                .slice(0, 20);
        },
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

                store.commit(SET_CAMPAIGN_RULE, this.rule);
            } else {
                if (this.drag.position) {
                    this.$emit(
                        "create-campaign",
                        this.rule,
                        this.drag.position.x,
                        this.drag.position.y
                    );
                }
                this.relating = false;
                store.commit(SET_CAMPAIGN_RULE, null);
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
