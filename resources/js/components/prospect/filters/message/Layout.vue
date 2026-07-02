<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="threadKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-prospects-table-filter-thread">
                <icon class="fa fa-filter" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.filters.message.with_messages')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeThreadStyle"
                    :title="
                        $t('prospect.table.filters.message.without_messages')
                    "
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
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";
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
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyThread,
                value: [],
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyThread,
                    value: [],
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        toggleExcludeThread() {
            // Add or remove with param
            store.commit(
                this.isExcludedThread
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyThread,
                    value: [],
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedThread
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyThread,
                    value: [],
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters([
            "threads",
            "prospect",
            "prospectFullName",
            "prospectsParamExists",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyThread() {
            return "withMessages";
        },

        /**
         *
         */
        withoutKeyThread() {
            return "withoutMessages";
        },

        /**
         *
         */
        isCheckedThread() {
            return (
                (this.prospectsParamExists(this.withKeyThread) &&
                    this.prospectsParamValue(this.withKeyThread).length == 0) ||
                (this.prospectsParamExists(this.withoutKeyThread) &&
                    this.prospectsParamValue(this.withoutKeyThread).length == 0)
            );
        },

        /**
         *
         */
        isExcludedThread() {
            return (
                this.prospectsParamExists(this.withoutKeyThread) &&
                this.prospectsParamValue(this.withoutKeyThread).length == 0
            );
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
