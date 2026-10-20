<template>
    <div class="sb-card" :class="{ 'is-kpi': isKpi, 'is-editing': editing }">
        <div class="sb-card-head" :class="{ 'is-floating': isKpi }">
            <div v-if="!isKpi" class="sb-card-titles">
                <div class="sb-card-title" :title="title">{{ title }}</div>
                <div v-if="subtitle" class="sb-card-sub">{{ subtitle }}</div>
            </div>
            <div class="sb-card-actions">
                <span v-if="editing" class="sb-drag" data-tip="Glisser pour déplacer"><i class="fa fa-grip-vertical"></i></span>
                <span v-if="activeFilters.length" class="sb-filter-badge" :data-tip="'Filtres globaux : ' + activeFilters.join(', ')">
                    <i class="fa fa-filter"></i>
                </span>
                <div class="sb-menu-wrap" ref="menu">
                    <button class="sb-icon-btn" data-tip="Options" @click.stop="open = !open">
                        <i class="fa fa-ellipsis"></i>
                    </button>
                    <div v-if="open" class="sb-menu" @click="open = false">
                        <button @click="$emit('edit')"><i class="fa fa-pen"></i> Modifier</button>
                        <button @click="$emit('duplicate')"><i class="fa fa-copy"></i> Dupliquer</button>
                        <button @click="$emit('expand')"><i class="fa fa-up-right-and-down-left-from-center"></i> Agrandir</button>
                        <button @click="$emit('collapse')"><i class="fa fa-down-left-and-up-right-to-center"></i> Réduire</button>
                        <button @click="refresh"><i class="fa fa-rotate"></i> Actualiser</button>
                        <button @click="$emit('reset-size')"><i class="fa fa-arrows-rotate"></i> Réinitialiser la taille</button>
                        <div class="sb-menu-sep"></div>
                        <button @click="$emit('hide')"><i class="fa fa-eye-slash"></i> Masquer</button>
                        <button class="is-danger" @click="$emit('remove')"><i class="fa fa-trash"></i> Supprimer</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="sb-card-body">
            <WidgetView :config="widget.config" :global-filters="globalFilters" :label="title" :refresh-key="refreshKey + localKey" />
        </div>

        <span v-if="editing" class="sb-grip" data-tip="Redimensionner" @pointerdown.prevent.stop="$emit('resize-start', $event)">
            <svg width="14" height="14" viewBox="0 0 14 14"><path d="M12 2 2 12M12 7 7 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none" /></svg>
        </span>
    </div>
</template>

<script>
import WidgetView from "./WidgetView.vue";
import { widgetTitle } from "./registry";

const LABELS = { period: "Période", user: "Utilisateur", team: "Équipe", role: "Rôle", status: "Statut", source: "Source" };

export default {
    components: { WidgetView },

    props: {
        widget: { type: Object, required: true },
        editing: { type: Boolean, default: false },
        globalFilters: { type: Object, default: () => ({}) },
        enabledFilters: { type: Array, default: () => [] },
        refreshKey: { type: Number, default: 0 },
    },

    emits: ["edit", "duplicate", "expand", "collapse", "hide", "remove", "reset-size", "resize-start"],

    data() {
        return { open: false, localKey: 0 };
    },

    computed: {
        isKpi() {
            return this.widget.type === "kpi";
        },
        title() {
            return widgetTitle(this.widget);
        },
        subtitle() {
            return this.widget.config.subtitle || "";
        },
        // Filtres globaux actifs ET pris en compte par ce widget
        activeFilters() {
            const respected = this.widget.config.globalFilters || [];
            return this.enabledFilters.filter((f) => respected.includes(f)).map((f) => LABELS[f]);
        },
    },

    mounted() {
        document.addEventListener("click", this.close);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.close);
    },

    methods: {
        close() {
            this.open = false;
        },
        refresh() {
            this.localKey++;
        },
    },
};
</script>

<style scoped>
.sb-card {
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    background: var(--sb-surface);
    border: 1px solid var(--sb-border);
    border-radius: 14px;
    padding: 14px 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: box-shadow 0.2s, border-color 0.2s, transform 0.2s;
    min-width: 0;
}
.sb-card:hover {
    box-shadow: 0 6px 20px -8px rgba(15, 23, 42, 0.18);
}
.sb-card.is-editing {
    border-style: dashed;
    border-color: var(--sb-accent);
}
.sb-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
    flex: none;
}
.sb-card-head.is-floating {
    position: absolute;
    top: 8px;
    right: 8px;
    margin: 0;
    z-index: 3;
    opacity: 0;
    transition: opacity 0.15s;
}
.sb-card:hover .sb-card-head.is-floating,
.sb-card.is-editing .sb-card-head.is-floating {
    opacity: 1;
}
.sb-card-titles {
    min-width: 0;
}
.sb-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--sb-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sb-card-sub {
    font-size: 12px;
    color: var(--sb-muted);
    margin-top: 1px;
}
.sb-card-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: none;
}
.sb-drag {
    cursor: grab;
    color: var(--sb-muted);
    padding: 4px 6px;
    border-radius: 6px;
}
.sb-drag:hover { background: var(--sb-bg-soft); }
.sb-filter-badge {
    color: var(--sb-accent);
    font-size: 11px;
    padding: 4px;
}
.sb-icon-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    color: var(--sb-muted);
    border-radius: 8px;
    cursor: pointer;
}
.sb-icon-btn:hover { background: var(--sb-bg-soft); color: var(--sb-text); }
.sb-menu-wrap { position: relative; }
.sb-menu {
    position: absolute;
    right: 0;
    top: 32px;
    z-index: 50;
    min-width: 200px;
    background: var(--sb-surface);
    border: 1px solid var(--sb-border);
    border-radius: 12px;
    box-shadow: 0 12px 32px -8px rgba(15, 23, 42, 0.25);
    padding: 6px;
    animation: sb-pop 0.12s ease-out;
}
.sb-menu button {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 8px 10px;
    border: none;
    background: transparent;
    border-radius: 8px;
    font-size: 13px;
    color: var(--sb-text);
    cursor: pointer;
    text-align: left;
}
.sb-menu button i { width: 14px; color: var(--sb-muted); text-align: center; }
.sb-menu button:hover { background: var(--sb-bg-soft); }
.sb-menu button.is-danger, .sb-menu button.is-danger i { color: #dc2626; }
.sb-menu-sep { height: 1px; background: var(--sb-border); margin: 4px 2px; }
@keyframes sb-pop {
    from { opacity: 0; transform: translateY(-4px) scale(0.98); }
    to { opacity: 1; transform: none; }
}
.sb-card-body {
    flex: 1;
    min-height: 0;
}
.sb-grip {
    position: absolute;
    right: 4px;
    bottom: 4px;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sb-accent);
    cursor: nwse-resize;
    touch-action: none;
    border-radius: 6px;
    z-index: 4;
}
.sb-grip:hover { background: var(--sb-accent-soft); }
@media (max-width: 1023px) {
    .sb-grip { display: none; }
    .sb-card-head.is-floating { opacity: 1; }
}
</style>
