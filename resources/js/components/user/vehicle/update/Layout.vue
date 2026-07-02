<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeVehicle"
        v-if="vehicleToUpdate"
    >
        <item-list gap="5px">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="vehicleToUpdate.name"
                    required
            /></v-field>
            <v-field label="Tracker" required v-slot="{ label }">
                <select v-model="vehicleToUpdate.tracker">
                    <option value="optimum-automotive">
                        Optimum Automotive
                    </option>
                </select>
            </v-field>
            <template v-if="vehicleToUpdate.tracker == 'optimum-automotive'">
                <v-field label="ID véhicule" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicleToUpdate.data.idVH"
                        required
                /></v-field>
                <v-field label="Type équipement" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicleToUpdate.data.typeEquip"
                        required
                /></v-field>
                <v-field label="Immatriculation" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicleToUpdate.data.immat"
                        required
                /></v-field>
                <v-field label="Modèle" required v-slot="{ label }"
                    ><input
                        :placeholder="'XX-XXX-XX'"
                        v-model="vehicleToUpdate.data.modele"
                        required
                /></v-field>
                <v-field label="Marque" required v-slot="{ label }"
                    ><input
                        :placeholder="'RENAULT, ...'"
                        v-model="vehicleToUpdate.data.marque"
                        required
                /></v-field>
                <v-field label="ID driver" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicleToUpdate.data.idDriver"
                        required
                /></v-field>
            </template>
        </item-list>
        <buttons>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading :loading="updatingVehicle || fetchingVehicle" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserVehicleService from "@/apis/project/user/vehicle";

// Actions
import { UPDATE_USER_VEHICLE } from "@/actions/project/user/vehicle";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            vehicle: {
                name: "Véhicule",
                tracker: "optimum-automotive",
                data: {
                    idVH: "",
                    typeEquip: "",
                    immat: "",
                    modele: "",
                    marque: "",
                    idDriver: "",
                },
            },
            vehicleToUpdate: this.userVehicle,
            fetchingVehicle: false,
            updatingVehicle: false,
        };
    },

    methods: {
        async storeVehicle() {
            this.updatingVehicle = true;
            try {
                await store.dispatch(UPDATE_USER_VEHICLE, this.vehicleToUpdate);
            } catch (e) {
                flashError({
                    title: "",
                    body: e.response.data.message,
                });
            } finally {
                this.updatingVehicle = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    watch: {
        async userVehicle(newValue) {
            if (newValue) {
                this.vehicleToUpdate = newValue;

                this.fetchingVehicle = true;
                const { data } = await UserVehicleService.show(
                    this.project.slug,
                    this.user.id,
                    newValue.id
                );
                this.vehicleToUpdate = data;
                this.fetchingVehicle = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "user", "userVehicle"]),
    },
};
</script>
