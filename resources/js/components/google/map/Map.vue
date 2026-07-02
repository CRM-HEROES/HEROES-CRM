<template>
    <div>
        <div style="width: 100%; min-height: 100%" ref="googleMap"></div>
        <template v-if="Boolean(this.google) && Boolean(this.map)">
            <slot />
        </template>
    </div>
</template>

<script>
import { computed } from "vue";
// import { Loader } from "@googlemaps/js-api-loader";
import GoogleMapInit from "@/utils/google-map";

export default {
    props: {
        options: Object,
        apiKey: String,
    },

    data() {
        return {
            google: null,
            map: null,
        };
    },

    /**
     * Add the map and google object
     * to the component context
     * so these can be injected into the child component
     */
    provide() {
        return {
            google: computed(() => this.google),
            map: computed(() => this.map),
        };
    },

    async mounted() {
        await GoogleMapInit();
        this.google = google;
        this.initializeMap();
        this.addListeners();

        /*
        const loader = new Loader({
            apiKey: this.apiKey,
        });

        loader.load().then((google) => {
            this.google = google;
            this.initializeMap();
            this.addListeners();
        });
        */
    },

    methods: {
        /**
         * Map initialization
         */
        initializeMap() {
            const mapContainer = this.$refs.googleMap;
            this.map = new this.google.maps.Map(mapContainer, this.options);
            this.$emit("map-loaded", this.map);
        },

        /**
         * Listen to all events
         * starting with 'map-'
         * added to the component
         */
        addListeners() {
            this.events.forEach((e) => {
                this.map.addListener(e, (event) => {
                    this.$emit("map-" + e, event, this.map, this.google);
                });
            });
        },

        /**
         * Remove event listeners
         * before unmount
         */
        removeListeners() {
            this.events.forEach((e) => {
                this.map.clearListeners(e);
            });
        },
    },

    beforeUnmount() {
        if (this.map) {
            // this.removeListeners();
        }
    },

    watch: {
        options(newValue) {
            if (this.map) {
                this.map.setOptions(newValue);
            }
        },
    },

    computed: {
        /**
         * Get all '@map-...'
         * event listener
         * from the component
         */
        events() {
            return Object.keys(this.$attrs)
                .filter(
                    (attrName) =>
                        attrName.startsWith("onMap") &&
                        attrName != "onMapLoaded"
                )
                .map((attrName) => attrName.slice(5).toLowerCase());
        },
    },
};
</script>
