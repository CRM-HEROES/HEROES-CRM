<template>
    <label
        :class="[
            'hc-default-cell-label',
            prospect.valid_address
                ? ''
                : prospect.valid_address === 0
                ? 'error'
                : 'warning',
        ]"
    >
        <google-map-input
            class="hc-default-cell-input"
            :value="value"
            @change="updateStreet"
            @changed="updateAddress"
            :disabled="disabled"
            placeholder=""
        />
        <span v-text="value"></span>
    </label>
</template>

<script>
import store from "@/store";

import { UPDATE_PROSPECT } from "@/actions/project/prospect";

export default {
    props: {
        /**
         * Prospect
         */
        prospect: {
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
         * Update prospect address
         * @param {*} e
         */
        updateAddress(e) {
            this.updatingAddress = true;

            // Mark the prospect address
            // as valid
            store.commit(UPDATE_PROSPECT, {
                id: this.prospect.id,
                valid_address: true,
            });

            // Update all prospect fields
            // related to address
            store.dispatch(UPDATE_PROSPECT, {
                id: this.prospect.id,

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

        updateStreet(e) {
            if (this.updatingAddress) {
                this.updatingAddress = false;
            } else {
                store.dispatch(UPDATE_PROSPECT, {
                    id: this.prospect.id,
                    [this.field]: e.target.value,
                });
            }
        },
    },

    computed: {
        /**
         * When value is updated
         * Send modification to the API
         */
        value() {
            return this.prospect[this.field];
        },

        /**
         * Do not allow user
         * to edit
         * on certain conditions applied to the prospect
         */
        disabled() {
            return this.prospect.deleted_at || this.prospect.processed_at;
        },
    },
};
</script>
