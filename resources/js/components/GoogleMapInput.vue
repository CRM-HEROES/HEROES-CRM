<template>
    <input ref="input" :value="modelValue" @change="change" />
</template>

<style>
.pac-container {
    border-radius: 5px;
    box-shadow: 0 5px 10px -5px #0005;
    min-width: 300px;
    border-top: none;
}

.pac-container:after {
    content: none !important;
}

.pac-item {
    border: none;
    line-height: 28px;
    padding: 0 10px;
}

.pac-item .pac-item-query {
    font-size: 11px;
}

.pac-item .pac-icon {
    display: none;
}

.pac-item:hover {
    background: #12a0f3;
    color: white;
    cursor: pointer;
}

.pac-item:hover .pac-item-query {
    color: white;
}

.dark .pac-container {
    background-color: #3f403f;
}

.dark .pac-item {
    color: #999999;
}

.dark .pac-item-query {
    color: #c9c9c9;
}
</style>

<script>
import GoogleMapInit from "@/utils/google-map";

export default {
    props: {
        /**
         *
         */
        modelValue: {
            type: String,
            default: "",
        },

        /**
         *
         */
        options: {
            type: Object,
            default: {
                // componentRestrictions: { country: "fr" },
                fields: [
                    "formatted_address",
                    "geometry",
                    "name",
                    "address_components",
                ],
                strictBounds: false,
            },
        },
    },

    data() {
        return {
            autocomplete: null,
        };
    },

    mounted() {
        this.initAutocomplete();
    },

    beforeUnmount() {
        this.destroyAutocomplete();
    },

    methods: {
        async initAutocomplete() {
            await GoogleMapInit();

            let input = this.$refs.input;

            if (!input || input.disabled) {
                return;
            }

            this.autocomplete = new google.maps.places.Autocomplete(
                input,
                this.options
            );

            this.autocomplete.addListener(
                "place_changed",
                this.handlePlaceChanged
            );
        },

        destroyAutocomplete() {
            if (this.autocomplete) {
                // Remove event listeners or perform cleanup before destroying the Autocomplete instance
                this.autocomplete.unbindAll();
                google.maps.event.clearInstanceListeners(this.$refs.input);

                // Destroy the Autocomplete instance
                this.autocomplete = null;
            }
        },

        handlePlaceChanged() {
            this.changeFromGMap = true;
            const place = this.autocomplete.getPlace();

            if (!place.address_components) {
                return;
            }

            const addresses = {
                street: [
                    this.getPlaceComponent(place, "street_number"),
                    this.getPlaceComponent(place, "route"),
                    this.getPlaceComponent(place, "neighborhood"),
                ]
                    .filter((e) => e)
                    .join(" "),
                postal_code: this.getPlaceComponent(place, "postal_code"),
                city: this.getPlaceComponent(place, "locality"),
                state: this.getPlaceComponent(
                    place,
                    "administrative_area_level_1"
                ),
                county: this.getPlaceComponent(
                    place,
                    "administrative_area_level_2"
                ),
                country: this.getPlaceComponent(place, "country"),
                place: place,
            };

            if (place.geometry.location) {
                // 5. Latitude
                addresses.latitude = place.geometry.location.lat();
                // 6. Longitude
                addresses.longitude = place.geometry.location.lng();
            }

            this.$emit("changed", addresses);
            this.$emit("update:modelValue", this.$refs.input.value);
        },

        change(event) {
            // this.$emit("change", event);
            this.$emit("update:modelValue", event.target.value);
        },

        getPlaceComponent(place, component) {
            let v = place.address_components.find(
                (a) => a.types.indexOf(component) >= 0
            );

            return v ? v.long_name : "";
        },

        focus() {
            this.$refs.input.focus();
        },
    },
};
</script>
