<template>
    <section class="sb-section">
        <div class="sb-section-title">Étapes du funnel</div>
        <div class="sb-hint-block">Chaque étape compte des éléments d'une source. Le taux de conversion est calculé par rapport à la première étape et à l'étape précédente.</div>

        <draggable v-model="config.steps" item-key="_id" handle=".sb-step-handle" :animation="180" ghost-class="sb-ghost" class="sb-steps">
            <template #item="{ element: step, index }">
                <div class="sb-step">
                    <div class="sb-step-head">
                        <span class="sb-step-handle"><i class="fa fa-grip-vertical"></i></span>
                        <span class="sb-step-num">{{ index + 1 }}</span>
                        <input class="sb-input" v-model="step.label" placeholder="Nom de l'étape" />
                        <select class="sb-input" :value="step.dataSource" @change="setSource(step, $event.target.value)">
                            <option v-for="s in eventSources" :key="s.key" :value="s.key">{{ s.label }}</option>
                        </select>
                        <button type="button" class="sb-btn-icon is-danger" @click="config.steps.splice(index, 1)"><i class="fa fa-xmark"></i></button>
                    </div>
                    <details class="sb-step-filters">
                        <summary>Filtres de l'étape ({{ (step.filters || []).length }})</summary>
                        <FiltersSection :holder="step" :source="sourceOf(step.dataSource)" title="Filtres de l'étape" />
                    </details>
                </div>
            </template>
        </draggable>

        <button type="button" class="sb-btn-dashed" @click="add"><i class="fa fa-plus"></i> Ajouter une étape</button>
    </section>
</template>

<script>
import draggable from "vuedraggable";
import FiltersSection from "./FiltersSection.vue";
import { uid } from "../registry";

export default {
    components: { draggable, FiltersSection },

    props: {
        config: { type: Object, required: true },
        sources: { type: Array, required: true },
    },

    created() {
        // Clé stable pour le drag & drop (le libellé est éditable)
        this.config.steps.forEach((s) => {
            if (!s._id) s._id = uid();
        });
    },

    computed: {
        eventSources() {
            return this.sources.filter((s) => s.kind === "events");
        },
    },

    methods: {
        sourceOf(key) {
            return this.sources.find((s) => s.key === key) || this.eventSources[0];
        },

        setSource(step, key) {
            step.dataSource = key;
            step.field = "records";
            step.agg = "count";
            step.filters = [];
            if (!step.label || this.eventSources.some((s) => s.label === step.label)) {
                step.label = this.sourceOf(key).label;
            }
        },

        add() {
            const s = this.eventSources[0];
            this.config.steps.push({ _id: uid(), label: s.label, dataSource: s.key, field: "records", agg: "count", filters: [] });
        },
    },
};
</script>

<style scoped>
.sb-steps { display: flex; flex-direction: column; gap: 8px; margin: 10px 0; }
.sb-step { border: 1px solid var(--sb-border); border-radius: 12px; padding: 10px; background: var(--sb-bg-soft); }
.sb-step-head { display: grid; grid-template-columns: auto auto 1fr 1fr auto; gap: 8px; align-items: center; }
.sb-step-handle { cursor: grab; color: var(--sb-muted); padding: 4px; }
.sb-step-num {
    width: 22px; height: 22px; border-radius: 50%; background: var(--sb-accent); color: #fff;
    font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center;
}
.sb-step-filters { margin-top: 8px; font-size: 12px; color: var(--sb-muted); }
.sb-step-filters summary { cursor: pointer; padding: 4px 0; }
.sb-hint-block { font-size: 12px; color: var(--sb-muted); }
.sb-ghost { opacity: 0.4; }
@media (max-width: 640px) { .sb-step-head { grid-template-columns: auto auto 1fr auto; } .sb-step-head select { grid-column: 1 / -1; } }
</style>
