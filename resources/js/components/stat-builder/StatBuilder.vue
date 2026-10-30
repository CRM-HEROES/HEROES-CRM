<template>
    <div class="sb-root sb-page">
        <!-- En-tête -->
        <header class="sb-header">
            <div class="sb-header-text">
                <h1>Statistiques</h1>
                <p>Construisez votre dashboard : choisissez vos données, personnalisez, organisez.</p>
            </div>

            <div class="sb-header-actions">
                <span class="sb-save" :class="'is-' + saveState" :data-tip="saveTip">
                    <i class="fa" :class="saveIcon"></i>
                    <span class="sb-hide-sm">{{ saveLabel }}</span>
                </span>

                <div v-if="hiddenWidgets.length" class="sb-pop-wrap" @click.stop>
                    <button class="sb-btn-ghost" @click="showHidden = !showHidden">
                        <i class="fa fa-eye-slash"></i> <span class="sb-hide-sm">Masqués</span> ({{ hiddenWidgets.length }})
                    </button>
                    <div v-if="showHidden" class="sb-pop">
                        <div class="sb-pop-title">Widgets masqués</div>
                        <div v-for="w in hiddenWidgets" :key="w.id" class="sb-pop-row">
                            <span><i class="fa" :class="typeIcon(w)"></i> {{ titleOf(w) }}</span>
                            <button class="sb-link" @click="show(w)">Afficher</button>
                        </div>
                    </div>
                </div>

                <button class="sb-btn-ghost" :class="{ 'is-on': editing }" @click="editing = !editing">
                    <i class="fa" :class="editing ? 'fa-check' : 'fa-up-down-left-right'"></i>
                    {{ editing ? "Terminer" : "Personnaliser" }}
                </button>

                <button class="sb-btn-primary" :disabled="!meta" @click="openWizard(null)">
                    <i class="fa fa-plus"></i> Ajouter un widget
                </button>

                <div class="sb-pop-wrap" @click.stop>
                    <button class="sb-icon-btn-lg" data-tip="Plus d'options" @click="showMore = !showMore">
                        <i class="fa fa-ellipsis-vertical"></i>
                    </button>
                    <div v-if="showMore" class="sb-pop is-right" @click="showMore = false">
                        <button class="sb-pop-item" @click="refreshAll"><i class="fa fa-rotate"></i> Tout actualiser</button>
                        <button class="sb-pop-item" @click="resetDashboard"><i class="fa fa-arrow-rotate-left"></i> Réinitialiser le dashboard</button>
                        <router-link class="sb-pop-item" :to="{ name: 'stat-classic' }"><i class="fa fa-clock-rotate-left"></i> Ancienne version</router-link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Filtres globaux -->
        <GlobalFilters
            v-if="meta && dashboard"
            :meta="meta"
            :model-value="dashboard.globalFilters"
            :enabled="dashboard.enabledFilters"
            @update:model-value="dashboard.globalFilters = $event"
            @update:enabled="dashboard.enabledFilters = $event"
        />

        <p v-if="editing" class="sb-edit-tip">
            <i class="fa fa-circle-info"></i>
            Mode personnalisation : glissez les widgets par la poignée <i class="fa fa-grip-vertical"></i> et redimensionnez-les avec le coin <strong>bas-droit</strong>.
        </p>

        <!-- Chargement -->
        <div v-if="loading" class="sb-grid-skel">
            <div v-for="i in 4" :key="i" class="sb-skel-card"></div>
        </div>

        <!-- Erreur -->
        <div v-else-if="loadError" class="sb-empty">
            <i class="fa fa-triangle-exclamation"></i>
            <h3>Impossible de charger le dashboard</h3>
            <p>{{ loadError }}</p>
            <button class="sb-btn-primary" @click="load()">Réessayer</button>
        </div>

        <!-- Empty state -->
        <div v-else-if="!visibleWidgets.length" class="sb-empty">
            <div class="sb-empty-art"><i class="fa fa-chart-pie"></i></div>
            <h3>Votre dashboard est vide</h3>
            <p>Créez votre premier widget : KPI, graphique, tableau… en quelques clics.</p>
            <button class="sb-btn-primary" :disabled="!meta" @click="openWizard(null)"><i class="fa fa-plus"></i> Créer un widget</button>
        </div>

        <!-- Grille -->
        <DashboardGrid
            v-else
            :widgets="visibleWidgets"
            :editing="editing"
            :global-filters="dashboard.globalFilters"
            :enabled-filters="dashboard.enabledFilters"
            :refresh-key="refreshKey"
            @reorder="reorder"
            @resize="onResize"
            @edit="openWizard"
            @duplicate="duplicate"
            @expand="expand"
            @collapse="collapse"
            @hide="hide"
            @remove="remove"
            @reset-size="resetSize"
        />

        <Wizard
            v-if="wizard.open"
            :meta="meta"
            :widget="wizard.widget"
            :global-filters="dashboard.globalFilters"
            @close="wizard.open = false"
            @save="onWizardSave"
        />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import ApiService from "@/apis/api.service";
import GlobalFilters from "./GlobalFilters.vue";
import DashboardGrid from "./DashboardGrid.vue";
import Wizard from "./wizard/Wizard.vue";
import { WIDGET_TYPES, clone, uid, widgetTitle } from "./registry";

export default {
    components: { GlobalFilters, DashboardGrid, Wizard },

    data() {
        return {
            meta: null,
            metaProject: null,
            loadRequest: 0,
            dashboard: null,
            loading: true,
            loadError: null,
            editing: false,
            refreshKey: 0,
            saveState: "saved", // saved | dirty | saving | error
            saveTimer: null,
            ready: false,
            showHidden: false,
            showMore: false,
            wizard: { open: false, widget: null },
        };
    },

    computed: {
        ...mapGetters(["project"]),

        visibleWidgets() {
            return this.dashboard ? this.dashboard.widgets.filter((w) => !w.hidden) : [];
        },

        hiddenWidgets() {
            return this.dashboard ? this.dashboard.widgets.filter((w) => w.hidden) : [];
        },

        saveLabel() {
            return { saved: "Enregistré", dirty: "Modifications…", saving: "Enregistrement…", error: "Erreur" }[this.saveState];
        },
        saveIcon() {
            return { saved: "fa-circle-check", dirty: "fa-pen", saving: "fa-spinner fa-spin", error: "fa-circle-exclamation" }[this.saveState];
        },
        saveTip() {
            return this.saveState === "error" ? "Cliquez sur Personnaliser puis réessayez" : "Sauvegarde automatique";
        },
    },

    watch: {
        "project.slug"() {
            this.load();
        },

        dashboard: {
            deep: true,
            handler() {
                if (!this.ready) return;
                this.saveState = "dirty";
                clearTimeout(this.saveTimer);
                this.saveTimer = setTimeout(this.save, 900);
            },
        },
    },

    created() {
        this.load();
        document.addEventListener("click", this.closePops);
    },

    beforeUnmount() {
        this.loadRequest++;
        document.removeEventListener("click", this.closePops);
        // Sauvegarde en attente avant de quitter la page
        if (this.saveState === "dirty") {
            clearTimeout(this.saveTimer);
            this.save();
        }
    },

    methods: {
        closePops() {
            this.showHidden = false;
            this.showMore = false;
        },

        typeIcon(w) {
            return (WIDGET_TYPES[w.type] || {}).icon;
        },

        titleOf: widgetTitle,

        async load(useDefault = false) {
            if (!this.project) return;
            const project = this.project.slug;
            const request = ++this.loadRequest;
            const isCurrent = () => request === this.loadRequest && this.project && this.project.slug === project;
            if (this.metaProject !== project) {
                clearTimeout(this.saveTimer);
                this.meta = null;
                this.wizard.open = false;
            }
            this.loading = true;
            this.loadError = null;
            this.ready = false;

            try {
                const base = `project/${project}/stat-dashboard`;
                const [meta, dash] = await Promise.all([
                    this.meta && this.metaProject === project && !useDefault ? Promise.resolve({ data: this.meta }) : ApiService.get(`${base}/meta`),
                    ApiService.get(base, { params: useDefault ? { default: 1 } : {} }),
                ]);
                if (!isCurrent()) return;
                this.meta = meta.data;
                this.metaProject = project;
                const d = dash.data;
                this.dashboard = {
                    widgets: d.widgets || [],
                    globalFilters: Array.isArray(d.globalFilters) || !d.globalFilters ? { period: { preset: "last_30_days" } } : d.globalFilters,
                    enabledFilters: d.enabledFilters || ["period"],
                };
                if (!this.dashboard.globalFilters.period) this.dashboard.globalFilters.period = { preset: "last_30_days" };
                this.saveState = useDefault ? "dirty" : "saved";
            } catch (e) {
                if (!isCurrent()) return;
                const res = e.response && e.response.data;
                this.loadError = (res && res.message) || "Erreur réseau.";
            } finally {
                if (!isCurrent()) return;
                this.loading = false;
                // Évite de déclencher une sauvegarde à cause du chargement initial
                this.$nextTick(() => {
                    if (!isCurrent()) return;
                    this.ready = true;
                    if (useDefault) {
                        this.saveState = "dirty";
                        clearTimeout(this.saveTimer);
                        this.saveTimer = setTimeout(this.save, 300);
                    }
                });
            }
        },

        async save() {
            if (!this.dashboard || !this.project) return;
            this.saveState = "saving";
            try {
                await ApiService.put(`project/${this.project.slug}/stat-dashboard`, {
                    widgets: this.dashboard.widgets,
                    globalFilters: this.dashboard.globalFilters,
                    enabledFilters: this.dashboard.enabledFilters,
                });
                if (this.saveState === "saving") this.saveState = "saved";
            } catch (e) {
                this.saveState = "error";
            }
        },

        // --- Widgets -------------------------------------------------------

        openWizard(widget) {
            if (!this.meta) return;
            this.wizard = { open: true, widget: widget ? clone(widget) : null };
        },

        onWizardSave(widget) {
            const list = this.dashboard.widgets;
            const i = list.findIndex((w) => w.id === widget.id);
            if (i >= 0) list.splice(i, 1, widget);
            else list.push(widget);
            this.wizard.open = false;
        },

        reorder(ordered) {
            this.dashboard.widgets = [...ordered, ...this.hiddenWidgets];
        },

        patch(w, layout) {
            const target = this.dashboard.widgets.find((x) => x.id === w.id);
            if (target) target.layout = { ...target.layout, ...layout };
        },

        onResize({ id, w, h }) {
            this.patch({ id }, { w, h });
        },

        duplicate(w) {
            const copy = clone(w);
            copy.id = uid();
            copy.title = `${widgetTitle(w)} (copie)`;
            copy.config.title = copy.title;
            const i = this.dashboard.widgets.findIndex((x) => x.id === w.id);
            this.dashboard.widgets.splice(i + 1, 0, copy);
        },

        expand(w) {
            const l = w.layout || {};
            this.patch(w, { prev: l.prev || { w: l.w, h: l.h }, w: 12, h: Math.max(l.h || 4, 7) });
        },

        collapse(w) {
            const l = w.layout || {};
            if (l.prev) {
                this.patch(w, { w: l.prev.w, h: l.prev.h, prev: null });
                return;
            }
            const min = (WIDGET_TYPES[w.type] || {}).minLayout || { w: 2, h: 2 };
            this.patch(w, { w: Math.max(min.w, Math.ceil((l.w || 6) / 2)), h: Math.max(min.h, (l.h || 4) - 1) });
        },

        resetSize(w) {
            const def = WIDGET_TYPES[w.type].layout;
            this.patch(w, { w: def.w, h: def.h, prev: null });
        },

        hide(w) {
            const t = this.dashboard.widgets.find((x) => x.id === w.id);
            if (t) t.hidden = true;
        },

        show(w) {
            const t = this.dashboard.widgets.find((x) => x.id === w.id);
            if (t) t.hidden = false;
            if (!this.hiddenWidgets.length) this.showHidden = false;
        },

        remove(w) {
            if (!window.confirm(`Supprimer le widget « ${widgetTitle(w)} » ?`)) return;
            this.dashboard.widgets = this.dashboard.widgets.filter((x) => x.id !== w.id);
        },

        refreshAll() {
            this.refreshKey++;
        },

        resetDashboard() {
            if (!window.confirm("Remplacer votre dashboard par la configuration par défaut ? Vos widgets actuels seront perdus.")) return;
            this.load(true);
        },
    },
};
</script>

<style>
/* ---- Thème & composants partagés du Dashboard Builder (non scopé : le wizard est téléporté) ---- */
.sb-root {
    --sb-bg: #f8fafc;
    --sb-bg-soft: #f1f5f9;
    --sb-surface: #ffffff;
    --sb-border: #e2e8f0;
    --sb-text: #0f172a;
    --sb-muted: #64748b;
    --sb-accent: #4f46e5;
    --sb-accent-soft: rgba(79, 70, 229, 0.1);
    font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
    color: var(--sb-text);
}
.sb-root *, .sb-root *::before, .sb-root *::after { box-sizing: border-box; }

.sb-page {
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    background: var(--sb-bg);
    padding: 24px clamp(14px, 3vw, 40px) 48px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Formulaires */
.sb-section { margin-bottom: 22px; }
.sb-section-title { font-size: 13px; font-weight: 700; color: var(--sb-text); margin-bottom: 8px; }
.sb-hint { font-weight: 400; color: var(--sb-muted); font-size: 12px; }
.sb-label { display: block; font-size: 11px; font-weight: 500; color: var(--sb-muted); margin-bottom: 3px; }
.sb-field { display: flex; flex-direction: column; min-width: 0; }
.sb-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
.sb-row > .sb-field { flex: 1 1 160px; }
.sb-input {
    width: 100%;
    height: 36px;
    border: 1px solid var(--sb-border);
    border-radius: 9px;
    padding: 0 10px;
    font-size: 13px;
    background: var(--sb-surface);
    color: var(--sb-text);
    transition: border-color 0.15s, box-shadow 0.15s;
}
.sb-input:focus { outline: none; border-color: var(--sb-accent); box-shadow: 0 0 0 3px var(--sb-accent-soft); }
.sb-input:disabled { background: var(--sb-bg-soft); color: var(--sb-muted); }
.sb-check { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; margin-top: 6px; color: var(--sb-text); }
.sb-check input { accent-color: var(--sb-accent); width: 15px; height: 15px; }

/* Boutons */
.sb-btn-primary, .sb-btn-ghost {
    height: 36px;
    border-radius: 10px;
    padding: 0 14px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: all 0.15s;
}
.sb-btn-primary { border: 1px solid var(--sb-accent); background: var(--sb-accent); color: #fff; box-shadow: 0 4px 12px -4px rgba(79, 70, 229, 0.6); }
.sb-btn-primary:hover:not(:disabled) { filter: brightness(1.08); transform: translateY(-1px); }
.sb-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; }
.sb-btn-ghost { border: 1px solid var(--sb-border); background: var(--sb-surface); color: var(--sb-text); font-weight: 500; }
.sb-btn-ghost:hover { border-color: var(--sb-accent); color: var(--sb-accent); }
.sb-btn-ghost.is-on { border-color: var(--sb-accent); background: var(--sb-accent-soft); color: var(--sb-accent); font-weight: 600; }
.sb-btn-dashed {
    width: 100%;
    height: 36px;
    border: 1.5px dashed var(--sb-border);
    background: transparent;
    border-radius: 10px;
    color: var(--sb-muted);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}
.sb-btn-dashed:hover { border-color: var(--sb-accent); color: var(--sb-accent); background: var(--sb-accent-soft); }
.sb-btn-icon {
    width: 32px; height: 32px; flex: none; border-radius: 8px; border: 1px solid var(--sb-border);
    background: var(--sb-surface); color: var(--sb-muted); cursor: pointer;
}
.sb-btn-icon:hover { color: var(--sb-text); border-color: var(--sb-text); }
.sb-btn-icon.is-danger:hover { color: #dc2626; border-color: #dc2626; }
.sb-icon-btn-lg { width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--sb-border); background: var(--sb-surface); color: var(--sb-muted); cursor: pointer; }
.sb-icon-btn-lg:hover { color: var(--sb-text); }
.sb-link { border: none; background: transparent; color: var(--sb-accent); font-size: 12px; cursor: pointer; }

/* Infobulles */
[data-tip]:not(.sb-grip) { position: relative; }
[data-tip]:hover::after {
    content: attr(data-tip);
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: #0f172a;
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    line-height: 1.3;
    white-space: nowrap;
    z-index: 9999;
    pointer-events: none;
}

/* En-tête de page */
.sb-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.sb-header h1 { font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -0.02em; color: var(--sb-text); }
.sb-header p { margin: 2px 0 0; font-size: 13px; color: var(--sb-muted); }
.sb-header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.sb-save { font-size: 12px; color: var(--sb-muted); display: inline-flex; align-items: center; gap: 6px; margin-right: 4px; }
.sb-save.is-saved i { color: #10b981; }
.sb-save.is-error { color: #dc2626; }
.sb-pop-wrap { position: relative; }
.sb-pop {
    position: absolute; top: calc(100% + 6px); left: 0; z-index: 70; min-width: 240px;
    background: var(--sb-surface); border: 1px solid var(--sb-border); border-radius: 12px; padding: 6px;
    box-shadow: 0 12px 32px -8px rgba(15, 23, 42, 0.25);
}
.sb-pop.is-right { left: auto; right: 0; }
.sb-pop-title { font-size: 11px; font-weight: 600; color: var(--sb-muted); text-transform: uppercase; letter-spacing: 0.04em; padding: 6px 8px; }
.sb-pop-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 6px 8px; font-size: 13px; }
.sb-pop-item {
    display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 10px; border: none; background: transparent;
    border-radius: 8px; font-size: 13px; color: var(--sb-text); cursor: pointer; text-align: left; text-decoration: none;
}
.sb-pop-item i { width: 14px; color: var(--sb-muted); }
.sb-pop-item:hover { background: var(--sb-bg-soft); }
.sb-edit-tip {
    margin: 0; font-size: 12px; color: var(--sb-accent); background: var(--sb-accent-soft);
    padding: 8px 12px; border-radius: 10px;
}

/* États */
.sb-empty {
    display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px;
    padding: 64px 20px; border: 2px dashed var(--sb-border); border-radius: 18px; background: var(--sb-surface); color: var(--sb-muted);
}
.sb-empty h3 { margin: 0; color: var(--sb-text); font-size: 18px; }
.sb-empty p { margin: 0 0 8px; font-size: 13px; max-width: 360px; }
.sb-empty > i { font-size: 32px; color: #ef4444; }
.sb-empty-art {
    width: 72px; height: 72px; border-radius: 20px; background: var(--sb-accent-soft); color: var(--sb-accent);
    display: flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 6px;
}
.sb-grid-skel { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
.sb-skel-card {
    height: 130px; border-radius: 14px; border: 1px solid var(--sb-border);
    background: linear-gradient(90deg, var(--sb-bg-soft) 25%, var(--sb-surface) 50%, var(--sb-bg-soft) 75%);
    background-size: 200% 100%; animation: sb-shimmer-page 1.3s infinite;
}
@keyframes sb-shimmer-page { from { background-position: 200% 0; } to { background-position: -200% 0; } }

@media (max-width: 767px) {
    .sb-page { padding: 16px 14px 40px; }
    .sb-header h1 { font-size: 20px; }
    .sb-header-actions { width: 100%; }
    .sb-hide-sm { display: none; }
    .sb-header-actions .sb-btn-primary { flex: 1; justify-content: center; }
}
</style>
