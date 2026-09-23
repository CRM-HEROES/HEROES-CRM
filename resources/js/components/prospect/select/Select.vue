<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div
                class="hc-flex-column"
                style="height: 100%; position: relative"
            >
                <item
                    @click="$emit('back')"
                    style="border-bottom: 1px solid #eee"
                >
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content hc-flex-column"
                        v-text="$t('event.choose_prospect')"
                    ></div>
                </item>
                <search v-model="prospectKeyword" />
                <div
                    class="hc-flex-1"
                    style="position: relative; overflow: hidden"
                >
                    <item-list
                        class="hc-flex-1"
                        padding="12px"
                        style="height: 100%; overflow: auto"
                    >
                        <prospect-row
                            v-for="prospect in prospects"
                            :key="prospect.id"
                            :prospect="prospect"
                            @click="selectProspect(prospect)"
                        />
                    </item-list>
                    <loading :loading="fetchingProspect" />
                </div>
                <buttons>
                    <button
                        @click.prevent="
                            (tab = 1), (emptyProspect = newProspect())
                        "
                        v-text="$t('new')"
                    ></button>
                </buttons>
            </div>
        </template>
        <template #2>
            <div
                class="hc-flex-column"
                style="height: 100%; position: relative"
            >
                <item @click="tab = 0" style="border-bottom: 1px solid #eee">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content hc-flex-column"
                        v-text="$t('event.new_prospect')"
                    ></div>
                </item>
                <div
                    class="hc-flex-1 hc-flex-column"
                    padding="12px"
                    style="position: relative; padding: 12px; overflow: auto"
                >
                    <prospect-field-row
                        v-for="field in prospectDefaultFields"
                        :key="field.id"
                        :field="field"
                        v-model="emptyProspect[field.slug]"
                        @update-address="updateNewProspectAddress"
                    />
                    <prospect-field-row
                        v-for="field in prospectMetaFields"
                        :key="field.id"
                        :field="field"
                        v-model="emptyProspect.meta[field.slug]"
                    />
                </div>
                <buttons>
                    <button
                        @click.prevent="addProspect"
                        v-text="$t('add')"
                    ></button>
                </buttons>
                <loading :loading="addingProspect" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

import { ADD_PROSPECT } from "@/actions/project/prospect";

import ProspectRow from "./ProspectRow.vue";
import ProspectFieldRow from "./ProspectFieldRow.vue";

export default {
    components: {
        ProspectRow,
        ProspectFieldRow,
    },

    props: {
        doFetchProspects: {
            type: Boolean,
            default: false,
        },

        searchFields: {
            type: Array,
            default: () => [
                "first_name",
                "last_name",
                "street",
                "street_bis",
                "postal_code",
                "city",
                "country",
            ],
        },
    },

    data() {
        return {
            tab: 0,
            prospectKeyword: "",
            prospects: [],
            emptyProspect: { meta: {} },
            fetchingProspect: false,
            addingProspect: false,
        };
    },

    mounted() {
        this.fetchProspects();
    },

    methods: {
        /**
         *
         */
        newProspect() {
            return {
                ...Object.fromEntries(
                    this.prospectDefaultFields.map((field) => [field.slug, ""])
                ),
                meta: {
                    ...Object.fromEntries(
                        this.prospectMetaFields.map((field) => [field.slug, ""])
                    ),
                },
            };
        },

        /**
         *
         * @param {*} address
         */
        updateNewProspectAddress(address) {
            this.emptyProspect.street = address.street;
            this.emptyProspect.postal_code = address.postal_code;
            this.emptyProspect.city = address.city;
            this.emptyProspect.country = address.country;
            this.emptyProspect.state = address.state;
            this.emptyProspect.county = address.county;
        },

        /**
         *
         */
        async addProspect() {
            this.addingProspect = true;

            try {
                const prospect = await store.dispatch(
                    ADD_PROSPECT,
                    this.emptyProspect
                );

                this.selectProspect(prospect);
            } finally {
                this.addingProspect = false;
                this.tab = 0;
            }
        },

        async fetchProspects() {
            this.fetchingProspect = this.prospects.length == 0;

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify({
                            query: encodeURI(this.prospectKeyword),
                        }),
                        fields: this.searchFields.join(","),
                        sortBy: "created_at",
                        sortOrder: "desc",
                    },
                });
                this.prospects = data.data;
            } finally {
                this.fetchingProspect = false;
            }
        },

        /**
         *
         * @param {*} prospect
         */
        selectProspect(prospect) {
            this.$emit("prospect-selected", prospect);
        },
    },

    watch: {
        async prospectKeyword() {
            this.fetchProspects();
        },
        /*
        doFetchProspects(newValue) {
            if (newValue) {
                this.fetchProspects();
            }
        },
*/
    },

    computed: {
        ...mapGetters(["project", "fields"]),

        /**
         *
         */
        prospectDefaultFields() {
            return this.fields.filter(
                (field) => !field.meta && field.for == "prospect"
            );
        },

        /**
         *
         */
        prospectMetaFields() {
            return this.fields.filter(
                (field) => field.meta && field.for == "prospect"
            );
        },
    },
};
</script>
