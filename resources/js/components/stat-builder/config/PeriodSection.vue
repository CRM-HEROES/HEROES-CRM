<template>
    <section class="sb-section">
        <div class="sb-section-title">Période</div>

        <label class="sb-check">
            <input type="checkbox" :checked="followsGlobal" @change="toggleGlobal($event.target.checked)" />
            <span>Suivre la période du dashboard <span class="sb-hint">(filtre global)</span></span>
        </label>

        <div v-if="!followsGlobal" class="sb-row">
            <label class="sb-field">
                <span class="sb-label">Période propre à ce widget</span>
                <select class="sb-input" v-model="config.period.preset">
                    <option v-for="p in periods" :key="p.value" :value="p.value">{{ p.label }}</option>
                </select>
            </label>
            <template v-if="config.period.preset === 'custom'">
                <label class="sb-field">
                    <span class="sb-label">Du</span>
                    <input class="sb-input" type="date" v-model="config.period.from" />
                </label>
                <label class="sb-field">
                    <span class="sb-label">Au</span>
                    <input class="sb-input" type="date" v-model="config.period.to" />
                </label>
            </template>
        </div>

        <label v-if="compare" class="sb-check">
            <input type="checkbox" v-model="config.compare" />
            <span>Comparer avec la période précédente</span>
        </label>
    </section>
</template>

<script>
export default {
    props: {
        config: { type: Object, required: true },
        periods: { type: Array, required: true },
        compare: { type: Boolean, default: false },
    },

    computed: {
        followsGlobal() {
            return (this.config.globalFilters || []).includes("period");
        },
    },

    created() {
        if (!this.config.period) this.config.period = { preset: "last_30_days" };
    },

    methods: {
        toggleGlobal(on) {
            const list = (this.config.globalFilters || []).filter((f) => f !== "period");
            if (on) list.push("period");
            this.config.globalFilters = list;
            if (!this.config.period) this.config.period = { preset: "last_30_days" };
        },
    },
};
</script>
