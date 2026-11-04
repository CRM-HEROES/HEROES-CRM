<template>
    <transition name="hc-agent-info">
        <div v-if="open" class="hc-agent-info-layer">
            <div class="hc-agent-info-backdrop" @click="$emit('close')"></div>
            <aside
                class="hc-agent-info"
                role="dialog"
                aria-modal="true"
                :aria-label="$t('agent.chat.info_of', { name: agent ? agent.name : '' })"
                @keydown.esc="$emit('close')"
            >
                <header>
                    <strong>{{ $t("agent.chat.info") }}</strong>
                    <ag-button
                        variant="ghost"
                        size="sm"
                        icon="fa fa-times"
                        icon-only
                        :aria-label="$t('agent.chat.close')"
                        @click="$emit('close')"
                    />
                </header>

                <p v-if="loading" class="hc-agent-info-hint">{{ $t("agent.chat.loading") }}</p>
                <p v-else-if="error" class="hc-agent-info-error" role="alert">{{ error }}</p>

                <div v-else-if="agent" class="hc-agent-info-body">
                    <div class="hc-agent-info-identity">
                        <img v-if="agent.avatar_url" :src="agent.avatar_url" :alt="agent.name" />
                        <i v-else class="fa fa-robot" aria-hidden="true"></i>
                        <h3 v-text="agent.name"></h3>
                        <p v-if="agent.specialty" class="hc-agent-info-specialty" v-text="agent.specialty"></p>
                        <span class="hc-agent-info-badge" :class="`is-${agent.autonomy}`">
                            {{ $t(`agent.autonomy_short.${agent.autonomy}`) }}
                        </span>
                    </div>

                    <section v-if="agent.description">
                        <h4>{{ $t("agent.field_description") }}</h4>
                        <p v-text="agent.description"></p>
                    </section>
                    <section v-if="agent.persona">
                        <h4>{{ $t("agent.persona") }}</h4>
                        <p v-text="agent.persona"></p>
                    </section>
                    <section v-if="agent.objectives">
                        <h4>{{ $t("agent.objectives") }}</h4>
                        <p v-text="agent.objectives"></p>
                    </section>
                    <section v-if="agent.instructions">
                        <h4>{{ $t("agent.instructions") }}</h4>
                        <p class="hc-agent-info-instructions" v-text="agent.instructions"></p>
                    </section>
                    <section>
                        <h4>{{ $t("agent.chat.can_do") }}</h4>
                        <p>{{ $t("agent.knows_all") }}</p>
                    </section>
                    <section>
                        <h4>{{ $t("agent.scope") }}</h4>
                        <p v-text="agent.project ? agent.project.name : $t('agent.all_projects')"></p>
                    </section>

                    <ag-button variant="secondary" icon="fa fa-pen" block @click="$emit('edit', agent)">
                        {{ $t("agent.edit") }}
                    </ag-button>
                </div>
            </aside>
        </div>
    </transition>
</template>

<style>
.hc-agent-info-layer {
    position: absolute;
    inset: 0;
    z-index: 20;
}

.hc-agent-info-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.3);
    backdrop-filter: blur(1px);
}

.hc-agent-info {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 380px;
    max-width: 94%;
    background: #fff;
    box-shadow: 8px 0 32px rgba(15, 23, 42, 0.18);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    color: var(--ag-ink);
}

.hc-agent-info header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px 12px 20px;
    border-bottom: 1px solid var(--ag-line);
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(4px);
    z-index: 1;
}

.hc-agent-info-body {
    padding: 22px 20px 28px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.hc-agent-info-identity {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 4px;
    padding-bottom: 6px;
}

.hc-agent-info-identity img,
.hc-agent-info-identity .fa-robot {
    width: 104px;
    height: 104px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--ag-surface);
    box-shadow: var(--ag-shadow);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 46px;
    color: #94a3b8;
}

.hc-agent-info-identity h3 {
    margin: 10px 0 0;
    font-size: 20px;
}

.hc-agent-info-specialty {
    margin: 0;
    color: var(--ag-muted);
}

.hc-agent-info-badge {
    margin-top: 4px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 3px 12px;
    border-radius: 999px;
    background: var(--ag-surface);
    color: var(--ag-muted);
    border: 1px solid var(--ag-line);
}

.hc-agent-info-badge.is-propose {
    background: var(--ag-accent-soft);
    color: var(--ag-accent-strong);
    border-color: transparent;
}

.hc-agent-info-badge.is-auto {
    background: var(--ag-warn-soft);
    color: #92400e;
    border-color: transparent;
}

.hc-agent-info-body section {
    padding-top: 14px;
    border-top: 1px solid var(--ag-line);
}

.hc-agent-info-body h4 {
    margin: 0 0 6px;
    font-size: 11px;
    font-weight: 700;
    color: var(--ag-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.hc-agent-info-body p {
    margin: 0;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    color: var(--ag-ink-soft);
    line-height: 1.5;
}

.hc-agent-info-instructions {
    max-height: 220px;
    overflow-y: auto;
    padding: 10px 12px;
    background: var(--ag-surface);
    border: 1px solid var(--ag-line);
    border-radius: 10px;
    font-size: 13px;
}

.hc-agent-info-hint,
.hc-agent-info-error {
    padding: 20px;
    margin: 0;
    color: var(--ag-muted);
}

.hc-agent-info-error {
    color: var(--ag-danger-strong);
}

/* The drawer slides in from the left */
.hc-agent-info-enter-active .hc-agent-info,
.hc-agent-info-leave-active .hc-agent-info {
    transition: transform 0.25s cubic-bezier(0.2, 0.7, 0.2, 1);
}

.hc-agent-info-enter-active .hc-agent-info-backdrop,
.hc-agent-info-leave-active .hc-agent-info-backdrop {
    transition: opacity 0.25s ease;
}

.hc-agent-info-enter-from .hc-agent-info,
.hc-agent-info-leave-to .hc-agent-info {
    transform: translateX(-100%);
}

.hc-agent-info-enter-from .hc-agent-info-backdrop,
.hc-agent-info-leave-to .hc-agent-info-backdrop {
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .hc-agent-info-enter-active .hc-agent-info,
    .hc-agent-info-leave-active .hc-agent-info,
    .hc-agent-info-enter-active .hc-agent-info-backdrop,
    .hc-agent-info-leave-active .hc-agent-info-backdrop {
        transition: none;
    }
}
</style>

<script>
import AgButton from "../ui/AgentButton.vue";

export default {
    components: { AgButton },

    props: {
        open: { type: Boolean, default: false },
        agent: { type: Object, default: null },
        loading: { type: Boolean, default: false },
        error: { type: String, default: "" },
    },
    emits: ["close", "edit"],
};
</script>
