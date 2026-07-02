<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.sms.title')"
        icon="fa fa-envelope"
        style="width: 300px"
    >
        <div class="hc-flex-column" style="height: 100%">
            <item-list padding="12px" style="height: auto">
                <v-field
                    :label="$t('campaign.action.prospect.sms.source')"
                    required
                >
                    <select v-model="message.value.source" required>
                        <option value="smsbox" v-text="'SMSBOX'"></option>
                        <option value="ultramsg" v-text="'UltraMsg'"></option>
                        <option value="mtarget" v-text="'MTarget'"></option>
                    </select>
                </v-field>
                <v-field
                    :label="$t('campaign.action.prospect.sms.sms')"
                    required
                >
                    <textarea v-model="message.value.message"></textarea>
                </v-field>
            </item-list>
            <buttons>
                <campaign-template
                    tag="button"
                    :field="message"
                    @dragging="dragging"
                    @dragged="dragged"
                    v-text="$t('add')"
                    :disabled="!message.value.message || !message.value.source"
                ></campaign-template>
            </buttons>
        </div>
    </slide>
</template>

<script>
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            name: "campaign-action-prospect-sms",
            tab: 0,
            message: {
                action: "prospect-sms",
                name: this.$t("campaign.action.prospect.sms.name"),
                value: {
                    message: "",
                    source: "smsbox",
                },
                category: "action",
                style: {},
            },
        };
    },

    methods: {
        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },
};
</script>
