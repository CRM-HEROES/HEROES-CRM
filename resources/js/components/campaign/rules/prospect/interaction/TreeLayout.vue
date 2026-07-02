<template>
    <tree-layout class="hc-campaign-rules-bloc" :tabulation="5">
        <template #header>
            <item>
                <icon class="fa fa-phone" color="#333333" />
                <div
                    class="hc-item-main-content hc-campaign-rules-bloc-title"
                    v-text="$t('campaign.rule.prospect.interaction.title')"
                ></div>
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <v-field :label="$t('campaign.rule.prospect.interaction.source')">
                <select v-model="source">
                    <option :value="null" v-text="$t('all')"></option>
                    <option
                        value="telephone"
                        v-text="$t('interaction.types.telephone')"
                    ></option>
                    <option value="ringover" v-text="'Ringover'"></option>
                    <option value="aircall" v-text="'Aircall'"></option>
                </select> </v-field
            >interaction
            <v-field :label="$t('campaign.rule.prospect.call.count')">
                <select v-model="count">
                    <option v-for="i in 5" :value="i" v-text="i"></option>
                </select>
            </v-field>
            <buttons>
                <campaign-template
                    tag="button"
                    :field="field"
                    @dragging="dragging"
                    @dragged="dragged"
                    v-text="$t('add')"
                ></campaign-template>
            </buttons>
        </template>
    </tree-layout>
</template>

<script>
// Components
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: {
                name: "Appelé 1 fois",
                rule: {
                    interactionsCount: {
                        count: 1,
                        sources: [],
                    },
                },
                category: "rule",
                type: "count",
                style: {
                    color: "#FFFFFF",
                    backgroundColor: "#00b539",
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
    },

    watch: {
        count() {
            this.field.name = this.fieldName;
        },
        source() {
            this.field.name = this.fieldName;
        },
    },

    computed: {
        source: {
            get: function () {
                return this.field.rule.interactionsCount.sources.length > 0
                    ? this.field.rule.interactionsCount.sources[0]
                    : null;
            },
            set: async function (value) {
                this.field.rule.interactionsCount.sources = [value];
            },
        },
        count: {
            get: function () {
                return this.field.rule.interactionsCount.count;
            },
            set: async function (value) {
                this.field.rule.interactionsCount.count = value;
            },
        },
        fieldName() {
            return (
                "Appelé " +
                this.count +
                " fois" +
                (this.source ? " via " + this.source : "")
            );
        },
    },
};
</script>
