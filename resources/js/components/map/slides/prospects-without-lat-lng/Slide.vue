<template>
    <slide
        name="map-prospects-without-lat-lng"
        :title="$t('map.prospect_without_lat_lng.title')"
        icon="fa fa-map-marker"
        style="width: 260px"
        @open="fetchProspects"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="prospectkeyword" />
            <div class="hc-flex-1" style="position: relative; overflow: hidden">
                <item-list padding="15px" gap="7px" style="height: 100%">
                    <prospect-row
                        v-for="prospect in prospects"
                        :key="prospect.id"
                        :prospect="prospect"
                        @updated="fetchProspects(false)"
                    />
                </item-list>
                <loading :loading="loading" />
            </div>
            <buttons>
                <button
                    @click.prevent="page--, fetchProspects()"
                    :disabled="page == 0"
                >
                    Précédent
                </button>
                <button
                    @click.prevent="page++, fetchProspects()"
                    :disabled="prospects.length < count"
                >
                    Suivant
                </button>
            </buttons>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import ProspectService from "@/apis/project/prospect";

import ProspectRow from "./ProspectRow.vue";

export default {
    components: {
        ProspectRow,
    },

    data() {
        return {
            prospects: [],
            prospectkeyword: "",
            count: 30,
            page: 1,
            loading: false,
        };
    },

    methods: {
        /**
         *
         */
        async fetchProspects(loading) {
            this.loading = loading === undefined || loading;
            const filters = {
                unknownAddress: 1,
                processed: 0,
            };

            if (this.prospectkeyword) {
                filters.query = this.prospectkeyword;
            }

            const { data } = await ProspectService.get(this.project.slug, {
                params: {
                    filters: JSON.stringify(filters),
                    fields: "id,first_name,last_name,street,street_bis,postal_code,city,country,county,state,valid_address",
                    page: this.page,
                    count: this.count,
                },
            });
            this.loading = false;
            this.prospects = data.data;
        },
    },

    watch: {
        prospectkeyword() {
            this.fetchProspects();
        },
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
