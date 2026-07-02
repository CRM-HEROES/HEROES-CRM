<template>
    <google-map-input
        class="hc-default-cell-input"
        v-model="value"
        @changed="updateAddress"
        :disabled="disabled"
        placeholder=""
    />
</template>

<script>
import store from "@/store";

import { UPDATE_PROJECT } from "@/actions/project";

export default {
    props: {
        /**
         * Project
         */
        project: {
            type: Object,
        },

        /**
         * Field name (street)
         */
        field: {
            type: String,
        },
    },

    data() {
        return {
            updatingAddress: false,
        };
    },

    methods: {
        /**
         * Select input content
         *
         * @param {*} e
         */
        select(e) {
            // e.target.select();
        },

        /**
         * Update project address
         * @param {*} e
         */
        updateAddress(e) {
            this.updatingAddress = true;

            // Mark the project address
            // as valid
            store.commit(UPDATE_PROJECT, {
                id: this.project.id,
                valid_address: true,
            });

            // Update all project fields
            // related to address
            store.dispatch(UPDATE_PROJECT, {
                id: this.project.id,

                // address fields
                street: e.street,
                postal_code: e.postal_code,
                city: e.city,
                country: e.country,
                state: e.state,
                county: e.county,

                // Lat and Lng
                latitude: e.latitude,
                longitude: e.longitude,
            });
        },
    },

    computed: {
        /**
         * When value is updated
         * Send modification to the API
         */
        value: {
            get() {
                return this.project[this.field];
            },
            set(newValue) {
                // We wait
                // to check if value was updated
                // from google map autocomplete
                // because this event is triggered
                // before the google map autocomplete event
                // (to do: replace it with better solution)
                setTimeout(() => {
                    if (this.updatingAddress) {
                        this.updatingAddress = false;
                    } else {
                        store.dispatch(UPDATE_PROJECT, {
                            id: this.project.id,
                            [this.field]: newValue,
                        });
                    }
                }, 500);
            },
        },

        /**
         * Do not allow user
         * to edit
         * on certain conditions applied to the project
         */
        disabled() {
            return this.project.deleted_at || this.project.processed_at;
        },
    },
};
</script>
