<template>
    <bloc icon="fa fa-user-md icon-blue" :name="$t('user.profile.blocs.roles')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addRole"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
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
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        Bloc,
        RoleRow,
    },

    methods: {
        /**
         * Add role
         * See: @/components/role/add/Modal.vue
         */
        addRole() {
            store.commit(OPEN_SLIDE, "user-manage-roles");
        },
    },

    computed: {
        ...mapGetters(["project", "userRoles"]),
    },
};
</script>
