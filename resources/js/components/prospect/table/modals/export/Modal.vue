<template>
    <modal
        name="prospect-export"
        :title="'Exporter des prospects'"
        @open="resetExportFields"
    >
        <form
            class="hc-flex-column hc-prospect-export-form"
            style="height: 100%"
            @submit.prevent="exportProspects"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field label="Mode d’export" required>
                    <select v-model="prospectExport.mode" :disabled="exporting">
                        <option value="email">Envoi par email</option>
                        <option value="download">Télécharger</option>
                    </select>
                </v-field>
                <v-field
                    :label="'Nombre de prospects à exporter'"
                    required
                >
                    <select v-model="prospectExport.count" :disabled="exporting">
                        <option value="0">Tous</option>
                        <option
                            v-for="i in 10"
                            :key="i"
                            :value="1000 * i"
                            v-text="1000 * i"
                        ></option>
                    </select>
                </v-field>
                <v-field
                    v-if="prospectExport.mode === 'email'"
                    label="Adresse email de destination"
                    required
                >
                    <input
                        v-model.trim="prospectExport.email"
                        type="email"
                        required
                        :disabled="exporting"
                    />
                </v-field>
                <fieldset class="hc-prospect-export-columns" :disabled="exporting">
                    <legend>Colonnes à exporter *</legend>
                    <p>Les colonnes affichées dans le tableau sont présélectionnées.</p>
                    <div class="hc-prospect-export-column-actions">
                        <button
                            type="button"
                            class="hc-button-secondary"
                            @click="selectAllExportFields"
                        >
                            Tout sélectionner
                        </button>
                        <button
                            type="button"
                            class="hc-button-secondary"
                            @click="prospectExport.fields = []"
                        >
                            Tout désélectionner
                        </button>
                    </div>
                    <div class="hc-prospect-export-column-list">
                        <label
                            v-for="column in exportColumns"
                            :key="column.key"
                            class="hc-prospect-export-column"
                        >
                            <input
                                v-model="prospectExport.fields"
                                type="checkbox"
                                :value="column.key"
                            />
                            <span v-text="column.name"></span>
                        </label>
                    </div>
                    <p role="status">
                        {{ selectedExportFields.length ? `Colonnes sélectionnées : ${selectedExportFields.length}` : 'Sélectionnez au moins une colonne à exporter.' }}
                    </p>
                </fieldset>
            </item-list>
            <p v-if="exportMessage" role="status" v-text="exportMessage"></p>
            <buttons>
                <button type="submit" :disabled="exporting || !selectedExportFields.length">
                    {{ exporting ? 'Préparation en cours…' : (prospectExport.mode === 'email' ? 'Envoyer par email' : 'Télécharger') }}
                </button>
            </buttons>
        </form>
    </modal>
</template>

<style scoped>
.hc-prospect-export-form,
.hc-prospect-export-form > .hc-item-list {
    min-height: 0;
}

.hc-prospect-export-columns {
    border: none;
    flex-shrink: 0;
    min-width: 0;
    margin: 0;
    padding: 5px;
    font-size: 12px;
}

.hc-prospect-export-columns > legend {
    float: none;
    width: auto;
    margin: 0;
    font-size: inherit;
    font-weight: 600;
    line-height: 20px;
}

.hc-prospect-export-columns > p {
    margin: 5px 0;
}

.hc-prospect-export-column-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin: 8px 0;
}

.hc-prospect-export-column-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 220px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #8884;
    border-radius: 3px;
}

.hc-prospect-export-column {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    overflow-wrap: anywhere;
}

.hc-prospect-export-column > input {
    flex-shrink: 0;
    width: 14px;
    height: 14px;
    margin: 0;
}
</style>

<script>
import axios from "axios";
import { mapGetters } from "vuex";
import store from "@/store";
import { API_URL } from "@/apis/common";

import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            exporting: false,
            exportMessage: "",
            prospectExport: {
                mode: "email",
                email: "",
                count: 0,
                fields: [],
            },
        };
    },

    created() {
        this.prospectExport.email = this.user.email;
    },

    beforeUnmount() {
        this.exporting = false;
    },

    methods: {
        resetExportFields() {
            const visibleKeys = new Set(
                this.projectUserSettingsProspectsTable
                    .filter((column) => !column.hidden)
                    .map((column) => column.key)
            );

            this.prospectExport.fields = this.exportColumns
                .filter((column) => visibleKeys.has(column.key))
                .map((column) => column.key);
        },

        selectAllExportFields() {
            this.prospectExport.fields = this.exportColumns.map((column) => column.key);
        },

        /**
         *
         */
        async exportProspects() {
            if (this.exporting) return;

            if (!this.selectedExportFields.length) {
                flashError("Sélectionnez au moins une colonne à exporter.");
                return;
            }

            this.exporting = true;
            this.exportMessage = "";

            try {
                const byEmail = this.prospectExport.mode === "email";
                const params = {
                    mode: this.prospectExport.mode,
                    count: this.prospectExport.count,
                    fields: this.selectedExportFields.join(","),
                    filters: JSON.stringify(this.prospectsParams || {}),
                };

                if (byEmail) {
                    params.email = this.prospectExport.email;
                }

                const response = await axios.get(
                    `${API_URL}/project/${this.project.slug}/export/create`,
                    { params, responseType: "json" }
                );

                if (byEmail) {
                    flashInfo(response.data.message);
                } else {
                    this.exportMessage = response.data.message;

                    if (!(await this.waitForDownload(response.data.status_url))) return;

                    const link = document.createElement("a");
                    link.href = response.data.download_url;
                    link.download = `${this.project.name}.xlsx`;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }

                store.commit(CLOSE_MODAL);
            } catch (error) {
                let data = error.response?.data;

                if (data instanceof Blob) {
                    try {
                        data = JSON.parse(await data.text());
                    } catch {
                        data = null;
                    }
                }

                const validationError = Object.values(data?.errors || {}).flat()[0];
                flashError(validationError || data?.message || error.message || "Impossible d’exporter les prospects. Veuillez réessayer.");
            } finally {
                this.exporting = false;
                this.exportMessage = "";
            }
        },

        async waitForDownload(statusUrl) {
            while (this.exporting) {
                await new Promise((resolve) => setTimeout(resolve, 3000));
                if (!this.exporting) return false;

                const { data } = await axios.get(statusUrl);

                if (data.status === "ready") return true;
                if (data.status === "failed" || data.status === "missing") {
                    throw new Error("La préparation du fichier a échoué. Veuillez relancer l’export.");
                }

                this.exportMessage = data.status === "queued"
                    ? "Votre export est en attente de préparation…"
                    : "Préparation du fichier Excel en cours…";
            }

            return false;
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),

        ...mapGetters([
            "prospectsParams",
            "project",
            "fields",
            "categories",
            "threads",
            "projectUserSettingsProspectsTable",
            "can",
            "canEvent",
        ]),

        exportColumns() {
            const columns = [
                ...this.fields
                    .filter((field) => field.for === "prospect")
                    .map((field) => ({
                        key: field.meta ? `meta->${field.slug}` : field.slug,
                        name: field.name,
                    })),
                ...this.categories
                    .filter((category) => category.for === "prospect")
                    .map((category) => ({
                        key: `category->${category.id}`,
                        name: category.name,
                    })),
                ...this.threads.map((thread) => ({
                    key: `thread->${thread.id}`,
                    name: thread.name,
                })),
                { key: "history", name: this.$t("prospect.table.column.others.history") },
                { key: "users", name: this.$t("prospect.table.column.others.affected_users") },
                { key: "groups", name: this.$t("prospect.table.column.others.affected_groups") },
                {
                    key: "events",
                    name: this.project.slug === "professionnel"
                        ? "Missions"
                        : this.$t("prospect.table.column.others.appointments"),
                },
                { key: "sms", name: this.$t("prospect.table.column.others.sms") },
                { key: "interactions", name: this.$t("prospect.table.column.others.calls") },
                { key: "orders", name: this.$t("prospect.table.column.others.orders") },
                { key: "links", name: this.$t("prospect.profile.setting.external_links") },
            ].filter((column) => {
                if (column.key === "events") {
                    return this.can("all.prospect.event.view") || this.canEvent;
                }
                if (column.key === "sms") return this.can("all.prospect.sms.view");
                if (column.key === "interactions") return this.can("all.prospect.interaction.view");
                return true;
            });

            const columnsByKey = new Map(columns.map((column) => [column.key, column]));
            const visibleColumns = this.projectUserSettingsProspectsTable
                .filter((column) => !column.hidden);
            const orderedKeys = [
                ...visibleColumns.filter((column) => column.fixed),
                ...visibleColumns.filter((column) => !column.fixed),
            ].map((column) => column.key);

            return [...new Set([...orderedKeys, ...columnsByKey.keys()])]
                .map((key) => columnsByKey.get(key))
                .filter(Boolean);
        },

        selectedExportFields() {
            const selectedKeys = new Set(this.prospectExport.fields);

            return this.exportColumns
                .filter((column) => selectedKeys.has(column.key))
                .map((column) => column.key);
        },
    },
};
</script>
