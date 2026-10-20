<template>
    <div :id="id" class="hc-cloudtalk">
        <!-- <div v-if="number" class="hc-cloudtalk-header">
            <div class="hc-cloudtalk-number" v-text="number"></div>
            <div class="hc-cloudtalk-actions">
                <button
                    type="button"
                    class="hc-cloudtalk-action"
                    :disabled="calling"
                    @click="requestCall"
                    aria-label="Appeler via CloudTalk"
                    title="Appeler via CloudTalk"
                >
                    <icon class="fa fa-phone" />
                </button>
                <button
                    type="button"
                    class="hc-cloudtalk-action"
                    @click="copyNumber"
                    aria-label="Copier le numéro"
                    title="Copier le numéro"
                >
                    <icon class="fa fa-copy" />
                </button>
            </div>
        </div> -->
        <div v-if="showLoading" class="hc-cloudtalk-loading">
            <loading :loading="showLoading" />
            <div
                v-if="showSlowLoadingMessage"
                class="hc-cloudtalk-loading-help"
            >
                
                <div class="hc-cloudtalk-loading-help-title">
                    En attente de connexion à CloudTalk
                </div>
                <div>
                    Vérifiez que
                    <strong>vous êtes déjà connecté à CloudTalk dans le CRM</strong>
                    et fermez les autres fenêtres
                    <a
                        href="https://phone.cloudtalk.io/"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        https://phone.cloudtalk.io/
                    </a>
                    connectées à ce compte.
                </div>

            </div>
        </div>
        <iframe
            ref="phoneFrame"
            :src="src"
            :class="[
                'hc-cloudtalk-iframe',
                { 'hc-cloudtalk-iframe-loading': showLoading },
            ]"
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
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
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

.hc-cloudtalk-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.hc-cloudtalk-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: transparent;
    border: none;
    color: #666;
    cursor: pointer;
}

.hc-cloudtalk-action:hover {
    color: #000;
}

.hc-cloudtalk-action:disabled {
    color: #aaa;
    cursor: wait;
}

.hc-cloudtalk-iframe {
    border: none;
    width: 100%;
    height: 100%;
    flex: 1;
    position: relative;
    z-index: 1;
    transition: filter 150ms ease-out;
    margin-bottom: 50px;
    zoom: 65%;
}

.hc-cloudtalk-iframe-loading {
    filter: blur(2px);
}

.hc-cloudtalk-loading {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 52px;
    height: auto;
    padding: 8px 10px 10px;
    background: rgba(255, 255, 255, 0.86);
}

.hc-cloudtalk-loading .hc-loading-overlay {
    background-color: transparent;
    position: relative;
    height: 28px;
    opacity: 1;
    visibility: visible;
}

.hc-cloudtalk-loading .hc-loading-overlay::before {
    height: 28px;
}

.hc-cloudtalk-loading-help {
    padding: 8px 10px;
    border: 1px solid #d6e4f0;
    border-radius: 6px;
    background: #f4f9ff;
    color: #334155;
    font-size: 12px;
    line-height: 1.4;
}

.hc-cloudtalk-loading-help-title {
    margin-bottom: 4px;
    font-weight: 700;
}

.hc-cloudtalk-loading-help a {
    color: #12a0f3;
    overflow-wrap: anywhere;
}
</style>

<script>
/**
 * CloudTalk Phone (https://phone.cloudtalk.io)
 *
 * The iframe remains the phone UI. Outbound calls are requested by emitting
 * an event to the parent Vue component, which calls the Laravel backend and
 * lets Laravel call the documented CloudTalk Make a Call API.
 *
 * CloudTalk recommends a minimum size of 700px x 420px.
 */
export default {
    emits: [
        "answered-call",
        "call-activity",
        "call-ended",
        "contact-info",
        "hangup-call",
        "make-call",
        "outgoing-call",
        "ringing-call",
    ],

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

        calling: {
            type: Boolean,
            default: false,
        },

        loading: {
            type: Boolean,
            default: false,
        },

        agentName: {
            type: String,
            default: "",
        },

        slowLoadingDelay: {
            type: Number,
            default: 16000,
        },
    },

    data() {
        return {
            origin: "https://phone.cloudtalk.io",
            allowedOrigin: /^https:\/\/([a-z0-9-]+\.)*cloudtalk\.io$/i,
            eventReceived: false,
            slowLoadingTimeout: null,
            showSlowLoadingMessage: false,
        };
    },

    mounted() {
        window.addEventListener("message", this.handleMessage);
        this.syncSlowLoadingMessage();
    },

    beforeUnmount() {
        window.removeEventListener("message", this.handleMessage);
        this.clearSlowLoadingTimer();
    },

    methods: {
        syncSlowLoadingMessage() {
            this.clearSlowLoadingTimer();

            if (!this.showLoading) {
                return;
            }

            this.slowLoadingTimeout = setTimeout(() => {
                this.showSlowLoadingMessage = true;
            }, this.slowLoadingDelay);
        },

        clearSlowLoadingTimer() {
            if (this.slowLoadingTimeout) {
                clearTimeout(this.slowLoadingTimeout);
                this.slowLoadingTimeout = null;
            }

            this.showSlowLoadingMessage = false;
        },

        requestCall() {
            if (!this.number || this.calling) {
                return;
            }

            this.$emit("make-call", this.number);
        },

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
            if (event.origin !== this.origin || (this.$refs.phoneFrame
                && event.source !== this.$refs.phoneFrame.contentWindow)) {
                return;
            }

            let data = event.data;
            console.log(data)

            if (typeof data === "string") {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    return;
                }
            }

            if (!data || typeof data.event !== "string" || !data.event.trim()) {
                return;
            }

            this.eventReceived = true;
            this.clearSlowLoadingTimer();

            const eventName = this.normalizeEventName(data.event);
            const properties = this.normalizeProperties(
                data.properties || {},
                eventName
            );

            if (this.isCallActivity(eventName) && !["hangup", "ended"].includes(eventName)) {
                this.$emit("call-activity", {
                    event: eventName,
                    properties,
                });
            }
            switch (eventName) {
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

        normalizeEventName(eventName) {
            return (
                {
                    call_ringing: "ringing",
                    "call.ringing": "ringing",
                    call_dialing: "dialing",
                    "call.dialing": "dialing",
                    call_calling: "calling",
                    "call.calling": "calling",
                    call_hangup: "hangup",
                    "call.hangup": "hangup",
                    call_ended: "ended",
                    "call.ended": "ended",
                }[eventName] || eventName
            );
        },

        isCallActivity(eventName) {
            return ["ringing", "dialing", "calling", "hangup", "ended"].includes(
                eventName
            );
        },

        normalizeProperties(properties, eventName) {
            const direction =
                properties.direction ||
                (eventName == "dialing"
                    ? "outbound"
                    : eventName == "ringing"
                    ? "inbound"
                    : null);
            const externalNumber =
                properties.external_number ||
                properties.customer_number ||
                properties.contact_phone ||
                properties.phone_number ||
                properties.number ||
                (direction == "outbound" ? properties.to : properties.from) ||
                properties.from ||
                properties.to ||
                "";

            return {
                ...properties,
                ...(direction ? { direction } : {}),
                ...(externalNumber ? { external_number: externalNumber } : {}),
            };
        },
    },

    watch: {
        loading(value) {
            if (value) this.eventReceived = false;
            this.syncSlowLoadingMessage();
        },
    },

    computed: {
        showLoading() {
            return this.loading && !this.eventReceived;
        },

        agentLabel() {
            return this.agentName || "l'utilisateur CloudTalk";
        },

        /**
         * CloudTalk Phone iframe url
         */
        src() {
            return this.origin + "?partner=" + this.partner;
        },
    },
};
</script>
