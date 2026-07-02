<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon
            v-if="can('all')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('user.table.filters.users.without_user', {
                    user: user.name,
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
} from "@/actions/project/user";
import { SET_USER } from "@/actions/project/user";

export default {
    props: {
        user: {
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
            store.commit(SET_USER, this.user);
            this.$router.push({
                name: "user.show",
                params: {
                    project: this.project.slug,
                    user: this.user.id,
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project", "usersParamExists", "can"]),

        /**
         *
         */
        withKey() {
            return "withUsers";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutUsers";
        },

        /**
         *
         */
        value() {
            return this.user.id;
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
    },
};
</script>
