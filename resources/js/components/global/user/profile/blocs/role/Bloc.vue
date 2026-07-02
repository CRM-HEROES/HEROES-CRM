<template>
    <bloc icon="fa fa-user-md" :name="$t('user.profile.blocs.roles')">
        <template #body>
            <div style="padding: 10px 15px">
                <h-field label="Rôle">
                    <select v-model="role">
                        <option :value="null">Aucun</option>
                        <option :value="'super_admin'">Super Admin</option>
                    </select>
                </h-field>
                <role-row
                    v-for="(role, i) in userRoles"
                    :key="role.id"
                    :role="role"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import { UPDATE_GLOBAL_USER } from "@/actions/user";

// Components
import Bloc from "@/components/global/user/profile/blocs/Bloc.vue";

export default {
    components: {
        Bloc,
    },

    props: {
        bloc: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["fields", "globalUser"]),

        role: {
            get() {
                return this.globalUser.role;
            },
            set(value) {
                const update = {
                    id: this.globalUser.id,
                    role: value,
                };

                store.dispatch(UPDATE_GLOBAL_USER, update);
            },
        },

        userRoles() {
            return Array.isArray(this.globalUser.roles)
                ? this.globalUser.roles
                : [];
        },
    },
};
</script>
