<template>
    <bloc icon="fa fa-user icon-brown" :name="$t('user.profile.blocs.users')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addUser"
            />
        </template>
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
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        Bloc,
        UserRow,
    },

    methods: {
        /**
         * Add user
         * See: @/components/user/add/Modal.vue
         */
        addUser() {
            store.commit(OPEN_SLIDE, "user-manage-users");
        },
    },

    computed: {
        ...mapGetters(["project", "userUsers"]),
    },
};
</script>
