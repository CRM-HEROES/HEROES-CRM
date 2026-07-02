<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="project.name"></div>
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('user.table.filters.project.without_project', {
                    project: project.name,
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
    FETCH_GLOBAL_USERS,
    ADD_GLOBAL_USER_PARAMS,
    REMOVE_GLOBAL_USER_PARAMS,
} from "@/actions/user";

export default {
    props: {
        project: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            // Remove without param
            store.commit(REMOVE_GLOBAL_USER_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_GLOBAL_USER_PARAMS
                    : REMOVE_GLOBAL_USER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_GLOBAL_USERS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded
                    ? ADD_GLOBAL_USER_PARAMS
                    : REMOVE_GLOBAL_USER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded
                    ? REMOVE_GLOBAL_USER_PARAMS
                    : ADD_GLOBAL_USER_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_GLOBAL_USERS);
        },
    },

    computed: {
        ...mapGetters(["globalUsersParamExists"]),

        /**
         *
         */
        withKey() {
            return "withProjects";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutProjects";
        },

        /**
         *
         */
        value() {
            return this.project.id;
        },

        /**
         *
         */
        isExcluded() {
            return this.globalUsersParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isChecked() {
            return (
                this.globalUsersParamExists(this.withKey, this.value) ||
                this.globalUsersParamExists(this.withoutKey, this.value)
            );
        },

        /**
         *
         */
        style() {
            return {
                color: this.project.bgcolor,
            };
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
