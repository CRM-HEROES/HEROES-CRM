<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.email.title')"
        icon="fa fa-envelope"
        style="width: 300px"
    >
        <div class="hc-flex-column" style="height: 100%">
            <item-list padding="12px" style="height: auto">
                <v-field
                    :label="$t('campaign.action.prospect.email.subject')"
                    required
                >
                    <input
                        type="text"
                        v-model="message.value.subject"
                        :placeholder="
                            $t('campaign.action.prospect.email.enter_subject')
                        "
                    />
                </v-field>
                <v-field
                    :label="$t('campaign.action.prospect.email.body')"
                    required
                >
                    <text-editor
                        v-model.lazy="message.value.body"
                        :placeholder="
                            $t('campaign.action.prospect.email.enter_message')
                        "
                        height="200px"
                    />
                </v-field>
            </item-list>
            <buttons>
                <campaign-template
                    tag="button"
                    :field="message"
                    @dragging="dragging"
                    @dragged="dragged"
                    v-text="$t('add')"
                    :disabled="!message.value.subject || !message.value.body"
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
            name: "campaign-action-prospect-email",
            tab: 0,
            message: {
                action: "prospect-email",
                name: this.$t("campaign.action.prospect.email.name"),
                value: {
                    subject: "",
                    body: "",
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
