<template>
    <div
        v-if="ruler.enabled"
        class="hc-screen-ruler"
        :class="{ 'is-vertical': ruler.vertical, 'is-dragging': dragging }"
        :style="rulerStyle"
        @pointerdown="startDrag"
    >
        <div class="hc-screen-ruler-ticks">
            <div
                v-for="tick in ticks"
                :key="tick.value"
                class="hc-screen-ruler-tick"
                :class="tick.type"
                :style="{
                    [ruler.vertical ? 'top' : 'left']: tick.value + 'px',
                }"
            >
                <span v-if="tick.label !== null" v-text="tick.label"></span>
            </div>
        </div>
        <div
            class="hc-screen-ruler-resize"
            title="Étirer la règle"
            @pointerdown.stop.prevent="startResize"
        ></div>
        <div class="hc-screen-ruler-actions" @pointerdown.stop>
            <input
                type="color"
                title="Couleur de la règle"
                v-model="ruler.color"
            />
            <button type="button" title="Pivoter" @click="rotate">
                <i class="fa fa-refresh"></i>
            </button>
            <button type="button" title="Fermer" @click="ruler.enabled = false">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</template>

<style>
.hc-screen-ruler {
    position: fixed;
    z-index: 9000;
    height: 44px;
    border: 1px solid var(--ruler-ink);
    color: var(--ruler-ink);
    border-radius: 3px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    cursor: grab;
    user-select: none;
    touch-action: none;
}
.hc-screen-ruler.is-vertical {
    width: 44px;
}
.hc-screen-ruler-resize {
    position: absolute;
    right: 0;
    top: 0;
    width: 10px;
    height: 100%;
    cursor: ew-resize;
    background: rgba(0, 0, 0, 0.18);
}
.hc-screen-ruler.is-vertical .hc-screen-ruler-resize {
    right: auto;
    top: auto;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 10px;
    cursor: ns-resize;
}
.hc-screen-ruler-actions input[type="color"] {
    width: 20px;
    height: 20px;
    padding: 0;
    border: 0;
    background: none;
    cursor: pointer;
}
.hc-screen-ruler.is-dragging {
    cursor: grabbing;
}
.hc-screen-ruler-ticks {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.hc-screen-ruler-tick {
    position: absolute;
    background: var(--ruler-ink);
}
.hc-screen-ruler:not(.is-vertical) .hc-screen-ruler-tick {
    top: 0;
    width: 1px;
    height: 8px;
}
.hc-screen-ruler:not(.is-vertical) .hc-screen-ruler-tick.half {
    height: 13px;
}
.hc-screen-ruler:not(.is-vertical) .hc-screen-ruler-tick.full {
    height: 20px;
}
.hc-screen-ruler.is-vertical .hc-screen-ruler-tick {
    left: 0;
    height: 1px;
    width: 8px;
}
.hc-screen-ruler.is-vertical .hc-screen-ruler-tick.half {
    width: 13px;
}
.hc-screen-ruler.is-vertical .hc-screen-ruler-tick.full {
    width: 20px;
}
.hc-screen-ruler-tick span {
    position: absolute;
    font-size: 10px;
    line-height: 1;
    color: var(--ruler-ink);
}
.hc-screen-ruler:not(.is-vertical) .hc-screen-ruler-tick span {
    top: 22px;
    left: 2px;
}
.hc-screen-ruler.is-vertical .hc-screen-ruler-tick span {
    left: 22px;
    top: 2px;
}
.hc-screen-ruler-actions {
    position: absolute;
    right: 14px;
    bottom: 3px;
    display: flex;
    gap: 2px;
}
.hc-screen-ruler.is-vertical .hc-screen-ruler-actions {
    flex-direction: column;
    right: 3px;
    bottom: 14px;
}
.hc-screen-ruler-actions button {
    width: 20px;
    height: 20px;
    padding: 0;
    border: 0;
    border-radius: 3px;
    background: rgba(0, 0, 0, 0.15);
    cursor: pointer;
}
.hc-screen-ruler-actions button:hover {
    background: rgba(0, 0, 0, 0.3);
}
</style>

<script>
import { screenRuler } from "@/utils/screen-ruler";

const MIN_LENGTH = 120;
const THICKNESS = 44;
// 1 cm CSS ≈ 37.8 px
const CM = 96 / 2.54;

export default {
    data() {
        return {
            ruler: screenRuler,
            dragging: false,
            offset: { x: 0, y: 0 },
        };
    },

    computed: {
        rulerStyle() {
            const c = this.ruler.color;
            const lum =
                (0.299 * parseInt(c.slice(1, 3), 16) +
                    0.587 * parseInt(c.slice(3, 5), 16) +
                    0.114 * parseInt(c.slice(5, 7), 16)) /
                255;
            const style = {
                left: this.ruler.x + "px",
                top: this.ruler.y + "px",
                background: c + "eb",
                "--ruler-ink": lum > 0.55 ? "#2b2b2b" : "#ffffff",
            };
            style[this.ruler.vertical ? "height" : "width"] =
                this.ruler.length + "px";
            return style;
        },

        ticks() {
            const ticks = [];
            for (let mm = 0; mm * (CM / 10) <= this.ruler.length; mm++) {
                const cm = mm % 10 === 0;
                ticks.push({
                    value: Math.round(mm * (CM / 10) * 100) / 100,
                    type: cm ? "full" : mm % 5 === 0 ? "half" : "",
                    label: cm ? mm / 10 : null,
                });
            }
            return ticks;
        },
    },

    beforeUnmount() {
        this.stopDrag();
    },

    methods: {
        startDrag(event) {
            if (event.button !== undefined && event.button !== 0) return;
            this.dragging = true;
            this.offset = {
                x: event.clientX - this.ruler.x,
                y: event.clientY - this.ruler.y,
            };
            window.addEventListener("pointermove", this.onDrag);
            window.addEventListener("pointerup", this.stopDrag);
            event.preventDefault();
        },

        move(x, y) {
            const w = this.ruler.vertical ? THICKNESS : this.ruler.length;
            const h = this.ruler.vertical ? this.ruler.length : THICKNESS;
            this.ruler.x = Math.min(Math.max(0, x), Math.max(0, window.innerWidth - w));
            this.ruler.y = Math.min(Math.max(0, y), Math.max(0, window.innerHeight - h));
        },

        onDrag(event) {
            this.move(event.clientX - this.offset.x, event.clientY - this.offset.y);
        },

        stopDrag() {
            this.dragging = false;
            window.removeEventListener("pointermove", this.onDrag);
            window.removeEventListener("pointerup", this.stopDrag);
            window.removeEventListener("pointermove", this.onResize);
            window.removeEventListener("pointerup", this.stopDrag);
        },

        startResize(event) {
            if (event.button !== undefined && event.button !== 0) return;
            this.dragging = true;
            window.addEventListener("pointermove", this.onResize);
            window.addEventListener("pointerup", this.stopDrag);
        },

        onResize(event) {
            const max = this.ruler.vertical
                ? window.innerHeight - this.ruler.y
                : window.innerWidth - this.ruler.x;
            const wanted = this.ruler.vertical
                ? event.clientY - this.ruler.y
                : event.clientX - this.ruler.x;
            this.ruler.length = Math.round(
                Math.min(Math.max(MIN_LENGTH, wanted), Math.max(MIN_LENGTH, max)),
            );
        },

        rotate() {
            this.ruler.vertical = !this.ruler.vertical;
            this.move(this.ruler.x, this.ruler.y);
        },
    },
};
</script>
