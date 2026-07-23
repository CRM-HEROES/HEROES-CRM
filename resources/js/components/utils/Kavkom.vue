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
            sipErrorDetails: "",
            connectionAttempts: 0,
            maxConnectionAttempts: 2,
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
        this.logInfo("Credentials received", {
            extension: this.extension,
            userContext: data.user_context,
        });

        // Suppression du pré-test WSS : il donnait un faux négatif car
        // il n'envoyait pas le sous-protocole "sip" exigé par le serveur.
        // SimpleUser.connect() gère ça correctement en interne.
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

        /**
         * Test WSS (WebSocket Secure) connectivity to the Kavkom server
         * before attempting SIP registration. This helps diagnose network issues.
         */
        async testWssConnectivity(userContext) {
            return new Promise((resolve) => {
                const wssUrl = `wss://${userContext}`;
                this.logInfo("Testing WSS connectivity", { url: wssUrl });

                const websocket = new WebSocket(wssUrl);
                let isResolved = false;
                const timeout = setTimeout(() => {
                    isResolved = true;
                    websocket.close();
                    this.logWarn("WSS connectivity test timed out", {
                        url: wssUrl,
                    });
                    resolve(false);
                }, 5000);

                websocket.onopen = () => {
                    if (!isResolved) {
                        isResolved = true;
                        clearTimeout(timeout);
                        websocket.close();
                        this.logInfo("WSS connectivity test succeeded", {
                            url: wssUrl,
                        });
                        resolve(true);
                    }
                };

                websocket.onerror = (event) => {
                    if (!isResolved) {
                        isResolved = true;
                        clearTimeout(timeout);
                        this.logError("WSS connectivity test failed", {
                            url: wssUrl,
                            error: event.message || "Unknown error",
                        });
                        resolve(false);
                    }
                };

                websocket.onclose = () => {
                    clearTimeout(timeout);
                };
            });
        },

        connectSip({ extension, password, user_context }) {
            const server = `wss://${user_context}`;
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
                    logBuiltinEnabled: false,
                    logConnector: this.createSipLogger(),
                },
                media: {
                    constraints: { audio: true, video: false },
                    remote: { audio: this.$refs.remoteAudio },
                },
            });

            this.simpleUser.delegate = {
                onCallReceived: async () => {
                    this.status = "ringing";
                    this.logInfo("Incoming call received");
                    this.$emit("ringing-call");
                },
                onCallAnswered: () => {
                    this.status = "in-call";
                    this.logInfo("Call answered");
                    this.$emit("answered-call");
                },
                onCallHangup: () => {
                    this.status = "registered";
                    this.logInfo("Call hung up");
                    this.$emit("hangup-call");
                },
            };

            this.simpleUser
                .connect()
                .then(() => {
                    this.logInfo("SIP connection established");
                    return this.simpleUser.register();
                })
                .then(() => {
                    this.status = "registered";
                    this.logInfo("SIP registration succeeded", {
                        extension: this.extension,
                    });
                })
                .catch((error) => {
                    this.status = "error";
                    const errorMsg = this.extractSipErrorMessage(error);
                    this.errorMessage = errorMsg;
                    this.sipErrorDetails = this.extractSipErrorDetails(error);
                    this.logError("SIP connection or registration failed", {
                        error: errorMsg,
                        details: this.sipErrorDetails,
                        originalError: error,
                    });

                    // Try to reconnect if this was the first attempt
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

        answer() {
            this.simpleUser
                ?.answer()
                .catch((error) => {
                    this.logError("Failed to answer call", { error });
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
                    });
            }
        },

        /**
         * Extract a user-friendly error message from SIP errors
         */
        extractSipErrorMessage(error) {
            // Handle different error scenarios
            if (error instanceof TypeError) {
                if (error.message.includes("WebSocket")) {
                    return "Impossible de se connecter au serveur SIP WebSocket (problème de certificat SSL, DNS, ou firewall).";
                }
                return "Erreur de connexion SIP : " + error.message;
            }

            if (error && typeof error === "object") {
                // Check for specific SIP error codes
                if (error.statusCode === 401 || error.statusCode === 407) {
                    return "Erreur d'authentification SIP (identifiants incorrects ou extension désactivée dans Kavkom).";
                }
                if (error.statusCode === 404) {
                    return "Extension SIP non trouvée sur le serveur Kavkom.";
                }
                if (error.reasonPhrase && error.reasonPhrase.includes("NOT_REGISTERED")) {
                    return "L'extension n'a pas pu s'enregistrer auprès du serveur SIP. Le serveur WebSocket n'est peut-être pas joignable.";
                }

                // Try to extract message from error object
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

        /**
         * Create a logger function for SIP.js
         */
        createSipLogger() {
            return (message) => {
                this.logDebug("SIP.js", { message });
            };
        },

        // Logging utilities
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
