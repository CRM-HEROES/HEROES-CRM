<template>
    <tree-layout class="hc-campaign-rules-bloc" :tabulation="5">
        <template #header>
            <item>
                <icon class="fa fa-comment" color="#333333" />
                <div
                    class="hc-item-main-content hc-campaign-rules-bloc-title"
                    v-text="$t('campaign.rule.prospect.sms.title')"
                ></div>
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <v-field :label="$t('campaign.rule.prospect.sms.source')">
                <select v-model="source">
                    <option :value="null" v-text="$t('all')"></option>
                    <option
                        value="telephone"
                        v-text="$t('interaction.types.telephone')"
                    ></option>
                    <option value="smsbox" v-text="'SMSBOX'"></option>
                    <option value="ultramsg" v-text="'UltraMsg'"></option>
                    <option value="mtarget" v-text="'MTarget'"></option>
                </select>
            </v-field>
            <v-field :label="$t('campaign.rule.prospect.sms.count')">
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
                name: "1 sms envoyé",
                rule: {
                    smsCount: {
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
                return this.field.rule.smsCount.sources.length > 0
                    ? this.field.rule.smsCount.sources[0]
                    : null;
            },
            set: async function (value) {
                this.field.rule.smsCount.sources = [value];
            },
        },
        count: {
            get: function () {
                return this.field.rule.smsCount.count;
            },
            set: async function (value) {
                this.field.rule.smsCount.count = value;
            },
        },
        fieldName() {
            return (
                this.count +
                " appel(s)" +
                (this.source ? " via " + this.source : "")
            );
        },
    },
};
</script>
