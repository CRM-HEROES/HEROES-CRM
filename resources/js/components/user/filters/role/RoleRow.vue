<template>
    <item tag="label">
        <icon class="fa fa-user-tie" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <div
            v-if="role.users_count"
            class="hc-item-count"
            v-text="role.users_count"
            @click.prevent.stop="search"
        ></div>
        <icon
            v-if="can('all.project.role.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('user.table.filters.roles.without_role', {
                    role: role.name,
                })
            "
            @click.prevent="toggleExclude"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_USERS,
    ADD_USER_PARAMS,
    REMOVE_USER_PARAMS,
    INIT_USER_PARAMS,
    SET_USER_PARAMS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ROLE } from "@/actions/project/role";

export default {
    props: {
        role: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_USERS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_USERS);
        },

        edit() {
            store.commit(OPEN_MODAL, "role-update");
            store.commit(SET_ROLE, this.role);
        },

        /**
         *
         */
        search() {
            store.commit(INIT_USER_PARAMS);

            store.commit(SET_USER_PARAMS, this.filter);
            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["project", "usersParamExists", "can"]),

        /**
         *
         */
        withKey() {
            return "withRoles";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutRoles";
        },

        /**
         *
         */
        value() {
            return this.role.id;
        },

        /**
         *
         */
        isExcluded() {
            return this.usersParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isChecked() {
            return (
                this.usersParamExists(this.withKey, this.value) ||
                this.usersParamExists(this.withoutKey, this.value)
            );
        },

        /**
         *
         */
        excludeStyle() {
            return {
                color: this.isExcluded ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filter() {
            return {
                [this.withKey]: [this.role.id],
            };
        },

        /**
         *
         */
        query() {
            return {
                filters: JSON.stringify(this.filter),
            };
        },
    },
};
</script>
