<template>
    <teleport to="body">
        <div class="sb-root sb-overlay" @mousedown.self="$emit('close')">
            <div class="sb-modal" role="dialog" aria-modal="true">
                <!-- En-tête + stepper -->
                <header class="sb-modal-head">
                    <div class="sb-modal-title">{{ editing ? "Modifier le widget" : "Nouveau widget" }}</div>
                    <ol class="sb-stepper">
                        <li
                            v-for="(s, i) in steps"
                            :key="s"
                            :class="{ 'is-active': step === i + 1, 'is-done': step > i + 1 }"
                            @click="goto(i + 1)"
                        >
                            <span class="sb-stepper-dot"><i v-if="step > i + 1" class="fa fa-check"></i><template v-else>{{ i + 1 }}</template></span>
                            <span class="sb-stepper-label">{{ s }}</span>
                        </li>
                    </ol>
                    <button class="sb-modal-close" data-tip="Fermer" @click="$emit('close')"><i class="fa fa-xmark"></i></button>
                </header>

                <!-- Contenu -->
                <div class="sb-modal-body">
                    <StepType v-if="step === 1" :model-value="type" @update:model-value="selectType" />

                    <div v-else-if="step === 2" class="sb-two-cols">
                        <StepData :config="config" :meta="meta" />
                        <aside class="sb-live">
                            <div class="sb-live-label"><i class="fa fa-bolt"></i> Aperçu en direct</div>
                            <PreviewCard :config="config" :global-filters="globalFilters" />
                        </aside>
                    </div>

                    <div v-else-if="step === 3">
                        <h3 class="sb-step-title">Aperçu avec vos données</h3>
                        <p class="sb-step-help">Ajustez le titre et l'affichage : le rendu se met à jour instantanément.</p>
                        <PreviewCard :config="config" :global-filters="globalFilters" />
                        <div style="margin-top: 16px">
                            <DisplaySection :config="config" :global-filters="meta.global_filters" />
                        </div>
                    </div>

                    <StepValidate v-else :config="config" :meta="meta" :global-filters="globalFilters" :editing="editing" />
                </div>

                <!-- Pied -->
                <footer class="sb-modal-foot">
                    <button v-if="step > 1" class="sb-btn-ghost" data-tip="Revenir à la configuration initiale" @click="reset">
                        <i class="fa fa-rotate-left"></i> <span class="sb-hide-sm">Réinitialiser</span>
                    </button>
                    <div class="sb-foot-msg" :class="{ 'is-error': !!error }">
                        <template v-if="error"><i class="fa fa-circle-exclamation"></i> {{ error }}</template>
                    </div>
                    <button class="sb-btn-ghost" @click="$emit('close')">Annuler</button>
                    <button v-if="step > 1" class="sb-btn-ghost" @click="step--"><i class="fa fa-arrow-left"></i> Précédent</button>
                    <button v-if="step < 4" class="sb-btn-primary" :disabled="!!error" @click="step++">
                        Suivant <i class="fa fa-arrow-right"></i>
                    </button>
                    <button v-else class="sb-btn-primary" :disabled="!!error" @click="save">
                        <i class="fa fa-check"></i> {{ editing ? "Enregistrer" : "Ajouter au dashboard" }}
                    </button>
                </footer>
            </div>
        </div>
    </teleport>
</template>

<script>
import StepType from "./StepType.vue";
import StepData from "./StepData.vue";
import StepValidate from "./StepValidate.vue";
import PreviewCard from "./PreviewCard.vue";
import DisplaySection from "../config/DisplaySection.vue";
import { WIDGET_TYPES, buildDefaultConfig, clone, uid } from "../registry";

export default {
    components: { StepType, StepData, StepValidate, PreviewCard, DisplaySection },

    props: {
        meta: { type: Object, required: true },
        // Widget existant (édition) ou null (création)
        widget: { type: Object, default: null },
        globalFilters: { type: Object, default: () => ({}) },
    },

    emits: ["close", "save"],

    data() {
        const editing = !!this.widget;

        return {
            editing,
            step: editing ? 2 : 1,
            steps: ["Type", "Données", "Aperçu", "Validation"],
            type: editing ? this.widget.type : "",
            config: editing ? clone(this.widget.config) : null,
            initial: editing ? clone(this.widget.config) : null,
        };
    },

    computed: {
        // Message bloquant pour passer à l'étape suivante
        error() {
            if (this.step === 1) return this.type ? "" : "Choisissez un type de visualisation.";
            return this.configError(this.config);
        },
    },

    mounted() {
        document.addEventListener("keydown", this.onKey);
        document.body.style.overflow = "hidden";
    },

    beforeUnmount() {
        document.removeEventListener("keydown", this.onKey);
        document.body.style.overflow = "";
    },

    methods: {
        onKey(e) {
            if (e.key === "Escape") this.$emit("close");
        },

        defaultSource() {
            return this.meta.sources[0];
        },

        selectType(type) {
            if (type === this.type) return;
            const prev = this.config;
            let source = (prev && this.meta.sources.find((s) => s.key === prev.dataSource)) || this.defaultSource();
            // La heatmap et le funnel exigent une source temporelle ("events")
            if (["heatmap", "funnel"].includes(type) && source.kind !== "events") {
                source = this.meta.sources.find((s) => s.kind === "events");
            }
            const next = buildDefaultConfig(type, source);

            // On conserve ce qui est commun d'un type à l'autre
            if (prev) {
                next.title = prev.title;
                next.subtitle = prev.subtitle;
                next.period = prev.period || next.period;
                next.globalFilters = prev.globalFilters || next.globalFilters;
            }

            this.type = type;
            this.config = next;
            if (!this.initial) this.initial = clone(next);
        },

        goto(n) {
            // On ne peut revenir que vers l'arrière, ou avancer si valide
            if (n <= this.step) {
                this.step = n;
                return;
            }
            if (!this.type) return;
            for (let i = this.step; i < n; i++) {
                if (i >= 2 && this.configError(this.config)) return;
            }
            this.step = n;
        },

        reset() {
            if (this.editing && this.initial) {
                this.config = clone(this.initial);
                this.type = this.config.type;
            } else {
                const source = this.meta.sources.find((s) => s.key === this.config.dataSource) || this.defaultSource();
                this.config = buildDefaultConfig(this.type, source);
            }
        },

        configError(c) {
            if (!c) return "Choisissez un type de visualisation.";
            const def = WIDGET_TYPES[c.type];

            if (this.step >= 2 && def.sections.includes("dimension") && !c.dimension) return "Choisissez une dimension.";
            if (this.step >= 2 && def.sections.includes("series") && !(c.series || []).length) return "Ajoutez au moins une série de données.";
            if (this.step >= 2 && def.sections.includes("columns") && !(c.columns || []).length) return "Ajoutez au moins une colonne.";
            if (this.step >= 2 && def.sections.includes("columns")) {
                const followups = (this.meta.options && this.meta.options.followup) || [];
                const selected = [];
                for (const column of c.columns || []) {
                    if (column.field === "followup") return "Choisissez le suivi à compter pour chaque colonne Suivi.";
                    if (!String(column.field).startsWith("followup_")) continue;
                    const id = String(column.field).slice("followup_".length);
                    if (c.dataSource !== "users" || !/^[1-9]\d*$/.test(id) || !followups.some((followup) => String(followup.value) === id)) {
                        return "Choisissez un suivi disponible dans ce projet.";
                    }
                    if (selected.includes(id)) return "Ce suivi est déjà présent dans les colonnes.";
                    selected.push(id);
                }
            }
            if (this.step >= 2 && c.type === "funnel" && (c.steps || []).length < 2) return "Un funnel nécessite au moins 2 étapes.";
            if (this.step >= 2 && c.period && c.period.preset === "custom" && !(c.globalFilters || []).includes("period") && (!c.period.from || !c.period.to)) {
                return "Renseignez les dates de la période personnalisée.";
            }

            return "";
        },

        save() {
            if (this.error) return;
            const config = clone(this.config);
            config.type = this.type;
            if (!config.title) config.title = WIDGET_TYPES[this.type].label;
            (config.steps || []).forEach((s) => delete s._id);

            const def = WIDGET_TYPES[this.type];
            const widget = this.editing
                ? { ...this.widget, type: this.type, title: config.title, config }
                : {
                      id: uid(),
                      type: this.type,
                      title: config.title,
                      config,
                      layout: { ...def.layout },
                      hidden: false,
                  };

            // Si le type change en édition, on adapte la taille par défaut
            if (this.editing && this.type !== this.widget.type) widget.layout = { ...def.layout };

            this.$emit("save", widget);
        },
    },
};
</script>

<style>
/* Non scopé : le wizard est téléporté dans <body> */
.sb-overlay {
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: sb-fade 0.15s ease;
}
.sb-modal {
    width: 100%;
    max-width: 1120px;
    height: min(860px, 100%);
    background: var(--sb-bg);
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 30px 80px -20px rgba(15, 23, 42, 0.5);
    animation: sb-rise 0.2s ease;
}
.sb-modal-head {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 14px 20px;
    background: var(--sb-surface);
    border-bottom: 1px solid var(--sb-border);
}
.sb-modal-title { font-size: 16px; font-weight: 700; color: var(--sb-text); white-space: nowrap; }
.sb-stepper { display: flex; gap: 6px; list-style: none; margin: 0 auto; padding: 0; }
.sb-stepper li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px 4px 4px;
    border-radius: 99px;
    color: var(--sb-muted);
    font-size: 13px;
    cursor: pointer;
    transition: background 0.15s;
}
.sb-stepper-dot {
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--sb-bg-soft); border: 1px solid var(--sb-border);
    font-size: 11px; font-weight: 700;
}
.sb-stepper li.is-active { color: var(--sb-accent); background: var(--sb-accent-soft); font-weight: 600; }
.sb-stepper li.is-active .sb-stepper-dot { background: var(--sb-accent); color: #fff; border-color: var(--sb-accent); }
.sb-stepper li.is-done .sb-stepper-dot { background: #10b981; color: #fff; border-color: #10b981; }
.sb-modal-close {
    width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent;
    color: var(--sb-muted); cursor: pointer; font-size: 16px;
}
.sb-modal-close:hover { background: var(--sb-bg-soft); color: var(--sb-text); }
.sb-modal-body { flex: 1; min-height: 0; overflow: auto; padding: 22px; }
.sb-modal-foot {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 20px; background: var(--sb-surface); border-top: 1px solid var(--sb-border);
}
.sb-foot-msg { flex: 1; font-size: 12px; color: var(--sb-muted); }
.sb-foot-msg.is-error { color: #dc2626; }
.sb-step-title { font-size: 18px; font-weight: 700; color: var(--sb-text); margin: 0; }
.sb-step-help { font-size: 13px; color: var(--sb-muted); margin: 4px 0 16px; }
.sb-two-cols { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 24px; align-items: start; }
.sb-live { position: sticky; top: 0; }
.sb-live-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--sb-accent); margin-bottom: 8px; }
.sb-hide-sm { display: inline; }
@keyframes sb-fade { from { opacity: 0; } to { opacity: 1; } }
@keyframes sb-rise { from { opacity: 0; transform: translateY(14px) scale(0.99); } to { opacity: 1; transform: none; } }

@media (max-width: 1000px) {
    .sb-two-cols { grid-template-columns: 1fr; }
    .sb-live { display: none; }
}
@media (max-width: 767px) {
    .sb-overlay { padding: 0; }
    .sb-modal { max-width: none; height: 100%; border-radius: 0; }
    .sb-modal-head { flex-wrap: wrap; gap: 8px 12px; padding: 12px 14px; }
    .sb-modal-title { flex: 1; }
    .sb-stepper { order: 3; width: 100%; justify-content: space-between; }
    .sb-stepper li { padding: 4px; }
    .sb-stepper-label { display: none; }
    .sb-stepper li.is-active .sb-stepper-label { display: inline; }
    .sb-modal-body { padding: 16px 14px; }
    .sb-modal-foot { flex-wrap: wrap; padding: 10px 14px; }
    .sb-foot-msg { flex: 1 1 100%; order: -1; }
    .sb-hide-sm { display: none; }
}
</style>
