<template>
    <div class="sb-filters">
        <div class="sb-filters-label"><i class="fa fa-sliders"></i> Filtres</div>

        <!-- Période -->
        <div v-if="enabled.includes('period')" class="sb-field">
            <span class="sb-field-label">Période</span>
            <select :value="period.preset" @change="setPreset($event.target.value)">
                <option v-for="p in meta.periods" :key="p.value" :value="p.value">{{ p.label }}</option>
            </select>
        </div>
        <div v-if="enabled.includes('period') && period.preset === 'custom'" class="sb-field sb-range">
            <input type="date" :value="period.from" @change="setRange('from', $event.target.value)" />
            <span>→</span>
            <input type="date" :value="period.to" @change="setRange('to', $event.target.value)" />
        </div>

        <!-- Filtres multi-valeurs -->
        <div v-for="key in multiKeys" :key="key" class="sb-field sb-dd" @click.stop>
            <span class="sb-field-label">{{ labels[key] }}</span>
            <button class="sb-dd-btn" :class="{ 'is-active': selected(key).length }" @click="toggle(key)">
                {{ summary(key) }} <i class="fa fa-chevron-down"></i>
            </button>
            <div v-if="openKey === key" class="sb-dd-pop">
                <input v-model="query" class="sb-dd-search" placeholder="Rechercher…" />
                <div class="sb-dd-list">
                    <label v-for="o in filteredOptions(key)" :key="o.value" class="sb-dd-opt">
                        <input type="checkbox" :checked="selected(key).includes(o.value)" @change="toggleValue(key, o.value)" />
                        <span>{{ o.label }}</span>
                    </label>
                    <div v-if="!filteredOptions(key).length" class="sb-dd-none">Aucun choix</div>
                </div>
                <button v-if="selected(key).length" class="sb-dd-clear" @click="clear(key)">Tout effacer</button>
            </div>
        </div>

        <div class="sb-filters-end">
            <button v-if="hasActive" class="sb-link" @click="resetAll"><i class="fa fa-xmark"></i> Réinitialiser</button>

            <div class="sb-dd" @click.stop>
                <button class="sb-dd-btn" @click="toggle('__cfg')"><i class="fa fa-plus"></i> Filtre</button>
                <div v-if="openKey === '__cfg'" class="sb-dd-pop is-right">
                    <div class="sb-dd-title">Filtres affichés</div>
                    <label v-for="f in meta.global_filters" :key="f.key" class="sb-dd-opt">
                        <input type="checkbox" :checked="enabled.includes(f.key)" @change="toggleEnabled(f.key)" />
                        <span>{{ f.label }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        meta: { type: Object, required: true },
        modelValue: { type: Object, required: true },
        enabled: { type: Array, required: true },
    },

    emits: ["update:modelValue", "update:enabled"],

    data() {
        return {
            openKey: null,
            query: "",
            labels: { user: "Utilisateur", team: "Équipe", role: "Rôle", status: "Statut", source: "Source" },
        };
    },

    computed: {
        period() {
            return this.modelValue.period || { preset: "last_30_days" };
        },
        multiKeys() {
            return ["user", "team", "role", "status", "source"].filter((k) => this.enabled.includes(k));
        },
        hasActive() {
            return (
                (this.period.preset && this.period.preset !== "last_30_days") ||
                ["user", "team", "role", "status", "source"].some((k) => this.selected(k).length)
            );
        },
    },

    mounted() {
        document.addEventListener("click", this.closeAll);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.closeAll);
    },

    methods: {
        closeAll() {
            this.openKey = null;
        },

        toggle(key) {
            this.openKey = this.openKey === key ? null : key;
            this.query = "";
        },

        emit(patch) {
            this.$emit("update:modelValue", { ...this.modelValue, ...patch });
        },

        setPreset(preset) {
            const p = { preset };
            if (preset === "custom") {
                const to = new Date();
                const from = new Date();
                from.setDate(from.getDate() - 29);
                p.from = from.toISOString().slice(0, 10);
                p.to = to.toISOString().slice(0, 10);
            }
            this.emit({ period: p });
        },

        setRange(k, v) {
            this.emit({ period: { ...this.period, [k]: v } });
        },

        options(key) {
            return this.meta.options[key] || [];
        },

        filteredOptions(key) {
            const q = this.query.trim().toLowerCase();
            return this.options(key).filter((o) => !q || o.label.toLowerCase().includes(q));
        },

        selected(key) {
            return this.modelValue[key] || [];
        },

        summary(key) {
            const s = this.selected(key);
            if (!s.length) return key === "role" || key === "team" ? "Tous" : "Tous";
            if (s.length === 1) {
                const o = this.options(key).find((x) => x.value === s[0]);
                return o ? o.label : "1 sélectionné";
            }
            return `${s.length} sélectionnés`;
        },

        toggleValue(key, value) {
            const s = this.selected(key).slice();
            const i = s.indexOf(value);
            i >= 0 ? s.splice(i, 1) : s.push(value);
            this.emit({ [key]: s });
        },

        clear(key) {
            this.emit({ [key]: [] });
        },

        toggleEnabled(key) {
            const list = this.enabled.slice();
            const i = list.indexOf(key);
            if (i >= 0) {
                list.splice(i, 1);
                // un filtre masqué ne doit plus filtrer
                if (key === "period") this.emit({ period: { preset: "last_30_days" } });
                else this.emit({ [key]: [] });
            } else {
                list.push(key);
            }
            this.$emit("update:enabled", list);
        },

        resetAll() {
            this.$emit("update:modelValue", { period: { preset: "last_30_days" } });
        },
    },
};
</script>

<style scoped>
.sb-filters {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px 12px;
    padding: 12px 14px;
    background: var(--sb-surface);
    border: 1px solid var(--sb-border);
    border-radius: 14px;
}
.sb-filters-label {
    align-self: center;
    font-size: 12px;
    font-weight: 600;
    color: var(--sb-muted);
    margin-right: 4px;
}
.sb-field {
    display: flex;
    flex-direction: column;
    gap: 3px;
    position: relative;
}
.sb-field-label {
    font-size: 11px;
    color: var(--sb-muted);
}
select,
input[type="date"],
.sb-dd-btn {
    height: 34px;
    border: 1px solid var(--sb-border);
    border-radius: 8px;
    padding: 0 10px;
    font-size: 13px;
    background: var(--sb-surface);
    color: var(--sb-text);
}
select:focus,
input[type="date"]:focus {
    outline: none;
    border-color: var(--sb-accent);
    box-shadow: 0 0 0 3px var(--sb-accent-soft);
}
.sb-range {
    flex-direction: row;
    align-items: flex-end;
    gap: 6px;
    color: var(--sb-muted);
}
.sb-range input { width: 140px; }
.sb-dd { position: relative; display: flex; flex-direction: column; gap: 3px; }
.sb-dd-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    min-width: 110px;
    justify-content: space-between;
}
.sb-dd-btn i { font-size: 10px; color: var(--sb-muted); }
.sb-dd-btn.is-active { border-color: var(--sb-accent); background: var(--sb-accent-soft); color: var(--sb-accent); }
.sb-dd-pop {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    z-index: 60;
    min-width: 230px;
    max-width: calc(100vw - 32px);
    background: var(--sb-surface);
    border: 1px solid var(--sb-border);
    border-radius: 12px;
    box-shadow: 0 12px 32px -8px rgba(15, 23, 42, 0.25);
    padding: 8px;
}
.sb-dd-pop.is-right { left: auto; right: 0; }
.sb-dd-search {
    width: 100%;
    height: 32px;
    border: 1px solid var(--sb-border);
    border-radius: 8px;
    padding: 0 10px;
    font-size: 13px;
    margin-bottom: 6px;
    background: var(--sb-surface);
    color: var(--sb-text);
}
.sb-dd-list { max-height: 220px; overflow: auto; }
.sb-dd-opt {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 13px;
    cursor: pointer;
    color: var(--sb-text);
}
.sb-dd-opt:hover { background: var(--sb-bg-soft); }
.sb-dd-none { padding: 10px; color: var(--sb-muted); font-size: 12px; text-align: center; }
.sb-dd-title { font-size: 11px; font-weight: 600; color: var(--sb-muted); padding: 4px 8px; text-transform: uppercase; letter-spacing: 0.04em; }
.sb-dd-clear {
    width: 100%;
    margin-top: 6px;
    border: none;
    background: transparent;
    color: var(--sb-accent);
    font-size: 12px;
    cursor: pointer;
    padding: 6px;
}
.sb-filters-end {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sb-filters-end .sb-dd-btn { min-width: 0; }
.sb-link {
    border: none;
    background: transparent;
    color: var(--sb-muted);
    font-size: 12px;
    cursor: pointer;
}
.sb-link:hover { color: var(--sb-accent); }
@media (max-width: 767px) {
    .sb-field { flex: 1 1 140px; }
    .sb-field select, .sb-dd-btn { width: 100%; }
    .sb-filters-end { width: 100%; justify-content: space-between; margin-left: 0; }
}
</style>
