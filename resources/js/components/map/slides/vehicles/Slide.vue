<template>
    <slide
        :name="name"
        :title="$t('map.vehicles.title')"
        icon="fa fa-car"
        style="width: 260px"
        @open="fetchVehicles"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="vehicleKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <vehicle-row
                    v-for="vehicle in filteredVehicles"
                    :key="vehicle.id"
                    :vehicle="vehicle"
                    :value="isVehicleChecked(vehicle)"
                />
            </item-list>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_VEHICLES } from "@/actions/project/vehicle";

// Components
import VehicleRow from "./VehicleRow.vue";

export default {
    components: {
        VehicleRow,
    },

    data() {
        return {
            name: "map-vehicles",
            tab: 0,
            vehicleKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} vehicle
         */
        isVehicleChecked(vehicle) {
            return this.mapVehicles.findIndex((l) => l.id == vehicle.id) >= 0;
        },

        /**
         *
         * @param {*} vehicle
         */
        fetchVehicles() {
            store.dispatch(FETCH_VEHICLES);
        },
    },

    computed: {
        ...mapGetters(["vehicles", "mapVehicles", "slideOpen"]),

        /**
         *
         */
        filteredVehicles() {
            const keyword = removeStringAccent(this.vehicleKeyword);

            return this.vehicles.filter(
                (vehicle) =>
                    removeStringAccent(vehicle.user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
