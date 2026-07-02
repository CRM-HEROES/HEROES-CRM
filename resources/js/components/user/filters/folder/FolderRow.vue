<template>
    <item tag="label">
        <icon class="fa fa-folder" :style="style" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <icon
            v-if="can('all.project.folder.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('user.table.filters.folders.without_folder', {
                    folder: folder.name,
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
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FOLDER } from "@/actions/project/folder";

export default {
    props: {
        folder: {
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
            store.commit(OPEN_MODAL, "folder-update");
            store.commit(SET_FOLDER, this.folder);
        },
    },

    computed: {
        ...mapGetters(["usersParamExists", "can"]),

        /**
         *
         */
        withKey() {
            return "withFolders";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutFolders";
        },

        /**
         *
         */
        value() {
            return this.folder.id;
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
        style() {
            return {
                color: this.folder.bgcolor,
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
