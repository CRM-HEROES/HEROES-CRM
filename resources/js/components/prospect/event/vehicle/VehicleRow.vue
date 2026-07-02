<template>
    <item>
        <icon class="fa fa-car" />
        <div class="hc-item-main-content" v-text="vehicle.name"></div>
        <!--icon
            v-if="can('all.project.vehicle.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <icon
            :class="[
                'fa',
                'fa-star',
                'hc-show-on-hover',
                isDefault ? 'icon-orange' : 'icon-grey',
            ]"
            @click.prevent.stop="toggleDefault"
        /-->
    </item>
</template>

<script>
/*import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_VEHICLE } from "@/actions/project/vehicle";
import {
    UPDATE_PROJECT_USER_SETTING,
    REMOVE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";*/

export default {
    props: {
        vehicle: {
            type: Object,
        },
    },

    methods: {
        /*edit(e) {
            store.commit(OPEN_MODAL, "vehicle-update");
            store.commit(SET_VEHICLE, this.vehicle);
        },

        toggleDefault() {
            if (this.isDefault) {
                store.dispatch(REMOVE_PROJECT_USER_SETTING, {
                    key: "events.default-vehicle",
                });
            } else {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.default-vehicle",
                    value: this.vehicle.id,
                });
            }
        },*/
    },

    computed: {
        // ...mapGetters(["projectUserSettingsEventsDefaultVehicle", "can"]),

        isDefault() {
            return (
                this.projectUserSettingsEventsDefaultVehicle &&
                this.projectUserSettingsEventsDefaultVehicle == this.vehicle.id
            );
        },
    },
};
</script>
