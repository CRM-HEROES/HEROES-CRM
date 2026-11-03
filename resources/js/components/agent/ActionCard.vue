<template>
    <div class="hc-agent-action" :class="`is-${action.status}`">
        <header>
            <strong>{{ $t(`agent.tools.${action.tool}`, action.tool) }}</strong>
            <span v-if="action.agent" class="hc-agent-action-meta">{{ action.agent }}</span>
            <span v-if="action.project" class="hc-agent-action-meta">{{ action.project.name }}</span>
            <span class="hc-agent-action-risk" :class="`is-${action.risk}`">{{ $t(`agent.risk.${action.risk}`) }}</span>
        </header>

        <!-- What will change, as computed by the server -->
        <dl class="hc-agent-action-preview">
            <template v-for="(value, key) in preview" :key="key">
                <dt>{{ $t(`agent.preview.${key}`, key) }}</dt>
                <dd>{{ format(value) }}</dd>
            </template>
        </dl>

        <p v-if="action.preview && action.preview.skipped_processed" class="hc-agent-action-note">
            {{ $t("agent.skipped_processed", { n: action.preview.skipped_processed }) }}
        </p>

        <progress v-if="action.running && action.progress" :max="action.progress.total" :value="action.progress.done"></progress>
        <p v-else-if="action.running">{{ $t("agent.running") }}</p>

        <p v-if="action.status === 'executed' && action.result" class="hc-agent-action-ok">
            {{ $t("agent.done", { updated: action.result.updated ?? 1, failed: action.result.failed ?? 0 }) }}
        </p>
        <p v-if="action.error" class="hc-agent-action-ko">{{ action.error }}</p>
        <p v-if="action.status === 'pending' && action.expires_at" class="hc-agent-action-meta">
            {{ $t("agent.expires", { date: new Date(action.expires_at).toLocaleString() }) }}
        </p>

        <footer v-if="action.status === 'pending'">
            <button type="button" :disabled="working" @click="$emit('approve')">{{ $t("agent.confirm") }}</button>
            <button type="button" class="is-secondary" :disabled="working" @click="$emit('reject')">{{ $t("agent.cancel") }}</button>
        </footer>
    </div>
</template>

<script>
export default {
    props: { action: { type: Object, required: true }, working: { type: Boolean, default: false } },
    emits: ["approve", "reject"],
    computed: {
        // The count and the sample are shown on their own line
        preview() {
            const { skipped_processed, ...rest } = this.action.preview || {};
            return rest;
        },
    },
    methods: {
        format(value) {
            if (Array.isArray(value)) return value.join(", ");
            if (value && typeof value === "object") return Object.entries(value).map(([k, v]) => `${k} : ${v}`).join(", ");
            return value;
        },
    },
};
</script>

<style scoped>
.hc-agent-action { border: 1px solid #e0b000; background: #fffbea; border-radius: 6px; padding: 10px; display: flex; flex-direction: column; gap: 6px; }
.hc-agent-action.is-executed { border-color: #2e7d32; background: #f1f8f1; }
.hc-agent-action.is-failed, .hc-agent-action.is-rejected { border-color: #b00020; background: #fdf1f1; }
.hc-agent-action header { display: flex; gap: 8px; align-items: baseline; flex-wrap: wrap; }
.hc-agent-action-meta { color: #777; font-size: 12px; }
.hc-agent-action-risk { font-size: 11px; padding: 0 6px; border-radius: 8px; background: #eee; }
.hc-agent-action-risk.is-destructive, .hc-agent-action-risk.is-communication { background: #fbd; }
.hc-agent-action-preview { display: grid; grid-template-columns: max-content 1fr; gap: 2px 10px; margin: 0; }
.hc-agent-action-preview dt { color: #777; }
.hc-agent-action-preview dd { margin: 0; }
.hc-agent-action footer { display: flex; gap: 8px; }
.hc-agent-action-ok { color: #2e7d32; }
.hc-agent-action-ko { color: #b00020; }
</style>
