<template>
    <div :id="id" class="hc-kavkom-webphone">
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
            <span v-else-if="status === 'connecting' || status === 'idle'">
                Connexion du softphone à l'extension Kavkom…
            </span>
            <span v-else-if="status === 'registered'">
                Softphone Kavkom prêt (extension {{ extension }}).
            </span>
            <span v-else-if="status === 'ringing'">
                <template v-if="isInboundCall">
                    Appel entrant de {{ callNumber || "numéro inconnu" }}…
                </template>
                <template v-else>
                    Votre poste sonne (mise en relation Kavkom)…
                </template>
            </span>
            <span v-else-if="status === 'in-call'">
                <template v-if="isInboundCall">
                    Appel entrant en cours{{ callNumber ? " (" + callNumber + ")" : "" }}.
                </template>
                <template v-else>
                    Appel en cours{{ callNumber ? " (" + callNumber + ")" : "" }}.
                </template>
            </span>
        </div>

        <!-- The agent leg of a CRM click-to-call is already answered, only
             a real incoming call waits for the agent's decision. -->
        <div
            class="hc-kavkom-webphone-controls"
            v-if="status === 'ringing' && !autoAnswered"
        >
            <button type="button" class="hc-button-secondary" @click="answer">
                Accepter l'appel
            </button>
            <button type="button" class="hc-button-danger" @click="decline">
                Refuser
            </button>
        </div>

        <div class="hc-kavkom-webphone-controls" v-else-if="status === 'in-call'">
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
import kavkomPhone from "@/utils/kavkom-phone";

/**
 * Kavkom softphone panel, shown in the "Kavkom" tab of the prospect
 * interaction slide.
 *
 * It owns no SIP connection: the single registration lives in
 * resources/js/utils/kavkom-phone.js and is started once for the whole
 * session by the global softphone widget, so incoming calls also ring when
 * this panel is closed. This component only renders the shared state and
 * forwards the agent's actions, which keeps a single SIP contact per
 * browser and avoids the PBX forking a call to two dialogs.
 */
export default {
    props: {
        id: {
            type: String,
        },
        // Only used as a fallback if this panel is mounted without the
        // global widget: the SIP identity is resolved per user.
        projectId: {
            type: [Number, String],
            default: null,
        },
    },

    computed: {
        status() {
            return kavkomPhone.state.status;
        },

        errorMessage() {
            return kavkomPhone.state.errorMessage;
        },

        sipErrorDetails() {
            return kavkomPhone.state.sipErrorDetails;
        },

        extension() {
            return kavkomPhone.state.extension;
        },

        callNumber() {
            return kavkomPhone.state.callNumber;
        },

        isInboundCall() {
            return kavkomPhone.state.callDirection === "inbound";
        },

        autoAnswered() {
            return kavkomPhone.state.callAutoAnswered;
        },
    },

    mounted() {
        console.log("------------------------------------------------------")
        kavkomPhone.bootstrap({ projectId: this.projectId });
    },

    methods: {
        answer() {
            kavkomPhone.answer();
        },

        decline() {
            kavkomPhone.decline();
        },

        hangup() {
            kavkomPhone.hangup();
        },
    },
};
</script>
