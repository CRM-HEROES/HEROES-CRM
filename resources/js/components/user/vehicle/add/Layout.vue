<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeVehicle"
    >
        <item-list gap="5px">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="vehicle.name"
                    required
            /></v-field>
            <v-field label="Tracker" required v-slot="{ label }">
                <select v-model="vehicle.tracker">
                    <option value="optimum-automotive">
                        Optimum Automotive
                    </option>
                </select>
            </v-field>
            <template v-if="vehicle.tracker == 'optimum-automotive'">
                <v-field label="ID véhicule" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicle.data.idVH"
                        required
                /></v-field>
                <v-field label="Type équipement" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicle.data.typeEquip"
                        required
                /></v-field>
                <v-field label="Immatriculation" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicle.data.immat"
                        required
                /></v-field>
                <v-field label="Modèle" required v-slot="{ label }"
                    ><input
                        :placeholder="'XX-XXX-XX'"
                        v-model="vehicle.data.modele"
                        required
                /></v-field>
                <v-field label="Marque" required v-slot="{ label }"
                    ><input
                        :placeholder="'RENAULT, ...'"
                        v-model="vehicle.data.marque"
                        required
                /></v-field>
                <v-field label="ID driver" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="vehicle.data.idDriver"
                        required
                /></v-field>
            </template>
        </item-list>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingVehicle" />
    </form>
</template>

<script>
import store from "@/store";

// Actions
import { ADD_USER_VEHICLE } from "@/actions/project/user/vehicle";
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
            addingVehicle: false,
        };
    },

    methods: {
        async storeVehicle() {
            this.addingVehicle = true;
            try {
                await store.dispatch(ADD_USER_VEHICLE, this.vehicle);
            } catch (e) {
                flashError({
                    title: "",
                    body: e.response.data.message,
                });
            } finally {
                this.addingVehicle = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },
};
</script>
