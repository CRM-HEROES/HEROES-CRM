<template>
    <bloc icon="fa fa-user" :name="$t('project.profile.blocs.users')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addUser" />
            <icon v-if="users.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="users.length > 0"
            >
                <user-row
                    v-for="(user, i) in users"
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
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_MODAL, "user-add");
        },
    },

    computed: {
        ...mapGetters(["project", "users"]),
    },
};
</script>
