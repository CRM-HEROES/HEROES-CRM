<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="threadKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" @change="change" />
            </item>
            <thread-row
                v-for="thread in filteredThreads"
                :key="thread.id"
                :thread="thread"
                :value="isThreadChecked(thread)"
                :disabled="canAll"
            />
        </item-list>
        <buttons v-if="can('all.project.thread.add')">
            <a @click.prevent="addThread" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_ROLE_THREADS,
    BULK_ADD_ROLE_THREAD,
    BULK_REMOVE_ROLE_THREAD,
    ADD_ROLE_THREAD,
    REMOVE_ROLE_THREAD,
} from "@/actions/project/role/thread";
import { FETCH_THREADS } from "@/actions/project/thread";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ThreadRow from "./ThreadRow.vue";

export default {
    components: {
        ThreadRow,
    },

    data() {
        return {
            name: "role-manage-threads",
            tab: 0,
            threadKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} thread
         */
        isThreadChecked(thread) {
            return (
                this.canAll ||
                this.roleThreads.findIndex((l) => l.id == thread.id) >= 0
            );
        },

        /**
         *
         */
        addThread() {
            store.commit(OPEN_MODAL, "thread-add");
        },
    },

    computed: {
        ...mapGetters([
            "threads",
            "role",
            "roleThreads",
            "rolePermissions",
            "can",
        ]),

        /**
         *
         */
        canAll() {
            return this.rolePermissions.includes("all");
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
                return (
                    this.canAll ||
                    this.roleThreads.length == this.threads.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.threads.forEach((f) => {
                        store.commit(ADD_ROLE_THREAD, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_THREAD, {
                        roles: [this.role.id],
                        threads: this.threads.map((f) => f.id),
                    });
                } else {
                    this.threads.forEach((f) => {
                        store.commit(REMOVE_ROLE_THREAD, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_THREAD, {
                        roles: [this.role.id],
                        threads: this.threads.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
