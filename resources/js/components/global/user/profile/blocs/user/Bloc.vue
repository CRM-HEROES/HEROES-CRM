<template>
    <bloc icon="fa fa-user icon-brown" :name="$t('user.profile.blocs.users')">
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <user-row
                    v-for="(user, i) in userUsers"
                    :key="user.id"
                    :user="user"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        Bloc,
        UserRow,
    },

    computed: {
        ...mapGetters(["globalUser"]),

        userUsers() {
            return Array.isArray(this.globalUser.users)
                ? this.globalUser.users
                : [];
        },
    },
};
</script>
