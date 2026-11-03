<template>
    <section class="hc-agent-chat" :aria-label="$t('agent.chat.conversation')">
        <header class="hc-agent-chat-header">
            <template v-if="agent">
                <img v-if="agent.avatar_url" :src="agent.avatar_url" alt="" width="32" height="32" />
                <i v-else class="fa fa-robot" aria-hidden="true"></i>
                <div class="hc-agent-chat-heading">
                    <strong v-text="agent.name"></strong>
                    <span v-if="title" v-text="title"></span>
                </div>
            </template>
            <strong v-else>{{ $t("agent.title") }}</strong>
        </header>

        <div ref="scroller" class="hc-agent-chat-messages" role="log" aria-live="polite">
            <div v-if="!agent" class="hc-agent-chat-welcome">
                <i class="fa fa-robot" aria-hidden="true"></i>
                <p>{{ $t("agent.no_agent_page") }}</p>
                <ag-button variant="primary" icon="fa fa-plus" @click="$emit('create')">{{ $t("agent.new") }}</ag-button>
            </div>

            <div v-else-if="!items.length && !busy" class="hc-agent-chat-welcome">
                <img v-if="agent.avatar_url" :src="agent.avatar_url" :alt="agent.name" />
                <i v-else class="fa fa-robot" aria-hidden="true"></i>
                <h3 v-text="agent.name"></h3>
                <p v-if="agent.specialty" v-text="agent.specialty"></p>
                <div class="hc-agent-chat-suggestions">
                    <button v-for="key in suggestions" :key="key" type="button" @click="send($t(`agent.chat.suggestion.${key}`))">
                        <i :class="suggestionIcons[key]" aria-hidden="true"></i>
                        <span>{{ $t(`agent.chat.suggestion.${key}`) }}</span>
                    </button>
                </div>
            </div>

            <template v-for="(item, i) in items" :key="i">
                <div v-if="item.kind === 'user'" class="hc-agent-msg is-user" v-text="item.text"></div>

                <div v-else-if="item.kind === 'agent'" class="hc-agent-msg is-agent">
                    <markdown :text="item.text" />
                </div>

                <div v-else-if="item.kind === 'tool'" class="hc-agent-msg-tool">
                    <i class="fa fa-wrench" aria-hidden="true"></i>
                    {{ $t("agent.tool_used", { tool: $t(`agent.tools.${item.tool}`, item.tool) }) }}
                </div>

                <div v-else-if="item.kind === 'system'" class="hc-agent-msg-system" v-text="item.text"></div>

                <button
                    v-else-if="item.kind === 'report'"
                    type="button"
                    class="hc-agent-report-card"
                    :class="{ 'is-open': item.id === openReportId }"
                    @click="$emit('open-report', item.id)"
                >
                    <span class="hc-agent-report-card-icon" aria-hidden="true"><i class="fa fa-file-lines"></i></span>
                    <span class="hc-agent-report-card-text">
                        <span class="hc-agent-report-card-title" v-text="item.title"></span>
                        <span class="hc-agent-report-card-kind">{{ $t("agent.chat.document") }}</span>
                    </span>
                    <span class="hc-agent-report-card-open">{{ $t("agent.chat.open_document") }} <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
                </button>
            </template>

            <div v-if="busy" class="hc-agent-msg is-agent is-working">
                <span class="hc-agent-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                {{ $t(stalled ? "agent.waiting_worker" : "agent.working") }}
            </div>

            <div v-for="action in pendingActions" :key="action.id" class="hc-agent-chat-action">
                <action-card :action="action" :working="deciding[action.id]" @approve="$emit('approve', action)" @reject="$emit('reject', action)" />
            </div>
        </div>

        <p v-if="error" class="hc-agent-chat-error" role="alert">
            <i class="fa fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ error }}</span>
        </p>

        <form class="hc-agent-chat-form" @submit.prevent="send(draft)">
            <textarea
                ref="input"
                v-model="draft"
                rows="1"
                maxlength="8000"
                :disabled="!agent || busy"
                :placeholder="agent ? $t('agent.chat.placeholder', { name: agent.name }) : ''"
                @input="grow"
                @keydown.enter.exact.prevent="send(draft)"
            ></textarea>
            <ag-button
                type="submit"
                variant="primary"
                icon="fa fa-arrow-up"
                icon-only
                :disabled="!canSend"
                :aria-label="$t('agent.send')"
                :title="$t('agent.send')"
            />
        </form>
        <p class="hc-agent-chat-hint">{{ $t("agent.chat.hint") }}</p>
    </section>
</template>

<style>
.hc-agent-chat {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    background: #fff;
    color: var(--ag-ink);
}

.hc-agent-chat-header {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 22px;
    border-bottom: 1px solid var(--ag-line);
}

.hc-agent-chat-header img,
.hc-agent-chat-header .fa-robot {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--ag-surface);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--ag-muted);
}

.hc-agent-chat-heading {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.25;
}

.hc-agent-chat-heading span {
    color: var(--ag-muted);
    font-size: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-agent-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    scroll-behavior: smooth;
}

.hc-agent-chat-welcome {
    margin: auto;
    max-width: 560px;
    text-align: center;
    color: var(--ag-muted);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.hc-agent-chat-welcome img,
.hc-agent-chat-welcome > .fa-robot {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--ag-surface);
    box-shadow: var(--ag-shadow);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    color: #94a3b8;
}

.hc-agent-chat-welcome h3 {
    margin: 6px 0 0;
    color: var(--ag-ink);
    font-size: 22px;
}

.hc-agent-chat-welcome p {
    margin: 0;
}

.hc-agent-chat-suggestions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 18px;
    width: 100%;
}

.hc-agent-chat-suggestions button {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border: 1px solid var(--ag-line);
    border-radius: var(--ag-radius);
    background: #fff;
    color: var(--ag-ink-soft);
    text-align: left;
    cursor: pointer;
    font: inherit;
    transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
}

.hc-agent-chat-suggestions button i {
    flex: 0 0 18px;
    color: var(--ag-accent);
    text-align: center;
}

.hc-agent-chat-suggestions button:hover {
    border-color: var(--ag-accent);
    box-shadow: var(--ag-shadow);
    transform: translateY(-1px);
}

.hc-agent-chat-suggestions button:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
}

.hc-agent-msg {
    max-width: min(760px, 94%);
    line-height: 1.55;
}

.hc-agent-msg.is-user {
    align-self: flex-end;
    padding: 10px 16px;
    border-radius: 18px 18px 4px 18px;
    background: var(--ag-accent-soft);
    color: var(--ag-ink);
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.hc-agent-msg.is-agent {
    align-self: flex-start;
    padding: 2px 4px;
    color: var(--ag-ink);
    font-size: 15px;
}

.hc-agent-msg.is-working {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    border-radius: 999px;
    background: var(--ag-surface);
    color: var(--ag-muted);
    font-size: 13px;
}

.hc-agent-dots {
    display: inline-flex;
    gap: 4px;
}

.hc-agent-dots i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--ag-accent);
    animation: hc-agent-blink 1.2s infinite ease-in-out;
}

.hc-agent-dots i:nth-child(2) { animation-delay: 0.2s; }
.hc-agent-dots i:nth-child(3) { animation-delay: 0.4s; }

@keyframes hc-agent-blink {
    0%, 80%, 100% { opacity: 0.25; transform: scale(0.85); }
    40% { opacity: 1; transform: scale(1); }
}

.hc-agent-msg-tool {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 3px 12px;
    border: 1px solid var(--ag-line);
    border-radius: 999px;
    background: var(--ag-surface);
    color: var(--ag-muted);
    font-size: 12px;
}

.hc-agent-msg-system {
    align-self: center;
    max-width: 90%;
    padding: 5px 14px;
    border-radius: 999px;
    background: var(--ag-warn-soft);
    color: #92400e;
    font-size: 12px;
    overflow-wrap: anywhere;
}

.hc-agent-report-card {
    align-self: flex-start;
    display: flex;
    align-items: center;
    gap: 14px;
    width: min(460px, 94%);
    padding: 12px 16px;
    border: 1px solid var(--ag-line);
    border-radius: var(--ag-radius);
    background: #fff;
    box-shadow: var(--ag-shadow);
    cursor: pointer;
    font: inherit;
    text-align: left;
    transition: border-color 0.15s ease, transform 0.15s ease;
}

.hc-agent-report-card:hover,
.hc-agent-report-card.is-open {
    border-color: var(--ag-accent);
}

.hc-agent-report-card:hover {
    transform: translateY(-1px);
}

.hc-agent-report-card:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
}

.hc-agent-report-card-icon {
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--ag-accent-soft);
    color: var(--ag-accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.hc-agent-report-card-text {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.hc-agent-report-card-title {
    font-weight: 600;
    color: var(--ag-ink);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-agent-report-card-kind {
    color: var(--ag-muted);
    font-size: 12px;
}

.hc-agent-report-card-open {
    color: var(--ag-accent-strong);
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.hc-agent-chat-action {
    width: min(660px, 100%);
}

.hc-agent-chat-error {
    flex: 0 0 auto;
    margin: 0 22px 10px;
    padding: 10px 14px;
    border-radius: 10px;
    background: var(--ag-danger-soft);
    color: var(--ag-danger-strong);
    display: flex;
    gap: 10px;
    align-items: flex-start;
    font-size: 13px;
}

.hc-agent-chat-form {
    flex: 0 0 auto;
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin: 0 22px;
    padding: 8px 8px 8px 18px;
    border: 1px solid var(--ag-line);
    border-radius: 22px;
    background: #fff;
    box-shadow: var(--ag-shadow);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.hc-agent-chat-form:focus-within {
    border-color: var(--ag-accent);
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.18), var(--ag-shadow);
}

.hc-agent-chat-form textarea {
    flex: 1;
    border: 0;
    outline: 0;
    resize: none;
    max-height: 200px;
    padding: 8px 0;
    font: inherit;
    font-size: 15px;
    line-height: 1.4;
    color: var(--ag-ink);
    background: transparent;
}

.hc-agent-chat-form textarea::placeholder {
    color: #94a3b8;
}

.hc-agent-chat-hint {
    flex: 0 0 auto;
    margin: 6px 22px 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 11px;
}
</style>

<script>
import ActionCard from "@/components/agent/ActionCard.vue";
import AgButton from "../ui/AgentButton.vue";
import Markdown from "./Markdown.vue";

const SUGGESTIONS = ["activity", "uncontacted", "appointments"];
const SUGGESTION_ICONS = {
    activity: "fa fa-chart-line",
    uncontacted: "fa fa-user-clock",
    appointments: "fa fa-calendar-check",
};

export default {
    components: { ActionCard, AgButton, Markdown },

    props: {
        agent: { type: Object, default: null },
        title: { type: String, default: "" },
        items: { type: Array, default: () => [] },
        busy: { type: Boolean, default: false },
        stalled: { type: Boolean, default: false },
        error: { type: String, default: "" },
        pendingActions: { type: Array, default: () => [] },
        deciding: { type: Object, default: () => ({}) },
        openReportId: { type: Number, default: null },
    },

    emits: ["send", "create", "approve", "reject", "open-report"],

    data() {
        return {
            draft: "",
            suggestions: SUGGESTIONS,
            suggestionIcons: SUGGESTION_ICONS,
        };
    },

    computed: {
        canSend() {
            return !!this.agent && !this.busy && !!this.draft.trim();
        },
    },

    watch: {
        // Stay at the bottom while the conversation grows
        items() {
            this.$nextTick(this.scrollDown);
        },
        busy() {
            this.$nextTick(this.scrollDown);
        },
    },

    methods: {
        send(text) {
            const message = (text || "").trim();

            if (!this.agent || this.busy || !message) {
                return;
            }

            this.$emit("send", message);
            this.draft = "";
            this.$nextTick(this.grow);
        },

        // The box grows with what is typed
        grow() {
            const box = this.$refs.input;

            if (box) {
                box.style.height = "auto";
                box.style.height = Math.min(box.scrollHeight, 200) + "px";
            }
        },

        scrollDown() {
            const box = this.$refs.scroller;

            if (box) {
                box.scrollTop = box.scrollHeight;
            }
        },
    },
};
</script>
