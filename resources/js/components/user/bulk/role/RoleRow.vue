<template>
    <item tag="label">
        <icon class="fa fa-user-tie" :style="style" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <icon
            v-if="can('all.project.role.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="role.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ROLE } from "@/actions/project/role";
import { UPDATE_USER_BULK_ROLES } from "@/actions/project/user";

export default {
    props: {
        role: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "role-update");
            store.commit(SET_ROLE, this.role);
        },
    },

    computed: {
        ...mapGetters(["userBulkRoles", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.role.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkRoles;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_ROLES, value);
            },
        },
    },
};
</script>
