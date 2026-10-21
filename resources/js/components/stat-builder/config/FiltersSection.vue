<template>
    <section class="sb-section">
        <div class="sb-section-title">
            {{ title }}
            <span class="sb-hint">(optionnel)</span>
        </div>

        <div v-for="(f, i) in holder.filters" :key="i" class="sb-filter-row">
            <select class="sb-input" v-model="f.field">
                <option v-for="o in fields" :key="o.key" :value="o.key">{{ o.label }}</option>
            </select>
            <select class="sb-input" v-model="f.op">
                <option v-for="op in ops" :key="op.value" :value="op.value">{{ op.label }}</option>
            </select>
            <input class="sb-input" v-model="f.value" placeholder="Valeur" />
            <button type="button" class="sb-btn-icon is-danger" data-tip="Retirer" @click="holder.filters.splice(i, 1)">
                <i class="fa fa-xmark"></i>
            </button>
        </div>

        <button type="button" class="sb-btn-dashed" @click="add"><i class="fa fa-plus"></i> Ajouter un filtre</button>
    </section>
</template>

<script>
import { FILTER_OPS } from "../registry";

export default {
    props: {
        // Objet portant `filters` (config du widget ou étape de funnel)
        holder: { type: Object, required: true },
        source: { type: Object, required: true },
        title: { type: String, default: "Filtres" },
    },

    data() {
        return { ops: FILTER_OPS };
    },

    computed: {
        fields() {
            return this.source.kind === "users"
                ? this.source.fields.filter((f) => f.type !== "audio_action")
                : this.source.dimensions;
        },
    },

    created() {
        if (!this.holder.filters) this.holder.filters = [];
    },

    methods: {
        add() {
            this.holder.filters.push({ field: this.fields[0].key, op: "eq", value: "" });
        },
    },
};
</script>

<style scoped>
.sb-filter-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 8px; margin-bottom: 8px; align-items: center; }
@media (max-width: 640px) { .sb-filter-row { grid-template-columns: 1fr 1fr; } }
</style>
