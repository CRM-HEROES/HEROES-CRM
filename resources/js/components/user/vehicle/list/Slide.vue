<template>
    <slide
        name="user-manage-vehicles"
        :title="$t('user.vehicles.title')"
        icon="fa fa-car"
        style="width: 260px"
        @open="fetchUserVehicles()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_VEHICLES } from "@/actions/project/user/vehicle";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    methods: {
        /**
         *
         */
        fetchUserVehicles() {
            if (this.user) {
                store.dispatch(FETCH_USER_VEHICLES, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user"]),
    },
};
</script>
