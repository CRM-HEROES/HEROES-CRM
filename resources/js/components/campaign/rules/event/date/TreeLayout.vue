<template>
    <tree-layout :tabulation="5" class="hc-campaign-rules-bloc">
        <template #header>
            <item>
                <icon class="fa fa-calendar" color="#333333" />
                <div
                    class="hc-item-main-content hc-campaign-rules-bloc-title"
                    v-text="$t('campaign.rule.appointment.date.title')"
                ></div>
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <v-field
                :label="
                    $t(
                        'campaign.rule.appointment.date.starts_in_more_than_mn',
                        {
                            minute: 'X',
                        }
                    )
                "
            >
                <input v-model.lazy="startsInMoreThanMn" type="number" />
            </v-field>
            <v-field
                :label="
                    $t(
                        'campaign.rule.appointment.date.starts_in_less_than_mn',
                        {
                            minute: 'X',
                        }
                    )
                "
            >
                <input v-model="startsInLessThanMn" type="number" />
            </v-field>
            <v-field
                :label="
                    $t(
                        'campaign.rule.appointment.date.started_more_than_ago_mn',
                        {
                            minute: 'X',
                        }
                    )
                "
            >
                <input v-model="startedMoreThanMn" type="number" />
            </v-field>
            <v-field
                :label="
                    $t(
                        'campaign.rule.appointment.date.started_less_than_ago_mn',
                        {
                            minute: 'X',
                        }
                    )
                "
            >
                <input v-model.lazy="startedLessThanMn" type="number" />
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
                name: "Date de RDV",
                rule: {},
                category: "rule",
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
        startsInMoreThanMn() {
            this.field.name = this.fieldName;
        },
        startsInLessThanMn() {
            this.field.name = this.fieldName;
        },
        startedLessThanMn() {
            this.field.name = this.fieldName;
        },
        startedMoreThanMn() {
            this.field.name = this.fieldName;
        },
    },

    computed: {
        startsInMoreThanMn: {
            get: function () {
                if (this.field.rule.startsInMoreThan) {
                    return parseInt(this.field.rule.startsInMoreThan / 60);
                }

                return "";
            },
            set: async function (value) {
                if (!value) {
                    delete this.field.rule.startsInMoreThan;
                    return;
                }

                this.field.rule.startsInMoreThan = value * 60;
            },
        },
        startsInLessThanMn: {
            get: function () {
                if (this.field.rule.startsInLessThan) {
                    return parseInt(this.field.rule.startsInLessThan / 60);
                }

                return "";
            },
            set: async function (value) {
                if (!value) {
                    delete this.field.rule.startsInLessThan;
                    return;
                }

                this.field.rule.startsInLessThan = value * 60;
            },
        },
        startedLessThanMn: {
            get: function () {
                if (this.field.rule.startedLessThan) {
                    return parseInt(this.field.rule.startedLessThan / 60);
                }

                return "";
            },
            set: async function (value) {
                if (!value) {
                    delete this.field.rule.startedLessThan;
                    return;
                }

                this.field.rule.startedLessThan = value * 60;
            },
        },
        startedMoreThanMn: {
            get: function () {
                if (this.field.rule.startedMoreThan) {
                    return parseInt(this.field.rule.startedMoreThan / 60);
                }

                return "";
            },
            set: async function (value) {
                if (!value) {
                    delete this.field.rule.startedMoreThan;
                    return;
                }

                this.field.rule.startedMoreThan = value * 60;
            },
        },
        fieldName() {
            let name = [];

            if (this.startsInMoreThanMn) {
                name.push(
                    this.$t(
                        "campaign.rule.appointment.date.starts_in_more_than_mn",
                        {
                            minute: this.startsInMoreThanMn,
                        }
                    )
                );
            }

            if (this.startsInLessThanMn) {
                name.push(
                    this.$t(
                        "campaign.rule.appointment.date.starts_in_less_than_mn",
                        {
                            minute: this.startsInLessThanMn,
                        }
                    )
                );
            }

            if (this.startedLessThanMn) {
                name.push(
                    this.$t(
                        "campaign.rule.appointment.date.started_less_than_ago_mn",
                        {
                            minute: this.startedLessThanMn,
                        }
                    )
                );
            }

            if (this.startedMoreThanMn) {
                name.push(
                    this.$t(
                        "campaign.rule.appointment.date.started_more_than_ago_mn",
                        {
                            minute: this.startedMoreThanMn,
                        }
                    )
                );
            }

            return name.join(" - ");
        },
    },
};
</script>
