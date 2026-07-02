<template>
    <item tag="label">
        <icon class="fa fa-user-tie" :style="style" />
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
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_USER_ROLE, REMOVE_USER_ROLE } from "@/actions/project/user/role";
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_ROLE, SHOW_ROLE } from "@/actions/project/role";

import {
    INIT_USER_PARAMS,
    SET_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        role: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked ? ADD_USER_ROLE : REMOVE_USER_ROLE,
                this.role
            );
            this.$emit("change", event);
        },

        async edit() {
            store.commit(OPEN_SLIDE, "manage-roles");
            store.commit(SET_ROLE, this.role);

            try {
                const u = await store.dispatch(SHOW_ROLE, this.role.id);
                store.commit(SET_ROLE, u);
            } finally {
            }
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
        ...mapGetters(["project", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.role.bgcolor,
            };
        },

        /**
         *
         */
        filterKey() {
            return "withRoles";
        },

        /**
         *
         */
        filter() {
            return {
                [this.filterKey]: [this.role.id],
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
