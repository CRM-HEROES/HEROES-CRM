<template>
    <div :id="id" class="hc-cloudtalk">
        <div v-if="number" class="hc-cloudtalk-header">
            <div class="hc-cloudtalk-number" v-text="number"></div>
            <button
                type="button"
                class="hc-cloudtalk-copy"
                @click="copyNumber"
                aria-label="Copier le numéro"
            >
                <icon class="fa fa-copy" />
            </button>
        </div>
        <iframe
            :src="src"
            class="hc-cloudtalk-iframe"
            allow="microphone *; camera *; display-capture *; autoplay *; clipboard-read *; clipboard-write *; fullscreen *"
            allowfullscreen
            referrerpolicy="origin"
        ></iframe>
    </div>
</template>

<style>
.hc-cloudtalk {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
}

.hc-cloudtalk-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-height: 42px;
    padding: 8px 12px;
    background: #f5f5f5;
    border-bottom: 1px solid #e5e5e5;
}

.hc-cloudtalk-number {
    flex: 1;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-cloudtalk-copy {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: transparent;
    border: none;
    color: #666;
    cursor: pointer;
}

.hc-cloudtalk-copy:hover {
    color: #000;
}

.hc-cloudtalk-iframe {
    border: none;
    width: 100%;
    height: 100%;
    flex: 1;
}
</style>

<script>
/**
 * CloudTalk Phone (https://phone.cloudtalk.io)
 *
 * CloudTalk only communicates from the iframe to the parent application
 * through window.postMessage (ringing, dialing, calling, hangup, ended,
 * contact_info). There is no documented API to trigger an outbound call
 * from the parent, so the phone cannot be dialed programmatically: the
 * `number` prop is kept for API consistency with the other providers.
 *
 * CloudTalk recommends a minimum size of 700px x 420px.
 */
export default {
    props: {
        id: {
            type: String,
        },

        partner: {
            type: String,
            default: "heroes",
        },

        number: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            origin: "https://phone.cloudtalk.io",
            allowedOrigin: /^https:\/\/([a-z0-9-]+\.)*cloudtalk\.io$/i,
        };
    },

    mounted() {
        window.addEventListener("message", this.handleMessage);
    },

    beforeUnmount() {
        window.removeEventListener("message", this.handleMessage);
    },

    methods: {
        copyNumber() {
            if (!this.number) {
                return;
            }

            navigator.clipboard
                .writeText(this.number)
                .then(() => {
                    flashInfo({
                        title: "CloudTalk",
                        body: "Numéro copié",
                        duration: 5000,
                    });
                })
                .catch(() => {
                    flashInfo({
                        title: "CloudTalk",
                        body: "Impossible de copier le numéro",
                        duration: 5000,
                    });
                });
        },

        /**
         * Handle events sent by the CloudTalk Phone iframe
         */
        handleMessage(event) {
            if (!this.allowedOrigin.test(event.origin)) {
                return;
            }

            let data = event.data;

            if (typeof data === "string") {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    return;
                }
            }

            if (!data || !data.event) {
                return;
            }

            const properties = data.properties || {};

            switch (data.event) {
                case "ringing":
                    this.$emit("ringing-call", properties);
                    break;
                case "dialing":
                    this.$emit("outgoing-call", properties);
                    break;
                case "calling":
                    this.$emit("answered-call", properties);
                    break;
                case "hangup":
                    this.$emit("hangup-call", properties);
                    break;
                case "ended":
                    this.$emit("call-ended", properties);
                    break;
                case "contact_info":
                    this.$emit("contact-info", properties);
                    break;
            }
        },
    },

    computed: {
        /**
         * CloudTalk Phone iframe url
         */
        src() {
            return this.origin + "?partner=" + this.partner;
        },
    },
};
</script>
