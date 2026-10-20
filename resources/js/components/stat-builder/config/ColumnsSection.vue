<template>
    <section class="sb-section">
        <div class="sb-section-title">Colonnes</div>

        <draggable v-model="config.columns" item-key="field" handle=".sb-col-handle" :animation="180" ghost-class="sb-ghost" class="sb-cols">
            <template #item="{ element: col, index }">
                <div class="sb-col">
                    <span class="sb-col-handle" data-tip="Réorganiser"><i class="fa fa-grip-vertical"></i></span>
                    <div class="sb-col-main">
                        <div class="sb-col-name">
                            {{ fieldOf(col.field).label }}
                            <span class="sb-badge-type">{{ typeLabel(fieldOf(col.field)) }}</span>
                        </div>
                        <div v-if="fieldOf(col.field).description" class="sb-col-desc">{{ fieldOf(col.field).description }}</div>
                    </div>
                    <input class="sb-input sb-col-label" v-model="col.label" :placeholder="fieldOf(col.field).label" data-tip="Nom affiché" />
                    <select
                        v-if="aggsOf(col.field).length > 1"
                        class="sb-input sb-col-agg"
                        :value="col.agg || defaultAgg(col.field)"
                        @change="col.agg = $event.target.value"
                    >
                        <option v-for="a in aggsOf(col.field)" :key="a" :value="a">{{ aggLabels[a] }}</option>
                    </select>
                    <button type="button" class="sb-btn-icon is-danger" data-tip="Supprimer la colonne" @click="config.columns.splice(index, 1)">
                        <i class="fa fa-xmark"></i>
                    </button>
                </div>
            </template>
        </draggable>

        <div v-if="!config.columns.length" class="sb-empty-inline">Ajoutez au moins une colonne.</div>

        <div v-if="available.length" class="sb-add-col">
            <select class="sb-input" v-model="toAdd">
                <option value="">+ Ajouter une colonne…</option>
                <option v-for="f in available" :key="f.key" :value="f.key">
                    {{ f.label }}{{ f.description ? " — " + f.description : "" }}
                </option>
            </select>
        </div>

        <div class="sb-subtitle">Tri par défaut</div>
        <div class="sb-row">
            <label class="sb-field">
                <span class="sb-label">Colonne de tri</span>
                <select class="sb-input" :value="sorting.field" @change="setSort('field', $event.target.value)">
                    <option value="">Aucun</option>
                    <option v-for="c in sortable" :key="c.field" :value="c.field">{{ c.label }}</option>
                </select>
            </label>
            <label class="sb-field">
                <span class="sb-label">Ordre</span>
                <select class="sb-input" :value="sorting.direction" @change="setSort('direction', $event.target.value)">
                    <option value="asc">Croissant</option>
                    <option value="desc">Décroissant</option>
                </select>
            </label>
        </div>
    </section>
</template>

<script>
import draggable from "vuedraggable";
import { AGG_LABELS } from "../registry";

const TYPE_LABELS = {
    string: "Texte",
    number: "Nombre",
    percentage: "Pourcentage",
    status: "Statut",
    datetime: "Date/heure",
    date: "Date",
    audio_action: "Action audio",
};

export default {
    components: { draggable },

    props: {
        config: { type: Object, required: true },
        source: { type: Object, required: true },
    },

    data() {
        return { toAdd: "", aggLabels: AGG_LABELS };
    },

    computed: {
        available() {
            const used = this.config.columns.map((c) => c.field);

            return this.source.fields.filter((f) => !used.includes(f.key));
        },
        sorting() {
            return { field: "", direction: "desc", ...(this.config.sorting || {}) };
        },
        sortable() {
            return this.config.columns
                .map((c) => ({ field: c.field, label: c.label || this.fieldOf(c.field).label, sortable: this.fieldOf(c.field).sortable !== false }))
                .filter((c) => c.sortable);
        },
    },

    watch: {
        toAdd(key) {
            if (!key) return;
            this.config.columns.push({ field: key, label: "" });
            this.toAdd = "";
        },
    },

    methods: {
        fieldOf(key) {
            return this.source.fields.find((f) => f.key === key) || { key, label: key, type: "string" };
        },
        typeLabel(f) {
            return f.measure ? "Mesure" : TYPE_LABELS[f.type] || f.type;
        },
        aggsOf(key) {
            if (this.source.kind === "users") return [];
            const m = this.source.measures.find((x) => x.key === key);

            return m ? m.aggs : [];
        },
        defaultAgg(key) {
            const m = this.source.measures.find((x) => x.key === key);

            return m ? m.default : "count";
        },
        setSort(k, v) {
            this.config.sorting = { ...this.sorting, [k]: v };
        },
    },
};
</script>

<style scoped>
.sb-cols { display: flex; flex-direction: column; gap: 6px; }
.sb-col {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border: 1px solid var(--sb-border);
    border-radius: 10px;
    background: var(--sb-surface);
    flex-wrap: wrap;
}
.sb-col-handle { cursor: grab; color: var(--sb-muted); padding: 4px; }
.sb-col-main { flex: 1 1 160px; min-width: 0; }
.sb-col-name { font-size: 13px; font-weight: 600; color: var(--sb-text); display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.sb-col-desc { font-size: 11px; color: var(--sb-muted); margin-top: 1px; }
.sb-badge-type {
    font-size: 10px;
    font-weight: 500;
    padding: 1px 6px;
    border-radius: 99px;
    background: var(--sb-bg-soft);
    color: var(--sb-muted);
    border: 1px solid var(--sb-border);
}
.sb-col-label { width: 150px; flex: 0 1 150px; }
.sb-col-agg { width: 110px; flex: 0 0 110px; }
.sb-add-col { margin-top: 8px; }
.sb-empty-inline { font-size: 12px; color: var(--sb-muted); padding: 10px; text-align: center; border: 1px dashed var(--sb-border); border-radius: 10px; }
.sb-subtitle { font-size: 12px; font-weight: 600; color: var(--sb-muted); margin: 16px 0 6px; }
.sb-ghost { opacity: 0.4; }
@media (max-width: 640px) { .sb-col-label, .sb-col-agg { flex: 1 1 100%; width: auto; } }
</style>
