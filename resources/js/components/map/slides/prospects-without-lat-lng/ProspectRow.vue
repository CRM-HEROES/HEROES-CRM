<template>
    <div
        :class="[
            'hc-map-prospect-without-lat-lng-row',
            prospect.valid_address === 0
                ? 'hc-map-prospect-without-lat-lng-invalid-address'
                : '',
        ]"
    >
        <div
            class="hc-map-prospect-without-lat-lng-name"
            v-text="fullName"
        ></div>
        <google-map-input :value="fullAddress" @changed="updateLatLng" />
        <loading :loading="updatingAddress" />
    </div>
</template>

<style>
.hc-map-prospect-without-lat-lng-row {
    position: relative;
    font-size: 12px;
}

.hc-map-prospect-without-lat-lng-name {
    width: 100%;
    color: #555555;
}

.hc-map-prospect-without-lat-lng-row > input {
    width: 100%;
    height: 26px;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 0 7px;
    font-size: 11px;
}

.hc-map-prospect-without-lat-lng-invalid-address > input {
    border: 1px solid #c78686;
    color: #af0000;
}
</style>

<script>
import store from "@/store";

import { UPDATE_PROSPECT } from "@/actions/project/prospect";

export default {
    props: {
        /**
         */
        prospect: {
            type: Object,
        },
    },

    data() {
        return {
            updatingAddress: false,
        };
    },

    methods: {
        /**
         *
         */
        async updateLatLng(e) {
            this.$emit("updating");
            this.updatingAddress = true;

            try {
                // Update all prospect fields
                // related to address
                await store.dispatch(UPDATE_PROSPECT, {
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
                this.$emit("updated");
            } finally {
                this.updatingAddress = false;
            }
        },
    },

    computed: {
        /**
         *
         */
        fullName() {
            const fullName = [this.prospect.first_name, this.prospect.last_name]
                .filter((n) => n)
                .join(" ");

            return fullName ? fullName : "Sans nom ...";
        },

        /**
         *
         */
        fullAddress() {
            return [
                this.prospect.street,
                this.prospect.street_bis,
                this.prospect.postal_code,
                this.prospect.county,
                this.prospect.state,
                this.prospect.city,
                this.prospect.country,
            ]
                .filter((n) => n)
                .join(" ");
        },
    },
};
</script>
