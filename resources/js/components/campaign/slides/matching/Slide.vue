<template>
    <slide
        name="campaign-matching-prospects"
        :title="$t('campaign.matching_prospects.title')"
        icon="fa fa-map-marker"
        style="width: 260px"
        @open="fetchProspects"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="prospectKeyword" />
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
                    :disabled="page == 1"
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
import ProspectService from "@/apis/project/campaign/prospect";

import ProspectRow from "./ProspectRow.vue";

export default {
    components: {
        ProspectRow,
    },

    data() {
        return {
            prospects: [],
            prospectKeyword: "",
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
            if (!this.campaign) {
                return;
            }

            this.loading = loading === undefined || loading;
            const filters = {};

            if (this.prospectKeyword) {
                filters.query = this.prospectKeyword;
            }

            const { data } = await ProspectService.matching(
                this.project.slug,
                this.campaign.id,
                {
                    params: {
                        page: this.page,
                        filters: JSON.stringify(filters),
                    },
                }
            );
            this.loading = false;
            this.prospects = data;
        },
    },

    watch: {
        prospectKeyword() {
            this.fetchProspects();
        },
    },

    computed: {
        ...mapGetters(["project", "campaign"]),
    },
};
</script>
