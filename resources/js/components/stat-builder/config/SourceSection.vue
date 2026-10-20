<template>
    <section class="sb-section">
        <div class="sb-section-title">Source de données</div>
        <div class="sb-source-grid">
            <button
                v-for="s in sources"
                :key="s.key"
                type="button"
                class="sb-source"
                :class="{ 'is-selected': s.key === config.dataSource }"
                @click="$emit('change', s)"
            >
                <i class="fa" :class="s.icon"></i>
                <span>{{ s.label }}</span>
            </button>
        </div>
    </section>
</template>

<script>
export default {
    props: {
        config: { type: Object, required: true },
        sources: { type: Array, required: true },
    },
    emits: ["change"],
};
</script>

<style scoped>
.sb-source-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.sb-source {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 10px;
    border: 1.5px solid var(--sb-border);
    background: var(--sb-surface);
    color: var(--sb-text);
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}
.sb-source i { color: var(--sb-muted); }
.sb-source:hover { border-color: var(--sb-accent); }
.sb-source.is-selected { border-color: var(--sb-accent); background: var(--sb-accent-soft); color: var(--sb-accent); font-weight: 600; }
.sb-source.is-selected i { color: var(--sb-accent); }
</style>
