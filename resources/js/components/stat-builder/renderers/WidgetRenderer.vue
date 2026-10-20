<template>
    <KpiView v-if="data.kind === 'kpi'" :data="data" :config="config" :label="label" />
    <FunnelView v-else-if="data.kind === 'funnel'" :data="data" :config="config" />
    <TableView v-else-if="data.kind === 'table'" :data="data" :config="config" :runtime="runtime" @runtime="$emit('runtime', $event)" />
    <HeatmapView v-else-if="config.type === 'heatmap'" :data="data" :config="config" />
    <ChartCanvas v-else-if="data.kind === 'series'" :type="config.type" :data="data" :options="config" />
</template>

<script>
import ChartCanvas from "./ChartCanvas.vue";
import KpiView from "./KpiView.vue";
import FunnelView from "./FunnelView.vue";
import HeatmapView from "./HeatmapView.vue";
import TableView from "./TableView.vue";

/**
 * Aiguillage donnée → rendu. Pour ajouter un type de visualisation :
 * créer son composant et ajouter une branche ici.
 */
export default {
    components: { ChartCanvas, KpiView, FunnelView, HeatmapView, TableView },

    props: {
        data: { type: Object, required: true },
        config: { type: Object, required: true },
        runtime: { type: Object, default: () => ({}) },
        label: { type: String, default: "" },
    },

    emits: ["runtime"],
};
</script>
