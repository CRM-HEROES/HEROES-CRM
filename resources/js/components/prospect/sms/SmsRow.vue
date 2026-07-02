<template>
    <div
        :class="[
            'hc-prospect-sms',
            'hc-flex-row',
            sms.from_user ? '' : 'hc-prospect-sms-from-prospect',
        ]"
    >
        <div class="hc-prospect-sms-content hc-flex-column">
            <div
                class="hc-prospect-sms-header"
                v-if="sms.creator"
                v-text="sms.creator.name"
            ></div>
            <div class="hc-prospect-sms-message" v-text="sms.message"></div>
            <div class="hc-prospect-sms-footer" v-text="footer"></div>
            <div
                class="hc-prospect-sms-error"
                v-if="sms.error"
                v-text="sms.error"
            ></div>
        </div>
    </div>
</template>

<style>
.hc-prospect-sms {
    justify-content: end;
}

.hc-prospect-sms-content {
    align-items: end;
    width: 100%;
}

.hc-prospect-sms-from-prospect .hc-prospect-sms-content {
    align-items: start;
}

.hc-prospect-sms-content > * {
    max-width: 90%;
    white-space: normal;
}

.hc-prospect-sms-header {
    font-size: 11px;
    color: #999999;
}

.hc-prospect-sms-message {
    background-color: #7939b8;
    color: #e6deef;
    padding: 7px 10px;
    border-radius: 15px;
    box-shadow: 0 3px 20px -3px #0005;
    position: relative;
    font-size: 11px;
}

.hc-prospect-sms-from-prospect .hc-prospect-sms-message {
    background-color: #eeeeee;
    color: #333333;
    border-radius: 7px 7px 7px 0;
    text-align: left;
}

.hc-prospect-sms-error {
    color: #b75959;
    font-size: 11px;
    text-align: right;
}

.hc-prospect-sms-footer {
    font-size: 11px;
    color: #999999;
    text-align: right;
    margin-top: 5px;
}
</style>

<script>
export default {
    props: {
        sms: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        footer() {
            return (
                dayjs(this.sms.created_at).fromNow() + " via " + this.sms.source
            );
        },
    },
};
</script>
