<template>
    <svg class="sb-tp" viewBox="0 0 120 70" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <!-- KPI -->
        <g v-if="type === 'kpi'">
            <rect x="8" y="10" width="104" height="50" rx="8" class="soft" />
            <text x="18" y="28" class="txt">Appels</text>
            <text x="18" y="48" class="big">1 284</text>
            <rect x="82" y="22" width="22" height="22" rx="6" class="acc a2" />
            <path d="M88 38l4-6 3 3 4-6" class="stroke" />
            <text x="60" y="28" class="up">+12%</text>
        </g>
        <!-- Bâtons -->
        <g v-else-if="type === 'bar'">
            <path d="M10 60h100" class="axis" />
            <rect class="acc anim" x="16" y="38" width="12" height="22" rx="3" style="--d: 0s" />
            <rect class="acc anim" x="34" y="24" width="12" height="36" rx="3" style="--d: 0.08s" />
            <rect class="acc anim" x="52" y="30" width="12" height="30" rx="3" style="--d: 0.16s" />
            <rect class="acc anim" x="70" y="12" width="12" height="48" rx="3" style="--d: 0.24s" />
            <rect class="acc anim" x="88" y="26" width="12" height="34" rx="3" style="--d: 0.32s" />
        </g>
        <!-- Barres horizontales -->
        <g v-else-if="type === 'hbar'">
            <path d="M14 8v54" class="axis" />
            <rect class="acc anim-x" x="14" y="10" width="86" height="9" rx="3" style="--d: 0s" />
            <rect class="acc anim-x" x="14" y="24" width="68" height="9" rx="3" style="--d: 0.08s" />
            <rect class="acc anim-x" x="14" y="38" width="50" height="9" rx="3" style="--d: 0.16s" />
            <rect class="acc anim-x" x="14" y="52" width="30" height="9" rx="3" style="--d: 0.24s" />
        </g>
        <!-- Courbe -->
        <g v-else-if="type === 'line'">
            <path d="M10 60h100" class="axis" />
            <path d="M12 48 C28 46 32 22 50 30 S76 50 90 24 S106 18 108 14 V60 H12Z" class="fill" />
            <path d="M12 48 C28 46 32 22 50 30 S76 50 90 24 S106 18 108 14" class="stroke" />
        </g>
        <!-- Multi-courbes -->
        <g v-else-if="type === 'multiline'">
            <path d="M10 60h100" class="axis" />
            <path d="M12 46 C30 40 38 20 56 28 S86 44 108 16" class="stroke" />
            <path d="M12 54 C30 50 44 44 62 40 S90 30 108 36" class="stroke a2" />
            <path d="M12 38 C28 42 44 52 62 50 S92 52 108 48" class="stroke a3" />
        </g>
        <!-- Combiné -->
        <g v-else-if="type === 'combo'">
            <path d="M10 60h100" class="axis" />
            <rect class="acc anim" x="16" y="38" width="12" height="22" rx="3" style="--d: 0s" />
            <rect class="acc anim" x="38" y="28" width="12" height="32" rx="3" style="--d: 0.08s" />
            <rect class="acc anim" x="60" y="34" width="12" height="26" rx="3" style="--d: 0.16s" />
            <rect class="acc anim" x="82" y="20" width="12" height="40" rx="3" style="--d: 0.24s" />
            <path d="M22 26 C40 14 58 30 76 12 S96 14 104 8" class="stroke a2" />
        </g>
        <!-- Camembert -->
        <g v-else-if="type === 'pie'">
            <circle cx="60" cy="35" r="26" class="acc" />
            <path d="M60 35V9a26 26 0 0 1 25 33Z" class="acc a2" />
            <path d="M60 35l25 7a26 26 0 0 1-30 19Z" class="acc a3" />
        </g>
        <!-- Donut -->
        <g v-else-if="type === 'donut'">
            <circle cx="60" cy="35" r="22" class="ring" stroke-width="12" />
            <circle cx="60" cy="35" r="22" class="ring r2" stroke-width="12" stroke-dasharray="52 138" transform="rotate(-90 60 35)" />
            <circle cx="60" cy="35" r="22" class="ring r3" stroke-width="12" stroke-dasharray="30 138" stroke-dashoffset="-52" transform="rotate(-90 60 35)" />
        </g>
        <!-- Funnel -->
        <g v-else-if="type === 'funnel'">
            <rect class="acc anim-x" x="10" y="8" width="100" height="12" rx="3" style="--d: 0s" />
            <rect class="acc a2 anim-x" x="22" y="26" width="76" height="12" rx="3" style="--d: 0.1s" />
            <rect class="acc a3 anim-x" x="36" y="44" width="48" height="12" rx="3" style="--d: 0.2s" />
        </g>
        <!-- Heatmap -->
        <g v-else-if="type === 'heatmap'">
            <template v-for="c in 14">
                <rect
                    v-for="r in 5"
                    :key="c + '-' + r"
                    :x="6 + (c - 1) * 8"
                    :y="8 + (r - 1) * 11"
                    width="6.5"
                    height="9"
                    rx="2"
                    class="acc"
                    :style="{ opacity: cell(c, r) }"
                />
            </template>
        </g>
        <!-- Tableau -->
        <g v-else-if="type === 'table'">
            <rect x="8" y="8" width="104" height="54" rx="6" class="soft" />
            <rect x="8" y="8" width="104" height="12" rx="6" class="acc" style="opacity: 0.25" />
            <path d="M8 31h104M8 43h104M8 55h104M42 8v54M78 8v54" class="axis" />
            <rect x="13" y="12" width="20" height="3" rx="1.5" class="acc" />
            <rect x="47" y="12" width="14" height="3" rx="1.5" class="acc" />
            <rect x="83" y="12" width="18" height="3" rx="1.5" class="acc" />
        </g>
    </svg>
</template>

<script>
export default {
    props: { type: { type: String, required: true } },
    methods: {
        // Motif pseudo-aléatoire stable pour la heatmap
        cell(c, r) {
            const v = Math.abs(Math.sin(c * 12.9898 + r * 78.233) * 43758.5453) % 1;
            return (0.15 + v * 0.85).toFixed(2);
        },
    },
};
</script>

<style scoped>
.sb-tp { width: 100%; height: 100%; }
.soft { fill: var(--sb-bg-soft); stroke: var(--sb-border); }
.axis { stroke: var(--sb-border); stroke-width: 1; fill: none; }
.acc { fill: var(--sb-accent); }
.acc.a2 { fill: #0ea5e9; }
.acc.a3 { fill: #10b981; }
.stroke { stroke: var(--sb-accent); stroke-width: 2.2; stroke-linecap: round; fill: none; }
.stroke.a2 { stroke: #0ea5e9; }
.stroke.a3 { stroke: #10b981; }
.fill { fill: var(--sb-accent); opacity: 0.12; }
.ring { stroke: var(--sb-accent); fill: none; }
.ring.r2 { stroke: #0ea5e9; }
.ring.r3 { stroke: #10b981; }
.txt { font: 500 7px sans-serif; fill: var(--sb-muted); }
.big { font: 700 15px sans-serif; fill: var(--sb-text); }
.up { font: 600 7px sans-serif; fill: #059669; }
.anim { transform-box: fill-box; transform-origin: bottom; }
.anim-x { transform-box: fill-box; transform-origin: left; }
:global(.sb-type-card:hover) .anim { animation: sb-grow 0.6s ease both; animation-delay: var(--d); }
:global(.sb-type-card:hover) .anim-x { animation: sb-growx 0.6s ease both; animation-delay: var(--d); }
@keyframes sb-grow { from { transform: scaleY(0.2); } to { transform: scaleY(1); } }
@keyframes sb-growx { from { transform: scaleX(0.2); } to { transform: scaleX(1); } }
</style>
