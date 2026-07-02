<template>
    <bloc
        icon="fa fa-folder icon-blue"
        :name="$t('user.profile.blocs.folders')"
    >
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

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import FolderRow from "./FolderRow.vue";

export default {
    components: {
        Bloc,
        FolderRow,
    },

    computed: {
        ...mapGetters(["globalUser"]),

        userFolders() {
            return Array.isArray(this.globalUser.folders)
                ? this.globalUser.folders
                : [];
        },
    },
};
</script>
