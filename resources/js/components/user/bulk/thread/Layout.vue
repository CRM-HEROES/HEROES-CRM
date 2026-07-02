<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="threadKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <thread-row
                v-for="thread in filteredThreads"
                :key="thread.id"
                :thread="thread"
            />
        </item-list>
        <buttons>
            <button @click.prevent="attach" :disabled="disabled">
                Assigner
            </button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
            >
                Retirer
            </button>
            <a
                v-if="can('all.project.thread.add')"
                @click.prevent="addThread"
                v-text="$t('add')"
            ></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_THREAD,
    BULK_REMOVE_USER_THREAD,
} from "@/actions/project/user/thread";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_THREADS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import ThreadRow from "./ThreadRow.vue";

export default {
    components: {
        ThreadRow,
    },

    data() {
        return {
            bulking: false,
            threadKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addThread() {
            store.commit(OPEN_MODAL, "thread-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_THREAD, {
                    users: usersSelected,
                    threads: this.userBulkThreads,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_THREADS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-threads");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_THREAD, {
                    users: usersSelected,
                    threads: this.userBulkThreads,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_THREADS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-threads");
            }
        },
    },

    computed: {
        ...mapGetters(["threads", "usersSelected", "userBulkThreads", "can"]),

        /**
         *
         */
        disabled() {
            return this.userBulkThreads.length == 0;
        },

        /**
         *
         */
        filteredThreads() {
            const keyword = removeStringAccent(this.threadKeyword);

            return this.threads.filter(
                (thread) =>
                    removeStringAccent(thread.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredThreads) {
                    if (
                        !this.userBulkThreads.find(
                            (thread) => thread == this.filteredThreads[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_THREADS,
                        this.filteredThreads.map((thread) => thread.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_THREADS, []);
                }
            },
        },
    },
};
</script>
