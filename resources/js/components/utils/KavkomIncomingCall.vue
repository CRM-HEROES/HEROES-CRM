<template>
    <div class="hc-kavkom-widget">
        <!-- The remote audio lives here, not in the slide: an ongoing call
             keeps its audio when the agent navigates or closes the slide. -->
        <audio ref="remoteAudio" autoplay style="display: none"></audio>

        <!-- Incoming call, waiting for the agent -->
        <div v-if="incomingCall" class="hc-kavkom-popup hc-kavkom-popup-incoming">
            <div class="hc-kavkom-popup-header">
                <span class="hc-kavkom-popup-icon">
                    <icon class="fa fa-phone" />
                </span>
                <div class="hc-kavkom-popup-identity">
                    <div class="hc-kavkom-popup-label">Appel entrant</div>
                    <div class="hc-kavkom-popup-name">
                        {{ callerName || phone.callNumber || "Numéro inconnu" }}
                    </div>
                    <div
                        v-if="callerName && phone.callNumber"
                        class="hc-kavkom-popup-number"
                    >
                        {{ phone.callNumber }}
                    </div>
                </div>
                <span class="hc-kavkom-popup-pulse"></span>
            </div>

            <div
                v-if="prospect"
                class="hc-kavkom-popup-prospect"
                @click="openProspect"
            >
                <icon class="fa fa-address-card" />
                <span>Fiche prospect reconnue</span>
                <icon class="fa fa-caret-right" />
            </div>
            <div v-else class="hc-kavkom-popup-prospect is-unknown">
                <icon class="fa fa-user-slash" />
                <span>Aucun prospect ne correspond à ce numéro</span>
            </div>

            <div class="hc-kavkom-popup-actions">
                <button
                    type="button"
                    class="hc-button-secondary hc-kavkom-accept"
                    @click="answer"
                >
                    <icon class="fa fa-phone" /> Accepter
                </button>
                <button
                    type="button"
                    class="hc-button-danger"
                    @click="decline"
                >
                    <icon class="fa fa-phone-slash" /> Refuser
                </button>
            </div>
        </div>

        <!-- Ongoing call (incoming or outgoing), so the agent can hang up
             from anywhere in the CRM -->
        <div v-else-if="isInCall" class="hc-kavkom-popup hc-kavkom-popup-incall">
            <div class="hc-kavkom-popup-header">
                <span class="hc-kavkom-popup-icon is-active">
                    <icon class="fa fa-phone" />
                </span>
                <div class="hc-kavkom-popup-identity">
                    <div class="hc-kavkom-popup-label">
                        {{ isInboundCall ? "Appel entrant" : "Appel sortant" }}
                    </div>
                    <div class="hc-kavkom-popup-name">
                        <!--
                            Pour un sortant, le numéro de l'INVITE est
                            celui de la ligne elle-même : le nom du prospect
                            appelé n'est affiché que pour un entrant.
                        -->
                        {{
                            callerName ||
                            (isInboundCall ? phone.callNumber : "") ||
                            "Kavkom"
                        }}
                    </div>
                </div>
                <div class="hc-kavkom-popup-duration">{{ duration }}</div>
            </div>

            <div class="hc-kavkom-popup-actions">
                <button
                    type="button"
                    class="hc-button-danger"
                    @click="hangup"
                >
                    <icon class="fa fa-phone-slash" /> Raccrocher
                </button>
            </div>
        </div>

        <!-- Connection problem, only when no call is in progress -->
        <div v-else-if="errorMessage" class="hc-kavkom-popup hc-kavkom-popup-error">
            <div class="hc-kavkom-popup-header">
                <span class="hc-kavkom-popup-icon is-error">
                    <icon class="fa fa-exclamation-triangle" />
                </span>
                <div class="hc-kavkom-popup-identity">
                    <div class="hc-kavkom-popup-label">Softphone Kavkom</div>
                    <div class="hc-kavkom-popup-error-message">
                        {{ errorMessage }}
                    </div>
                </div>
                <icon class="fa fa-times hc-kavkom-popup-close" @click="errorMessage = ''" />
            </div>

            <div v-if="phone.status === 'error'" class="hc-kavkom-popup-actions">
                <button
                    type="button"
                    class="hc-button-secondary"
                    @click="reconnect"
                >
                    <icon class="fa fa-sync" /> Reconnecter le softphone
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hc-kavkom-popup {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 3000;
    width: 330px;
    max-width: calc(100vw - 36px);
    padding: 14px;
    background: #ffffff;
    border: 1px solid #e4e6eb;
    border-radius: 14px;
    box-shadow: 0 14px 34px rgba(31, 41, 55, 0.18);
    animation: hc-kavkom-popup-in 0.18s ease-out;
}
.hc-kavkom-popup-incoming {
    border-color: #cfe3d0;
}
.hc-kavkom-popup-error {
    border-color: #f2d3cc;
}
@keyframes hc-kavkom-popup-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.hc-kavkom-popup-header {
    display: flex;
    align-items: center;
    gap: 11px;
}
.hc-kavkom-popup-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: none;
    width: 38px;
    height: 38px;
    color: #ffffff;
    background: #489f1f;
    border-radius: 10px;
}
.hc-kavkom-popup-icon.is-active {
    background: #8e24aa;
}
.hc-kavkom-popup-icon.is-error {
    background: #c62828;
}
.hc-kavkom-popup-identity {
    flex: 1;
    min-width: 0;
}
.hc-kavkom-popup-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #8a8f98;
}
.hc-kavkom-popup-name {
    overflow: hidden;
    font-size: 15px;
    font-weight: 600;
    color: #2f3237;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.hc-kavkom-popup-number,
.hc-kavkom-popup-duration {
    font-size: 12px;
    color: #6c757d;
}
.hc-kavkom-popup-duration {
    flex: none;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    color: #8e24aa;
}
.hc-kavkom-popup-error-message {
    font-size: 12px;
    color: #c62828;
}
.hc-kavkom-popup-close {
    flex: none;
    cursor: pointer;
    color: #adb5bd;
}
.hc-kavkom-popup-pulse {
    flex: none;
    width: 9px;
    height: 9px;
    background: #489f1f;
    border-radius: 50%;
    animation: hc-kavkom-pulse 1.2s infinite;
}
@keyframes hc-kavkom-pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.35;
        transform: scale(1.5);
    }
}
.hc-kavkom-popup-prospect {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 8px 10px;
    font-size: 12px;
    color: #6d28d9;
    background: #f7f2fd;
    border-radius: 9px;
    cursor: pointer;
}
.hc-kavkom-popup-prospect > span {
    flex: 1;
}
.hc-kavkom-popup-prospect.is-unknown {
    color: #6c757d;
    background: #f4f5f7;
    cursor: default;
}
.hc-kavkom-popup-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}
.hc-kavkom-popup-actions > button {
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 38px;
}
.hc-kavkom-accept {
    color: #ffffff;
    background: #489f1f;
    border-color: #489f1f;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ApiService from "@/apis/api.service";
import EventBus from "@/utils/event-bus";
import kavkomPhone, { KAVKOM_EVENTS } from "@/utils/kavkom-phone";
import { FETCH_PROSPECT_INTERACTIONS } from "@/actions/project/prospect/interaction";

/**
 * Global Kavkom softphone widget.
 *
 * Mounted once in the main layout, it starts the single SIP registration of
 * the session (see resources/js/utils/kavkom-phone.js) and shows every
 * incoming call, whichever page the agent is on. It also identifies the
 * caller (CRM prospect lookup by phone number) and writes the call in the
 * prospect history as it rings, is answered and ends.
 */
export default {
    data() {
        return {
            /** Prospect matched by the incoming caller number. */
            prospect: null,
            /** Interaction opened for the ongoing inbound call. */
            interactionId: null,
            /**
             * Number of the call the lookup/logging results belong to. Only
             * replaced by the next incoming call, so a late response of a
             * finished call can never be applied to the current one.
             */
            activeCallNumber: "",
            connectedAt: null,
            now: Date.now(),
            timer: null,
            errorMessage: "",
            errorTimer: null,
            ringtoneContext: null,
            ringtoneTimer: null,
            ringtoneOscillators: [],
        };
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters(["lines"]),

        phone() {
            return kavkomPhone.state;
        },

        /**
         * A configured Kavkom "Line" assigned to this agent is enough to
         * know the SIP identity is ready (same rule as the interaction
         * slide). Nothing is registered for the other users.
         */
        kavkomConfigured() {
            return this.lines.some(
                (line) =>
                    line.operator === "kavkom" &&
                    String(line.user_id) === String(this.user?.id)
            );
        },

        isInCall() {
            return this.phone.status === "in-call";
        },

        isInboundCall() {
            return this.phone.callDirection === "inbound";
        },

        /** A ringing call the CRM did not start itself. */
        incomingCall() {
            return (
                this.phone.status === "ringing" &&
                this.isInboundCall &&
                !this.phone.callAutoAnswered
            );
        },

        callerName() {
            return this.prospect?.full_name || this.phone.callDisplayName || "";
        },

        duration() {
            const startedAt = this.connectedAt || this.phone.callStartedAt;

            if (!startedAt) {
                return "00:00";
            }

            const seconds = Math.max(
                0,
                Math.floor((this.now - startedAt) / 1000)
            );

            return `${String(Math.floor(seconds / 60)).padStart(2, "0")}:${String(
                seconds % 60
            ).padStart(2, "0")}`;
        },
    },

    watch: {
        kavkomConfigured: {
            immediate: true,
            handler(configured) {
                if (configured) {
                    kavkomPhone.bootstrap();
                }
            },
        },

        incomingCall(ringing) {
            ringing ? this.playRingtone() : this.stopRingtone();
        },

        isInCall(active) {
            if (active) {
                this.connectedAt = this.phone.callStartedAt || Date.now();
                this.startDurationTimer();
            } else {
                this.stopDurationTimer();
            }
        },
    },

    created() {
        // The history events of a call are posted one after the other, so
        // the answer/hangup always update the interaction the ringing event
        // opened, even when the agent answers within milliseconds.
        this.logChain = Promise.resolve();
    },

    mounted() {
        kavkomPhone.setRemoteAudio(this.$refs.remoteAudio);
        this.subscribe();
    },

    beforeUnmount() {
        this.stopRingtone();
        this.stopDurationTimer();
        this.unsubscribe();
    },

    methods: {
        subscribe() {
            EventBus.on(KAVKOM_EVENTS.INCOMING_CALL, this.onIncomingCall);
            EventBus.on(KAVKOM_EVENTS.CALL_ANSWERED, this.onCallAnswered);
            EventBus.on(KAVKOM_EVENTS.CALL_HANGUP, this.onCallHangup);
            EventBus.on(KAVKOM_EVENTS.CALL_FAILED, this.onCallFailed);
            EventBus.on(KAVKOM_EVENTS.CONNECTION_ERROR, this.onConnectionError);
        },

        unsubscribe() {
            EventBus.off(KAVKOM_EVENTS.INCOMING_CALL, this.onIncomingCall);
            EventBus.off(KAVKOM_EVENTS.CALL_ANSWERED, this.onCallAnswered);
            EventBus.off(KAVKOM_EVENTS.CALL_HANGUP, this.onCallHangup);
            EventBus.off(KAVKOM_EVENTS.CALL_FAILED, this.onCallFailed);
            EventBus.off(KAVKOM_EVENTS.CONNECTION_ERROR, this.onConnectionError);
        },

        onIncomingCall({ direction, number }) {
            this.clearError();
            this.prospect = null;
            this.interactionId = null;
            this.connectedAt = null;
            this.activeCallNumber = number || "";

            if (direction !== "inbound") {
                return;
            }

            // The 'ringing' event is also the caller identification: the
            // response carries the matching prospect, if any.
            this.logCall("ringing", number);
        },

        onCallAnswered({ direction, number }) {
            if (direction === "inbound") {
                this.logCall("answered", number);
            }
        },

        onCallHangup({ direction, number, missed, declined, durationMs }) {
            if (direction !== "inbound") {
                return;
            }

            this.logCall(missed ? "missed" : "hangup", number);

            // A rejected call is a missed call for the prospect history,
            // but the agent knows he rejected it: no alert for that one.
            if (missed && !declined) {
                flashWarning({
                    title: "Kavkom",
                    body: `Appel manqué${number ? " de " + number : ""}.`,
                    duration: 8000,
                });
            }

            // The call is closed in the history: refresh the open prospect
            // file so the agent sees it immediately.
            this.refreshOpenProspectInteractions(this.prospect);

            console.log("[Kavkom][Debug] Inbound call ended.", {
                number,
                missed,
                durationMs,
            });
        },

        onCallFailed({ message } = {}) {
            this.showError(message);
        },

        onConnectionError(message) {
            this.showError(message);
        },

        /**
         * History one step of the call in the prospect file. The backend
         * answers with the matched prospect (first call) and with the
         * interaction id, which is sent back on every following event so a
         * single call produces a single interaction.
         */
        logCall(status, number) {
            if (!number) {
                return;
            }

            this.logChain = this.logChain
                .catch(() => {})
                .then(() => this.postCallStatus(status, number));
        },

        async postCallStatus(status, number) {
            const isCurrentCall = () => number === this.activeCallNumber;

            try {
                const { data } = await ApiService.post(
                    "settings/kavkom/incoming",
                    {
                        number,
                        status,
                        interaction_id: isCurrentCall()
                            ? this.interactionId
                            : null,
                    }
                );

                // A response arriving after the call ended (or after the
                // next one started) must not overwrite the current state.
                if (!isCurrentCall()) {
                    return;
                }

                if (data.interaction_id) {
                    this.interactionId = data.interaction_id;
                }

                if (data.prospect) {
                    this.prospect = {
                        ...data.prospect,
                        project: data.project || this.prospect?.project,
                    };
                }

                if (!data.success && data.message) {
                    console.warn("[Kavkom]", data.message);
                }
            } catch (error) {
                console.warn(
                    "[Kavkom] Impossible d'historiser l'appel entrant.",
                    error.response?.data?.message || error.message
                );
            }
        },

        refreshOpenProspectInteractions(prospect) {
            const opened = store.getters["interactionProspect"];

            if (!prospect || !opened || opened.id !== prospect.id) {
                return;
            }

            store.dispatch(FETCH_PROSPECT_INTERACTIONS);
        },

        openProspect() {
            const slug = this.prospect?.project?.slug;

            if (!slug) {
                return;
            }

            this.$router.push({
                name: "prospect.show",
                params: {
                    project: slug,
                    prospect: this.prospect.id,
                },
            });
        },

        /** Retry the SIP registration after a connection failure. */
        reconnect() {
            this.clearError();
            kavkomPhone.refresh();
        },

        answer() {
            kavkomPhone.answer();
        },

        decline() {
            kavkomPhone.decline();
        },

        hangup() {
            kavkomPhone.hangup();
        },

        showError(message) {
            this.errorMessage = message;

            if (this.errorTimer) {
                window.clearTimeout(this.errorTimer);
            }

            this.errorTimer = window.setTimeout(() => {
                this.errorMessage = "";
                this.errorTimer = null;
            }, 8000);
        },

        clearError() {
            if (this.errorTimer) {
                window.clearTimeout(this.errorTimer);
                this.errorTimer = null;
            }

            this.errorMessage = "";
        },

        startDurationTimer() {
            this.stopDurationTimer();
            this.now = Date.now();
            this.timer = window.setInterval(() => {
                this.now = Date.now();
            }, 1000);
        },

        stopDurationTimer() {
            if (this.timer) {
                window.clearInterval(this.timer);
                this.timer = null;
            }
        },

        /**
         * Ringtone of an incoming call. Synthesized so no audio file is
         * needed, and stopped as soon as the agent answers, declines or the
         * caller hangs up.
         */
        playRingtone() {
            this.stopRingtone();

            const AudioContext =
                window.AudioContext || window.webkitAudioContext;

            if (!AudioContext) {
                return;
            }

            try {
                this.ringtoneContext = new AudioContext();
                this.ringtoneContext.resume?.();

                const ring = () => {
                    if (!this.ringtoneContext || !this.incomingCall) {
                        return;
                    }

                    const now = this.ringtoneContext.currentTime;
                    const gain = this.ringtoneContext.createGain();
                    gain.gain.setValueAtTime(0.0001, now);
                    gain.gain.exponentialRampToValueAtTime(0.08, now + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.7);
                    gain.connect(this.ringtoneContext.destination);

                    [440, 480].forEach((frequency) => {
                        const oscillator =
                            this.ringtoneContext.createOscillator();
                        oscillator.frequency.value = frequency;
                        oscillator.connect(gain);
                        oscillator.start(now);
                        oscillator.stop(now + 0.7);
                        this.ringtoneOscillators.push(oscillator);
                    });
                };

                ring();
                this.ringtoneTimer = window.setInterval(ring, 2000);
            } catch (error) {
                console.warn(
                    "[Kavkom] Impossible de jouer la sonnerie locale",
                    error
                );
                this.stopRingtone();
            }
        },

        stopRingtone() {
            if (this.ringtoneTimer) {
                window.clearInterval(this.ringtoneTimer);
                this.ringtoneTimer = null;
            }

            this.ringtoneOscillators.forEach((oscillator) => {
                try {
                    oscillator.stop();
                } catch (_) {
                    // L'oscillateur a déjà été arrêté.
                }
            });
            this.ringtoneOscillators = [];

            if (this.ringtoneContext) {
                this.ringtoneContext.close().catch(() => {});
                this.ringtoneContext = null;
            }
        },
    },
};
</script>
