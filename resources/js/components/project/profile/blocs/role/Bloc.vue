<template>
    <bloc icon="fa fa-user-md" :name="$t('project.profile.blocs.roles')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addRole" />
            <icon v-if="roles.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="roles.length > 0"
            >
                <role-row
                    v-for="(role, i) in roles"
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
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_MODAL, "role-add");
        },
    },

    computed: {
        ...mapGetters(["project", "roles"]),
    },
};
</script>
