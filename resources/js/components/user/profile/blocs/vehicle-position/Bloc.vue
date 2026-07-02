<template>
    <bloc icon="fa fa-car" :name="vehicle ? vehicle.name : 'Vehicule'">
        <template #body>
            <google-map
                v-if="vehicle"
                style="width: 100%; height: 300px"
                :api-key="apiKey"
                :options="{
                    center: { lat: 50.1773063, lng: 3.2337914 },
                    zoom: 18,
                    fullscreenControl: false,
                    disableDefaultUI: true,
                }"
            >
                <map-marker
                    v-for="marker in markers"
                    :key="marker.id"
                    :options="{
                        position: marker,
                    }"
                />
                <!--map-direction
                    v-if="origin && destination"
                    :options="{
                        origin: origin,
                        destination: destination,
                        waypoints: waypoints,
                        suppressMarkers: true,
                        polylineOptions: { strokeColor: '#FF000055' },
                    }"
                /-->
            </google-map>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";

import VehicleService from "@/apis/project/vehicle";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import GoogleMap from "@/components/google/map/Map.vue";
import MapMarker from "@/components/google/map/Marker.vue";
import MapDirectionsRenderer from "@/components/google/map/DirectionsRenderer.vue";
import MapDirectionsService from "@/components/google/map/DirectionsService.vue";
import MapInfoWindow from "@/components/google/map/InfoWindow.vue";
import vehicle from "../../../../../apis/project/vehicle";

export default {
    components: {
        Bloc,
        GoogleMap,
        MapMarker,
        MapDirectionsRenderer,
        MapDirectionsService,
        MapInfoWindow,
    },

    data() {
        return {
            apiKey: process.env.MIX_GOOGLE_MAP_API_KEY,
            fetchingPositions: false,
            positions: [],
        };
    },

    created() {
        this.fetchVehiclePositions();
    },

    methods: {
        async fetchVehiclePositions() {
            if (!this.vehicle) {
                return;
            }

            this.fetchingPositions = true;

            try {
                const { data } = await VehicleService.positions(
                    this.project.slug,
                    this.vehicle.id
                );

                this.positions = data;
            } finally {
                this.fetchingPositions = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "user", "userVehicles"]),

        vehicle() {
            if (this.userVehicles.length > 0) {
                return this.userVehicles[this.userVehicles.length - 1];
            }

            return null;
        },

        markers() {
            return this.positions.map((position) => ({
                id: position.id,
                lat: position.latitude,
                lng: position.longitude,
            }));
        },

        origin() {
            if (this.markers.length < 2) {
                return null;
            }

            return this.markers[0];
        },

        destination() {
            if (this.markers.length < 2) {
                return null;
            }

            return this.markers[this.markers.length - 1];
        },

        waypoints() {
            if (this.markers.length < 2) {
                return null;
            }

            return this.markers
                .slice(1, this.markers.length - 1)
                .map(({ lat, lng }) => ({
                    location: { lat, lng },
                    stopover: true,
                }));
        },
    },
};
</script>
