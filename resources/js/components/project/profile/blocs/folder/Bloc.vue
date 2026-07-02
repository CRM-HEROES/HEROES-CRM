<template>
    <bloc icon="fa fa-folder" :name="$t('project.profile.blocs.folders')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addFolder" />
            <icon v-if="folders.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="folders.length > 0"
            >
                <folder-row
                    v-for="(folder, i) in folders"
                    :key="folder.id"
                    :folder="folder"
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
import FolderRow from "./FolderRow.vue";

export default {
    components: {
        Bloc,
        FolderRow,
    },

    methods: {
        /**
         * Add folder
         * See: @/components/folder/add/Modal.vue
         */
        addFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },
    },

    computed: {
        ...mapGetters(["project", "folders"]),
    },
};
</script>
