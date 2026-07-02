<template>
    <bloc icon="fa fa-envelope" :name="$t('project.profile.blocs.threads')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addThread" />
            <icon v-if="threads.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="threads.length > 0"
            >
                <thread-row
                    v-for="(thread, i) in threads"
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
import { OPEN_MODAL } from "@/actions/modal";

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
            store.commit(OPEN_MODAL, "thread-add");
        },
    },

    computed: {
        ...mapGetters(["project", "threads"]),
    },
};
</script>
