<template>
    <bloc
        icon="fa fa-file-download icon-green"
        :name="$t('user.profile.blocs.exports')"
    >
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <export-row
                    v-for="(userExport, i) in user.exports ? user.exports : []"
                    :key="userExport.id"
                    :user-export="userExport"
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
import ExportRow from "./ExportRow.vue";

export default {
    components: {
        Bloc,
        ExportRow,
    },

    computed: {
        ...mapGetters(["project", "user"]),
    },
};
</script>
