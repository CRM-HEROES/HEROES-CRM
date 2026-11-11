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
                    <div>
                        <input
                            type="checkbox"
                            v-model="caseSensitive"
                            @change="resetResult"
                        />
                        Respecter la casse
                    </div>
                                        <div class="hc-find-replace-search">
                        <span class="fa fa-list-ol" aria-hidden="true"></span>
                        <select
                            v-model="searchLimit"
                            aria-label="Nombre maximum de prospects à rechercher"
                            @click.stop
                        >
                            <option v-for="limit in searchLimits" :key="limit" :value="limit">
                                {{ limit }}
                            </option>
                            <option value="custom">Autre...</option>
                        </select>
                        <input
                            v-if="searchLimit === 'custom'"
                            v-model.number="customSearchLimit"
                            type="number"
                            min="1"
                            max="200"
                            aria-label="Nombre maximum personnalisé"
                            @click.stop
                            @keyup.enter="find"
                        />
                    </div>
                    
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
                                    <del>
                                        <template
                                            v-for="part in sampleParts(s)"
                                        >
                                            <span
                                                v-if="part.match"
                                                :key="'match-' + part.occurrenceIndex"
                                                class="hc-find-replace-occurrence"
                                                :class="{
                                                    'hc-find-replace-occurrence-current': isCurrentOccurrence(s, part.occurrenceIndex),
                                                }"
                                            >
                                                {{ part.text }}
                                                <span
                                                    v-if="isCurrentOccurrence(s, part.occurrenceIndex)"
                                                    class="hc-find-replace-occurrence-actions"
                                                >
                                                    <button
                                                        type="button"
                                                        title="Remplacer cette occurrence"
                                                        aria-label="Remplacer cette occurrence"
                                                        :disabled="loading"
                                                        @click.stop.prevent="replaceOccurrence(s, part.occurrenceIndex)"
                                                    >
                                                        <span class="fa fa-check" aria-hidden="true"></span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        title="Passer à l’occurrence suivante"
                                                        aria-label="Passer à l’occurrence suivante"
                                                        :disabled="loading"
                                                        @click.stop.prevent="skipOccurrence"
                                                    >
                                                        <span class="fa fa-forward" aria-hidden="true"></span>
                                                    </button>
                                                </span>
                                            </span>
                                            <span v-else :key="'text-' + part.index">
                                                {{ part.text }}
                                            </span>
                                        </template>
                                    </del>
                                <span class="fa fa-arrow-right"></span>
                                <ins v-text="s.after"></ins>
                                </label>
                            </li>
                        </ul>
                        <div
                            v-if="reviewing"
                            class="hc-find-replace-review"
                            aria-live="polite"
                        >
                            Survolez l’occurrence en surbrillance pour la remplacer ou passer à la suivante
                            ({{ reviewPosition + 1 }}/{{ reviewQueue.length }}).
                        </div>
                        <div
                            v-else-if="reviewCompleted"
                            class="hc-find-replace-review"
                            aria-live="polite"
                        >
                            Parcours terminé.
                        </div>
                        <button
                            v-else
                            type="button"
                            class="hc-find-replace-review-button"
                            @click="startReview"
                        >
                            Remplacer occurrence par occurrence
                        </button>
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
            searchLimit: 10,
            customSearchLimit: 10,
            searchLimits: [10, 20, 30, 50, 100, 200],
            result: null,
            selectedSamples: [],
            confirming: false,
            loading: false,
            error: null,
            reviewQueue: [],
            reviewPosition: 0,
            reviewing: false,
            reviewCompleted: false,
        };
    },

    mounted() {
        this.$refs.search.focus();
    },

    methods: {
        resetResult() {
            this.result = null;
            this.selectedSamples = [];
            this.reviewQueue = [];
            this.reviewPosition = 0;
            this.reviewing = false;
            this.reviewCompleted = false;
        },

        async run(mode, extraParams = {}) {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    field: this.field,
                    search: this.search,
                    replace: this.replace,
                    case_sensitive: this.caseSensitive,
                    limit: this.limit,
                    mode,
                    ...extraParams,
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
            if (!this.canFind) {
                return;
            }
            this.result = null;
            this.selectedSamples = [];
            this.reviewing = false;
            this.reviewCompleted = false;
            this.result = (await this.run("preview")) || null;
        },

        findOccurrences(value) {
            if (!this.search || !value) {
                return [];
            }

            const numericSearch = /^\+?[\d\s().-]+$/u.test(this.search);
            const search = numericSearch
                ? this.search.replace(/\s+/gu, "")
                : this.search;
            if (!search) {
                return [];
            }

            const pattern = numericSearch
                ? Array.from(search, character => this.escapeRegExp(character)).join("\\s*")
                : this.escapeRegExp(search);
            const flags = numericSearch || this.caseSensitive ? "gu" : "giu";
            const expression = new RegExp(pattern, flags);
            const occurrences = [];
            let match;

            while ((match = expression.exec(value)) !== null) {
                occurrences.push({
                    index: match.index,
                    length: match[0].length,
                    text: match[0],
                });
            }

            return occurrences;
        },

        escapeRegExp(value) {
            return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        },

        sampleParts(sample) {
            const occurrences = this.findOccurrences(sample.before);
            const parts = [];
            let cursor = 0;

            occurrences.forEach((occurrence, occurrenceIndex) => {
                if (occurrence.index > cursor) {
                    parts.push({
                        index: parts.length,
                        match: false,
                        text: sample.before.slice(cursor, occurrence.index),
                    });
                }
                parts.push({
                    match: true,
                    occurrenceIndex,
                    text: occurrence.text,
                });
                cursor = occurrence.index + occurrence.length;
            });

            if (cursor < sample.before.length || !parts.length) {
                parts.push({
                    index: parts.length,
                    match: false,
                    text: sample.before.slice(cursor),
                });
            }

            return parts;
        },

        startReview() {
            this.reviewQueue = this.result.samples.flatMap(sample =>
                this.findOccurrences(sample.before).map((_, occurrenceIndex) => ({
                    prospectId: sample.id,
                    occurrenceIndex,
                }))
            );
            this.reviewPosition = 0;
            this.reviewCompleted = false;
            this.reviewing = this.reviewQueue.length > 0;
            if (!this.reviewing) {
                this.error = "Aucune occurrence à traiter dans les exemples affichés.";
            }
        },

        isCurrentOccurrence(sample, occurrenceIndex) {
            const current = this.reviewQueue[this.reviewPosition];
            return this.reviewing
                && current?.prospectId === sample.id
                && current.occurrenceIndex === occurrenceIndex;
        },

        async replaceOccurrence(sample, occurrenceIndex) {
            if (!this.isCurrentOccurrence(sample, occurrenceIndex) || this.loading) {
                return;
            }

            const currentValue = sample.before;
            const data = await this.run("replace_occurrence", {
                prospect_ids: [sample.id],
                occurrence_index: occurrenceIndex,
                expected_value: currentValue,
            });
            const updatedValue = data?.samples?.[0]?.after;
            if (typeof updatedValue !== "string") {
                return;
            }

            sample.before = updatedValue;
            sample.after = this.findOccurrences(updatedValue).length
                ? this.replaceMatches(updatedValue)
                : updatedValue;

            const replacementMatches = this.findOccurrences(this.replace).length;
            const indexShift = replacementMatches - 1;
            for (let index = this.reviewPosition + 1; index < this.reviewQueue.length; index++) {
                const queued = this.reviewQueue[index];
                if (queued.prospectId === sample.id && queued.occurrenceIndex > occurrenceIndex) {
                    queued.occurrenceIndex += indexShift;
                }
            }

            this.advanceReview();
            try {
                await store.dispatch(FETCH_PROSPECTS);
            } catch (error) {
                this.error = "L’occurrence a été remplacée, mais la liste n’a pas pu être actualisée.";
            }
        },

        replaceMatches(value) {
            const occurrences = this.findOccurrences(value);
            let cursor = 0;
            let replaced = "";
            occurrences.forEach(occurrence => {
                replaced += value.slice(cursor, occurrence.index) + this.replace;
                cursor = occurrence.index + occurrence.length;
            });
            return replaced + value.slice(cursor);
        },

        skipOccurrence() {
            if (this.reviewing && !this.loading) {
                this.advanceReview();
            }
        },

        advanceReview() {
            this.reviewPosition++;
            if (this.reviewPosition >= this.reviewQueue.length) {
                this.reviewing = false;
                this.reviewCompleted = true;
            }
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
        searchLimit() {
            this.resetResult();
        },
        customSearchLimit() {
            this.resetResult();
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

        limit() {
            return this.searchLimit === "custom"
                ? this.customSearchLimit
                : this.searchLimit;
        },

        canFind() {
            return this.search.length > 0
                && Number.isInteger(this.limit)
                && this.limit >= 1
                && this.limit <= 200
                && !this.loading;
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
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    margin-bottom: 8px;
    width: 100%;
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

.hc-find-replace-occurrence {
    position: relative;
    background: #fff3a6;
    text-decoration: line-through;
}

.hc-find-replace-occurrence-current {
    background: #ffcf5c;
    outline: 1px solid #c78600;
}

.hc-find-replace-occurrence-actions {
    position: absolute;
    z-index: 1;
    left: 50%;
    bottom: 100%;
    display: none;
    gap: 2px;
    padding: 2px;
    background: white;
    border: 1px solid #bbb;
    border-radius: 4px;
    box-shadow: 0 2px 6px #0003;
    text-decoration: none;
}

.hc-find-replace-occurrence-current:hover .hc-find-replace-occurrence-actions,
.hc-find-replace-occurrence-current:focus-within .hc-find-replace-occurrence-actions {
    display: flex;
}

.hc-find-replace-occurrence-actions button {
    border: 0;
    border-radius: 3px;
    padding: 4px 6px;
    color: white;
    background: #1a73e8;
    cursor: pointer;
}

.hc-find-replace-occurrence-actions button:last-child {
    background: #666;
}

.hc-find-replace-review,
.hc-find-replace-review-button {
    margin-top: 8px;
    font-size: 12px;
}

.hc-find-replace-review-button {
    padding: 5px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
    cursor: pointer;
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

.hc-find-replace-search {
    display: flex;
    align-items: center;
    gap: 6px;
}

.hc-find-replace-search > .fa {
    color: #777;
}

.hc-find-replace-search select,
.hc-find-replace-search input {
    min-width: 56px;
    padding: 6px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
}

.hc-find-replace-search input {
    width: 76px;
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
