<template>
    <aside class="hc-agent-canvas" :aria-label="$t('agent.chat.renders')">
        <header>
            <div class="hc-agent-canvas-title">
                <i class="fa fa-file-lines" aria-hidden="true"></i>
                <strong v-text="report ? report.title : $t('agent.chat.renders')"></strong>
            </div>
            <div class="hc-agent-canvas-actions">
                <template v-if="report">
                    <ag-button
                        variant="ghost"
                        size="sm"
                        :icon="copied ? 'fa fa-check' : 'fa fa-copy'"
                        icon-only
                        :title="$t('agent.chat.copy')"
                        :aria-label="$t('agent.chat.copy')"
                        @click="copy"
                    />
                    <ag-button
                        variant="ghost"
                        size="sm"
                        icon="fa fa-download"
                        icon-only
                        :title="$t('agent.chat.download')"
                        :aria-label="$t('agent.chat.download')"
                        @click="download"
                    />
                </template>
                <ag-button
                    variant="ghost"
                    size="sm"
                    icon="fa fa-times"
                    icon-only
                    :title="$t('agent.chat.close')"
                    :aria-label="$t('agent.chat.close')"
                    @click="$emit('close')"
                />
            </div>
        </header>

        <p v-if="loading" class="hc-agent-canvas-empty">{{ $t("agent.chat.loading") }}</p>
        <p v-else-if="error" class="hc-agent-canvas-empty is-error" role="alert">{{ error }}</p>
        <div v-else-if="report" class="hc-agent-canvas-body">
            <p class="hc-agent-canvas-meta">
                <span v-if="report.agent" v-text="report.agent"></span>
                <span v-if="report.created_at" v-text="date(report.created_at)"></span>
            </p>
            <markdown :text="report.content" />
        </div>
        <div v-else class="hc-agent-canvas-empty">
            <i class="fa fa-file-lines" aria-hidden="true"></i>
            <p>{{ $t("agent.chat.renders_empty") }}</p>
        </div>
    </aside>
</template>

<style>
.hc-agent-canvas {
    flex: 0 0 42%;
    max-width: 660px;
    min-width: 320px;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-left: 1px solid var(--ag-line);
    overflow: hidden;
    color: var(--ag-ink);
}

.hc-agent-canvas > header {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 12px 10px 18px;
    border-bottom: 1px solid var(--ag-line);
}

.hc-agent-canvas-title {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.hc-agent-canvas-title .fa-file-lines {
    color: var(--ag-accent);
}

.hc-agent-canvas-title strong {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-agent-canvas-actions {
    display: flex;
    gap: 2px;
}

.hc-agent-canvas-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px 24px 32px;
}

.hc-agent-canvas-meta {
    margin: 0 0 12px;
    color: var(--ag-muted);
    font-size: 12px;
    display: flex;
    gap: 12px;
}

.hc-agent-canvas-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 28px;
    margin: 0;
    color: var(--ag-muted);
    text-align: center;
}

.hc-agent-canvas-empty .fa {
    font-size: 40px;
    color: #cbd5e1;
}

.hc-agent-canvas-empty.is-error {
    color: var(--ag-danger-strong);
}

@media (max-width: 1100px) {
    .hc-agent-canvas {
        position: absolute;
        z-index: 12;
        top: 0;
        right: 0;
        bottom: 0;
        width: min(94%, 580px);
        max-width: none;
        box-shadow: -8px 0 32px rgba(15, 23, 42, 0.18);
    }
}
</style>

<script>
import AgButton from "../ui/AgentButton.vue";
import Markdown from "./Markdown.vue";

export default {
    components: { AgButton, Markdown },

    props: {
        report: { type: Object, default: null },
        loading: { type: Boolean, default: false },
        error: { type: String, default: "" },
    },
    emits: ["close"],

    data() {
        return { copied: false };
    },

    methods: {
        date(value) {
            return new Date(value).toLocaleString();
        },

        async copy() {
            try {
                await navigator.clipboard.writeText(this.report.content);
                this.copied = true;
                setTimeout(() => (this.copied = false), 1500);
            } catch (e) {
                // The browser refused (no HTTPS, no permission): nothing else to do
            }
        },

        // The document as a Markdown file
        download() {
            const blob = new Blob([this.report.content], { type: "text/markdown;charset=utf-8" });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = (this.report.title || "document").replace(/[^\w\-. ]+/g, "").trim().slice(0, 80) + ".md";
            document.body.appendChild(link);
            link.click();
            link.remove();
            setTimeout(() => URL.revokeObjectURL(link.href), 1000);
        },
    },
};
</script>
