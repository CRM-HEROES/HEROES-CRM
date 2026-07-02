<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.creator"
        icon="fa fa-envelope"
    >
        <div class="hc-prospect-profile-history-message">
            <b
                class="hc-prospect-profile-history-message-thread"
                v-if="thread"
                v-text="thread.name + ' : '"
            ></b>
            <div
                class="hc-prospect-profile-history-message-body"
                v-html="body"
            ></div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-message {
    display: flex;
    flex-direction: row;
    gap: 8px;
}
</style>

<script>
import { mapGetters } from "vuex";

// Components
import HistoryRow from "./Row.vue";

export default {
    props: {
        item: {
            type: Object,
        },
    },

    components: {
        HistoryRow,
    },

    computed: {
        ...mapGetters(["threads"]),

        body() {
            return this.item.body
                .replace(/<br\s*\/?>/g, "\n")
                .replace(/<[^>]+>/g, "")
                .replace(/\n>/g, "<br>");
        },

        thread() {
            if (!this.item.thread_id) {
                return null;
            }

            return this.threads.find(
                (thread) => thread.id == this.item.thread_id
            );
        },
    },
};
</script>
