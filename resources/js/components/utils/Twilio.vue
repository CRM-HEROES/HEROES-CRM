<template>
    <div :id="id" class="hc-twilio-webphone">
        <div class="hc-twilio-webphone-status">
            <span v-if="status === 'not-configured'">
                {{ errorMessage || "Configuration Twilio manquante côté serveur." }}
            </span>
            <span v-else-if="status === 'error'">
                {{ errorMessage || "Erreur de connexion au service Twilio." }}
            </span>
            <span v-else-if="status === 'connecting'">
                Connexion au service Twilio…
            </span>
            <span v-else-if="status === 'registered'">Prêt.</span>
            <span v-else-if="status === 'calling'">Appel en cours de connexion…</span>
            <span v-else-if="status === 'ringing'">Ça sonne…</span>
            <span v-else-if="status === 'in-call'">Appel en cours.</span>
            <span v-else-if="status === 'ended'">Appel terminé.</span>
        </div>

        <div v-if="dialedNumber" class="hc-twilio-webphone-number">
            {{ dialedNumber }}
        </div>

        <div class="hc-twilio-webphone-controls" v-if="canHangup">
            <button type="button" class="hc-button-danger" @click="hangup">
                Raccrocher
            </button>
        </div>
    </div>
</template>

<style scoped>
.hc-twilio-webphone {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.hc-twilio-webphone-status {
    font-size: 13px;
    color: #6c757d;
}
.hc-twilio-webphone-number {
    font-size: 15px;
    font-weight: 600;
}
.hc-twilio-webphone-controls {
    display: flex;
    gap: 8px;
}
</style>

<script>
import { Device } from "@twilio/voice-sdk";
import ApiService from "@/apis/api.service";

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

    data() {
        return {
            device: null,
            activeCall: null,
            deviceReady: false,
            status: "connecting",
            errorMessage: "",
            dialedNumber: "",
        };
    },

    computed: {
        canHangup() {
            return ["calling", "ringing", "in-call"].includes(this.status);
        },
    },

    async mounted() {
        await this.initDevice();
    },

    beforeUnmount() {
        this.activeCall?.disconnect();
        this.device?.destroy();
    },

    methods: {
        async initDevice() {
            let token;

            try {
                const { data } = await ApiService.post("settings/twilio/token");

                if (!data.success) {
                    this.status = "not-configured";
                    this.errorMessage = data.message;
                    this.$emit("call-error", data.message);
                    return;
                }

                token = data.token;
            } catch (error) {
                this.status = "error";
                this.errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Erreur inattendue lors de la récupération du jeton Twilio.";
                this.$emit("call-error", this.errorMessage);
                return;
            }

            this.device = new Device(token, { logLevel: "warn" });

            this.device.on("registered", () => {
                this.deviceReady = true;
                this.status = "registered";
                this.handleNumber();
            });

            this.device.on("error", (error) => {
                this.status = "error";
                this.errorMessage = error.message || "Erreur du SDK Twilio.";
                this.$emit("call-error", this.errorMessage);
            });

            this.device.on("tokenWillExpire", async () => {
                try {
                    const { data } = await ApiService.post("settings/twilio/token");
                    if (data.success) {
                        this.device.updateToken(data.token);
                    }
                } catch (error) {
                    // Le prochain appel échouera si le rafraîchissement a
                    // échoué ; l'utilisateur relancera manuellement.
                }
            });

            try {
                await this.device.register();
            } catch (error) {
                this.status = "error";
                this.errorMessage =
                    error.message || "Impossible de connecter le service Twilio.";
                this.$emit("call-error", this.errorMessage);
            }
        },

        async handleNumber() {
            if (!this.number || !this.device || !this.deviceReady) {
                return;
            }

            this.activeCall?.disconnect();
            this.status = "calling";
            this.dialedNumber = this.number;

            try {
                const call = await this.device.connect({
                    params: { To: this.number },
                });
                this.activeCall = call;

                this.$emit("outgoing-call", {
                    call_sid: call.parameters?.CallSid || null,
                });

                call.on("ringing", () => {
                    this.status = "ringing";
                    this.$emit("ringing-call");
                });

                call.on("accept", () => {
                    this.status = "in-call";
                    this.$emit("answered-call");
                });

                call.on("disconnect", () => {
                    this.status = "ended";
                    this.activeCall = null;
                    this.$emit("hangup-call");
                });

                call.on("error", (error) => {
                    this.status = "error";
                    this.errorMessage = error.message || "Erreur pendant l'appel Twilio.";
                    this.$emit("call-error", this.errorMessage);
                });
            } catch (error) {
                this.status = "error";
                this.errorMessage =
                    error.message || "Impossible de démarrer l'appel Twilio.";
                this.$emit("call-error", this.errorMessage);
            }
        },

        hangup() {
            this.activeCall?.disconnect();
        },
    },

    watch: {
        number() {
            this.handleNumber();
        },
    },
};
</script>
