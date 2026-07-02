<template>
    <div class="hc-flex-column" style="height: 100%; position: relative">
        <item @click="$emit('back')" style="border-bottom: 1px solid #eee">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="prospectFullName"
            ></div>
        </item>
        <div
            class="hc-flex-1 hc-flex-column"
            padding="12px"
            style="position: relative; padding: 12px; overflow: auto"
        >
            <template v-if="prospectToUpdate">
                <prospect-field-row
                    v-for="field in prospectDefaultFields"
                    :key="field.id"
                    :field="field"
                    v-model="prospectToUpdate[field.slug]"
                    @update-address="(e) => updateNewProspectAddress(e)"
                />
                <prospect-field-row
                    v-for="field in prospectMetaFields"
                    :key="field.id"
                    :field="field"
                    v-model="prospectToUpdate.meta[field.slug]"
                />
            </template>
        </div>
        <buttons>
            <button
                @click.prevent="updateProspect"
                v-text="$t('update')"
            ></button>
        </buttons>
        <loading :loading="updatingProspect || fetchingProspect" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

import { UPDATE_PROSPECT } from "@/actions/project/prospect";

import ProspectFieldRow from "./ProspectFieldRow.vue";

export default {
    components: {
        ProspectFieldRow,
    },

    data() {
        return {
            updatingProspect: false,
            fetchingProspect: false,
            prospectToUpdate: null,
        };
    },

    mounted() {
        this.fetchProspect();
    },

    methods: {
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
        async updateProspect() {
            this.updatingProspect = true;

            try {
                await store.dispatch(UPDATE_PROSPECT, this.prospectToUpdate);
                this.$emit("prospect-updated", this.prospectToUpdate);
            } finally {
                this.updatingProspect = false;
                this.tab = 0;
            }
        },

        async fetchProspect() {
            if (!this.prospect) {
                return;
            }

            this.fetchingProspect = true;

            try {
                const { data } = await ProspectService.show(
                    this.project.slug,
                    this.prospect.id
                );
                this.prospectToUpdate = data;
            } finally {
                this.fetchingProspect = false;
            }
        },
    },

    watch: {
        async prospect() {
            this.fetchProspect();
        },
    },

    computed: {
        ...mapGetters(["project", "fields", "prospect"]),

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

        /**
         *
         */
        prospectFullName() {
            if (!this.prospect) {
                return "";
            }

            return [this.prospect.first_name, this.prospect.last_name]
                .filter((e) => e)
                .join(" ");
        },
    },
};
</script>
