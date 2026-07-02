<template>
    <bloc icon="fa fa-users icon-brown" :name="$t('user.profile.blocs.groups')">
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <group-row
                    v-for="(group, i) in userGroups"
                    :key="group.id"
                    :group="group"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import GroupRow from "./GroupRow.vue";

export default {
    components: {
        Bloc,
        GroupRow,
    },

    computed: {
        ...mapGetters(["globalUser"]),

        userGroups() {
            return Array.isArray(this.globalUser.groups)
                ? this.globalUser.groups
                : [];
        },
    },
};
</script>
