<template>
    <div class="hc-flex-column" style="height: 100%">
        <div class="hc-flex-column hc-flex-1" style="overflow: hidden">
            <search v-model="keyword" />
            <item-list class="hc-flex-1" padding="5px" style="overflow: auto">
                <item tag="label">
                    <icon class="fa fa-check-square" />
                    <div class="hc-item-main-content" v-text="$t('all')"></div>

                    <checkbox v-model="all" />
                </item>

                <thread-row
                    v-for="thread in threads"
                    :key="thread.id"
                    :thread="thread"
                    v-model="selectedThreads"
                />

                <message-row
                    v-for="message in messages"
                    :key="message.id"
                    :message="message"
                    v-model="selectedMessages"
                />
                <loading :loading="removing" />
            </item-list>
            <buttons>
                <button
                    @click.prevent="restore"
                    v-text="$t('restore')"
                ></button>
                <button
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
            </buttons>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import TrashService from "@/apis/project/trash";
import ThreadRow from "./ThreadRow.vue";
import MessageRow from "./MessageRow.vue";

export default {
    components: {
        ThreadRow,
        MessageRow,
    },

    data() {
        return {
            keyword: "",
            threads: [],
            messages: [],
            selectedThreads: [],
            selectedMessages: [],
            removing: false,
        };
    },

    mounted() {
        this.fetch();
    },

    methods: {
        async remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                try {
                    this.removing = true;

                    await TrashService.bulkDestroy(this.project.slug, {
                        threads: this.selectedThreads,
                        messages: this.selectedMessages,
                    });
                    this.fetch();
                } finally {
                    this.removing = false;
                }
            });
        },

        async restore() {
            try {
                await TrashService.bulkRestore(this.project.slug, {
                    threads: this.selectedThreads,
                    messages: this.selectedMessages,
                });
                this.fetch();
            } finally {
            }
        },

        /**
         * Fetch trashed threads and messages
         */
        async fetch() {
            this.fetchThreads();
            this.fetchMessages();
        },

        /**
         * Fetch trashed threads
         */
        async fetchThreads() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.thread(this.project.slug, {
                params,
            });

            this.threads = data.data;
        },

        /**
         * Fetch trashed messages
         */
        async fetchMessages() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.message(this.project.slug, {
                params,
            });

            this.messages = data.data;
        },
    },

    watch: {
        keyword() {
            this.fetch();
        },
    },

    computed: {
        ...mapGetters(["project"]),

        all: {
            get() {
                return (
                    this.selectedThreads.length == this.threads.length &&
                    this.selectedMessages.length == this.messages.length
                );
            },
            set(value) {
                this.selectedThreads = value
                    ? this.threads.map((thread) => thread.id)
                    : [];
                this.selectedMessages = value
                    ? this.messages.map((message) => message.id)
                    : [];
            },
        },
    },
};
</script>
