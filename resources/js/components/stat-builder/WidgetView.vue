<template>
    <div class="sb-view">
        <!-- Skeleton -->
        <div v-if="loading && !data" class="sb-skel" :class="'is-' + skeletonKind">
            <template v-if="skeletonKind === 'kpi'">
                <div class="sb-skel-line" style="width: 40%"></div>
                <div class="sb-skel-line big" style="width: 60%"></div>
                <div class="sb-skel-line" style="width: 30%"></div>
            </template>
            <template v-else-if="skeletonKind === 'table'">
                <div v-for="i in 5" :key="i" class="sb-skel-line row"></div>
            </template>
            <template v-else>
                <div class="sb-skel-bars">
                    <span v-for="h in [40, 65, 50, 80, 55, 90, 60, 75]" :key="h" :style="{ height: h + '%' }"></span>
                </div>
            </template>
        </div>

        <!-- Erreur -->
        <div v-else-if="error" class="sb-state is-error">
            <i class="fa fa-triangle-exclamation"></i>
            <div class="sb-state-title">Impossible de charger les données</div>
            <div class="sb-state-text">{{ error }}</div>
            <button class="sb-btn-ghost" @click="fetch()">Réessayer</button>
        </div>

        <!-- Vide -->
        <div v-else-if="data && isEmpty" class="sb-state">
            <i class="fa fa-inbox"></i>
            <div class="sb-state-title">Aucune donnée</div>
            <div class="sb-state-text">Aucun résultat sur la période ou avec les filtres sélectionnés.</div>
        </div>

        <WidgetRenderer
            v-else-if="data"
            :class="{ 'is-refreshing': loading }"
            :data="data"
            :config="config"
            :runtime="runtime"
            :label="label"
            @runtime="onRuntime"
        />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import ApiService from "@/apis/api.service";
import WidgetRenderer from "./renderers/WidgetRenderer.vue";

export default {
    components: { WidgetRenderer },

    props: {
        config: { type: Object, required: true },
        globalFilters: { type: Object, default: () => ({}) },
        label: { type: String, default: "" },
        refreshKey: { type: Number, default: 0 },
        debounce: { type: Number, default: 0 },
    },

    emits: ["loaded", "error"],

    data() {
        return {
            data: null,
            loading: false,
            error: null,
            runtime: { search: "", page: 1, sort: null },
            seq: 0,
            timer: null,
        };
    },

    computed: {
        ...mapGetters(["project"]),

        skeletonKind() {
            return this.config.type === "kpi" ? "kpi" : this.config.type === "table" ? "table" : "chart";
        },

        isEmpty() {
            const d = this.data;
            if (!d) return false;
            if (d.kind === "series") return !d.labels.length || d.series.every((s) => s.data.every((v) => !Number(v)));
            if (d.kind === "funnel") return !d.steps.length;
            if (d.kind === "table") return !d.rows.length && !this.runtime.search;
            return false;
        },

        // Tout ce qui doit déclencher un rechargement
        signature() {
            return JSON.stringify([this.config, this.globalFilters, this.runtime, this.refreshKey, this.project && this.project.slug]);
        },
    },

    watch: {
        signature() {
            this.schedule();
        },
        // Un changement de config réinitialise l'état runtime du tableau
        "config.type"() {
            this.runtime = { search: "", page: 1, sort: null };
        },
        "config.dataSource"() {
            this.runtime = { search: "", page: 1, sort: null };
        },
    },

    created() {
        this.fetch();
    },

    beforeUnmount() {
        clearTimeout(this.timer);
        this.seq++;
    },

    methods: {
        schedule() {
            clearTimeout(this.timer);
            if (this.debounce > 0) {
                this.timer = setTimeout(this.fetch, this.debounce);
            } else {
                this.fetch();
            }
        },

        onRuntime(r) {
            this.runtime = r;
        },

        async fetch() {
            if (!this.project) return;
            const id = ++this.seq;
            this.loading = true;
            this.error = null;

            try {
                const { data } = await ApiService.post(`project/${this.project.slug}/stat-dashboard/query`, {
                    config: this.config,
                    global: this.globalFilters,
                    runtime: { ...this.runtime, sort: this.runtime.sort || undefined },
                });
                if (id !== this.seq) return;
                this.data = data;
                this.$emit("loaded", data);
            } catch (e) {
                if (id !== this.seq) return;
                const res = e.response && e.response.data;
                this.error = (res && res.message) || "Une erreur est survenue.";
                this.$emit("error", this.error);
            } finally {
                if (id === this.seq) this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.sb-view {
    position: relative;
    height: 100%;
    min-height: 0;
    display: flex;
    flex-direction: column;
}
.sb-view > * {
    flex: 1;
    min-height: 0;
}
.is-refreshing {
    opacity: 0.55;
    transition: opacity 0.15s;
}
.sb-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 6px;
    color: var(--sb-muted);
    padding: 12px;
}
.sb-state > i {
    font-size: 26px;
    opacity: 0.6;
}
.sb-state.is-error > i {
    color: #ef4444;
    opacity: 1;
}
.sb-state-title {
    font-weight: 600;
    font-size: 13px;
    color: var(--sb-text);
}
.sb-state-text {
    font-size: 12px;
    max-width: 280px;
}
.sb-skel {
    display: flex;
    flex-direction: column;
    gap: 10px;
    justify-content: center;
}
.sb-skel-line {
    height: 12px;
    border-radius: 6px;
    background: linear-gradient(90deg, var(--sb-border) 25%, var(--sb-bg-soft) 50%, var(--sb-border) 75%);
    background-size: 200% 100%;
    animation: sb-shimmer 1.3s infinite;
}
.sb-skel-line.big { height: 28px; }
.sb-skel-line.row { height: 28px; }
.sb-skel-bars {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    height: 100%;
    min-height: 90px;
}
.sb-skel-bars span {
    flex: 1;
    border-radius: 6px 6px 0 0;
    background: linear-gradient(90deg, var(--sb-border) 25%, var(--sb-bg-soft) 50%, var(--sb-border) 75%);
    background-size: 200% 100%;
    animation: sb-shimmer 1.3s infinite;
}
@keyframes sb-shimmer {
    from { background-position: 200% 0; }
    to { background-position: -200% 0; }
}
.sb-btn-ghost {
    margin-top: 4px;
    border: 1px solid var(--sb-border);
    background: var(--sb-surface);
    color: var(--sb-text);
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 12px;
    cursor: pointer;
}
.sb-btn-ghost:hover { border-color: var(--sb-accent); color: var(--sb-accent); }
</style>
