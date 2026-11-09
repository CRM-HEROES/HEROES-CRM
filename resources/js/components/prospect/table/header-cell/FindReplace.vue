<template>
    <teleport to="body">
        <div class="hc-find-replace-overlay" @click.self="$emit('close')">
            <div class="hc-find-replace">
                <h4>
                    Rechercher et remplacer
                    <small v-text="'· ' + column.name"></small>
                </h4>

                <input
                    v-model="search"
                    placeholder="Rechercher ..."
                    ref="search"
                    @input="resetResult"
                    @keyup.enter="find"
                />
                <input
                    v-model="replace"
                    placeholder="Remplacer par ..."
                    @keyup.enter="result ? apply() : find()"
                />

                <label class="hc-find-replace-case">
                    <input
                        type="checkbox"
                        v-model="caseSensitive"
                        @change="resetResult"
                    />
                    Respecter la casse
                </label>

                <div v-if="result" class="hc-find-replace-result">
                    <p v-if="result.matches == 0">Aucun résultat trouvé.</p>
                    <template v-else>
                        <p>
                            <b v-text="result.matches"></b>
                            occurrence(s) dans
                            <b v-text="result.prospects"></b>
                            prospect(s)
                            <span v-if="hasFilters">(filtres actuels)</span>
                        </p>
                        <ul>
                            <li v-for="s in result.samples" :key="s.id">
                                <label class="hc-find-replace-sample">
                                    <input
                                        v-model="selectedSamples"
                                        type="checkbox"
                                        :value="s.id"
                                        :aria-label="'Remplacer dans le prospect ' + s.id"
                                    />
                                <del v-text="s.before"></del>
                                <span class="fa fa-arrow-right"></span>
                                <ins v-text="s.after"></ins>
                                </label>
                            </li>
                        </ul>
                    </template>
                </div>

                <p v-if="error" class="hc-find-replace-error" v-text="error"></p>

                <div class="hc-find-replace-actions">
                    <button type="button" @click="$emit('close')">
                        Annuler
                    </button>
                    <button
                        type="button"
                        :disabled="!search || loading"
                        @click="find"
                    >
                        Rechercher
                    </button>
                    <button
                        type="button"
                        class="primary"
                        :disabled="!result || result.matches == 0 || loading"
                        @click="apply"
                    >
                        <template v-if="confirming">
                            <template v-if="selectedSamples.length">
                                Confirmer ({{ selectedSamples.length }} prospect(s))
                            </template>
                            <template v-else>
                                Confirmer ({{ result.matches }})
                            </template>
                        </template>
                        <template v-else-if="selectedSamples.length">
                            Remplacer la sélection ({{ selectedSamples.length }})
                        </template>
                        <template v-else>Tout remplacer</template>
                    </button>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    props: {
        column: {
            type: Object,
        },
    },

    emits: ["close"],

    data() {
        return {
            search: "",
            replace: "",
            caseSensitive: false,
            result: null,
            selectedSamples: [],
            confirming: false,
            loading: false,
            error: null,
        };
    },

    mounted() {
        this.$refs.search.focus();
    },

    methods: {
        resetResult() {
            this.result = null;
            this.selectedSamples = [];
        },

        async run(mode) {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    field: this.field,
                    search: this.search,
                    replace: this.replace,
                    case_sensitive: this.caseSensitive,
                    mode,
                };

                if (mode === "replace" && this.selectedSamples.length) {
                    params.prospect_ids = this.selectedSamples;
                }

                if (this.hasFilters) {
                    params.filters = JSON.stringify(this.prospectsParams);
                }

                const { data } = await ProspectService.findReplace(
                    this.project.slug,
                    params
                );
                return data;
            } catch (e) {
                this.error = "Une erreur est survenue.";
            } finally {
                this.loading = false;
            }
        },

        async find() {
            if (!this.search) {
                return;
            }
            this.result = null;
            this.selectedSamples = [];
            this.result = (await this.run("preview")) || null;
        },

        async apply() {
            // First click asks for confirmation, second one replaces
            if (!this.confirming) {
                this.confirming = true;
                return;
            }

            const data = await this.run("replace");

            if (data) {
                await store.dispatch(FETCH_PROSPECTS);
                this.$emit("close");
            }
        },
    },

    watch: {
        // Any change to the search invalidates the pending confirmation
        search() {
            this.confirming = false;
        },
        replace() {
            this.confirming = false;
        },
        caseSensitive() {
            this.confirming = false;
        },
        selectedSamples() {
            this.confirming = false;
        },
        result() {
            this.confirming = false;
        },
    },

    computed: {
        ...mapGetters(["project", "prospectsParams"]),

        field() {
            return this.column.category == "meta"
                ? "meta->" + this.column.id
                : this.column.id;
        },

        hasFilters() {
            return Object.keys(this.prospectsParams || {}).length > 0;
        },
    },
};
</script>

<style scoped>
.hc-find-replace-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: #0004;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hc-find-replace {
    background: white;
    color: #222;
    border-radius: 6px;
    padding: 16px;
    width: 420px;
    max-width: 95vw;
    box-shadow: 0 8px 30px #0005;
}

.hc-find-replace h4 {
    margin: 0 0 12px;
}

.hc-find-replace h4 small {
    color: #777;
    font-weight: normal;
}

.hc-find-replace > input {
    display: block;
    width: 100%;
    margin-bottom: 8px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.hc-find-replace-case {
    display: block;
    font-size: 12px;
    margin-bottom: 8px;
}

.hc-find-replace-result {
    font-size: 12px;
    max-height: 200px;
    overflow: auto;
    margin-bottom: 8px;
}

.hc-find-replace-result ul {
    list-style: none;
    padding: 0;
    margin: 6px 0 0;
}

.hc-find-replace-result li {
    padding: 2px 0;
    border-top: 1px solid #eee;
}

.hc-find-replace-sample {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.hc-find-replace-sample input {
    flex: none;
}

.hc-find-replace-result del {
    color: #c0392b;
}

.hc-find-replace-result ins {
    color: #27ae60;
    text-decoration: none;
}

.hc-find-replace-error {
    color: #c0392b;
    font-size: 12px;
}

.hc-find-replace-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.hc-find-replace-actions button {
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
    cursor: pointer;
}

.hc-find-replace-actions button.primary {
    background: #1a73e8;
    border-color: #1a73e8;
    color: white;
}

.hc-find-replace-actions button:disabled {
    opacity: 0.5;
    cursor: default;
}
</style>
