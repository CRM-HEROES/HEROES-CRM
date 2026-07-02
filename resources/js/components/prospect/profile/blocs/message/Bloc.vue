<template>
    <bloc icon="fa fa-envelope icon-green" :name="thread.name">
        <template #options>
            <icon
                tag="a"
                class="fa fa-comments"
                @click.prevent.stop="manageMessages"
            />
        </template>
        <template #body>
            <template v-for="(m, i) in messages">
                <message-row
                    :key="m.id"
                    v-if="i <= shownMessagesCount"
                    :message="m"
                    @edit="message = { ...m }"
                />
            </template>

            <buttons v-if="messages.length > shownMessagesCount">
                <button
                    @click.prevent="showMoreMessages"
                    v-text="$t('show_more')"
                ></button>
            </buttons>
            <form
                v-if="!disabled"
                class="hc-flex-column"
                @submit.prevent="message.id ? updateMessage() : addMessage()"
                style="padding: 0 10px; position: relative"
            >
                <template v-if="showTextEditor">
                    <text-editor
                        v-model.lazy.enter="message.body"
                        :min-height="40"
                        @enter="message.id ? updateMessage() : addMessage()"
                        placeholder="Saisir un message ..."
                        ref="textarea"
                    />
                    <div
                        class="hc-prospect-new-message-attachments"
                        v-if="attachments.length"
                    >
                        <uploading-attachment-row
                            v-for="c in attachments"
                            :key="c.name"
                            :attachment="c"
                            @remove="removeAttachment"
                        />
                    </div>
                </template>
                <div class="hc-flex-row">
                    <icon
                        v-if="showTextEditor"
                        tag="a"
                        class="fa fa-caret-down"
                        v-tooltip="'Reduire'"
                        @click="showTextEditor = false"
                    />
                    <icon
                        v-else
                        tag="a"
                        class="fa fa-caret-up"
                        v-tooltip="'Agrandir'"
                        @click="showTextEditor = true"
                    />
                    <div class="hc-item-main-content"></div>
                    <icon
                        tag="label"
                        class="fa fa-link"
                        v-tooltip="'Ajouter des PJ'"
                        ><input type="file" @change="addAttachments" multiple
                    /></icon>
                    <icon
                        tag="button"
                        class="fa fa-paper-plane"
                        style="color: #8641a6"
                        v-tooltip="'Envoyer'"
                    />
                </div>
                <loading :loading="addingMessage" />
            </form>
            <loading :loading="fetchingMessages" />
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import MessageService from "@/apis/project/prospect/message";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT } from "@/actions/project/prospect";
import {
    ADD_PROSPECT_MESSAGE,
    UPDATE_PROSPECT_MESSAGE,
    SET_PROSPECT_MESSAGE_THREAD,
} from "@/actions/project/prospect/message";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import MessageRow from "./MessageRow.vue";
import UploadingAttachmentRow from "./UploadingAttachmentRow.vue";

export default {
    components: {
        Bloc,
        MessageRow,
        UploadingAttachmentRow,
    },

    props: {
        bloc: {
            type: Object,
        },
        thread: {
            type: Object,
        },
    },

    data() {
        return {
            shownMessagesCount: 5,
            fetchingMessages: false,
            messages: [],

            attachments: [],
            showTextEditor: true,
            message: this.newMessage(),
            addingMessage: false,
        };
    },

    created() {
        if (this.prospect) {
            this.fetchMessages();
        }
    },

    methods: {
        showMoreMessages() {
            this.shownMessagesCount += 20;
        },

        async fetchMessages() {
            this.fetchingMessages = true;

            try {
                let { data } = await MessageService.get(
                    this.project.slug,
                    this.prospect.id,
                    this.thread.id
                );
                this.messages = data.sort((m1, m2) =>
                    m1.created_at >= m2.created_at ? 1 : -1
                );
            } finally {
                this.fetchingMessages = false;
            }
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages() {
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_PROSPECT_MESSAGE_THREAD, this.thread);
        },

        /**
         *
         */
        newMessage() {
            return {
                body: "",
            };
        },

        /**
         *
         */
        async addAttachments(e) {
            this.attachments = [...this.attachments, ...e.target.files];
        },

        /**
         *
         */
        async dropAttachments(e) {
            this.attachments = [...this.attachments, ...e.dataTransfer.files];
        },

        /**
         *
         * @param {*} attachment
         */
        removeAttachment(attachment) {
            this.attachments = this.attachments.filter((a) => a != attachment);
        },

        /**
         *
         */
        async addMessage() {
            this.addingMessage = true;

            var formData = new FormData();
            for (const i in this.message) {
                formData.append(i, this.message[i]);
            }

            this.attachments.forEach((attachment) => {
                formData.append("attachments[]", attachment, attachment.name);
            });

            try {
                const message = await store.dispatch(ADD_PROSPECT_MESSAGE, {
                    thread: this.thread.id,
                    params: formData,
                });
                this.messages = [...this.messages, message];
            } finally {
                this.addingMessage = false;
                this.attachments = [];
                this.message = this.newMessage();
            }
        },

        /**
         *
         */
        async updateMessage() {
            this.addingMessage = true;

            const { id, body, thread_id } = this.message;

            try {
                await store.dispatch(UPDATE_PROSPECT_MESSAGE, {
                    id,
                    body,
                    thread_id,
                });
                this.messages = this.messages.map((m) =>
                    m.id == this.message.id ? { ...m, ...this.message } : m
                );
            } finally {
                this.addingMessage = false;
                this.message = this.newMessage();
            }
        },
    },

    watch: {
        prospect(newValue, oldValue) {
            if (newValue && (!oldValue || oldValue.id != newValue.id)) {
                this.fetchMessages();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "prospect"]),
    },
};
</script>
