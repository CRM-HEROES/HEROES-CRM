<template>
    <item tag="label">
        <icon
            :class="['fa', folder.private ? 'fa-folder' : 'fa-folder-open']"
            :style="style"
        />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <icon
            v-if="can('all.project.folder.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="folder.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FOLDER } from "@/actions/project/folder";
import { UPDATE_USER_BULK_FOLDERS } from "@/actions/project/user";

export default {
    props: {
        folder: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "folder-update");
            store.commit(SET_FOLDER, this.folder);
        },
    },

    computed: {
        ...mapGetters(["userBulkFolders", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.folder.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkFolders;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_FOLDERS, value);
            },
        },
    },
};
</script>
