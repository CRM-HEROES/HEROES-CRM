<template>
    <article
        class="hc-ag-perm"
        :class="[`is-${action.status}`, `risk-${action.risk}`]"
        role="group"
        :aria-label="title"
    >
        <header class="hc-ag-perm-head">
            <span class="hc-ag-perm-icon" aria-hidden="true"><i :class="icon"></i></span>
            <div class="hc-ag-perm-heading">
                <span class="hc-ag-perm-eyebrow">{{ eyebrow }}</span>
                <h4 v-text="title"></h4>
            </div>
            <span class="hc-ag-perm-risk" v-text="$t(`agent.risk.${action.risk}`)"></span>
        </header>

        <div v-if="count !== null || details.length || sample.length || skipped" class="hc-ag-perm-body">
            <div v-if="count !== null" class="hc-ag-perm-stat">
                <strong>{{ count }}</strong>
                <span>{{ $t(count === 1 ? "agent.perm.concerned_one" : "agent.perm.concerned_many") }}</span>
            </div>

            <dl v-if="details.length" class="hc-ag-perm-details">
                <template v-for="detail in details" :key="detail.key">
                    <dt v-text="$t(`agent.preview.${detail.key}`, detail.key)"></dt>
                    <dd v-text="detail.value"></dd>
                </template>
            </dl>

            <ul v-if="sample.length" class="hc-ag-perm-sample" :aria-label="$t('agent.preview.sample')">
                <li v-for="(name, i) in sample" :key="i" v-text="name"></li>
            </ul>

            <p v-if="skipped" class="hc-ag-perm-note">
                <i class="fa fa-circle-info" aria-hidden="true"></i>
                {{ $t("agent.skipped_processed", { n: skipped }) }}
            </p>
        </div>

        <!-- While it runs -->
        <div v-if="action.running" class="hc-ag-perm-state is-running" role="status">
            <template v-if="action.progress">
                <div class="hc-ag-perm-progress">
                    <span :style="{ width: percent + '%' }"></span>
                </div>
                <small>{{ action.progress.done }} / {{ action.progress.total }}</small>
            </template>
            <template v-else>
                <span class="hc-ag-perm-spinner" aria-hidden="true"></span>
                {{ $t("agent.running") }}
            </template>
        </div>

        <!-- The outcome -->
        <p v-if="action.status === 'executed' && action.result" class="hc-ag-perm-state is-ok" role="status">
            <i class="fa fa-circle-check" aria-hidden="true"></i>
            {{ $t("agent.done", { updated: action.result.updated ?? 1, failed: action.result.failed ?? 0 }) }}
        </p>
        <p v-if="action.error" class="hc-ag-perm-state is-ko" role="alert">
            <i class="fa fa-circle-exclamation" aria-hidden="true"></i>
            {{ action.error }}
        </p>
        <p v-if="action.status === 'rejected'" class="hc-ag-perm-state is-muted">
            <i class="fa fa-ban" aria-hidden="true"></i> {{ $t("agent.perm.rejected_note") }}
        </p>
        <p v-if="action.status === 'expired'" class="hc-ag-perm-state is-muted">
            <i class="fa fa-clock" aria-hidden="true"></i> {{ $t("agent.perm.expired_note") }}
        </p>

        <!-- The decision -->
        <footer v-if="action.status === 'pending'" class="hc-ag-perm-foot">
            <span v-if="action.expires_at" class="hc-ag-perm-expiry">
                <i class="fa fa-clock" aria-hidden="true"></i>
                {{ $t("agent.expires", { date: expiry }) }}
            </span>
            <div class="hc-ag-perm-actions">
                <ag-button variant="ghost" :disabled="working" @click="$emit('reject')">
                    {{ $t("agent.perm.reject") }}
                </ag-button>
                <ag-button
                    :variant="destructive ? 'danger' : 'success'"
                    :icon="destructive ? 'fa fa-trash' : 'fa fa-check'"
                    :loading="working"
                    @click="$emit('approve')"
                >
                    {{ $t(destructive ? "agent.perm.approve_destructive" : "agent.perm.approve") }}
                </ag-button>
            </div>
        </footer>
    </article>
</template>

<style>
.hc-ag-perm {
    --perm: var(--ag-warn);
    --perm-soft: var(--ag-warn-soft);
    border: 1px solid var(--ag-line);
    border-left: 4px solid var(--perm);
    border-radius: var(--ag-radius);
    background: #fff;
    box-shadow: var(--ag-shadow);
    overflow: hidden;
}

.hc-ag-perm.risk-communication {
    --perm: var(--ag-accent);
    --perm-soft: var(--ag-accent-soft);
}

.hc-ag-perm.risk-destructive {
    --perm: var(--ag-danger);
    --perm-soft: var(--ag-danger-soft);
}

.hc-ag-perm.is-executed {
    --perm: var(--ag-success);
    --perm-soft: var(--ag-success-soft);
}

.hc-ag-perm.is-rejected,
.hc-ag-perm.is-expired {
    --perm: #94a3b8;
    --perm-soft: var(--ag-surface);
    opacity: 0.85;
}

.hc-ag-perm.is-failed {
    --perm: var(--ag-danger);
    --perm-soft: var(--ag-danger-soft);
}

.hc-ag-perm-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px 10px;
}

.hc-ag-perm-icon {
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--perm-soft);
    color: var(--perm);
    font-size: 16px;
}

.hc-ag-perm-heading {
    flex: 1;
    min-width: 0;
}

.hc-ag-perm-eyebrow {
    display: block;
    font-size: 12px;
    color: var(--ag-muted);
}

.hc-ag-perm-heading h4 {
    margin: 2px 0 0;
    font-size: 15px;
    line-height: 1.3;
    color: var(--ag-ink);
    overflow-wrap: anywhere;
}

.hc-ag-perm-risk {
    flex: 0 0 auto;
    padding: 3px 10px;
    border-radius: 999px;
    background: var(--perm-soft);
    color: var(--perm);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.hc-ag-perm-body {
    padding: 0 16px 12px 66px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.hc-ag-perm-stat {
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.hc-ag-perm-stat strong {
    font-size: 28px;
    line-height: 1;
    color: var(--ag-ink);
    font-variant-numeric: tabular-nums;
}

.hc-ag-perm-stat span {
    color: var(--ag-muted);
}

.hc-ag-perm-details {
    display: grid;
    grid-template-columns: max-content 1fr;
    gap: 4px 14px;
    margin: 0;
    font-size: 13px;
}

.hc-ag-perm-details dt {
    color: var(--ag-muted);
}

.hc-ag-perm-details dd {
    margin: 0;
    color: var(--ag-ink-soft);
    overflow-wrap: anywhere;
    white-space: pre-wrap;
    display: -webkit-box;
    -webkit-line-clamp: 6;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.hc-ag-perm-sample {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.hc-ag-perm-sample li {
    padding: 3px 10px;
    border-radius: 999px;
    background: var(--ag-surface);
    border: 1px solid var(--ag-line);
    color: var(--ag-ink-soft);
    font-size: 12px;
}

.hc-ag-perm-note {
    margin: 0;
    padding: 8px 10px;
    border-radius: 8px;
    background: var(--ag-surface);
    color: var(--ag-muted);
    font-size: 12px;
    display: flex;
    gap: 8px;
}

.hc-ag-perm-state {
    margin: 0 16px 12px 66px;
    padding: 9px 12px;
    border-radius: 8px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.hc-ag-perm-state.is-running {
    background: var(--ag-accent-soft);
    color: var(--ag-accent-strong);
}

.hc-ag-perm-state.is-ok {
    background: var(--ag-success-soft);
    color: #166534;
}

.hc-ag-perm-state.is-ko {
    background: var(--ag-danger-soft);
    color: var(--ag-danger-strong);
}

.hc-ag-perm-state.is-muted {
    background: var(--ag-surface);
    color: var(--ag-muted);
}

.hc-ag-perm-progress {
    flex: 1;
    height: 6px;
    border-radius: 3px;
    background: rgba(18, 160, 243, 0.2);
    overflow: hidden;
}

.hc-ag-perm-progress span {
    display: block;
    height: 100%;
    background: var(--ag-accent);
    transition: width 0.3s ease;
}

.hc-ag-perm-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: hc-ag-spin 0.7s linear infinite;
}

.hc-ag-perm-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px 16px;
    border-top: 1px solid var(--ag-line);
    background: var(--ag-surface);
}

.hc-ag-perm-expiry {
    color: var(--ag-muted);
    font-size: 12px;
    display: inline-flex;
    gap: 6px;
    align-items: center;
}

.hc-ag-perm-actions {
    display: flex;
    gap: 8px;
    margin-left: auto;
}

@media (max-width: 560px) {
    .hc-ag-perm-body,
    .hc-ag-perm-state {
        padding-left: 16px;
        margin-left: 16px;
    }

    .hc-ag-perm-actions {
        width: 100%;
    }

    .hc-ag-perm-actions .hc-ag-btn {
        flex: 1;
    }
}
</style>

<script>
import AgButton from "./ui/AgentButton.vue";

const ICONS = { write: "fa fa-pen", communication: "fa fa-paper-plane", destructive: "fa fa-trash" };

// What is shown on its own line, not in the list of details
const APART = ["action", "prospects", "sample", "skipped_processed", "error"];

/**
 * A request for authorisation: an agent wants to do something that changes the
 * CRM or leaves it (messages, deletions, many records) and waits for a human.
 * It says what will happen, to how many records, and how risky it is; the
 * decision itself is taken on the server.
 */
export default {
    components: { AgButton },

    props: {
        action: { type: Object, required: true },
        working: { type: Boolean, default: false },
    },

    emits: ["approve", "reject"],

    computed: {
        preview() {
            return this.action.preview || {};
        },

        // What the agent wants to do, in words; the tool name when the server gave none
        title() {
            return typeof this.preview.action === "string" && this.preview.action
                ? this.preview.action
                : this.$t(`agent.tools.${this.action.tool}`, this.action.tool);
        },

        icon() {
            return ICONS[this.action.risk] || "fa fa-shield-halved";
        },

        destructive() {
            return this.action.risk === "destructive";
        },

        eyebrow() {
            const states = {
                pending: this.$t("agent.perm.asks", { agent: this.action.agent || "L'agent" }),
                approved: this.$t("agent.perm.status.running"),
                executed: this.$t("agent.perm.status.executed"),
                rejected: this.$t("agent.perm.status.rejected"),
                failed: this.$t("agent.perm.status.failed"),
                expired: this.$t("agent.perm.status.expired"),
            };

            return states[this.action.status] || "";
        },

        count() {
            return Number.isInteger(this.preview.prospects) ? this.preview.prospects : null;
        },

        sample() {
            return Array.isArray(this.preview.sample) ? this.preview.sample : [];
        },

        skipped() {
            return Number(this.preview.skipped_processed) || 0;
        },

        details() {
            return Object.entries(this.preview)
                .filter(([key, value]) => !APART.includes(key) && value !== null && value !== "" && value !== undefined)
                .map(([key, value]) => ({ key, value: this.format(value) }));
        },

        percent() {
            const progress = this.action.progress;

            return progress && progress.total ? Math.min(100, Math.round((progress.done / progress.total) * 100)) : 0;
        },

        expiry() {
            return new Date(this.action.expires_at).toLocaleString();
        },
    },

    methods: {
        format(value) {
            if (Array.isArray(value)) return value.map((item) => this.format(item)).join(", ");
            if (value && typeof value === "object") {
                return Object.entries(value)
                    .map(([key, item]) => `${key} : ${this.format(item)}`)
                    .join("\n");
            }
            if (typeof value === "boolean") return this.$t(value ? "agent.perm.yes" : "agent.perm.no");

            return String(value);
        },
    },
};
</script>
