<template>
    <div :id="id" class="hc-kavkom-webphone">
        <audio ref="remoteAudio" autoplay style="display: none"></audio>

        <div class="hc-kavkom-webphone-status">
            <span v-if="status === 'not-configured'">
                Configurez le jeton Kavkom et le domain UUID dans les
                paramètres pour activer le softphone.
            </span>
            <span v-else-if="status === 'error'">
                {{ errorMessage || "Erreur de connexion au softphone Kavkom." }}
            </span>
            <span v-else-if="status === 'connecting'">
                Connexion du softphone à l’extension Kavkom…
            </span>
            <span v-else-if="status === 'registered'">
                Softphone Kavkom prêt (extension {{ extension }}).
            </span>
            <span v-else-if="status === 'ringing'">Appel entrant…</span>
            <span v-else-if="status === 'in-call'">Appel en cours.</span>
        </div>

        <div
            class="hc-kavkom-webphone-controls"
            v-if="['ringing', 'in-call'].includes(status)"
        >
            <button
                v-if="status === 'ringing'"
                type="button"
                class="hc-button-success"
                @click="answer"
            >
                Répondre
            </button>
            <button type="button" class="hc-button-danger" @click="hangup">
                Raccrocher
            </button>
        </div>
    </div>
</template>

<style scoped>
.hc-kavkom-webphone {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.hc-kavkom-webphone-status {
    font-size: 13px;
    color: #6c757d;
}
.hc-kavkom-webphone-controls {
    display: flex;
    gap: 8px;
}
</style>

<script>
import { SimpleUser } from "sip.js/lib/platform/web";
import ApiService from "@/apis/api.service";

export default {
    props: {
        id: {
            type: String,
        },
    },

    data() {
        return {
            simpleUser: null,
            status: "connecting",
            errorMessage: "",
            extension: "",
        };
    },

    async mounted() {
        await this.registerWebphone();
    },

    beforeUnmount() {
        this.teardown();
    },

    methods: {
        async registerWebphone() {
            this.status = "connecting";
            this.errorMessage = "";

            try {
                const { data } = await ApiService.get(
                    "settings/kavkom/credentials"
                );

                if (!data.success) {
                    this.status = "not-configured";
                    this.errorMessage = data.message;
                    return;
                }

                this.extension = data.extension;
                this.connectSip(data);
            } catch (error) {
                this.status = "error";
                this.errorMessage =
                    error.response?.data?.message ||
                    "Erreur inattendue lors de la connexion à Kavkom.";
            }
        },

        connectSip({ extension, password, user_context }) {
            const server = `wss://${user_context}`;
            const aor = `sip:${extension}@${user_context}`;

            this.simpleUser = new SimpleUser(server, {
                aor,
                userAgentOptions: {
                    authorizationUsername: extension,
                    authorizationPassword: password,
                },
                media: {
                    constraints: { audio: true, video: false },
                    remote: { audio: this.$refs.remoteAudio },
                },
            });

            this.simpleUser.delegate = {
                onCallReceived: async () => {
                    this.status = "ringing";
                    this.$emit("ringing-call");
                },
                onCallAnswered: () => {
                    this.status = "in-call";
                    this.$emit("answered-call");
                },
                onCallHangup: () => {
                    this.status = "registered";
                    this.$emit("hangup-call");
                },
            };

            this.simpleUser
                .connect()
                .then(() => this.simpleUser.register())
                .then(() => {
                    this.status = "registered";
                })
                .catch(() => {
                    this.status = "error";
                    this.errorMessage =
                        "Impossible de connecter le softphone à l’extension Kavkom.";
                });
        },

        answer() {
            this.simpleUser?.answer().catch(() => {});
        },

        hangup() {
            this.simpleUser?.hangup().catch(() => {});
        },

        teardown() {
            if (this.simpleUser) {
                this.simpleUser.disconnect().catch(() => {});
                this.simpleUser = null;
            }
        },
    },
};
</script>
