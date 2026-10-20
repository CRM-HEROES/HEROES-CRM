<template>
    <div>
        <h3 class="sb-step-title">Comment voulez-vous afficher vos données ?</h3>
        <p class="sb-step-help">Choisissez une visualisation. Vous pourrez la configurer et la prévisualiser à l'étape suivante.</p>

        <div v-for="group in groups" :key="group.name" class="sb-type-group">
            <div class="sb-group-name">{{ group.name }}</div>
            <div class="sb-type-grid">
                <button
                    v-for="t in group.items"
                    :key="t.key"
                    type="button"
                    class="sb-type-card"
                    :class="{ 'is-selected': modelValue === t.key }"
                    @click="$emit('update:modelValue', t.key)"
                >
                    <div class="sb-type-preview"><TypePreview :type="t.key" /></div>
                    <div class="sb-type-info">
                        <div class="sb-type-label"><i class="fa" :class="t.icon"></i> {{ t.label }}</div>
                        <div class="sb-type-desc">{{ t.description }}</div>
                    </div>
                    <span v-if="modelValue === t.key" class="sb-type-check"><i class="fa fa-check"></i></span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import TypePreview from "./TypePreview.vue";
import { WIDGET_TYPES, TYPE_ORDER } from "../registry";

export default {
    components: { TypePreview },

    props: { modelValue: { type: String, default: "" } },

    emits: ["update:modelValue"],

    computed: {
        groups() {
            const map = {};
            TYPE_ORDER.forEach((key) => {
                const t = { key, ...WIDGET_TYPES[key] };
                (map[t.group] = map[t.group] || { name: t.group, items: [] }).items.push(t);
            });

            return Object.values(map);
        },
    },
};
</script>

<style scoped>
.sb-type-group { margin-top: 18px; }
.sb-group-name {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--sb-muted);
    margin-bottom: 8px;
}
.sb-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 12px;
}
.sb-type-card {
    position: relative;
    text-align: left;
    border: 1.5px solid var(--sb-border);
    background: var(--sb-surface);
    border-radius: 14px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.18s ease;
    color: var(--sb-text);
}
.sb-type-card:hover {
    border-color: var(--sb-accent);
    transform: translateY(-2px);
    box-shadow: 0 10px 24px -12px rgba(79, 70, 229, 0.45);
}
.sb-type-card.is-selected {
    border-color: var(--sb-accent);
    background: var(--sb-accent-soft);
    box-shadow: 0 0 0 3px var(--sb-accent-soft);
}
.sb-type-preview {
    height: 82px;
    background: var(--sb-bg-soft);
    border-radius: 10px;
    padding: 6px;
    margin-bottom: 10px;
}
.sb-type-label { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.sb-type-label i { color: var(--sb-accent); font-size: 12px; }
.sb-type-desc { font-size: 12px; color: var(--sb-muted); margin-top: 3px; line-height: 1.35; }
.sb-type-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--sb-accent);
    color: #fff;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: sb-pop-in 0.18s ease;
}
@keyframes sb-pop-in { from { transform: scale(0.4); opacity: 0; } to { transform: none; opacity: 1; } }
@media (max-width: 480px) {
    .sb-type-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
    .sb-type-preview { height: 64px; }
    .sb-type-desc { display: none; }
}
</style>
