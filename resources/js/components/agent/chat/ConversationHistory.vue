<template>
    <aside
        class="hc-agent-history"
        :class="{ 'is-collapsed': collapsed }"
        :aria-label="$t('agent.chat.history')"
    >
        <div class="hc-agent-history-top">
            <ag-button
                variant="ghost"
                size="sm"
                :icon="collapsed ? 'fa fa-angle-double-right' : 'fa fa-angle-double-left'"
                icon-only
                class="hc-agent-history-toggle"
                :title="$t(collapsed ? 'agent.chat.expand' : 'agent.chat.collapse')"
                :aria-label="$t(collapsed ? 'agent.chat.expand' : 'agent.chat.collapse')"
                :aria-expanded="!collapsed"
                @click="$emit('toggle')"
            />
            <ag-button
                variant="secondary"
                icon="fa fa-plus"
                :icon-only="collapsed"
                :block="!collapsed"
                :title="$t('agent.chat.new_conversation')"
                :aria-label="$t('agent.chat.new_conversation')"
                @click="$emit('new')"
            >
                {{ $t("agent.chat.new_conversation") }}
            </ag-button>
        </div>

        <template v-if="!collapsed">
            <p v-if="loading" class="hc-agent-history-hint">{{ $t("agent.chat.loading") }}</p>
            <p v-else-if="!conversations.length" class="hc-agent-history-hint">
                {{ $t("agent.chat.no_history") }}
            </p>

            <ul class="hc-agent-history-list">
                <li
                    v-for="conversation in conversations"
                    :key="conversation.id"
                    :class="{ 'is-active': conversation.id === activeId }"
                >
                    <button
                        type="button"
                        class="hc-agent-history-item"
                        :title="conversation.title"
                        @click="$emit('open', conversation.id)"
                    >
                        <img
                            v-if="conversation.agent && conversation.agent.avatar_url"
                            :src="conversation.agent.avatar_url"
                            alt=""
                            width="20"
                            height="20"
                        />
                        <i v-else class="fa fa-robot" aria-hidden="true"></i>
                        <span class="hc-agent-history-title" v-text="conversation.title || $t('agent.chat.untitled')"></span>
                    </button>
                    <ag-button
                        variant="ghost"
                        size="sm"
                        icon="fa fa-trash"
                        icon-only
                        class="hc-agent-history-remove"
                        :title="$t('agent.chat.delete_conversation')"
                        :aria-label="$t('agent.chat.delete_conversation')"
                        @click.stop="$emit('remove', conversation)"
                    />
                </li>
            </ul>
        </template>
    </aside>
</template>

<style>
.hc-agent-history {
    flex: 0 0 290px;
    width: 290px;
    display: flex;
    flex-direction: column;
    background: var(--ag-surface);
    border-right: 1px solid var(--ag-line);
    transition: width 0.2s ease, flex-basis 0.2s ease;
    overflow: hidden;
}

.hc-agent-history.is-collapsed {
    flex-basis: 56px;
    width: 56px;
}

.hc-agent-history-top {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
}

.hc-agent-history.is-collapsed .hc-agent-history-top {
    align-items: center;
    padding: 12px 0;
}

.hc-agent-history-toggle {
    align-self: flex-end;
}

.hc-agent-history.is-collapsed .hc-agent-history-toggle {
    align-self: center;
}

.hc-agent-history-hint {
    margin: 0;
    padding: 6px 16px;
    color: var(--ag-muted);
    font-size: 13px;
}

.hc-agent-history-list {
    list-style: none;
    margin: 0;
    padding: 4px 8px 12px;
    overflow-y: auto;
    flex: 1;
}

.hc-agent-history-list li {
    display: flex;
    align-items: center;
    border-radius: 10px;
    transition: background-color 0.12s ease;
}

.hc-agent-history-list li:hover {
    background: rgba(15, 23, 42, 0.05);
}

.hc-agent-history-list li.is-active {
    background: #fff;
    box-shadow: 0 0 0 1px var(--ag-line), 0 1px 3px rgba(15, 23, 42, 0.06);
}

.hc-agent-history-item {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 0;
    background: transparent;
    cursor: pointer;
    font: inherit;
    text-align: left;
    padding: 9px 10px;
    color: var(--ag-ink-soft);
    border-radius: 10px;
}

.hc-agent-history-item:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
}

.hc-agent-history-list li.is-active .hc-agent-history-item {
    color: var(--ag-ink);
    font-weight: 600;
}

.hc-agent-history-item img,
.hc-agent-history-item .fa-robot {
    flex: 0 0 22px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    object-fit: cover;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e2e8f0;
    color: var(--ag-muted);
}

.hc-agent-history-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-agent-history-remove {
    margin-right: 4px;
    visibility: hidden;
}

.hc-agent-history-list li:hover .hc-agent-history-remove,
.hc-agent-history-remove:focus-visible {
    visibility: visible;
}

@media (max-width: 767px) {
    .hc-agent-history {
        flex-basis: 56px;
        width: 56px;
    }

    .hc-agent-history:not(.is-collapsed) {
        position: absolute;
        z-index: 15;
        top: 0;
        bottom: 0;
        left: 0;
        width: 290px;
        box-shadow: 8px 0 32px rgba(15, 23, 42, 0.18);
    }
}
</style>

<script>
import AgButton from "../ui/AgentButton.vue";

export default {
    components: { AgButton },

    props: {
        conversations: { type: Array, default: () => [] },
        activeId: { type: Number, default: null },
        collapsed: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
    },
    emits: ["open", "new", "remove", "toggle"],
};
</script>
