<template>
    <item>
        <icon class="fa fa-car" />
        <div class="hc-item-main-content" v-text="vehicle.name"></div>
        <icon
            v-if="can('all')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_USER_VEHICLE } from "@/actions/project/user/vehicle";

export default {
    props: {
        vehicle: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "user-vehicle-update");
            store.commit(SET_USER_VEHICLE, this.vehicle);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.vehicle.bgcolor,
            };
        },
    },
};
</script>
