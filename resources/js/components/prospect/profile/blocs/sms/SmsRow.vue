<template>
    <div class="hc-prospect-profile-sms hc-flex-column">
        <div class="hc-prospect-profile-sms-header">
            <icon class="fa fa-comment" />
            <div
                class="hc-prospect-profile-sms-author"
                v-if="sms.creator"
                v-text="sms.creator.name"
            ></div>
            <span
                class="hc-item-main-content hc-prospect-profile-sms-date"
                v-text="date"
            ></span>
        </div>
        <div class="hc-prospect-profile-sms-body" v-html="sms.message"></div>
        <div
            class="hc-prospect-profile-sms-error"
            v-if="sms.error"
            v-html="sms.error"
        ></div>
    </div>
</template>

<style>
.hc-prospect-profile-sms {
    padding: 0px 5px 5px 5px;
    position: relative;
    border-bottom: 1px solid #eee;
}

.hc-prospect-profile-sms-header {
    align-items: center;
    display: flex;
    flex-direction: row;
    width: 100%;
    font-size: 12px;
}

.hc-prospect-profile-sms .hc-icon {
    font-size: 11px;
}

.hc-prospect-profile-sms > * {
    width: 100%;
}

.hc-prospect-profile-sms-date {
    color: #777777;
    font-size: 11px;
    text-align: right;
}

.hc-prospect-profile-sms-body {
    white-space: normal;
    padding: 0 10px;
}

.hc-prospect-profile-sms-error {
    color: #bb0000;
    padding: 5px 10px 0 10px;
}
</style>

<script>
export default {
    props: {
        /**
         *
         */
        sms: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        date() {
            const date = dayjs(new Date(this.sms.created_at)).format(
                "DD MMM YYYY HH:mm"
            );

            if (date == "Invalid date") {
                return this.sms.created_at;
            }

            return date;
        },
    },
};
</script>
