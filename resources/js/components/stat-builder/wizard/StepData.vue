<template>
    <div class="sb-stepdata">
        <h3 class="sb-step-title">Configurer les données</h3>
        <p class="sb-step-help">
            Les paramètres ci-dessous s'adaptent au type « {{ typeDef.label }} ». L'aperçu se met à jour automatiquement.
        </p>

        <SourceSection v-if="has('source')" :config="config" :sources="allowedSources" @change="changeSource" />

        <template v-if="source">
            <DimensionSection v-if="has('dimension')" :config="config" :source="source" />
            <SeriesSection v-if="has('series')" :config="config" :source="source" />
            <ColumnsSection v-if="has('columns')" :key="'cols-' + config.dataSource" :config="config" :source="source" :followups="(meta.options && meta.options.followup) || []" />
            <StepsSection v-if="has('steps')" :config="config" :sources="meta.sources" />
            <KpiSection v-if="has('kpi')" :config="config" />
        </template>
        <StepsSection v-else-if="has('steps')" :config="config" :sources="meta.sources" />

        <PeriodSection v-if="has('period')" :config="config" :periods="meta.periods" :compare="has('compare') && source && source.kind === 'events'" />

        <template v-if="source">
            <SortSection v-if="has('sort')" :config="config" />
            <FiltersSection v-if="has('filters')" :holder="config" :source="source" />
            <TableOptionsSection v-if="has('tableOptions')" :config="config" :source="source" />
        </template>
    </div>
</template>

<script>
import SourceSection from "../config/SourceSection.vue";
import DimensionSection from "../config/DimensionSection.vue";
import SeriesSection from "../config/SeriesSection.vue";
import ColumnsSection from "../config/ColumnsSection.vue";
import StepsSection from "../config/StepsSection.vue";
import KpiSection from "../config/KpiSection.vue";
import PeriodSection from "../config/PeriodSection.vue";
import SortSection from "../config/SortSection.vue";
import FiltersSection from "../config/FiltersSection.vue";
import TableOptionsSection from "../config/TableOptionsSection.vue";
import { WIDGET_TYPES, applySourceDefaults } from "../registry";

export default {
    components: {
        SourceSection,
        DimensionSection,
        SeriesSection,
        ColumnsSection,
        StepsSection,
        KpiSection,
        PeriodSection,
        SortSection,
        FiltersSection,
        TableOptionsSection,
    },

    props: {
        config: { type: Object, required: true },
        meta: { type: Object, required: true },
    },

    computed: {
        typeDef() {
            return WIDGET_TYPES[this.config.type];
        },
        // Certains rendus ont besoin d'une dimension temporelle (donc d'une source "events")
        allowedSources() {
            return ["heatmap"].includes(this.config.type) ? this.meta.sources.filter((s) => s.kind === "events") : this.meta.sources;
        },
        source() {
            return this.meta.sources.find((s) => s.key === this.config.dataSource) || null;
        },
    },

    methods: {
        has(section) {
            return this.typeDef.sections.includes(section);
        },

        changeSource(source) {
            if (source.key === this.config.dataSource) return;
            applySourceDefaults(this.config, source);
        },
    },
};
</script>
