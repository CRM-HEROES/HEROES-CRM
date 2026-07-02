<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="threadKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-thread">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.threads.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeThreadStyle"
                    :title="$t('user.table.filters.threads.without')"
                    @click.prevent="toggleExcludeThread"
                />
                <checkbox
                    :model-value="isCheckedThread"
                    @change="changeThread"
                />
            </item>
            <thread-row
                v-for="thread in filteredThreads"
                :key="thread.id"
                :thread="thread"
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
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ThreadRow from "./ThreadRow.vue";

export default {
    components: {
        ThreadRow,
    },

    data() {
        return {
            tab: 0,
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

        changeThread(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyThread,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyThread,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeThread() {
            // Add or remove with param
            store.commit(
                this.isExcludedThread ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyThread,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedThread ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyThread,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters([
            "threads",
            "user",
            "userFullName",
            "usersParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyThread() {
            return "withThreads";
        },

        /**
         *
         */
        withoutKeyThread() {
            return "withoutThreads";
        },

        /**
         *
         */
        isCheckedThread() {
            return (
                this.usersParamValue(this.withKeyThread) === "" ||
                this.usersParamValue(this.withoutKeyThread) === ""
            );
        },

        /**
         *
         */
        isExcludedThread() {
            return this.usersParamValue(this.withoutKeyThread) === "";
        },

        /**
         *
         */
        excludeThreadStyle() {
            return {
                color: this.isExcludedThread ? "#CC0000" : "#CCCCCC",
            };
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
    },
};
</script>
