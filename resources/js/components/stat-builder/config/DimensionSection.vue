<template>
    <section class="sb-section">
        <div class="sb-section-title">{{ label }}</div>
        <select class="sb-input" v-model="config.dimension">
            <option v-for="d in source.dimensions" :key="d.key" :value="d.key">{{ d.label }}</option>
        </select>
    </section>
</template>

<script>
export default {
    props: {
        config: { type: Object, required: true },
        source: { type: Object, required: true },
    },
    computed: {
        label() {
            return { pie: "Catégorie", donut: "Catégorie", hbar: "Regrouper par", heatmap: "Date" }[this.config.type] || "Axe X (dimension)";
        },
    },
};
</script>
