<template>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="$t('campaign.action.prospect.message.thread.title')"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="thread">
                <option
                    v-for="thread in threads"
                    :key="thread.id"
                    :value="thread.id"
                    v-text="thread.name"
                ></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="$t('campaign.action.prospect.message.user.title')"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="user">
                <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                    v-text="user.name"
                ></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="$t('campaign.action.prospect.message.template.title')"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select @change="setMessageTemplates">
                <option
                    v-for="messageTemplates in messageTemplatess"
                    :key="messageTemplates.id"
                    :value="messageTemplates.id"
                    v-text="messageTemplates.name"
                ></option>
            </select>
        </div>
    </label>
    <div class="hc-campaign-item-value-row">
        <text-editor
            v-model.lazy="body"
            :placeholder="$t('campaign.action.prospect.message.enter_message')"
            height="80px"
            style="width: 100%"
        />
    </div>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { UPDATE_CAMPAIGN_ACTION } from "@/actions/project/campaign";

export default {
    props: {
        action: {
            type: Object,
        },
    },

    methods: {
        setMessageTemplates(e) {
            const messageTemplate = this.messageTemplates.find(
                (mt) => mt.id == e.target.value
            );

            if (!messageTemplate) {
                return;
            }

            this.body = template.body;
        },
    },

    computed: {
        ...mapGetters(["threads", "users", "messageTemplates", "can"]),

        ...Object.fromEntries(
            ["thread", "user", "body"].map((key) => [
                key,
                {
                    // Get selected field CSS value
                    get() {
                        return this.action.value[key]
                            ? this.action.value[key]
                            : "";
                    },
                    // Set selected field CSS value
                    set(value) {
                        store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                            id: this.action.id,
                            value: {
                                ...this.action.value,
                                [key]: value,
                            },
                        });
                    },
                },
            ])
        ),
    },
};
</script>
