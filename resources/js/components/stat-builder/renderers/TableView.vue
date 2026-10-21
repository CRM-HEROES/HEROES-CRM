<template>
    <div class="sb-table">
        <div v-if="config.search !== false" class="sb-table-tools">
            <div class="sb-table-search">
                <i class="fa fa-search"></i>
                <input
                    type="search"
                    placeholder="Rechercher…"
                    :value="runtime.search"
                    @input="onSearch($event.target.value)"
                />
            </div>
        </div>

        <div class="sb-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th
                            v-for="c in data.columns"
                            :key="c.field"
                            :class="{ 'is-sortable': c.sortable, 'is-num': isNum(c) }"
                            @click="c.sortable && toggleSort(c.field)"
                        >
                            {{ c.label }}
                            <i v-if="c.sortable" class="fa sb-sort" :class="sortIcon(c.field)"></i>
                        </th>
                        <th v-if="actions.length" class="is-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, ri) in data.rows" :key="ri">
                        <td v-for="c in data.columns" :key="c.field" :class="{ 'is-num': isNum(c) }">
                            <template v-if="c.type === 'percentage'">
                                <div class="sb-pct">
                                    <div class="sb-pct-bar"><span :style="{ width: clampPct(row[c.field]) + '%', background: pctColor(row[c.field]) }"></span></div>
                                    <span class="sb-pct-val">{{ fmtNum(row[c.field]) }}%</span>
                                </div>
                            </template>
                            <template v-else-if="c.type === 'status'">
                                <span class="sb-badge" :class="statusClass(row[c.field])">
                                    <i class="sb-dot"></i>{{ row[c.field] }}
                                </span>
                            </template>
                            <template v-else-if="c.type === 'audio_action'">
                                <button
                                    v-if="row[c.field]"
                                    class="sb-play"
                                    :class="{ 'is-playing': playing === row[c.field] }"
                                    data-tip="Écouter le dernier appel"
                                    @click="togglePlay(row[c.field])"
                                >
                                    <i class="fa" :class="playing === row[c.field] ? 'fa-pause' : 'fa-play'"></i>
                                </button>
                                <span v-else class="sb-muted">—</span>
                            </template>
                            <template v-else-if="c.type === 'datetime'">{{ fmtDate(row[c.field]) }}</template>
                            <template v-else-if="c.format">{{ fmtValue(row[c.field], c.format) }}</template>
                            <template v-else-if="c.type === 'number'">{{ fmtNum(row[c.field]) }}</template>
                            <template v-else>{{ row[c.field] === null || row[c.field] === undefined || row[c.field] === "" ? "—" : row[c.field] }}</template>
                        </td>
                        <td v-if="actions.length" class="is-actions">
                            <a
                                v-for="(a, ai) in actions"
                                :key="ai"
                                class="sb-action"
                                :href="actionUrl(a, row)"
                                target="_blank"
                                rel="noopener"
                                :data-tip="a.label"
                            >
                                <i class="fa" :class="a.icon || 'fa-arrow-up-right-from-square'"></i>
                            </a>
                        </td>
                    </tr>
                    <tr v-if="!data.rows.length">
                        <td :colspan="data.columns.length + (actions.length ? 1 : 0)" class="sb-empty-row">Aucun résultat</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="config.pagination" class="sb-table-foot">
            <span>{{ rangeLabel }}</span>
            <div class="sb-pager">
                <button :disabled="data.page <= 1" @click="goto(data.page - 1)"><i class="fa fa-chevron-left"></i></button>
                <span>{{ data.page }} / {{ pages }}</span>
                <button :disabled="data.page >= pages" @click="goto(data.page + 1)"><i class="fa fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</template>

<script>
import { formatNumber, formatValue, formatDateTime } from "../format";

export default {
    props: {
        data: { type: Object, required: true },
        config: { type: Object, default: () => ({}) },
        runtime: { type: Object, default: () => ({}) },
    },

    emits: ["runtime"],

    data() {
        return { playing: null, audio: null, searchTimer: null };
    },

    computed: {
        actions() {
            return (this.config.actions || []).filter((a) => a.label && a.url);
        },
        pages() {
            return Math.max(1, Math.ceil(this.data.total / (this.data.per_page || 1)));
        },
        activeSort() {
            return this.runtime.sort || this.config.sorting || null;
        },
        rangeLabel() {
            if (!this.data.total) return "0 résultat";
            const start = (this.data.page - 1) * this.data.per_page + 1;
            const end = Math.min(this.data.total, start + this.data.rows.length - 1);
            return `${start}–${end} sur ${this.data.total}`;
        },
    },

    beforeUnmount() {
        this.stop();
        clearTimeout(this.searchTimer);
    },

    methods: {
        fmtNum: formatNumber,
        fmtValue: formatValue,
        fmtDate: formatDateTime,

        isNum(c) {
            return c.type === "number" || c.format === "minutes";
        },

        clampPct(v) {
            return Math.max(0, Math.min(100, Number(v) || 0));
        },

        pctColor(v) {
            const n = Number(v) || 0;
            return n >= 75 ? "#10b981" : n >= 50 ? "#f59e0b" : "#ef4444";
        },

        statusClass(v) {
            return { "is-on": v === "Connecté", "is-away": v === "Absent", "is-off": v === "Déconnecté" };
        },

        sortIcon(field) {
            const s = this.activeSort;
            if (!s || s.field !== field) return "fa-sort is-idle";
            return s.direction === "desc" ? "fa-sort-down" : "fa-sort-up";
        },

        toggleSort(field) {
            const s = this.activeSort;
            const direction = s && s.field === field && s.direction === "asc" ? "desc" : "asc";
            this.$emit("runtime", { ...this.runtime, sort: { field, direction }, page: 1 });
        },

        goto(page) {
            this.$emit("runtime", { ...this.runtime, page });
        },

        onSearch(value) {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.$emit("runtime", { ...this.runtime, search: value, page: 1 });
            }, 300);
        },

        actionUrl(action, row) {
            return action.url.replace(/\{(\w+)\}/g, (_, k) => encodeURIComponent(row[k] ?? ""));
        },

        togglePlay(url) {
            if (this.playing === url) {
                this.stop();
                return;
            }
            this.stop();
            this.audio = new Audio(url);
            this.audio.addEventListener("ended", () => (this.playing = null));
            this.audio.addEventListener("error", () => (this.playing = null));
            this.audio.play().then(() => (this.playing = url)).catch(() => (this.playing = null));
        },

        stop() {
            if (this.audio) {
                this.audio.pause();
                this.audio = null;
            }
            this.playing = null;
        },
    },
};
</script>

<style scoped>
.sb-table {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 0;
    gap: 8px;
}
.sb-table-search {
    position: relative;
    max-width: 260px;
}
.sb-table-search i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: var(--sb-muted);
}
.sb-table-search input {
    width: 100%;
    height: 32px;
    border: 1px solid var(--sb-border);
    border-radius: 8px;
    padding: 0 10px 0 30px;
    font-size: 13px;
    background: var(--sb-surface);
    color: var(--sb-text);
}
.sb-table-search input:focus {
    outline: none;
    border-color: var(--sb-accent);
    box-shadow: 0 0 0 3px var(--sb-accent-soft);
}
.sb-table-wrap {
    flex: 1;
    min-height: 0;
    overflow: auto;
    border: 1px solid var(--sb-border);
    border-radius: 10px;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
th {
    position: sticky;
    top: 0;
    background: var(--sb-bg-soft);
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--sb-muted);
    padding: 9px 12px;
    white-space: nowrap;
    z-index: 1;
}
th.is-sortable { cursor: pointer; user-select: none; }
th.is-sortable:hover { color: var(--sb-text); }
.sb-sort { margin-left: 4px; font-size: 10px; }
.sb-sort.is-idle { opacity: 0.35; }
td {
    padding: 9px 12px;
    border-top: 1px solid var(--sb-border);
    color: var(--sb-text);
    white-space: nowrap;
}
tbody tr:hover td { background: var(--sb-bg-soft); }
.is-num { text-align: right; font-variant-numeric: tabular-nums; }
.is-actions { text-align: right; width: 1%; }
.sb-empty-row { text-align: center; color: var(--sb-muted); padding: 24px; }
.sb-muted { color: var(--sb-muted); }
.sb-pct { display: flex; align-items: center; gap: 8px; min-width: 110px; }
.sb-pct-bar { flex: 1; height: 6px; border-radius: 99px; background: var(--sb-border); overflow: hidden; }
.sb-pct-bar span { display: block; height: 100%; border-radius: 99px; transition: width 0.4s ease; }
.sb-pct-val { font-size: 12px; font-weight: 600; width: 38px; text-align: right; }
.sb-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 2px 9px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 500;
    background: #f1f5f9;
    color: #475569;
}
.sb-dot { width: 7px; height: 7px; border-radius: 50%; background: #94a3b8; }
.sb-badge.is-on { background: #d1fae5; color: #047857; }
.sb-badge.is-on .sb-dot { background: #10b981; }
.sb-badge.is-away { background: #fef3c7; color: #b45309; }
.sb-badge.is-away .sb-dot { background: #f59e0b; }
.sb-play, .sb-action {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid var(--sb-border);
    background: var(--sb-surface);
    color: var(--sb-text);
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
    margin-left: 4px;
    transition: all 0.15s;
}
.sb-play:hover, .sb-action:hover { background: var(--sb-accent); color: #fff; border-color: var(--sb-accent); }
.sb-play.is-playing { background: var(--sb-accent); color: #fff; border-color: var(--sb-accent); }
.sb-table-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    color: var(--sb-muted);
}
.sb-pager { display: flex; align-items: center; gap: 8px; }
.sb-pager button {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: 1px solid var(--sb-border);
    background: var(--sb-surface);
    color: var(--sb-text);
    cursor: pointer;
}
.sb-pager button:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
