<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="vehicleKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <vehicle-row
                v-for="vehicle in filteredVehicles"
                :key="vehicle.id"
                :vehicle="vehicle"
            />
        </item-list>
        <buttons>
            <a @click.prevent="addVehicle" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import VehicleRow from "./VehicleRow.vue";

export default {
    components: {
        VehicleRow,
    },

    data() {
        return {
            name: "user-manage-vehicles",
            vehicleKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addVehicle() {
            store.commit(OPEN_MODAL, "user-vehicle-add");
        },
    },

    computed: {
        ...mapGetters(["user", "userVehicles"]),

        /**
         *
         */
        filteredVehicles() {
            const keyword = removeStringAccent(this.vehicleKeyword);

            return this.userVehicles.filter(
                (vehicle) =>
                    removeStringAccent(vehicle.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
