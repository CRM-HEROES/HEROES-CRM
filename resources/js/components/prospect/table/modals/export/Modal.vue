<template>
    <modal name="prospect-export" :title="'Exporter des prospects'">
        <form
            class="hc-flex-column"
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
            </item-list>
            <p v-if="exportMessage" role="status" v-text="exportMessage"></p>
            <buttons>
                <button type="submit" :disabled="exporting">
                    {{ exporting ? 'Préparation en cours…' : (prospectExport.mode === 'email' ? 'Envoyer par email' : 'Télécharger') }}
                </button>
            </buttons>
        </form>
    </modal>
</template>

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
        /**
         *
         */
        async exportProspects() {
            if (this.exporting) return;

            this.exporting = true;
            this.exportMessage = "";

            try {
                const byEmail = this.prospectExport.mode === "email";
                const params = {
                    mode: this.prospectExport.mode,
                    count: this.prospectExport.count,
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

        ...mapGetters(["prospectsParams", "project"]),
    },
};
</script>
