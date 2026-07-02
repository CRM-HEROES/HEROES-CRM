<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1" style="height: 100%">
        <template #1>
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
                        <v-field
                            label="Type équipement"
                            required
                            v-slot="{ label }"
                            ><input
                                :placeholder="label + ' ...'"
                                v-model="vehicle.data.typeEquip"
                                required
                        /></v-field>
                        <v-field
                            label="Immatriculation"
                            required
                            v-slot="{ label }"
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
                        <item @click="tab = 1">
                            <icon class="fa fa-user" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t('vehicle.affected_user', {
                                        user: affectedUser
                                            ? affectedUser.name
                                            : '...',
                                    })
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                    </template>
                </item-list>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingVehicle" />
            </form>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t('vehicle.affected_user', {
                                user: '',
                            })
                        "
                    ></div>
                </item>
                <search v-model="userKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        @click.prevent="(vehicle.user_id = user.id), (tab = 0)"
                    />
                </item-list>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_VEHICLE } from "@/actions/project/vehicle";
import { CLOSE_MODAL } from "@/actions/modal";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            tab: 0,
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
                user_id: null,
            },
            addingVehicle: false,
            userKeyword: "",
        };
    },

    methods: {
        async storeVehicle() {
            this.addingVehicle = true;
            try {
                await store.dispatch(ADD_VEHICLE, this.vehicle);
            } catch (e) {
                console.error(e);
            } finally {
                this.addingVehicle = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["project", "users"]),

        /**
         *
         */
        affectedUser() {
            return this.users.find((user) => user.id == this.vehicle.user_id);
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
