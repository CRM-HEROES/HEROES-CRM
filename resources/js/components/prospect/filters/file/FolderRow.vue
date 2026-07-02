<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <icon
            v-if="can('all.project.calendar.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('prospect.table.filters.file.without_folder', {
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
    FETCH_PROSPECTS,
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
} from "@/actions/project/prospect";
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
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded ? ADD_PROSPECT_PARAMS : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded ? REMOVE_PROSPECT_PARAMS : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },

        edit() {
            store.commit(OPEN_MODAL, "folder-update");
            store.commit(SET_FOLDER, this.folder);
        },
    },

    computed: {
        ...mapGetters(["prospectsParamExists", "can"]),

        /**
         *
         */
        withKey() {
            return "withFiles";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutFiles";
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
            return this.prospectsParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isChecked() {
            return (
                this.prospectsParamExists(this.withKey, this.value) ||
                this.prospectsParamExists(this.withoutKey, this.value)
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
