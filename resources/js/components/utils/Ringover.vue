<template>
    <div :id="id" class="hc-ringover"></div>
</template>

<style>
.hc-ringover > div {
    box-shadow: none !important;
    padding-top: 0 !important;
}
</style>

<script>
import RingoverInit from "@/utils/ringover";

export default {
    props: {
        id: {
            type: String,
        },

        number: {
            type: String,
            default: null,
        },

        tab: {
            type: String,
            default: "phone",
        },

        options: {
            type: Object,
            default: {
                animation: false,
                size: "auto",
                type: "relative",
            },
        },
    },

    data() {
        return {
            ringoverSDK: null,
        };
    },

    async mounted() {
        await RingoverInit();
        this.initRingover();
    },

    beforeUnmount() {
        if (this.ringoverSDK) {
            this.ringoverSDK.off();
        }
    },

    methods: {
        initRingover() {
            // Create instance
            this.ringoverSDK = new window.RingoverSDK({
                ...this.options,
                container: this.id,
            });

            this.ringoverSDK.on("dialerReady", (e) => {
                setTimeout(this.handleNumber, 5000);
            });

            this.ringoverSDK.on("smsSent", (e) => {
                this.$emit("sms-sent", e);
            });

            this.ringoverSDK.on("smsReceived", (e) => {
                this.$emit("sms-received", e);
            });

            this.ringoverSDK.on("ringingCall", (e) => {
                this.$emit("ringing-call", e);
            });

            this.ringoverSDK.on("hangupCall", (e) => {
                this.$emit("hangup-call", e);
            });

            this.ringoverSDK.on("answeredCall", (e) => {
                this.$emit("answered-call", e);
            });

            // Generate iframe
            this.ringoverSDK.generate();
            this.ringoverSDK.show();
            // Check iframe status
            this.ringoverSDK.checkStatus();
        },

        /**
         *
         */
        handleNumber() {
            if (this.number && this.ringoverSDK) {
                if (this.tab == "phone") {
                    this.ringoverSDK.dial(this.number);
                    this.$emit("new-call");
                } else if (this.tab == "sms") {
                    this.ringoverSDK.sendSMS(this.number, "Bonjour,");
                }
            }
        },
    },

    watch: {
        /**
         *
         */
        number() {
            this.handleNumber();
        },
    },
};
</script>
