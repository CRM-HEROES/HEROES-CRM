<template>
    <modal :name="name" :title="$t('agent.title')" @open="load" @closed="stop">
        <div class="hc-agents">
            <nav class="hc-agents-tabs" role="tablist">
                <button v-for="t in tabs" :key="t" type="button" role="tab" :class="{ active: tab === t }" @click="tab = t">
                    {{ $t(`agent.tab.${t}`) }}
                    <span v-if="t === 'actions' && pending.length" class="hc-agents-badge">{{ pending.length }}</span>
                </button>
            </nav>

            <p v-if="error" class="hc-agents-error" role="alert">{{ error }}</p>

            <!-- Discussion -->
            <section v-if="tab === 'chat'" class="hc-agents-chat">
                <p v-if="!agents.length" class="hc-agents-hint">{{ $t("agent.no_agent") }}</p>
                <template v-else>
                    <select v-model="agentId" @change="newConversation">
                        <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                    <div class="hc-agents-messages" ref="messages">
                        <div v-for="(m, i) in messages" :key="i" :class="['hc-agents-message', `is-${m.role}`, `is-${m.kind}`]">
                            <template v-if="m.kind === 'tool'">{{ $t("agent.tool_used", { tool: m.tool }) }}</template>
                            <template v-else>{{ m.text }}</template>
                        </div>
                        <div v-if="busy" class="hc-agents-message is-agent is-working">{{ $t("agent.working") }}</div>
                    </div>

                    <div v-for="action in conversationActions" :key="action.id" class="hc-agents-action">
                        <action-card :action="action" :working="deciding[action.id]" @approve="approve(action)" @reject="reject(action)" />
                    </div>

                    <form class="hc-agents-form" @submit.prevent="send">
                        <textarea v-model="draft" rows="2" maxlength="8000" :disabled="busy" :placeholder="$t('agent.placeholder')" @keydown.enter.exact.prevent="send"></textarea>
                        <button type="submit" :disabled="busy || !draft.trim()">{{ $t("agent.send") }}</button>
                    </form>
                </template>
            </section>

            <!-- Actions waiting for a decision -->
            <section v-else-if="tab === 'actions'" class="hc-agents-actions">
                <p v-if="!pending.length" class="hc-agents-hint">{{ $t("agent.no_action") }}</p>
                <action-card v-for="action in pending" :key="action.id" :action="action" :working="deciding[action.id]" @approve="approve(action)" @reject="reject(action)" />
            </section>

            <!-- Agents -->
            <section v-else class="hc-agents-config">
                <ul class="hc-agents-list">
                    <li v-for="a in agents" :key="a.id">
                        <strong>{{ a.name }}</strong>
                        <span class="hc-agents-hint">{{ catalog.autonomies?.[a.autonomy] }}</span>
                        <button type="button" @click="remove(a)">{{ $t("agent.delete") }}</button>
                    </li>
                </ul>

                <form class="hc-agents-new" @submit.prevent="create">
                    <h4>{{ $t("agent.new") }}</h4>
                    <input v-model="form.name" required maxlength="100" :placeholder="$t('agent.name')" />
                    <textarea v-model="form.instructions" rows="4" maxlength="20000" :placeholder="$t('agent.instructions')"></textarea>
                    <label>{{ $t("agent.model") }}
                        <select v-model="form.model">
                            <option v-for="(label, id) in catalog.models" :key="id" :value="id">{{ label }}</option>
                        </select>
                    </label>
                    <label>{{ $t("agent.autonomy") }}
                        <select v-model="form.autonomy">
                            <option v-for="(label, id) in catalog.autonomies" :key="id" :value="id">{{ label }}</option>
                        </select>
                    </label>
                    <fieldset>
                        <legend>{{ $t("agent.skills") }}</legend>
                        <label v-for="s in catalog.skills" :key="s.key" class="hc-agents-skill">
                            <input type="checkbox" :value="s.key" v-model="form.skills" />
                            {{ s.label }} <em v-if="s.writes">({{ $t("agent.can_write") }})</em>
                        </label>
                    </fieldset>
                    <button type="submit" :disabled="!form.name.trim()">{{ $t("agent.create") }}</button>
                </form>
            </section>
        </div>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import AgentService from "@/apis/agent";
import ActionCard from "@/components/agent/ActionCard.vue";

const POLL_MS = 1500;

export default {
    components: { ActionCard },
    data() {
        return {
            name: "agents",
            tabs: ["chat", "actions", "config"],
            tab: "chat",
            agents: [],
            catalog: {},
            agentId: null,
            conversationId: null,
            messages: [],
            conversationActions: [],
            pending: [],
            draft: "",
            busy: false,
            deciding: {},
            error: "",
            timer: null,
            form: this.blankForm(),
        };
    },
    computed: {
        ...mapGetters(["project"]),
    },
    beforeUnmount() {
        this.stop();
    },
    methods: {
        blankForm() {
            return { name: "", instructions: "", model: "claude-opus-5-5", autonomy: "propose", skills: ["crm_exploration"] };
        },
        message(e) {
            return e?.response?.data?.message || this.$t("agent.error");
        },
        stop() {
            clearTimeout(this.timer);
            this.timer = null;
        },
        async load() {
            this.error = "";
            try {
                const [agents, catalog] = await Promise.all([AgentService.list(), AgentService.catalog()]);
                this.agents = agents.data;
                this.catalog = catalog.data;
                this.form.model = catalog.data.default_model;
                if (!this.agentId && this.agents.length) this.agentId = this.agents[0].id;
                await this.loadActions();
            } catch (e) {
                this.error = this.message(e);
            }
        },
        async loadActions() {
            const { data } = await AgentService.actions("pending");
            this.pending = data;
            // A decided action may still be running: keep following it
            if (data.some((a) => a.running)) this.schedule(this.loadActions);
        },
        schedule(fn) {
            clearTimeout(this.timer);
            this.timer = setTimeout(fn, POLL_MS);
        },
        newConversation() {
            this.conversationId = null;
            this.messages = [];
            this.conversationActions = [];
        },
        async send() {
            const text = this.draft.trim();
            if (!text || this.busy || !this.project) return;
            this.error = "";
            this.busy = true;
            this.messages.push({ role: "user", kind: "text", text });
            this.draft = "";
            try {
                const { data } = await AgentService.ask(this.agentId, this.project.slug, text, this.conversationId);
                this.conversationId = data.conversation_id;
                this.follow(data.run_id);
            } catch (e) {
                this.busy = false;
                this.error = this.message(e);
            }
        },
        // Ask for the run until it is over, then show the whole conversation
        async follow(runId) {
            try {
                const { data } = await AgentService.run(runId);
                if (!data.finished) return this.schedule(() => this.follow(runId));
                this.busy = false;
                if (data.error) this.error = data.error;
                await this.refresh();
            } catch (e) {
                this.busy = false;
                this.error = this.message(e);
            }
        },
        async refresh() {
            if (!this.conversationId) return;
            const { data } = await AgentService.conversation(this.conversationId);
            this.messages = data.messages;
            this.conversationActions = data.pending_actions;
            await this.loadActions();
            this.$nextTick(() => {
                const box = this.$refs.messages;
                if (box) box.scrollTop = box.scrollHeight;
            });
        },
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
            await this.loadActions().catch(() => {});
        },
        async create() {
            this.error = "";
            try {
                await AgentService.create({ ...this.form });
                this.form = this.blankForm();
                this.form.model = this.catalog.default_model;
                await this.load();
            } catch (e) {
                this.error = this.message(e);
            }
        },
        async remove(agent) {
            if (!confirm(this.$t("agent.confirm_delete", { name: agent.name }))) return;
            try {
                await AgentService.remove(agent.id);
                if (this.agentId === agent.id) this.agentId = null;
                await this.load();
            } catch (e) {
                this.error = this.message(e);
            }
        },
    },
};
</script>

<style scoped>
.hc-agents { display: flex; flex-direction: column; gap: 10px; min-width: 460px; }
.hc-agents-tabs { display: flex; gap: 6px; }
.hc-agents-tabs button { padding: 6px 12px; border: 1px solid #ccc; background: #fff; border-radius: 4px; cursor: pointer; }
.hc-agents-tabs button.active { background: #333; color: #fff; }
.hc-agents-badge { background: #d33; color: #fff; border-radius: 10px; padding: 0 6px; margin-left: 4px; font-size: 12px; }
.hc-agents-messages { height: 320px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px; display: flex; flex-direction: column; gap: 6px; }
.hc-agents-message { padding: 6px 10px; border-radius: 6px; white-space: pre-wrap; max-width: 90%; }
.hc-agents-message.is-user { align-self: flex-end; background: #e8f0fe; }
.hc-agents-message.is-agent { background: #f3f3f3; }
.hc-agents-message.is-tool { background: none; color: #777; font-size: 12px; padding: 0 10px; }
.hc-agents-message.is-system { align-self: center; background: #fff7e0; font-size: 12px; }
.hc-agents-message.is-working { color: #777; font-style: italic; }
.hc-agents-form { display: flex; gap: 8px; }
.hc-agents-form textarea { flex: 1; }
.hc-agents-error { color: #b00020; }
.hc-agents-hint { color: #777; font-size: 12px; }
.hc-agents-new { display: flex; flex-direction: column; gap: 8px; }
.hc-agents-list { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.hc-agents-list li { display: flex; gap: 10px; align-items: center; }
</style>
