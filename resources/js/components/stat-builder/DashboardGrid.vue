<template>
    <draggable
        ref="drag"
        class="sb-grid"
        :class="{ 'is-editing': editing, 'is-resizing': resizing }"
        :model-value="widgets"
        item-key="id"
        :disabled="!editing"
        handle=".sb-drag"
        :animation="200"
        ghost-class="sb-ghost"
        :force-fallback="false"
        @update:model-value="$emit('reorder', $event)"
    >
        <template #item="{ element }">
            <div class="sb-item" :style="itemStyle(element)" :data-id="element.id">
                <WidgetCard
                    :widget="element"
                    :editing="editing"
                    :global-filters="globalFilters"
                    :enabled-filters="enabledFilters"
                    :refresh-key="refreshKey"
                    @edit="$emit('edit', element)"
                    @duplicate="$emit('duplicate', element)"
                    @expand="$emit('expand', element)"
                    @collapse="$emit('collapse', element)"
                    @hide="$emit('hide', element)"
                    @remove="$emit('remove', element)"
                    @reset-size="$emit('reset-size', element)"
                    @resize-start="startResize($event, element)"
                />
            </div>
        </template>
    </draggable>
</template>

<script>
import draggable from "vuedraggable";
import WidgetCard from "./WidgetCard.vue";
import { WIDGET_TYPES } from "./registry";

const ROW_H = 64;
const GAP = 16;

export default {
    components: { draggable, WidgetCard },

    props: {
        widgets: { type: Array, required: true },
        editing: { type: Boolean, default: false },
        globalFilters: { type: Object, default: () => ({}) },
        enabledFilters: { type: Array, default: () => [] },
        refreshKey: { type: Number, default: 0 },
    },

    emits: ["reorder", "resize", "resize-end", "edit", "duplicate", "expand", "collapse", "hide", "remove", "reset-size"],

    data() {
        return { resizing: false };
    },

    beforeUnmount() {
        this.stopListening();
    },

    methods: {
        itemStyle(w) {
            const l = w.layout || {};
            const cols = Math.min(12, Math.max(1, l.w || 6));

            return {
                "--w": cols,
                // Tablette : les widgets prennent plus de largeur
                "--wt": cols >= 7 ? 12 : 6,
                "--h": Math.min(24, Math.max(1, l.h || 4)),
            };
        },

        startResize(e, widget) {
            const item = e.target.closest(".sb-item");
            const grid = this.$refs.drag.$el;
            if (!item || !grid) return;

            const rect = item.getBoundingClientRect();
            const gridWidth = grid.getBoundingClientRect().width;
            const colW = (gridWidth - GAP * 11) / 12;
            const min = (WIDGET_TYPES[widget.type] && WIDGET_TYPES[widget.type].minLayout) || { w: 2, h: 2 };

            this.drag = { id: widget.id, x: e.clientX, y: e.clientY, width: rect.width, height: rect.height, colW, min };
            this.resizing = true;
            document.body.style.userSelect = "none";
            window.addEventListener("pointermove", this.onMove);
            window.addEventListener("pointerup", this.onUp);
            window.addEventListener("pointercancel", this.onUp);
        },

        onMove(e) {
            const d = this.drag;
            if (!d) return;

            const w = Math.round((d.width + (e.clientX - d.x) + GAP) / (d.colW + GAP));
            const h = Math.round((d.height + (e.clientY - d.y) + GAP) / (ROW_H + GAP));

            this.$emit("resize", {
                id: d.id,
                w: Math.max(d.min.w, Math.min(12, w)),
                h: Math.max(d.min.h, Math.min(24, h)),
            });
        },

        onUp() {
            this.stopListening();
            this.$emit("resize-end");
        },

        stopListening() {
            this.drag = null;
            this.resizing = false;
            document.body.style.userSelect = "";
            window.removeEventListener("pointermove", this.onMove);
            window.removeEventListener("pointerup", this.onUp);
            window.removeEventListener("pointercancel", this.onUp);
        },
    },
};
</script>

<style scoped>
.sb-grid {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    grid-auto-rows: 64px;
    grid-auto-flow: row dense;
    gap: 16px;
    width: 100%;
}
.sb-item {
    grid-column: span var(--w);
    grid-row: span var(--h);
    min-width: 0;
    min-height: 0;
    animation: sb-in 0.25s ease-out;
}
.sb-grid.is-resizing .sb-item {
    transition: none;
}
.sb-ghost {
    opacity: 0.35;
}
@keyframes sb-in {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: none; }
}
@media (max-width: 1023px) {
    .sb-item { grid-column: span var(--wt); }
}
@media (max-width: 767px) {
    .sb-item { grid-column: 1 / -1; }
    .sb-grid { gap: 12px; }
}
</style>
