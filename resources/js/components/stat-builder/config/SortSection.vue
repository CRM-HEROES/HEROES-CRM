<template>
    <section class="sb-section">
        <div class="sb-section-title">Tri et limite</div>
        <div class="sb-row">
            <label class="sb-field">
                <span class="sb-label">Trier par</span>
                <select class="sb-input" :value="sort.by" @change="set('by', $event.target.value)">
                    <option value="value">Valeur</option>
                    <option value="dimension">Libellé</option>
                </select>
            </label>
            <label class="sb-field">
                <span class="sb-label">Ordre</span>
                <select class="sb-input" :value="sort.direction" @change="set('direction', $event.target.value)">
                    <option value="desc">Décroissant</option>
                    <option value="asc">Croissant</option>
                </select>
            </label>
            <label class="sb-field">
                <span class="sb-label">Nombre max. d'éléments</span>
                <input class="sb-input" type="number" min="0" max="100" v-model.number="config.limit" placeholder="Tous" />
            </label>
        </div>
    </section>
</template>

<script>
export default {
    props: { config: { type: Object, required: true } },

    computed: {
        sort() {
            return { by: "value", direction: "desc", ...(this.config.sort || {}) };
        },
    },

    methods: {
        set(k, v) {
            this.config.sort = { ...this.sort, [k]: v };
        },
    },
};
</script>
