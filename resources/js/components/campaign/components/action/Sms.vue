<template>
    <label class="hc-campaign-item-value-row">
        <div
            class="hc-campaign-item-value-row-label"
            v-text="$t('campaign.action.prospect.sms.source')"
        ></div>
        <div class="hc-campaign-item-value-row-select">
            <select v-model="source">
                <option value="smsbox" v-text="'SMSBOX'"></option>
                <option value="ultramsg" v-text="'UltraMsg'"></option>
                <option value="mtarget" v-text="'MTarget'"></option>
            </select>
        </div>
    </label>
    <label class="hc-campaign-item-value-row">
        <textarea
            placeholder="Saisir le SMS à envoyer ..."
            v-model.lazy="message"
        ></textarea>
    </label>
</template>

<style></style>

<script>
import store from "@/store";
import { UPDATE_CAMPAIGN_ACTION } from "@/actions/project/campaign";

export default {
    props: {
        action: {
            type: Object,
        },
    },

    computed: {
        source: {
            get() {
                return this.action.value.source
                    ? this.action.value.source
                    : "smsbox";
            },
            set(value) {
                store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                    id: this.action.id,
                    value: {
                        ...this.action.value,
                        source: value,
                    },
                });
            },
        },

        message: {
            get() {
                return this.action.value.message
                    ? this.action.value.message
                    : "";
            },
            set(value) {
                store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                    id: this.action.id,
                    value: {
                        ...this.action.value,
                        message: value,
                    },
                });
            },
        },
    },
};
</script>
