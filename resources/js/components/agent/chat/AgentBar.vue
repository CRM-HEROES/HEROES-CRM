<template>
    <nav class="hc-agent-bar" :aria-label="$t('agent.chat.agents_of_project')">
        <div class="hc-agent-bar-scroll">
            <div
                v-for="agent in agents"
                :key="agent.id"
                class="hc-agent-chip"
                :class="{ 'is-selected': agent.id === selectedId }"
            >
                <button
                    type="button"
                    class="hc-agent-chip-main"
                    :aria-pressed="agent.id === selectedId"
                    :title="agent.specialty || agent.name"
                    @click="$emit('select', agent.id)"
                >
                    <img
                        v-if="agent.avatar_url"
                        :src="agent.avatar_url"
                        :alt="''"
                        width="28"
                        height="28"
                    />
                    <i v-else class="fa fa-robot" aria-hidden="true"></i>
                    <span class="hc-agent-chip-name" v-text="agent.name"></span>
                </button>
                <button
                    type="button"
                    class="hc-agent-chip-info"
                    :title="$t('agent.chat.info')"
                    :aria-label="$t('agent.chat.info_of', { name: agent.name })"
                    @click.stop="$emit('info', agent.id)"
                >
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </button>
            </div>

            <button type="button" class="hc-agent-chip-add" @click="$emit('create')">
                <i class="fa fa-plus" aria-hidden="true"></i>
                <span>{{ $t("agent.new") }}</span>
            </button>
        </div>
    </nav>
</template>

<style>
.hc-agent-bar {
    flex: 0 0 auto;
    background: #fff;
    border-bottom: 1px solid var(--ag-line);
}

.hc-agent-bar-scroll {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    overflow-x: auto;
    scrollbar-width: thin;
}

.hc-agent-chip {
    display: flex;
    align-items: center;
    flex: 0 0 auto;
    border: 1px solid var(--ag-line);
    border-radius: 999px;
    background: #fff;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.hc-agent-chip:hover {
    border-color: #cbd5e1;
}

.hc-agent-chip.is-selected {
    border-color: var(--ag-accent);
    background: var(--ag-accent-soft);
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.12);
}

.hc-agent-chip button {
    border: 0;
    background: transparent;
    cursor: pointer;
    font: inherit;
}

.hc-agent-chip button:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
}

.hc-agent-chip-main {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 5px 6px 5px 6px;
    border-radius: 999px;
    color: var(--ag-ink);
}

.hc-agent-chip-main img,
.hc-agent-chip-main .fa-robot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--ag-surface);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: var(--ag-muted);
}

.hc-agent-chip-name {
    font-weight: 600;
    white-space: nowrap;
}

.hc-agent-chip-info {
    width: 30px;
    height: 30px;
    margin-right: 4px;
    padding: 0;
    border-radius: 50%;
    color: #94a3b8;
    font-size: 15px;
}

.hc-agent-chip-info:hover {
    background: rgba(18, 160, 243, 0.14);
    color: var(--ag-accent-strong);
}

.hc-agent-chip-add {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
    padding: 8px 14px;
    border: 1px dashed #cbd5e1;
    border-radius: 999px;
    background: transparent;
    color: var(--ag-muted);
    cursor: pointer;
    font: inherit;
    white-space: nowrap;
    transition: border-color 0.15s ease, color 0.15s ease;
}

.hc-agent-chip-add:hover {
    border-color: var(--ag-accent);
    color: var(--ag-accent-strong);
}

.hc-agent-chip-add:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
}
</style>

<script>
export default {
    props: {
        agents: { type: Array, default: () => [] },
        selectedId: { type: Number, default: null },
    },
    emits: ["select", "info", "create"],
};
</script>
