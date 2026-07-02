<template>
    <modal name="prospect-export" :title="'Exporter des prospects'">
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="exportProspects"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field
                    :label="'Nombre de prospects à exporter'"
                    required
                    v-slot="p"
                >
                    <select v-model="prospectExport.count">
                        <option value="0">Tous</option>
                        <option
                            v-for="i in 10"
                            :value="1000 * i"
                            v-text="1000 * i"
                        ></option>
                    </select>
                </v-field>
                <v-field label="Envoyer au email ..." required
                    ><input v-model.lazy="prospectExport.email" required
                /></v-field>
            </item-list>
            <buttons>
                <button v-text="'Exporter'"></button>
            </buttons>
            <loading :loading="exporting" />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { API_URL } from "@/apis/common";

import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            exporting: false,
            prospectExport: {
                email: "",
                count: 0,
            },
        };
    },

    created() {
        this.prospectExport.email = this.user.email;
    },

    methods: {
        /**
         *
         */
        async exportProspects() {
            this.exporting = true;

            try {
                let params = this.prospectsParams;

                // Build query URL
                params = JSON.stringify(params);

                // Download prospects list
                location.href =
                    `${API_URL}/project/${this.project.slug}/export/create?count=${this.prospectExport.count}&email=${this.prospectExport.email}` +
                    (params ? "&filters=" + params : "");
            } finally {
                this.exporting = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),

        ...mapGetters(["prospectsParams", "project"]),
    },
};
</script>
