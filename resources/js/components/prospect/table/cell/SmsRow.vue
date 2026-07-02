<template>
    <div
        v-once
        :class="['hc-sms-cell-row', sms.error ? 'hc-sms-cell-row-error' : '']"
    >
        <div class="hc-sms-cell-row-header">
            <i class="fa fa-comment"></i>
            <span v-text="header"></span>
        </div>
        <div class="hc-sms-cell-row-body" v-html="body"></div>
    </div>
</template>

<script>
import dayjs from "dayjs";

export default {
    props: {
        /**
         *
         */
        prospect: {
            type: Object,
        },

        /**
         *
         */
        sms: {
            type: Object,
        },
    },

    computed: {
        /**
         */
        header() {
            return (
                (this.sms.creator ? this.sms.creator.name : "") +
                " le " +
                dayjs(new Date(this.sms.created_at)).format("DD/MM/YYYY") +
                " à " +
                dayjs(new Date(this.sms.created_at)).format("hh:mm")
            );
        },

        /**
         *
         */
        body() {
            return this.sms.message;
        },
    },
};
</script>
