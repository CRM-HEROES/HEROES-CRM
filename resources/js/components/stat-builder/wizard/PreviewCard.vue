<template>
    <div class="sb-preview" :class="{ 'is-kpi': config.type === 'kpi' }">
        <div v-if="config.type !== 'kpi'" class="sb-preview-head">
            <div class="sb-preview-title">{{ config.title || placeholder }}</div>
            <div v-if="config.subtitle" class="sb-preview-sub">{{ config.subtitle }}</div>
        </div>
        <div class="sb-preview-body">
            <WidgetView
                :config="config"
                :global-filters="globalFilters"
                :label="config.title || placeholder"
                :debounce="450"
            />
        </div>
    </div>
</template>

<script>
import WidgetView from "../WidgetView.vue";
import { WIDGET_TYPES } from "../registry";

export default {
    components: { WidgetView },

    props: {
        config: { type: Object, required: true },
        globalFilters: { type: Object, default: () => ({}) },
    },

    computed: {
        placeholder() {
            return WIDGET_TYPES[this.config.type].label;
        },
    },
};
</script>

<style scoped>
.sb-preview {
    background: var(--sb-surface);
    border: 1px solid var(--sb-border);
    border-radius: 14px;
    padding: 14px 16px;
    height: 360px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 8px 24px -14px rgba(15, 23, 42, 0.25);
}
.sb-preview.is-kpi { height: 128px; }
.sb-preview-head { margin-bottom: 10px; }
.sb-preview-title { font-size: 14px; font-weight: 600; color: var(--sb-text); }
.sb-preview-sub { font-size: 12px; color: var(--sb-muted); margin-top: 1px; }
.sb-preview-body { flex: 1; min-height: 0; }
</style>
