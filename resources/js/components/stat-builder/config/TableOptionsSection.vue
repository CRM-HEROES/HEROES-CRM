<template>
    <section class="sb-section">
        <div class="sb-section-title">Affichage du tableau</div>

        <div class="sb-switches">
            <label class="sb-check"><input type="checkbox" v-model="config.search" /><span>Barre de recherche</span></label>
            <label class="sb-check"><input type="checkbox" v-model="config.pagination" /><span>Pagination</span></label>
        </div>

        <div class="sb-row">
            <label v-if="config.pagination" class="sb-field">
                <span class="sb-label">Lignes par page</span>
                <select class="sb-input" v-model.number="config.perPage">
                    <option v-for="n in [5, 10, 25, 50]" :key="n" :value="n">{{ n }}</option>
                </select>
            </label>
            <label class="sb-field">
                <span class="sb-label">Nombre total de lignes affichées</span>
                <input class="sb-input" type="number" min="0" max="500" v-model.number="config.limit" placeholder="Toutes" />
            </label>
        </div>

        <div class="sb-subtitle">Actions personnalisées <span class="sb-hint">(bouton par ligne)</span></div>
        <div v-for="(a, i) in actions" :key="i" class="sb-action-row">
            <select class="sb-input" v-model="a.icon">
                <option v-for="ic in icons" :key="ic.v" :value="ic.v">{{ ic.l }}</option>
            </select>
            <input class="sb-input" v-model="a.label" placeholder="Libellé (infobulle)" />
            <input class="sb-input" v-model="a.url" :placeholder="urlHint" />
            <button type="button" class="sb-btn-icon is-danger" @click="actions.splice(i, 1)"><i class="fa fa-xmark"></i></button>
        </div>
        <button type="button" class="sb-btn-dashed" @click="add"><i class="fa fa-plus"></i> Ajouter une action</button>
        <div class="sb-hint-block">Utilisez <code>{champ}</code> pour insérer la valeur d'une colonne (ex. <code>mailto:{email}</code>).</div>
    </section>
</template>

<script>
export default {
    props: {
        config: { type: Object, required: true },
        source: { type: Object, required: true },
    },

    data() {
        return {
            icons: [
                { v: "fa-arrow-up-right-from-square", l: "Ouvrir" },
                { v: "fa-envelope", l: "Email" },
                { v: "fa-phone", l: "Appel" },
                { v: "fa-user", l: "Profil" },
            ],
        };
    },

    computed: {
        actions() {
            if (!this.config.actions) this.config.actions = [];

            return this.config.actions;
        },
        urlHint() {
            return this.source.kind === "users" ? "mailto:{email}" : "https://…";
        },
    },

    methods: {
        add() {
            this.actions.push({ icon: "fa-arrow-up-right-from-square", label: "", url: "" });
        },
    },
};
</script>

<style scoped>
.sb-switches { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 8px; }
.sb-subtitle { font-size: 12px; font-weight: 600; color: var(--sb-muted); margin: 16px 0 6px; }
.sb-action-row { display: grid; grid-template-columns: 110px 1fr 1.4fr auto; gap: 8px; margin-bottom: 8px; align-items: center; }
.sb-hint-block { font-size: 11px; color: var(--sb-muted); margin-top: 6px; }
code { background: var(--sb-bg-soft); padding: 1px 5px; border-radius: 4px; }
@media (max-width: 640px) { .sb-action-row { grid-template-columns: 1fr 1fr; } }
</style>
