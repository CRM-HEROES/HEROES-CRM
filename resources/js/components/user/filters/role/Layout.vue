<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="roleKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-role">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.roles.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeRoleStyle"
                    :title="$t('user.table.filters.roles.without')"
                    @click.prevent="toggleExcludeRole"
                />
                <checkbox :model-value="isCheckedRole" @change="changeRole" />
            </item>
            <role-row
                v-for="role in filteredRoles"
                :key="role.id"
                :role="role"
            />
        </item-list>
        <buttons v-if="can('all.project.role.add')">
            <a @click.prevent="addRole" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        RoleRow,
    },

    data() {
        return {
            tab: 0,
            roleKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addRole() {
            store.commit(OPEN_MODAL, "role-add");
        },

        changeRole(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyRole,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyRole,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeRole() {
            // Add or remove with param
            store.commit(
                this.isExcludedRole ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyRole,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedRole ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyRole,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters([
            "roles",
            "user",
            "userFullName",
            "usersParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyRole() {
            return "withRoles";
        },

        /**
         *
         */
        withoutKeyRole() {
            return "withoutRoles";
        },

        /**
         *
         */
        isCheckedRole() {
            return (
                this.usersParamValue(this.withKeyRole) === "" ||
                this.usersParamValue(this.withoutKeyRole) === ""
            );
        },

        /**
         *
         */
        isExcludedRole() {
            return this.usersParamValue(this.withoutKeyRole) === "";
        },

        /**
         *
         */
        excludeRoleStyle() {
            return {
                color: this.isExcludedRole ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredRoles() {
            const keyword = removeStringAccent(this.roleKeyword);

            return this.roles.filter(
                (role) => removeStringAccent(role.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
