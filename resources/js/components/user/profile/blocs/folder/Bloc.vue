<template>
    <bloc
        icon="fa fa-folder icon-blue"
        :name="$t('user.profile.blocs.folders')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addFolder"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <folder-row
                    v-for="(folder, i) in userFolders"
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
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_SLIDE, "user-manage-folders");
        },
    },

    computed: {
        ...mapGetters(["project", "userFolders"]),
    },
};
</script>
