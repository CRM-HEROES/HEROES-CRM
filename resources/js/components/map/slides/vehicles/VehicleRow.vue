<template>
    <item tag="label" class="hc-map-vehicle">
        <icon class="fa fa-car" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-map-vehicle-name" v-text="vehicle.name"></div>
            <div class="hc-map-vehicle-user" v-text="vehicle.user.name"></div>
        </div>
        <checkbox :model-value="value" @change="change" />
        <loading :loading="addingVehicle" />
    </item>
</template>

<style>
.hc-map-vehicle {
    padding: 2px 0 !important;
}

.hc-map-vehicle-name {
    flex: 1;
}

.hc-map-vehicle-user {
    opacity: 0.8;
}
</style>

<script>
import store from "@/store";

// Actions
import { ADD_MAP_VEHICLE, REMOVE_MAP_VEHICLE } from "@/actions/project/map";
import { OPEN_MODAL } from "@/actions/modal";
import { SHOW_VEHICLE } from "@/actions/project/vehicle";
import { SET_USER_VEHICLE } from "@/actions/project/user/vehicle";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        vehicle: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
            addingVehicle: false,
        };
    },

    methods: {
        async change(event) {
            if (event.target.checked) {
                this.addingVehicle = true;

                try {
                    const vehicle = await store.dispatch(
                        SHOW_VEHICLE,
                        this.vehicle.id
                    );
                    store.commit(ADD_MAP_VEHICLE, vehicle);
                } finally {
                    this.addingVehicle = false;
                }
            } else {
                store.commit(REMOVE_MAP_VEHICLE, this.vehicle);
            }
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "user-vehicle-update");
            store.commit(SET_USER, this.vehicle.user);
            store.commit(SET_USER_VEHICLE, this.vehicle);
        },
    },
};
</script>
