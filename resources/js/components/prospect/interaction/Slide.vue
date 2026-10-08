<template>
    <slide
        :name="name"
        @open="fetchInteractions(), fetchSelectedProspects()"
        @closed="resetCloudtalkSlideState"
        :title="
            $t('prospect.interaction.title', {
                prospect: interactionTitleProspect
                    ? interactionTitleProspect.last_name
                    : '',
            })
        "
        :url="
            interactionTitleProspect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: interactionTitleProspect.id,
                      },
                  }
                : null
        "
        :left="true"
        :eager="true"
        icon="fa fa-phone"
        :style="{ width: slideWidth }"
    >
        <div class="hc-prospect-interaction-root">
            <cloudtalk-panel
                :displayed="cloudtalkPhoneDisplayed"
                :context-visible="cloudtalkCallContextVisible"
                :number="interaction.number"
                :calling="callingCloudtalk"
                :loading="cloudtalkLoading"
                :lookup-loading="cloudtalkLookupLoading"
                :lookup-number="cloudtalkLookup.number"
                :prospect="cloudtalkCallProspect"
                :prospect-name="cloudtalkProspectName"
                :project="project"
                :threads="cloudtalkLookupThreads"
                :creating-prospect="cloudtalkCreatingProspect"
                :can-create-prospect="can('all.prospect.add')"
                :messages-for-thread="cloudtalkMessagesForThread"
                :format-date="formatCloudtalkDate"
                :message-preview="messagePreview"
                @make-call="makeCloudtalkCall"
                @call-activity="displayCloudtalkPhoneFromIframe"
                @ringing-call="cloudtalkCallRinging"
                @outgoing-call="cloudtalkCallOutgoing"
                @call-ended="cloudtalkCallEnded"
                @hangup-call="cloudtalkCallHangup"
                @answered-call="cloudtalkCallAnswered"
                @contact-info="cloudtalkCallContactInfo"
                @create-prospect="createCloudtalkProspect"
                @context-panel-visible="
                    cloudtalkContextPanelVisible = $event
                "
            />

            <tab-layout :count="2" :tab="tab" class="hc-flex-1">
                <template #1 v-if="interactionProspect">
                    <interaction-list
                        :interaction-prospect="interactionProspect"
                        :prospect-interactions="prospectInteractions"
                        :adding-history="addingHistory"
                        :can-add="can('all.prospect.interaction.add')"
                        @edit-phone="(tab = 1), (frameTab = 3)"
                        @edit-mobile="(tab = 1), (frameTab = 4)"
                        @call-telephone="interactionViaTelephone"
                        @call-aircall="interactionViaAircall"
                        @call-ringover="interactionViaRingover"
                        @call-cloudtalk="interactionViaCloudtalk"
                        @add-history="addHistory"
                    />
                </template>

                <!-- List of interaction -->
                <template #2>
                    <interaction-frames
                        v-model:phone-number="phoneNumber"
                        v-model:mobile-phone-number="mobilePhoneNumber"
                        :interaction-prospect="interactionProspect"
                        :interaction="interaction"
                        :tab="tab"
                        :frame-tab="frameTab"
                        :aircall-tab="aircallTab"
                        :aircall-webhook-url="aircallWebhookURL"
                        :updating-phone-number="updatingPhoneNumber"
                        :updating-mobile-phone-number="updatingMobilePhoneNumber"
                        @back="tab = 0"
                        @aircall-setting="aircallTab = 1"
                        @aircall-back-setting="aircallTab = 0"
                        @copy-aircall-webhook="copyAircallWebhookURLToClipboard"
                        @ringover-setting="ringoverSetting"
                        @update-interaction="updateInteraction"
                        @next-interaction="nextInteraction"
                        @prospect-selected="setInteractionProspect"
                        @update-phone-number="updateProspectPhoneNumber"
                        @update-mobile-phone-number="
                            updateProspectMobilePhoneNumber
                        "
                        @back-cloudtalk="backFromCloudtalk"
                    />
                </template>
            </tab-layout>
        </div>
    </slide>
</template>

<style>
.hc-prospect-interaction-root {
    position: relative;
    width: 100%;
    height: 100%;
    overflow-y: hidden;
}

.hc-prospect-interaction-item {
    padding: 4px 0 !important;
    text-decoration: none;
}
.hc-prospect-interaction-item-number {
    font-size: 11px;
    color: #999999;
}

.hc-prospect-interaction-cloudtalk-panel {
    --cloudtalk-phone-width: 440px;
    --cloudtalk-context-width: 320px;
    position: absolute;
    top: 42px;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 5;
    display: flex;
    background: #ffffff;
    opacity: 0;
    pointer-events: none;
    visibility: hidden;
}

.hc-prospect-interaction-cloudtalk-panel.with-context {
    right: auto;
    width: var(--cloudtalk-phone-width);
}

.hc-prospect-interaction-cloudtalk-panel.context-open {
    width: calc(
        var(--cloudtalk-phone-width) + var(--cloudtalk-context-width)
    );
}

.hc-prospect-interaction-cloudtalk-panel.visible {
    opacity: 1;
    pointer-events: auto;
    visibility: visible;
}

.hc-prospect-interaction-cloudtalk-phone {
    flex: 1 1 auto;
    min-width: 0;
}

.hc-prospect-interaction-cloudtalk-panel.with-context
    .hc-prospect-interaction-cloudtalk-phone {
    flex: 0 0 var(--cloudtalk-phone-width);
    width: var(--cloudtalk-phone-width);
}

.hc-prospect-interaction-cloudtalk-context-toggle {
    position: absolute;
    top: 50%;
    right: 0;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 42px;
    padding: 0;
    border: 1px solid #dddddd;
    border-right: 0;
    border-radius: 4px 0 0 4px;
    background: #ffffff;
    color: #555555;
    cursor: pointer;
    transform: translateY(-50%);
}

.hc-prospect-interaction-cloudtalk-context-toggle:hover {
    background: #f5f5f5;
}

.hc-prospect-interaction-cloudtalk-panel.context-open
    .hc-prospect-interaction-cloudtalk-context-toggle {
    right: var(--cloudtalk-context-width);
}

.hc-prospect-interaction-cloudtalk-context {
    display: flex;
    flex: 0 0 var(--cloudtalk-context-width);
    flex-direction: column;
    gap: 14px;
    height: 100%;
    overflow: auto;
    padding: 14px;
    border-left: 1px solid #e5e5e5;
    background: #fafafa;
}

.hc-prospect-interaction-cloudtalk-context-loading {
    position: relative;
    min-height: 80px;
}

.hc-prospect-interaction-cloudtalk-context-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}

.hc-prospect-interaction-cloudtalk-context-title {
    font-size: 16px;
    font-weight: 700;
    line-height: 1.2;
    color: #222222;
    overflow-wrap: anywhere;
}

.hc-prospect-interaction-cloudtalk-context-subtitle {
    margin-top: 3px;
    font-size: 12px;
    color: #777777;
    overflow-wrap: anywhere;
}

.hc-prospect-interaction-cloudtalk-context-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    color: #555555;
    text-decoration: none;
}

.hc-prospect-interaction-cloudtalk-context-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.hc-prospect-interaction-cloudtalk-context-heading {
    font-size: 11px;
    font-weight: 700;
    color: #777777;
    text-transform: uppercase;
}

.hc-prospect-interaction-cloudtalk-context-line {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    font-size: 12px;
    color: #333333;
}

.hc-prospect-interaction-cloudtalk-context-line span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-prospect-interaction-cloudtalk-context-empty {
    font-size: 12px;
    color: #999999;
}

.hc-prospect-interaction-cloudtalk-context-button {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 34px;
    padding: 8px 10px;
    border: 1px solid #dddddd;
    border-radius: 4px;
    background: #ffffff;
    color: #333333;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
}

.hc-prospect-interaction-cloudtalk-context-button:hover {
    background: #f5f5f5;
}

.hc-prospect-interaction-cloudtalk-context-button:disabled {
    color: #999999;
    cursor: default;
}

.hc-prospect-interaction-cloudtalk-thread {
    padding: 10px;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    background: #ffffff;
}

.hc-prospect-interaction-cloudtalk-thread-title {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    font-size: 13px;
    font-weight: 700;
    color: #222222;
}

.hc-prospect-interaction-cloudtalk-thread-title > span:nth-child(2) {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-prospect-interaction-cloudtalk-thread-title small {
    font-size: 11px;
    font-weight: 600;
    color: #777777;
}

.hc-prospect-interaction-cloudtalk-thread-color {
    display: inline-block;
    flex: 0 0 10px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.hc-prospect-interaction-cloudtalk-message {
    margin-top: 9px;
    padding-top: 9px;
    border-top: 1px solid #eeeeee;
}

.hc-prospect-interaction-cloudtalk-message-meta {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    font-size: 10px;
    color: #999999;
}

.hc-prospect-interaction-cloudtalk-message-body {
    margin-top: 4px;
    display: -webkit-box;
    overflow: hidden;
    color: #333333;
    font-size: 12px;
    line-height: 1.35;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

.hc-prospect-interaction-cloudtalk-message-users {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 5px;
    font-size: 11px;
    color: #777777;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";
import ProspectInteractionService from "@/apis/project/prospect/interaction";

import { OPEN_MODAL } from "@/actions/modal";
import {
    ADD_PROSPECT,
    SET_PROSPECT,
    UPDATE_PROSPECT,
} from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { OPEN_LEFT_SLIDE } from "@/actions/slide";
import {
    FETCH_PROSPECT_INTERACTIONS,
    ADD_PROSPECT_INTERACTION,
    UPDATE_PROSPECT_INTERACTION,
    SET_PROSPECT_INTERACTION_TAB,
    SET_PROSPECT_INTERACTION_FRAME_TAB,
} from "@/actions/project/prospect/interaction";
import {
    MAKE_CLOUDTALK_CALL,
    LOOKUP_CLOUDTALK_CALL,
    FETCH_CLOUDTALK_CALL_HISTORY,
} from "@/actions/project/line";

// Components
import CloudtalkPanel from "./components/CloudtalkPanel.vue";
import InteractionFrames from "./components/InteractionFrames.vue";
import InteractionList from "./components/InteractionList.vue";

export default {
    components: {
        CloudtalkPanel,
        InteractionFrames,
        InteractionList,
    },

    data() {
        return {
            name: "prospect-manage-interactions",
            tab: 0,
            frameTab: 0,
            aircallTab: 0,
            currentProspectIndex: 0,
            selectedProspects: [],
            phoneNumber: null,
            mobilePhoneNumber: null,
            interaction: this.newInteraction(),
            addingHistory: false,
            fetchingInteraction: false,
            updatingPhoneNumber: false,
            updatingMobilePhoneNumber: false,
            callingCloudtalk: false,
            cloudtalkWaitingForEvent: false,
            cloudtalkLoginRequired: false,
            cloudtalkPhoneVisible: false,
            cloudtalkContextPanelVisible: false,
            cloudtalkEventTimeout: null,
            cloudtalkCreatingProspect: false,
            cloudtalkLookup: {
                number: null,
                prospect: null,
                threads: [],
                messages: [],
            },
            cloudtalkLookupLoading: false,
            cloudtalkLookupRequest: 0,
            cloudtalkHistoryTimeouts: [],
        };
    },

    created() {
        store.commit(SET_PROSPECT_INTERACTION_TAB, 0);
        store.commit(SET_PROSPECT_INTERACTION_FRAME_TAB, 0);
    },

    beforeUnmount() {
        this.clearCloudtalkEventTimeout();
        this.clearCloudtalkHistoryTimeout();
    },

    methods: {
        newInteraction() {
            return {
                source: "telephone",
                number: "",
                status: "new",
                data: {},
            };
        },

        async fetchInteractions() {
            if (this.interactionProspect) {
                this.phoneNumber = this.interactionProspect.phone_number;
                this.mobilePhoneNumber =
                    this.interactionProspect.mobile_phone_number;

                this.fetchingInteraction = true;

                try {
                    await store.dispatch(FETCH_PROSPECT_INTERACTIONS);
                } finally {
                    this.fetchingInteraction = false;
                }
            } else if (this.prospectsSelected.length == 0) {
                this.tab = 1;
                this.frameTab = 2;
            }
        },

        addHistory() {
            this.addingHistory = true;
            this.interaction = this.newInteraction();
            this.interaction.source = "telephone";
            this.interaction.number = "";
            this.interaction.from_number = this.user.mobile_phone_number
                ? this.user.mobile_phone_number
                : this.user.phone_number
                ? this.user.phone_number
                : "";
            this.addInteraction().finally(() => {
                this.addingHistory = false;
            });
        },

        interactionViaTelephone(number) {
            this.interaction = this.newInteraction();
            this.interaction.source = "telephone";
            this.interaction.number = number;
            this.addInteraction();
        },

        interactionViaAircall(number) {
            this.tab = 1;
            this.frameTab = 0;
            this.interaction = this.newInteraction();
            this.interaction.source = "aircall";
            this.interaction.number = number;
            this.addInteraction();
        },

        interactionViaRingover(number) {
            this.tab = 1;
            this.frameTab = 1;
            this.interaction = this.newInteraction();
            this.interaction.source = "ringover";
            this.interaction.number = number;
            this.addInteraction();
        },

        async interactionViaCloudtalk(number) {
            this.tab = 1;
            this.frameTab = 5;
            this.cloudtalkPhoneVisible = true;
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            this.interaction = this.newInteraction();
            this.interaction.source = "cloudtalk";
            this.interaction.number = number;
            await this.addInteraction();
            this.fetchCloudtalkCallContext({ external_number: number });
            this.makeCloudtalkCall();
        },

        async makeCloudtalkCall() {
            if (this.callingCloudtalk || !this.interaction.number) {
                return;
            }

            this.callingCloudtalk = true;
            this.cloudtalkPhoneVisible = true;
            this.cloudtalkWaitingForEvent = true;
            this.cloudtalkLoginRequired = false;
            this.clearCloudtalkEventTimeout();

            try {
                if (!this.interaction.id && this.interactionProspect) {
                    await this.addInteraction();
                }

                const params = {
                    number: this.interaction.number,
                };

                if (this.cloudtalkLine) {
                    params.line_id = this.cloudtalkLine.id;
                }

                if (this.interaction.id) {
                    params.interaction_id = this.interaction.id;
                }

                const response = await store.dispatch(
                    MAKE_CLOUDTALK_CALL,
                    params
                );

                const callId = this.cloudtalkCallId(response, false);
                this.interaction.status = "initiated";
                this.interaction.data = {
                    ...(this.interaction.data || {}),
                    cloudtalk: response.responseData,
                    ...(callId ? { id: callId, call_id: callId } : {}),
                };

                if (response.interaction) {
                    this.interaction = response.interaction;
                    store.commit(UPDATE_PROSPECT_INTERACTION, response.interaction);
                } else {
                    await this.updateInteraction();
                }

                if (this.cloudtalkWaitingForEvent) {
                    this.startCloudtalkEventTimeout();
                }
            } catch (error) {
                this.cloudtalkWaitingForEvent = false;
                this.clearCloudtalkEventTimeout();

                const status =
                    error.response &&
                    error.response.data &&
                    error.response.data.status
                        ? error.response.data.status
                        : error.response
                        ? error.response.status
                        : null;

                const message =
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                        ? error.response.data.message
                        : "Impossible de lancer l'appel CloudTalk.";

                if (status == 403) {
                    this.showCloudtalkLogin();
                }

                this.interaction.status = "failed";
                this.interaction.data = {
                    ...(this.interaction.data || {}),
                    error: message,
                };
                this.updateInteraction();

                flashError({
                    title: "CloudTalk",
                    body: message,
                    duration: 7000,
                });
            } finally {
                this.callingCloudtalk = false;
            }
        },

        showCloudtalkPhone() {
            this.clearCloudtalkEventTimeout();
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            this.cloudtalkPhoneVisible = true;
            this.tab = 1;
            this.frameTab = 5;
        },

        displayCloudtalkPhoneFromIframe(payload = {}) {
            this.prepareCloudtalkInteractionFromIframe(payload.properties || {});

            if (!this.leftSlideOpen(this.name)) {
                store.commit(OPEN_LEFT_SLIDE, this.name);
            }

            this.showCloudtalkPhone();

            setTimeout(() => {
                this.showCloudtalkPhone();
            }, 0);
        },

        prepareCloudtalkInteractionFromIframe(callInfos = {}) {
            this.syncIncomingCloudtalkProspect(callInfos);

            const callId =
                callInfos.call_uuid || callInfos.call_id || callInfos.id;
            const currentCallId =
                this.interaction &&
                this.interaction.data &&
                (this.interaction.data.id ||
                    this.interaction.data.call_uuid ||
                    this.interaction.data.cdr_id ||
                    this.interaction.data.call_id);

            if (
                this.interaction &&
                this.interaction.source == "cloudtalk" &&
                (!callId || !currentCallId || currentCallId == callId)
            ) {
                this.fetchCloudtalkCallContext(callInfos);
                return;
            }

            this.interaction = this.newInteraction();
            this.interaction.source = "cloudtalk";
            this.interaction.number = this.cloudtalkCallNumber(callInfos);
            this.interaction.from_number = callInfos.internal_number || "";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
        },

        showCloudtalkLogin() {
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = true;
            this.cloudtalkPhoneVisible = false;
            this.tab = 1;
            this.frameTab = 5;
        },

        backFromCloudtalk() {
            this.resetCloudtalkSlideState();
        },

        resetCloudtalkSlideState() {
            if (this.interaction.source != "cloudtalk" && this.frameTab != 5) {
                return;
            }

            this.cloudtalkPhoneVisible = false;
            this.cloudtalkContextPanelVisible = false;
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            this.clearCloudtalkEventTimeout();
            this.tab = 0;
            this.frameTab = 0;
        },

        startCloudtalkEventTimeout() {
            this.clearCloudtalkEventTimeout();

            this.cloudtalkEventTimeout = setTimeout(() => {
                if (!this.cloudtalkWaitingForEvent) {
                    return;
                }

                this.cloudtalkWaitingForEvent = false;
                this.showCloudtalkLogin();

                flashWarning({
                    title: "CloudTalk",
                    body: "Aucun evenement CloudTalk recu. Connectez-vous dans CloudTalk puis relancez l'appel.",
                    duration: 7000,
                });
            }, 25000);
        },

        clearCloudtalkEventTimeout() {
            if (this.cloudtalkEventTimeout) {
                clearTimeout(this.cloudtalkEventTimeout);
                this.cloudtalkEventTimeout = null;
            }
        },

        cloudtalkCallRinging(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "ringing";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
        },

        cloudtalkCallOutgoing(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "initiated";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
        },

        cloudtalkCallAnswered(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "answered";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
        },

        cloudtalkCallHangup(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "hangup";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
            if (this.interactionProspect) {
                this.nextInteraction();
            }
        },

        cloudtalkCallEnded(callInfos) {
            this.showCloudtalkPhone();
            this.interaction.status = "ended";
            this.setCloudtalkCallData(callInfos);
            this.fetchCloudtalkCallContext(callInfos);
            this.updateInteraction();
            this.scheduleCloudtalkHistorySync(
                callInfos,
                1,
                this.cloneInteraction(this.interaction),
                this.interactionProspect ? this.interactionProspect.id : null
            );
            if (this.interactionProspect) {
                this.nextInteraction();
            }
        },

        cloudtalkCallContactInfo(callInfos) {
            this.fetchCloudtalkCallContext(callInfos);
        },

        async createCloudtalkProspect() {
            const number =
                this.cloudtalkLookup && this.cloudtalkLookup.number
                    ? this.cloudtalkLookup.number
                    : this.interaction.number;

            if (!number || this.cloudtalkCreatingProspect) {
                return;
            }

            this.cloudtalkCreatingProspect = true;

            try {
                const prospect = await store.dispatch(ADD_PROSPECT, {
                    mobile_phone_number: number,
                });

                this.setActiveInteractionProspect(prospect);
                this.cloudtalkLookup = {
                    ...this.cloudtalkLookup,
                    number,
                    numberKey: this.normalizePhone(number),
                    prospect,
                    threads: [],
                    messages: [],
                    resolved: true,
                };

                if (
                    this.interaction &&
                    this.interaction.source == "cloudtalk" &&
                    !this.interaction.id
                ) {
                    try {
                        await this.addInteraction();
                    } catch (error) {
                        this.interaction.data = {
                            ...(this.interaction.data || {}),
                            error: error.message,
                        };
                    }
                }
            } finally {
                this.cloudtalkCreatingProspect = false;
            }
        },

        setCloudtalkCallData(callInfos = {}) {
            const callId = this.cloudtalkCallId(callInfos);
            const number = this.cloudtalkCallNumber(callInfos, true);
            const recordingUrl =
                callInfos.recording_url ||
                callInfos.recording ||
                callInfos.recording_link ||
                "";

            if (number) {
                this.interaction.number = number;
            }

            if (callInfos.started_at || callInfos.startedAt) {
                this.interaction.started_at =
                    callInfos.started_at || callInfos.startedAt;
            }

            if (callInfos.ended_at || callInfos.endedAt) {
                this.interaction.ended_at =
                    callInfos.ended_at || callInfos.endedAt;
            }

            this.interaction.data = {
                ...(this.interaction.data || {}),
                ...(callId ? { id: callId, call_id: callId } : {}),
                ...(callInfos.call_uuid
                    ? { call_uuid: callInfos.call_uuid }
                    : {}),
                ...(number ? { external_number: number } : {}),
                ...(callInfos.direction
                    ? { direction: callInfos.direction }
                    : {}),
                ...(recordingUrl ? { recording_url: recordingUrl } : {}),
                ...(callInfos.recorded !== undefined
                    ? { recorded: callInfos.recorded }
                    : {}),
            };
        },

        cloudtalkCallId(callInfos = {}, useFallback = false) {
            const responseData = callInfos.responseData || {};
            const data = callInfos.data || responseData.data || {};
            const call = callInfos.call || responseData.call || data.call || {};
            const cloudtalkCall =
                callInfos.cloudtalk_call ||
                responseData.cloudtalk_call ||
                data.cloudtalk_call ||
                {};

            return (
                callInfos.call_uuid ||
                callInfos.call_id ||
                callInfos.cdr_id ||
                callInfos.id ||
                data.call_uuid ||
                data.call_id ||
                data.cdr_id ||
                data.id ||
                call.call_uuid ||
                call.call_id ||
                call.cdr_id ||
                call.id ||
                cloudtalkCall.call_uuid ||
                cloudtalkCall.call_id ||
                cloudtalkCall.cdr_id ||
                cloudtalkCall.id ||
                responseData.call_uuid ||
                responseData.call_id ||
                responseData.cdr_id ||
                responseData.id ||
                (useFallback &&
                this.interaction &&
                this.interaction.data &&
                (this.interaction.data.call_id ||
                    this.interaction.data.cdr_id ||
                    this.interaction.data.id)
                    ? this.interaction.data.call_id ||
                      this.interaction.data.cdr_id ||
                      this.interaction.data.id
                    : "") ||
                ""
            );
        },

        /**
         * Number already attached to the call handled by the interaction.
         *
         * Returns an empty string when the event belongs to another call, so a
         * new call can still bring its own number.
         */
        knownCloudtalkCallNumber(callInfos = {}) {
            if (
                !this.interaction ||
                this.interaction.source != "cloudtalk" ||
                !this.interaction.number
            ) {
                return "";
            }

            const currentCallId =
                (this.interaction.data &&
                    (this.interaction.data.call_id ||
                        this.interaction.data.cdr_id ||
                        this.interaction.data.id)) ||
                "";
            const eventCallId = this.cloudtalkCallId(callInfos, false);

            if (currentCallId && eventCallId && currentCallId != eventCallId) {
                return "";
            }

            return this.interaction.number;
        },

        cloudtalkCallNumber(callInfos = {}, useFallback = false) {
            const known = this.knownCloudtalkCallNumber(callInfos);
            const candidate = this.cloudtalkCandidateNumber(callInfos);

            if (candidate) {
                // CloudTalk also reports its own numbers inside the call
                // events (internal agent number like "365811021001", caller
                // id, ...): while the event belongs to the call already
                // started, the number chosen when the call began wins.
                if (
                    known &&
                    this.normalizePhone(known) != this.normalizePhone(candidate)
                ) {
                    return known;
                }

                return candidate;
            }

            if (useFallback) {
                return (
                    (this.cloudtalkLookup && this.cloudtalkLookup.number) ||
                    known ||
                    ""
                );
            }

            return "";
        },

        cloudtalkCandidateNumber(callInfos = {}) {
            return (
                callInfos.external_number ||
                callInfos.customer_number ||
                callInfos.contact_phone ||
                callInfos.phone_number ||
                callInfos.number ||
                callInfos.from ||
                callInfos.to ||
                ""
            );
        },

        async fetchCloudtalkCallContext(callInfos = {}) {
            const number = this.cloudtalkCallNumber(callInfos, true);
            const numberKey = this.normalizePhone(number);
            const callId = this.cloudtalkCallId(callInfos, true);
            const previousLookup = { ...(this.cloudtalkLookup || {}) };
            const sameResolvedCall =
                previousLookup.prospect &&
                previousLookup.callId &&
                callId &&
                previousLookup.callId == callId;

            if (!numberKey || numberKey.length < 6) {
                return;
            }

            if (
                this.cloudtalkLookup.numberKey == numberKey &&
                (this.cloudtalkLookup.resolved || this.cloudtalkLookupLoading)
            ) {
                return;
            }

            const request = ++this.cloudtalkLookupRequest;
            this.cloudtalkLookupLoading = true;
            this.cloudtalkLookup = {
                ...this.cloudtalkLookup,
                number,
                numberKey,
                callId,
                resolved: false,
            };

            try {
                const data = await store.dispatch(LOOKUP_CLOUDTALK_CALL, {
                    number,
                });

                if (request != this.cloudtalkLookupRequest) {
                    return;
                }

                if (!data.prospect && sameResolvedCall) {
                    this.cloudtalkLookup = {
                        ...previousLookup,
                        number: previousLookup.number || data.number || number,
                        numberKey: previousLookup.numberKey || numberKey,
                        callId,
                        resolved: true,
                    };
                    return;
                }

                this.cloudtalkLookup = {
                    number: data.number || number,
                    numberKey,
                    callId,
                    prospect: data.prospect || null,
                    threads: data.threads || [],
                    messages: data.messages || [],
                    resolved: true,
                };

                if (data.prospect) {
                    this.setActiveInteractionProspect(data.prospect);

                    if (
                        this.interaction &&
                        this.interaction.source == "cloudtalk" &&
                        !this.interaction.id
                    ) {
                        try {
                            await this.addInteraction();
                        } catch (error) {
                            this.interaction.data = {
                                ...(this.interaction.data || {}),
                                error: error.message,
                            };
                        }
                    }
                }
            } catch (error) {
                if (request == this.cloudtalkLookupRequest) {
                    this.cloudtalkLookup = {
                        number,
                        numberKey,
                        callId,
                        prospect: null,
                        threads: [],
                        messages: [],
                        resolved: true,
                    };
                }
            } finally {
                if (request == this.cloudtalkLookupRequest) {
                    this.cloudtalkLookupLoading = false;
                }
            }
        },

        scheduleCloudtalkHistorySync(
            callInfos = {},
            attempt = 1,
            interaction = null,
            prospectId = null
        ) {
            const timeout = setTimeout(() => {
                this.cloudtalkHistoryTimeouts =
                    this.cloudtalkHistoryTimeouts.filter(
                        (item) => item != timeout
                    );
                this.syncCloudtalkCallHistory(
                    callInfos,
                    attempt,
                    interaction,
                    prospectId
                );
            }, attempt == 1 ? 3000 : 8000);

            this.cloudtalkHistoryTimeouts.push(timeout);
        },

        clearCloudtalkHistoryTimeout() {
            this.cloudtalkHistoryTimeouts.forEach((timeout) =>
                clearTimeout(timeout)
            );
            this.cloudtalkHistoryTimeouts = [];
        },

        async syncCloudtalkCallHistory(
            callInfos = {},
            attempt = 1,
            interaction = null,
            prospectId = null
        ) {
            const targetInteraction = interaction || this.interaction;
            const targetProspectId =
                prospectId ||
                (this.interactionProspect ? this.interactionProspect.id : null);

            if (
                !targetInteraction ||
                targetInteraction.source != "cloudtalk" ||
                !targetProspectId
            ) {
                return;
            }

            const number =
                this.cloudtalkCallNumber(callInfos, false) ||
                targetInteraction.number;
            const callId =
                this.cloudtalkCallId(callInfos, false) ||
                (targetInteraction.data || {}).call_id ||
                (targetInteraction.data || {}).cdr_id ||
                (targetInteraction.data || {}).id;
            const params = {
                number,
                call_id: callId,
                started_at: targetInteraction.started_at,
                ended_at: targetInteraction.ended_at,
                direction:
                    callInfos.direction ||
                    (targetInteraction.data || {}).direction ||
                    null,
            };

            if (this.cloudtalkLine) {
                params.line_id = this.cloudtalkLine.id;
            }

            try {
                const data = await store.dispatch(
                    FETCH_CLOUDTALK_CALL_HISTORY,
                    params
                );

                if (!data.call) {
                    if (attempt < 3) {
                        this.scheduleCloudtalkHistorySync(
                            callInfos,
                            attempt + 1,
                            targetInteraction,
                            targetProspectId
                        );
                    }
                    return;
                }

                const updatedInteraction = this.applyCloudtalkHistory(
                    data.call,
                    targetInteraction
                );

                if (updatedInteraction.id) {
                    const { data: savedInteraction } =
                        await ProspectInteractionService.update(
                            this.project.slug,
                            targetProspectId,
                            updatedInteraction.id,
                            updatedInteraction
                        );

                    if (
                        this.interaction &&
                        this.interaction.id == updatedInteraction.id
                    ) {
                        this.interaction = savedInteraction;
                    }

                    if (
                        this.interactionProspect &&
                        this.interactionProspect.id == targetProspectId
                    ) {
                        await this.fetchInteractions();
                    }
                }
            } catch (error) {
                if (attempt < 3) {
                    this.scheduleCloudtalkHistorySync(
                        callInfos,
                        attempt + 1,
                        targetInteraction,
                        targetProspectId
                    );
                }
            }
        },

        applyCloudtalkHistory(call, interaction = null) {
            const targetInteraction = interaction || this.interaction;
            const existingData = targetInteraction.data || {};

            targetInteraction.data = {
                ...existingData,
                call_id: call.id,
                cdr_id: call.id,
                recorded: call.recorded,
                recording_url: call.recording_url || existingData.recording_url,
                recording_link:
                    call.recording_link || existingData.recording_link,
                cloudtalk_call: call,
                cloudtalk_history: call.raw,
            };

            targetInteraction.number = call.number || targetInteraction.number || "";
            targetInteraction.from_number =
                call.from_number || targetInteraction.from_number || "";
            targetInteraction.started_at =
                call.started_at || targetInteraction.started_at || null;
            targetInteraction.ended_at =
                call.ended_at || targetInteraction.ended_at || null;
            targetInteraction.status = call.status || targetInteraction.status;

            return targetInteraction;
        },

        cloneInteraction(interaction) {
            if (!interaction) {
                return null;
            }

            return {
                ...interaction,
                data: {
                    ...(interaction && interaction.data ? interaction.data : {}),
                },
            };
        },

        normalizePhone(number) {
            return String(number || "").replace(/\D+/g, "");
        },

        isCloudtalkIncomingCall(callInfos = {}) {
            return ["inbound", "incoming"].includes(
                String(callInfos.direction || callInfos.type || "").toLowerCase()
            );
        },

        syncIncomingCloudtalkProspect(callInfos = {}) {
            if (!this.isCloudtalkIncomingCall(callInfos)) {
                return;
            }

            const number = this.cloudtalkCandidateNumber(callInfos);

            if (
                !number ||
                !this.interactionProspect ||
                !this.prospectMatchesPhone(this.interactionProspect, number)
            ) {
                this.setActiveInteractionProspect(null);
            }
        },

        prospectMatchesPhone(prospect, number) {
            return (
                this.phoneNumbersMatch(prospect.phone_number, number) ||
                this.phoneNumbersMatch(prospect.mobile_phone_number, number)
            );
        },

        phoneNumbersMatch(firstNumber, secondNumber) {
            const first = this.normalizePhone(firstNumber);
            const second = this.normalizePhone(secondNumber);

            if (!first || !second) {
                return false;
            }

            if (first === second) {
                return true;
            }

            const length = Math.min(first.length, second.length, 9);

            return (
                length >= 6 &&
                first.slice(-length) === second.slice(-length)
            );
        },

        setActiveInteractionProspect(prospect) {
            if (!prospect) {
                store.commit(SET_INTERACTION_PROSPECT, null);
                return;
            }

            store.commit(SET_PROSPECT, prospect);
            store.commit(SET_INTERACTION_PROSPECT, prospect);
        },

        cloudtalkMessagesForThread(thread) {
            return this.cloudtalkLookupMessages.filter(
                (message) => message.thread_id == thread.id
            );
        },

        messagePreview(body) {
            return String(body || "")
                .replace(/<br\s*\/?>/g, " ")
                .replace(/<[^>]+>/g, " ")
                .replace(/\s+/g, " ")
                .trim();
        },

        formatCloudtalkDate(value) {
            if (!value) {
                return "";
            }

            return new Date(value).toLocaleString();
        },

        /**
         *
         */
        async addInteraction() {
            this.addingInteraction = true;

            try {
                this.interaction = await store.dispatch(
                    ADD_PROSPECT_INTERACTION,
                    this.interaction
                );
            } finally {
                this.addingInteraction = false;
            }
        },

        /**
         *
         */
        async updateInteraction() {
            if (!this.interaction || !this.interactionProspect) {
                return;
            }

            if (!this.interaction.id) {
                this.interaction = await store.dispatch(
                    ADD_PROSPECT_INTERACTION,
                    this.interaction
                );
                return;
            }

            store.dispatch(UPDATE_PROSPECT_INTERACTION, this.interaction);
        },

        /**
         *
         */
        nextInteraction() {
            if (
                this.selectedProspects.length - 1 >
                this.this.currentProspectIndex
            ) {
                this.currentProspectIndex++;
            } else {
                this.setActiveInteractionProspect(null);
            }
        },

        /**
         *
         */
        setInteractionProspect(prospect) {
            this.setActiveInteractionProspect(prospect);
            this.tab = 0;
        },

        async updateProspectPhoneNumber() {
            this.updatingPhoneNumber = true;

            try {
                await store.dispatch(UPDATE_PROSPECT, {
                    id: this.interactionProspect.id,
                    phone_number: this.phoneNumber,
                });
            } finally {
                this.updatingPhoneNumber = false;
                this.tab = 0;
            }
        },

        async updateProspectMobilePhoneNumber() {
            this.updatingMobilePhoneNumber = true;

            try {
                await store.dispatch(UPDATE_PROSPECT, {
                    id: this.interactionProspect.id,
                    mobile_phone_number: this.mobilePhoneNumber,
                });
            } finally {
                this.updatingMobilePhoneNumber = false;
                this.tab = 0;
            }
        },

        async fetchSelectedProspects() {
            if (this.prospectsSelected.length == 0) {
                this.selectedProspects = [];
                return;
            }

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify({
                            ids: this.prospectsSelected,
                        }),
                        fields: "first_name,last_name,phone_number,mobile_phone_number",
                    },
                });
                this.selectedProspects = data.data.filter(
                    (prospect) =>
                        prospect.phone_number || prospect.mobile_phone_number
                );
            } finally {
                this.fetchingProspect = false;
            }
        },

        /**
         * Copy webhook URL to clipboard
         */
        copyAircallWebhookURLToClipboard() {
            navigator.clipboard.writeText(this.aircallWebhookURL);

            flashInfo({
                title: "Aircall",
                body: "URL Webhook Aircall copié",
                duration: 5000,
            });
        },

        ringoverSetting() {
            store.commit(OPEN_MODAL, "setting-ringover");
        },
    },

    watch: {
        async interactionProspect(newValue, oldValue) {
            if (newValue && this.leftSlideOpen(this.name)) {
                this.fetchInteractions();

                if (
                    newValue.phone_number &&
                    (!oldValue ||
                        oldValue.phone_number == this.interaction.number)
                ) {
                    this.interaction.number = newValue.phone_number;
                } else if (newValue.mobile_phone_number) {
                    // never clear interaction.number: it is the number used
                    // to resolve the context of the call in progress.
                    this.interaction.number = newValue.mobile_phone_number;
                }
            }
        },

        selectedProspects() {
            this.currentProspectIndex = 0;
        },

        interactionTab() {
            this.tab = this.interactionTab;
        },

        interactionFrameTab() {
            this.frameTab = this.interactionFrameTab;
        },

        currentProspect(newValue) {
            if (newValue) {
                setTimeout(() => {
                    this.setActiveInteractionProspect(newValue);
                }, 1000);
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "interactionProspect",
            "prospectFullName",
            "prospectInteractions",
            "interactionTab",
            "interactionFrameTab",
            "prospectsSelected",
            "leftSlideOpen",
            "can",
            "lines",
        ]),

        currentProspect() {
            if (this.selectedProspects.length > this.currentProspectIndex) {
                return this.selectedProspects[this.currentProspectIndex];
            }

            return null;
        },

        interactionTitleProspect() {
            if (this.cloudtalkPhoneDisplayed && this.cloudtalkCallProspect) {
                return this.cloudtalkCallProspect;
            }

            return this.interactionProspect || this.cloudtalkCallProspect;
        },

        slideWidth() {
            if (this.cloudtalkPhoneDisplayed) {
                if (
                    this.cloudtalkCallContextVisible &&
                    this.cloudtalkContextPanelVisible
                ) {
                    return "720px";
                }

                return "400px";
            }

            if (this.tab == 1 && this.frameTab == 0) {
                return "395px";
            }

            return "350px";
        },

        /**
         * Webhook URL
         * for web service import
         */
        aircallWebhookURL: function () {
            return window.location.origin + "/webhook/aircall";
        },

        cloudtalkLoading() {
            return this.callingCloudtalk || this.cloudtalkWaitingForEvent;
        },

        cloudtalkPhoneDisplayed() {
            return (
                (this.cloudtalkPhoneVisible ||
                    this.cloudtalkLoginRequired ||
                    this.cloudtalkLoading) &&
                this.tab == 1 &&
                this.frameTab == 5
            );
        },

        cloudtalkCallContextVisible() {
            return (
                this.cloudtalkLookupLoading ||
                !!this.cloudtalkCallProspect ||
                (this.cloudtalkPhoneDisplayed &&
                    this.cloudtalkLookup &&
                    this.cloudtalkLookup.resolved &&
                    !!this.cloudtalkLookup.number)
            );
        },

        cloudtalkCallProspect() {
            return this.cloudtalkLookup && this.cloudtalkLookup.prospect
                ? this.cloudtalkLookup.prospect
                : null;
        },

        cloudtalkProspectName() {
            if (!this.cloudtalkCallProspect) {
                return "";
            }

            const name = [
                this.cloudtalkCallProspect.first_name,
                this.cloudtalkCallProspect.last_name,
            ]
                .filter((value) => value)
                .join(" ");

            return (
                name ||
                this.cloudtalkCallProspect.company_name ||
                this.cloudtalkCallProspect.email ||
                this.cloudtalkCallProspect.phone_number ||
                this.cloudtalkCallProspect.mobile_phone_number ||
                this.cloudtalkLookup.number ||
                ""
            );
        },

        cloudtalkLookupThreads() {
            return this.cloudtalkLookup && this.cloudtalkLookup.threads
                ? this.cloudtalkLookup.threads
                : [];
        },

        cloudtalkLookupMessages() {
            return this.cloudtalkLookup && this.cloudtalkLookup.messages
                ? this.cloudtalkLookup.messages
                : [];
        },

        cloudtalkLine() {
            return this.lines.find(
                (line) =>
                    line.operator === "cloudtalk" &&
                    this.user &&
                    line.user_id == this.user.id
            );
        },
    },
};
</script>
