<template>
    <bloc
        icon="fa fa-envelope icon-green"
        :name="$t('user.profile.blocs.threads')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addThread"
            />
        </template>
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
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import ThreadRow from "./ThreadRow.vue";

export default {
    components: {
        Bloc,
        ThreadRow,
    },

    methods: {
        /**
         * Add thread
         * See: @/components/thread/add/Modal.vue
         */
        addThread() {
            store.commit(OPEN_SLIDE, "user-manage-threads");
        },
    },

    computed: {
        ...mapGetters(["project", "userThreads"]),
    },
};
</script>
