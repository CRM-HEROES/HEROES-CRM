<template>
    <bloc
        icon="fa fa-map-marker icon-brown"
        :name="$t('user.profile.blocs.geoips')"
    >
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <geoip-row
                    v-for="(geoip, i) in userGeoips"
                    :key="geoip.latitude + '-' + geoip.longitude"
                    :geoip="geoip"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import SessionService from "@/apis/user/session";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import GeoipRow from "./GeoipRow.vue";

export default {
    components: {
        Bloc,
        GeoipRow,
    },

    data() {
        return {
            userGeoips: [],
            fetchingUserGeoips: false,
        };
    },

    created() {
        this.fetchGeoips();
    },

    methods: {
        async fetchGeoips() {
            this.fetchingUserGeoips = true;

            try {
                const { data } = await SessionService.geoips(
                    this.globalUser.id
                );
                this.userGeoips = data;
            } finally {
                this.fetchingUserGeoips = false;
            }
        },
    },

    computed: {
        ...mapGetters(["globalUser"]),
    },
};
</script>
