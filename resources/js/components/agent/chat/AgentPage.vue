<template>
    <div id="hc-agent-page">
        <agent-bar
            :agents="projectAgents"
            :selected-id="selectedAgentId"
            @select="selectAgent"
            @info="showInfo"
            @create="createAgent"
        />

        <div class="hc-agent-page-body">
            <conversation-history
                :conversations="conversations"
                :active-id="conversationId"
                :collapsed="historyCollapsed"
                :loading="historyLoading"
                @toggle="toggleHistory"
                @new="newConversation"
                @open="openConversation"
                @remove="removeConversation"
            />

            <chat-panel
                :agent="selectedAgent"
                :title="conversationTitle"
                :items="items"
                :busy="busy"
                :stalled="stalled"
                :error="error"
                :pending-actions="pendingActions"
                :deciding="deciding"
                :open-report-id="openedReportId"
                @send="send"
                @create="createAgent"
                @approve="approve"
                @reject="reject"
                @open-report="openReportById"
            />

            <report-panel
                v-if="canvasOpen"
                :report="openedReport"
                :loading="reportLoading"
                :error="reportError"
                @close="closeCanvas"
            />

            <agent-info
                :open="infoOpen"
                :agent="infoAgent"
                :loading="infoLoading"
                :error="infoError"
                @close="infoOpen = false"
                @edit="editAgent"
            />
        </div>
    </div>
</template>

<style>
#hc-agent-page {
    height: 100%;
    display: flex;
    flex-direction: column;
    background: #fff;
}

.hc-agent-page-body {
    flex: 1;
    min-height: 0;
    position: relative;
    display: flex;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import AgentService from "@/apis/agent";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import AgentBar from "./AgentBar.vue";
import AgentInfo from "./AgentInfo.vue";
import ChatPanel from "./ChatPanel.vue";
import ConversationHistory from "./ConversationHistory.vue";
import ReportPanel from "./ReportPanel.vue";
import { buildItems, newReports } from "./items";

const POLL_MS = 1500;
const STALLED_AFTER_S = 20;
const GIVE_UP_AFTER_S = 120;
const COLLAPSED_KEY = "hc-agent-history-collapsed";
const WIDE_SCREEN = 1100;

/**
 * Talking to the AI agents of a project: the agents on top, the history of the
 * conversations on the left, the conversation in the middle, and what the
 * agents produce (documents) on the right.
 */
export default {
    components: { AgentBar, AgentInfo, ChatPanel, ConversationHistory, ReportPanel },

    data() {
        return {
            selectedAgentId: null,

            conversations: [],
            historyLoading: false,
            historyCollapsed: false,

            conversationId: null,
            conversationTitle: "",
            messages: [],
            reports: [],
            pendingActions: [],
            deciding: {},

            busy: false,
            stalled: false,
            error: "",
            // Changes with every conversation opened: late answers of the previous one are ignored
            token: 0,
            timer: null,

            infoOpen: false,
            infoAgent: null,
            infoLoading: false,
            infoError: "",

            canvasOpen: false,
            openedReport: null,
            reportLoading: false,
            reportError: "",
        };
    },

    computed: {
        ...mapGetters(["project", "agents"]),

        // The agents that work in this project: the ones for every project and the ones bound to it
        projectAgents() {
            return this.agents.filter((agent) => !agent.project || agent.project.id === this.project.id);
        },

        selectedAgent() {
            return this.projectAgents.find((agent) => agent.id === this.selectedAgentId) || null;
        },

        items() {
            return buildItems(this.messages, this.reports);
        },

        openedReportId() {
            return this.openedReport ? this.openedReport.id : null;
        },
    },

    watch: {
        // Another project: everything on the page belongs to the previous one
        "project.id"() {
            this.start();
        },

        // The first agent created here is the one to talk to; one deleted no longer is
        projectAgents(list) {
            if (!list.some((agent) => agent.id === this.selectedAgentId)) {
                this.selectedAgentId = list.length ? list[0].id : null;
            }
        },
    },

    created() {
        try {
            this.historyCollapsed = localStorage.getItem(COLLAPSED_KEY) === "1";
        } catch (e) {
            // No storage: the history stays open
        }

        this.canvasOpen = window.innerWidth > WIDE_SCREEN;
        this.start();
    },

    beforeUnmount() {
        this.stop();
    },

    methods: {
        message(e) {
            return e?.response?.data?.message || this.$t("agent.error");
        },

        stop() {
            clearTimeout(this.timer);
            this.timer = null;
            this.token++;
        },

        schedule(fn) {
            clearTimeout(this.timer);
            this.timer = setTimeout(fn, POLL_MS);
        },

        // Load

        async start() {
            this.stop();
            this.resetConversation();
            this.selectedAgentId = null;

            try {
                await store.dispatch("FETCH_AGENTS");
            } catch (e) {
                this.error = this.message(e);
            }

            if (!this.selectedAgent && this.projectAgents.length) {
                this.selectedAgentId = this.projectAgents[0].id;
            }

            await this.loadHistory();
        },

        async loadHistory() {
            this.historyLoading = this.conversations.length === 0;

            try {
                const { data } = await AgentService.conversations(this.project.slug);
                this.conversations = data;
            } catch (e) {
                this.error = this.message(e);
            } finally {
                this.historyLoading = false;
            }
        },

        toggleHistory() {
            this.historyCollapsed = !this.historyCollapsed;

            try {
                localStorage.setItem(COLLAPSED_KEY, this.historyCollapsed ? "1" : "0");
            } catch (e) {
                // No storage: it only is not remembered
            }
        },

        // Conversations

        resetConversation() {
            this.conversationId = null;
            this.conversationTitle = "";
            this.messages = [];
            this.reports = [];
            this.pendingActions = [];
            this.busy = false;
            this.stalled = false;
            this.error = "";
        },

        newConversation() {
            this.stop();
            this.resetConversation();
        },

        selectAgent(id) {
            if (id === this.selectedAgentId) {
                return;
            }

            this.selectedAgentId = id;

            // A conversation belongs to one agent: choosing another one starts a new one
            this.newConversation();
        },

        async openConversation(id) {
            if (id === this.conversationId) {
                return;
            }

            this.stop();
            this.resetConversation();
            const token = this.token;

            try {
                const { data } = await AgentService.conversation(id);

                if (token !== this.token) {
                    return;
                }

                this.conversationId = data.id;
                this.apply(data);

                if (data.agent && this.projectAgents.some((agent) => agent.id === data.agent.id)) {
                    this.selectedAgentId = data.agent.id;
                }

                // An answer still being written: carry on following it
                if (data.run && !data.run.finished) {
                    this.busy = true;
                    this.follow(data.run.id, token);
                }
            } catch (e) {
                if (token === this.token) {
                    this.error = this.message(e);
                }
            }
        },

        async removeConversation(conversation) {
            if (!confirm(this.$t("agent.chat.confirm_delete_conversation", { title: conversation.title || "" }))) {
                return;
            }

            try {
                await AgentService.archiveConversation(conversation.id);

                if (conversation.id === this.conversationId) {
                    this.newConversation();
                }

                await this.loadHistory();
            } catch (e) {
                this.error = this.message(e);
            }
        },

        // What the server returns for a conversation
        apply(data) {
            this.conversationTitle = data.title || "";
            this.messages = data.messages;
            this.reports = data.reports || [];
            this.pendingActions = data.pending_actions || [];
        },

        // Sending

        async send(text) {
            const agent = this.selectedAgent;

            if (!agent || this.busy || !text.trim()) {
                return;
            }

            this.error = "";
            this.busy = true;
            this.messages = [...this.messages, { role: "user", kind: "text", text }];
            const token = this.token;

            try {
                const { data } = await AgentService.ask(agent.id, this.project.slug, text, this.conversationId);

                if (token !== this.token) {
                    return;
                }

                this.conversationId = data.conversation_id;
                this.follow(data.run_id, token);
            } catch (e) {
                if (token === this.token) {
                    this.busy = false;
                    this.error = this.message(e);
                }
            }
        },

        // Ask for the run until it is over, then show the whole conversation
        async follow(runId, token) {
            try {
                const { data } = await AgentService.run(runId);

                if (token !== this.token) {
                    return;
                }

                if (!data.finished) {
                    // Nobody picked the job up: the worker of the "agents" queue is not running
                    this.stalled = data.waiting_seconds > STALLED_AFTER_S;

                    if (data.waiting_seconds > GIVE_UP_AFTER_S) {
                        this.busy = false;
                        this.error = this.$t("agent.not_started");

                        return;
                    }

                    return this.schedule(() => this.follow(runId, token));
                }

                this.stalled = false;
                this.busy = false;

                if (data.error) {
                    this.error = data.error;
                }

                await this.refresh(token);
                this.loadHistory();
            } catch (e) {
                if (token === this.token) {
                    this.busy = false;
                    this.error = this.message(e);
                }
            }
        },

        async refresh(token = this.token) {
            if (!this.conversationId) {
                return;
            }

            const before = this.reports;
            const { data } = await AgentService.conversation(this.conversationId);

            if (token !== this.token) {
                return;
            }

            this.apply(data);

            // A document the agent has just written opens by itself
            const created = newReports(before, this.reports);

            if (created.length) {
                this.openReportById(created[created.length - 1].id);
            }
        },

        // Documents

        async openReportById(id) {
            this.canvasOpen = true;

            if (this.openedReport && this.openedReport.id === id) {
                return;
            }

            this.openedReport = null;
            this.reportError = "";
            this.reportLoading = true;

            try {
                const { data } = await AgentService.report(id);
                this.openedReport = data;
            } catch (e) {
                this.reportError = this.message(e);
            } finally {
                this.reportLoading = false;
            }
        },

        closeCanvas() {
            this.canvasOpen = false;
            this.openedReport = null;
        },

        // Decisions on what an agent proposed

        async approve(action) {
            await this.decide(action, () => AgentService.approve(action.id));
        },

        async reject(action) {
            await this.decide(action, () => AgentService.reject(action.id));
        },

        async decide(action, call) {
            this.error = "";
            this.deciding = { ...this.deciding, [action.id]: true };

            try {
                await call();
            } catch (e) {
                this.error = this.message(e);
            }

            this.deciding = { ...this.deciding, [action.id]: false };
            await this.refresh().catch(() => {});
        },

        // Agents

        async showInfo(id) {
            this.infoOpen = true;
            this.infoAgent = null;
            this.infoError = "";
            this.infoLoading = true;

            try {
                const { data } = await AgentService.get(id);

                // Another agent may have been asked for in the meantime
                if (this.infoOpen) {
                    this.infoAgent = data;
                }
            } catch (e) {
                this.infoError = this.message(e);
            } finally {
                this.infoLoading = false;
            }
        },

        // See: @/components/agent/FormModal.vue
        editAgent(agent) {
            this.infoOpen = false;
            store.commit("SET_AGENT_EDITING", agent);
            store.commit(OPEN_MODAL, "agent-form");
        },

        createAgent() {
            store.commit("SET_AGENT_EDITING", null);
            store.commit(OPEN_MODAL, "agent-form");
        },
    },
};
</script>
