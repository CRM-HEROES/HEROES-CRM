<template>
    <div>
        <item @click="$emit('back')" style="border-bottom: 1px solid #eee">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="$t('event.choose_vehicle')"
            ></div>
        </item>
        <search v-model="vehicleKeyword" />
        <item-list class="hc-flex-1" padding="12px" style="position: relative">
            <vehicle-row
                v-for="vehicle in filteredVehicles"
                :key="vehicle.id"
                :vehicle="vehicle"
                @click="$emit('vehicle-selected', vehicle)"
            />
            <loading :loading="fetchingVehicles" />
        </item-list>
        <buttons v-if="can('all.project.vehicle.add')">
            <a @click.prevent="addVehicle" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Components
import VehicleRow from "./VehicleRow.vue";

import { OPEN_MODAL } from "@/actions/modal";

export default {
    components: {
        VehicleRow,
    },

    data() {
        return {
            vehicleKeyword: "",
        };
    },

    methods: {
        addVehicle() {
            store.commit(OPEN_MODAL, "vehicle-add");
        },
    },

    computed: {
        ...mapGetters(["vehicles", "can"]),

        /**
         *
         */
        filteredVehicles() {
            const keyword = removeStringAccent(this.vehicleKeyword);

            return this.vehicles.filter(
                (vehicle) =>
                    removeStringAccent(vehicle.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
