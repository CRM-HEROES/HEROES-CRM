import EventBus from "@/utils/event-bus";
import ProspectService from "@/apis/project/prospect";
import store from "@/store";
import { OPEN_LEFT_SLIDE, CLOSE_SLIDE } from "@/actions/slide";

export default {
    data() {
        return { cloudtalkSequence: null, cloudtalkSequenceRequest: 0, cloudtalkSequenceIgnoredIds: [] };
    },

    created() {
        EventBus.on("cloudtalk:start-sequence", this.startCloudtalkSequence);
    },

    beforeUnmount() {
        EventBus.off("cloudtalk:start-sequence", this.startCloudtalkSequence);
        this.stopCloudtalkSequence("discard");
    },

    computed: {
        cloudtalkSequenceProspect() {
            return this.cloudtalkSequence?.prospects[this.cloudtalkSequence.index] || null;
        },

        cloudtalkSequenceNextProspect() {
            const sequence = this.cloudtalkSequence;
            return sequence?.prospects[sequence.index + 1] || null;
        },

        cloudtalkSequenceFiltering() {
            return !!this.cloudtalkSequence || this.cloudtalkSequenceIgnoredIds.length > 0;
        },
    },

    watch: {
        "project.slug"(slug, previousSlug) {
            if (slug !== previousSlug && this.cloudtalkSequence) {
                this.stopCloudtalkSequence("discard");
                this.cloudtalkPhoneVisible = false;
                this.tab = 0;
                this.frameTab = 0;
            }
        },
    },

    methods: {
        cloudtalkSequenceNumber(prospect) {
            return [prospect?.mobile_phone_number, prospect?.phone_number]
                .map(number => String(number || "").trim()).find(Boolean) || "";
        },

        cloudtalkSequenceName(prospect) {
            return [prospect?.first_name, prospect?.last_name].filter(Boolean).join(" ")
                || this.cloudtalkSequenceNumber(prospect);
        },

        openCloudtalkSequencePhone() {
            store.commit(OPEN_LEFT_SLIDE, this.name);
            this.cloudtalkPhoneVisible = true;
            this.tab = 1;
            this.frameTab = 5;
        },

        async startCloudtalkSequence({ projectSlug, prospectIds } = {}) {
            if (projectSlug !== this.project?.slug || !prospectIds?.length || !this.cloudtalkLine
                || !this.can('all.prospect.interaction.view') || !this.can('all.prospect.interaction.add')) return;

            // Reopening an existing sequence never sends another call request.
            const visibleCall = this.cloudtalkPhoneVisible && this.interaction.source === "cloudtalk"
                && ["initiated", "ringing", "answered"].includes(this.interaction.status);
            if (this.cloudtalkSequence || this.callingCloudtalk || this.cloudtalkWaitingForEvent || visibleCall) {
                this.openCloudtalkSequencePhone();
                return;
            }

            const request = ++this.cloudtalkSequenceRequest;
            const ids = [...new Set(prospectIds.map(String))];
            this.cloudtalkSequence = {
                projectSlug, prospects: [], index: 0, loading: true, awaitingChoice: false,
                stopAction: null, error: "", attempt: null, completedIds: [],
            };
            this.openCloudtalkSequencePhone();

            try {
                const prospects = new Map();
                for (let offset = 0; offset < ids.length; offset += 500) {
                    const { data } = await ProspectService.get(projectSlug, {
                        params: {
                            filters: JSON.stringify({ ids: ids.slice(offset, offset + 500) }),
                            fields: "first_name,last_name,phone_number,mobile_phone_number",
                            count: 500,
                        },
                    });
                    if (request !== this.cloudtalkSequenceRequest || projectSlug !== this.project?.slug) return;
                    for (const prospect of data.data) prospects.set(String(prospect.id), prospect);
                }
                const sequence = this.cloudtalkSequence;
                sequence.prospects = ids.map(id => prospects.get(id))
                    .filter(prospect => this.cloudtalkSequenceNumber(prospect));
                sequence.loading = false;
                if (!sequence.prospects.length) {
                    flashWarning({ title: "CloudTalk", body: this.$t("prospect.interaction.batch.no_numbers") });
                    this.stopCloudtalkSequence();
                    return;
                }
                if (sequence.prospects.length < ids.length) {
                    flashWarning({ title: "CloudTalk", body: this.$t("prospect.interaction.batch.skipped", {
                        count: ids.length - sequence.prospects.length,
                    }) });
                }
                return this.callCloudtalkSequenceProspect();
            } catch (error) {
                if (request !== this.cloudtalkSequenceRequest) return;
                flashError({ title: "CloudTalk", body: error.response?.data?.message
                    || this.$t("prospect.interaction.batch.load_error") });
                this.stopCloudtalkSequence();
            }
        },

        async callCloudtalkSequenceProspect() {
            const sequence = this.cloudtalkSequence;
            const prospect = this.cloudtalkSequenceProspect;
            if (!sequence || !prospect || this.callingCloudtalk || sequence.loading || sequence.stopAction) return;

            sequence.awaitingChoice = false;
            sequence.error = "";
            this.clearCloudtalkHistoryTimeout();
            sequence.attempt = {
                number: this.cloudtalkSequenceNumber(prospect), callIds: [], iframeCallId: null,
                activitySeen: false, started: false,
            };
            const attempt = sequence.attempt;
            this.cloudtalkLookupRequest++;
            this.cloudtalkLookupLoading = false;
            this.cloudtalkLookup = { number: null, prospect, threads: [], messages: [] };
            this.setActiveInteractionProspect(prospect);
            await this.$nextTick();
            if (sequence !== this.cloudtalkSequence || sequence.attempt !== attempt || sequence.stopAction) return;
            attempt.started = true;
            return this.interactionViaCloudtalk(attempt.number);
        },

        retryCloudtalkSequenceCall() {
            if (this.cloudtalkSequence?.awaitingChoice && !this.cloudtalkSequence.stopAction) {
                return this.callCloudtalkSequenceProspect();
            }
        },

        nextCloudtalkSequenceCall() {
            const sequence = this.cloudtalkSequence;
            if (!sequence?.awaitingChoice || sequence.stopAction || this.callingCloudtalk || !this.cloudtalkSequenceNextProspect) return;
            sequence.index++;
            return this.callCloudtalkSequenceProspect();
        },

        rememberIgnoredCloudtalkSequenceIds(ids) {
            this.cloudtalkSequenceIgnoredIds = [...new Set([
                ...this.cloudtalkSequenceIgnoredIds, ...ids.filter(Boolean).map(String),
            ])].slice(-256);
        },

        acceptCloudtalkSequenceEvent(callInfos = {}, eventName = "") {
            const id = this.cloudtalkCallId(callInfos, false);
            const callId = id ? String(id) : "";
            if (callId && this.cloudtalkSequenceIgnoredIds.includes(callId)) return false;
            const sequence = this.cloudtalkSequence;
            if (!sequence) {
                // An anonymous terminal event after stopping must not reopen the old call.
                return !!callId || !["hangup", "ended"].includes(eventName)
                    || this.interaction.source === "cloudtalk";
            }
            const attempt = sequence.attempt;
            if (!attempt?.started || sequence.awaitingChoice) return false;
            if (callId && sequence.completedIds.includes(callId)) return false;

            // Callback events describe the agent leg: their numbers and inferred
            // direction do not identify the prospect. Bind the first iframe UUID
            // to the locally requested attempt and use it only to reject other calls.
            if (callId && attempt.iframeCallId && !attempt.callIds.includes(callId)) return false;
            const terminal = ["hangup", "ended"].includes(eventName);
            if (terminal && !attempt.activitySeen && !attempt.callIds.includes(callId) && !callId) return false;
            if (["ringing", "dialing", "calling"].includes(eventName)) attempt.activitySeen = true;
            if (callId) {
                if (!attempt.iframeCallId) attempt.iframeCallId = callId;
                if (!attempt.callIds.includes(callId)) attempt.callIds.push(callId);
            }
            return true;
        },

        cloudtalkSequenceEvent(callInfos = {}, eventName = "") {
            if (!this.acceptCloudtalkSequenceEvent(callInfos, eventName)) return null;
            if (!this.cloudtalkSequence) return callInfos;

            this.clearCloudtalkEventTimeout();
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            const callId = this.cloudtalkCallId(callInfos, false);
            const number = this.cloudtalkSequence.attempt.number;
            // Only identity is taken from the iframe. Contact, number, direction
            // and outcome come from the local request and backend history.
            return {
                ...(callId ? { call_id: callId } : {}),
                ...(callInfos.call_uuid ? { call_uuid: callInfos.call_uuid } : {}),
                number,
                external_number: number,
                direction: "outgoing",
            };
        },

        completeCloudtalkSequenceCall(callInfos = {}, error = "") {
            const sequence = this.cloudtalkSequence;
            if (!sequence || sequence.awaitingChoice) return;
            sequence.error = error;
            sequence.awaitingChoice = true;
            sequence.completedIds = [...new Set([
                ...sequence.completedIds, ...(sequence.attempt?.callIds || []), ...this.currentCloudtalkCallIds(),
            ])];
            this.cloudtalkWaitingForEvent = false;
            this.clearCloudtalkEventTimeout();
            if (!error) {
                this.scheduleCloudtalkHistorySync(callInfos, 1,
                    this.cloneInteraction(this.interaction), this.cloudtalkSequenceProspect?.id);
            }
        },

        requestStopCloudtalkSequence(action = "stay") {
            if (this.cloudtalkSequence) this.cloudtalkSequence.stopAction = action;
        },

        cancelStopCloudtalkSequence() {
            const sequence = this.cloudtalkSequence;
            if (!sequence) return;
            sequence.stopAction = null;
            if (!sequence.loading && !sequence.awaitingChoice && !sequence.attempt?.started) {
                return this.callCloudtalkSequenceProspect();
            }
        },

        confirmStopCloudtalkSequence() {
            if (this.cloudtalkSequence?.stopAction) this.stopCloudtalkSequence(this.cloudtalkSequence.stopAction);
        },

        beforeCloudtalkSlideClose() {
            if (!this.cloudtalkSequence) return true;
            this.requestStopCloudtalkSequence("close");
            return false;
        },

        handleCloudtalkSlideClosed() {
            if (this.cloudtalkSequence) {
                if (this.cloudtalkSequence.projectSlug !== this.project?.slug) {
                    this.stopCloudtalkSequence("discard");
                    return;
                }
                // Also catch Escape or a global close that bypasses the slide button.
                this.openCloudtalkSequencePhone();
                this.requestStopCloudtalkSequence("close");
                return;
            }
            this.resetCloudtalkSlideState();
        },

        stopCloudtalkSequence(action = "stay") {
            const sequence = this.cloudtalkSequence;
            if (!sequence) return;
            this.rememberIgnoredCloudtalkSequenceIds([
                ...sequence.completedIds, ...(sequence.attempt?.callIds || []), ...this.currentCloudtalkCallIds(),
            ]);
            this.cloudtalkSequenceRequest++;
            this.cloudtalkCallRequest++;
            this.cloudtalkSequence = null;
            this.callingCloudtalk = false;
            this.cloudtalkWaitingForEvent = false;
            this.cloudtalkLoginRequired = false;
            this.cloudtalkPhoneVisible = false;
            this.cloudtalkContextPanelVisible = false;
            this.cloudtalkLookupRequest++;
            this.cloudtalkLookupLoading = false;
            this.cloudtalkLookup = { number: null, prospect: null, threads: [], messages: [] };
            this.cloudtalkReturnProspect = null;
            this.clearCloudtalkEventTimeout();
            this.clearCloudtalkHistoryTimeout();
            // Reset the local status as well, so a stopped sequence cannot leave a busy lock.
            this.interaction = this.newInteraction();
            if (action === "discard") return;
            this.showDefaultInteractionView();
            if (action === "close") store.commit(CLOSE_SLIDE, true);
        },
    },
};
