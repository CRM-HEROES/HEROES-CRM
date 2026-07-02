<template>
    <div :id="id" class="hc-aircall"></div>
</template>

<style></style>

<script>
import aircallPhone from "aircall-everywhere";

window.aircallPhone = null;

export default {
    props: {
        id: {
            type: String,
        },

        number: {
            type: String,
            default: null,
        },
    },

    async mounted() {
        this.initAircall();
    },

    methods: {
        initAircall() {
            window.aircallPhone = new aircallPhone({
                domToLoadPhone: "#" + this.id,
                onLogin: (settings) => {
                    this.handleNumber();
                },
            });

            // Incoming call event
            window.aircallPhone.on("incoming_call", (callInfos) => {
                this.$emit("incoming-call", callInfos);
            });

            // Call ended event
            window.aircallPhone.on("call_ended", (callInfos) => {
                this.$emit("call-ended", callInfos);
            });

            // Call end ringtone
            window.aircallPhone.on("call_end_ringtone", (callInfos) => {
                this.$emit("call-end-ringtone", callInfos);
            });

            // Call outgoing event
            window.aircallPhone.on("outgoing_call", (callInfos) => {
                this.$emit("outgoing-call", callInfos);
            });

            // Call outgoing answered event
            window.aircallPhone.on("outgoing_answered", (callInfos) => {
                this.$emit("answered-call", callInfos);
            });
        },

        /**
         *
         */
        handleNumber() {
            if (this.number && window.aircallPhone) {
                const payload = {
                    phone_number: this.number,
                };

                window.aircallPhone.send(
                    "dial_number",
                    payload,
                    (success, data) => {}
                );
                this.$emit("initiated-call");
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
