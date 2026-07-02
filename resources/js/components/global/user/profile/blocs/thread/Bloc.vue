<template>
    <bloc
        icon="fa fa-envelope icon-green"
        :name="$t('user.profile.blocs.threads')"
    >
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <thread-row
                    v-for="(thread, i) in userThreads"
                    :key="thread.id"
                    :thread="thread"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import ThreadRow from "./ThreadRow.vue";

export default {
    components: {
        Bloc,
        ThreadRow,
    },

    computed: {
        ...mapGetters(["globalUser"]),

        userThreads() {
            return Array.isArray(this.globalUser.threads)
                ? this.globalUser.threads
                : [];
        },
    },
};
</script>
