<template>
    <div :id="id" class="hc-kavkom-webphone">
        <audio ref="remoteAudio" autoplay style="display: none"></audio>

        <div class="hc-kavkom-webphone-status">
            <span v-if="status === 'not-configured'">
                Configurez le jeton Kavkom et le domain UUID dans les
                paramètres pour activer le softphone.
            </span>
            <span v-else-if="status === 'error'">
                <div style="color: #dc3545; margin-bottom: 4px;">{{ errorMessage || "Erreur de connexion au softphone Kavkom." }}</div>
                <div v-if="sipErrorDetails" style="font-size: 11px; color: #dc3545; opacity: 0.85;">
                    {{ sipErrorDetails }}
                </div>
            </span>
            <span v-else-if="status === 'connecting'">
                Connexion du softphone à l'extension Kavkom…
            </span>
            <span v-else-if="status === 'registered'">
                Softphone Kavkom prêt (extension {{ extension }}).
            </span>
            <span v-else-if="status === 'calling'">Appel en cours de connexion…</span>
            <span v-else-if="status === 'ringing'">
                Votre poste sonne (mise en relation Kavkom)…
            </span>
            <span v-else-if="status === 'in-call'">Appel en cours.</span>
        </div>

        <div
            class="hc-kavkom-webphone-controls"
            v-if="status === 'in-call'"
        >
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
            userContext: "",
            isRegistered: false,
            sipErrorDetails: "",
            connectionAttempts: 0,
            maxConnectionAttempts: 2,
            pcDiagnosticInterval: null,
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
            this.isRegistered = false;
            this.errorMessage = "";
            this.sipErrorDetails = "";

            try {
                const { data } = await ApiService.get(
                    "settings/kavkom/credentials"
                );

                if (!data.success) {
                    this.status = "not-configured";
                    this.errorMessage = data.message;
                    this.logError("Credentials fetch failed", { response: data });
                    return;
                }

                this.extension = data.extension;
                this.userContext = data.user_context;
                this.logInfo("Credentials received", {
                    extension: this.extension,
                    userContext: data.user_context,
                });

                this.connectSip(data);
            } catch (error) {
                this.status = "error";
                this.errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Erreur inattendue lors de la connexion à Kavkom.";
                this.logError("Credentials fetch error", { error });
            }
        },

        connectSip({ extension, password, user_context }) {
            const server = `wss://${user_context}/`;
            const aor = `sip:${extension}@${user_context}`;

            this.logInfo("Connecting to SIP", {
                server,
                aor,
                extension,
            });

            this.simpleUser = new SimpleUser(server, {
                aor,
                userAgentOptions: {
                    authorizationUsername: extension,
                    authorizationPassword: password,
                    logBuiltinEnabled: true,
                    logLevel: "debug",
                    logConnector: this.createSipLogger(),
                },
                media: {
                    constraints: { audio: true, video: false },
                    remote: { audio: this.$refs.remoteAudio },
                },
            });

            this.simpleUser.delegate = {
                onRegistered: () => {
                    this.isRegistered = true;
                    this.status = "registered";
                    this.logInfo("SIP registration succeeded", {
                        extension: this.extension,
                    });
                    this.$emit("ready");
                },
                onUnregistered: () => {
                    this.isRegistered = false;
                },
                onServerDisconnect: (error) => {
                    this.isRegistered = false;

                    if (error) {
                        this.status = "error";
                        this.errorMessage = "La connexion au softphone Kavkom a été interrompue.";
                        this.$emit("connection-error", this.errorMessage);
                    }
                },

                /**
                 * ARCHITECTURE CLICK-TO-CALL KAVKOM (recommandée par leur doc) :
                 * On ne compose plus le numéro de destination directement depuis
                 * le navigateur. C'est le PBX Kavkom qui appelle CETTE extension
                 * (le "leg agent") après un déclenchement via l'API REST
                 * POST /api/pbx/v1/active_call/call côté backend. On doit donc
                 * auto-répondre à cet appel entrant : le PBX se charge ensuite,
                 * de son côté, de mettre en relation avec le numéro externe
                 * (le "leg destination"), sans jamais exposer cette négociation
                 * média PSTN à notre navigateur.
                 */
                onCallReceived: async () => {
                    this.status = "ringing";
                    this.logInfo("Appel entrant du PBX Kavkom (leg agent)");
                    this.$emit("ringing-call");

                    try {
                        await this.simpleUser.answer();
                        this.logInfo("Auto-réponse au leg agent réussie");
                    } catch (error) {
                        this.logError("Échec de l'auto-réponse au leg agent", { error });
                        this.$emit(
                            "call-failed",
                            "Impossible de décrocher automatiquement l'appel entrant du PBX Kavkom."
                        );
                    }
                },

                onCallAnswered: () => {
                    this.status = "in-call";
                    this.logInfo("Appel établi (leg agent connecté)");
                    this.$emit("answered-call");
                },

                onCallHangup: () => {
                    this.status = "registered";
                    this.logInfo("Appel terminé");
                    this.$emit("hangup-call");
                },
            };

            this.simpleUser
                .connect()
                .then(() => {
                    this.logInfo("SIP connection established");
                    return this.simpleUser.register();
                })
                .catch((error) => {
                    this.isRegistered = false;
                    this.status = "error";
                    const errorMsg = this.extractSipErrorMessage(error);
                    this.errorMessage = errorMsg;
                    this.sipErrorDetails = this.extractSipErrorDetails(error);
                    this.logError("SIP connection or registration failed", {
                        error: errorMsg,
                        details: this.sipErrorDetails,
                        originalError: error,
                    });
                    this.$emit("connection-error", this.errorMessage);

                    if (this.connectionAttempts < this.maxConnectionAttempts) {
                        this.connectionAttempts++;
                        this.logWarn("Retrying SIP connection", {
                            attempt: this.connectionAttempts,
                            maxAttempts: this.maxConnectionAttempts,
                        });
                        setTimeout(() => {
                            this.registerWebphone();
                        }, 2000);
                    }
                });
        },

        hangup() {
            this.simpleUser
                ?.hangup()
                .catch((error) => {
                    this.logError("Failed to hang up call", { error });
                });
        },

        teardown() {
            if (this.simpleUser) {
                this.simpleUser
                    .disconnect()
                    .catch(() => {})
                    .finally(() => {
                        this.simpleUser = null;
                        this.isRegistered = false;
                    });
            }
        },

        /**
         * Extract a user-friendly error message from SIP errors
         */
        extractSipErrorMessage(error) {
            if (error instanceof TypeError) {
                if (error.message.includes("WebSocket")) {
                    return "Impossible de se connecter au serveur SIP WebSocket (problème de certificat SSL, DNS, ou firewall).";
                }
                return "Erreur de connexion SIP : " + error.message;
            }

            if (error && typeof error === "object") {
                if (error.statusCode === 401 || error.statusCode === 407) {
                    return "Erreur d'authentification SIP (identifiants incorrects ou extension désactivée dans Kavkom).";
                }
                if (error.statusCode === 404) {
                    return "Extension SIP non trouvée sur le serveur Kavkom.";
                }
                if (error.reasonPhrase && error.reasonPhrase.includes("NOT_REGISTERED")) {
                    return "L'extension n'a pas pu s'enregistrer auprès du serveur SIP. Le serveur WebSocket n'est peut-être pas joignable.";
                }
                if (error.message) {
                    return "Erreur SIP : " + error.message;
                }
                if (error.statusCode) {
                    return "Erreur SIP (HTTP " + error.statusCode + ").";
                }
            }

            return "Impossible de connecter le softphone à l'extension Kavkom.";
        },

        /**
         * Extract detailed error information for debugging
         */
        extractSipErrorDetails(error) {
            const details = [];

            if (error && typeof error === "object") {
                if (error.statusCode) {
                    details.push(`Status: ${error.statusCode}`);
                }
                if (error.reasonPhrase) {
                    details.push(`Reason: ${error.reasonPhrase}`);
                }
                if (error.cause) {
                    details.push(`Cause: ${error.cause}`);
                }
                if (error.message) {
                    details.push(`Message: ${error.message}`);
                }
            }

            return details.join(" | ");
        },

        createSipLogger() {
            return (message) => {
                this.logDebug("SIP.js", { message });
            };
        },

        logInfo(message, data = {}) {
            console.log(
                `[Kavkom] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },

        logDebug(message, data = {}) {
            console.debug(
                `[Kavkom DEBUG] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },

        logWarn(message, data = {}) {
            console.warn(
                `[Kavkom WARNING] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },

        logError(message, data = {}) {
            console.error(
                `[Kavkom ERROR] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },
    },
};
</script>