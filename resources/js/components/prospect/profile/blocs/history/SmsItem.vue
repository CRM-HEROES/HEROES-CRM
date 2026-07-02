<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.creator"
        icon="fa fa-comment"
    >
        <div class="hc-prospect-profile-history-sms">
            <b
                class="hc-prospect-profile-history-sms-source"
                v-text="'SMS - ' + source + ' : '"
            ></b>
            <div
                class="hc-prospect-profile-history-sms-body"
                v-text="item.message"
            ></div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-sms {
    display: flex;
    flex-direction: row;
    gap: 8px;
}
</style>

<script>
// Components
import HistoryRow from "./Row.vue";

export default {
    props: {
        item: {
            type: Object,
        },
    },

    components: {
        HistoryRow,
    },

    computed: {
        source() {
            if (this.item.source == "telephone") {
                return "Téléphone";
            }

            if (this.item.source == "whatsapp") {
                return "Whatsapp";
            }

            if (this.item.source == "smsbox") {
                return "SMSBOX";
            }

            if (this.item.source == "ringover") {
                return "Ringover";
            }

            if (this.item.source == "ultramsg") {
                return "UltraMsg";
            }

            if (this.item.source == "mtarget") {
                return "MTarget";
            }

            return this.item.source;
        },
    },
};
</script>
