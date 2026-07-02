<template>
    <label
        :class="['hc-sms-cell-label', error ? 'hc-sms-cell-label-error' : '']"
    >
        <span
            class="hc-sms-cell-count"
            v-if="sms.length > 1"
            v-text="sms.length"
        ></span>
        <div class="hc-sms-cell-resume">
            <span v-html="resume"></span>
        </div>
        <div class="hc-sms-cell-sms" v-if="!disabled && sms.length > 0">
            <sms-row
                v-for="message in sms"
                :key="message.id"
                :prospect="prospect"
                :sms="message"
            />
        </div>
    </label>
</template>

<script>
import dayjs from "dayjs";

// Components
import SmsRow from "./SmsRow.vue";

export default {
    components: {
        SmsRow,
    },

    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },
    },

    data() {
        return {
            message: "",
        };
    },

    computed: {
        /**
         * List if sms
         */
        sms() {
            if (!this.prospect.sms) {
                return [];
            }

            const sms = [...this.prospect.sms];

            sms.sort((m1, m2) => (m2.created_at > m1.created_at ? 1 : -1));

            return sms;
        },

        /**
         *
         */
        resume() {
            if (this.sms.length == 0) {
                return "";
            }

            const message = this.sms[0];

            return (
                "<b>" +
                dayjs(new Date(message.created_at)).format("DD/MM") +
                "</b>" +
                " " +
                message.message
            );
        },

        /**
         *
         */
        error() {
            if (this.sms.length == 0) {
                return "";
            }

            const message = this.sms[0];

            return message.error;
        },

        /**
         * Do not allow user
         * to edit some field
         * or on certain conditions applied to the prospect
         */
        disabled() {
            return (
                // deleted or archived prospect
                this.prospect.deleted_at || this.prospect.processed_at
            );
        },
    },
};
</script>
