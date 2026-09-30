<template>
    <slide
        :name="name"
        @open="fetchThreads(), fetchSelectedProspects()"
        :title="$t('prospect.message.title', { prospect: prospectFullName })"
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-thread"
        style="width: 340px"
    >
        <tab-layout :count="3" :tab="tab" class="hc-flex-1">
            <!-- List of threads -->
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="threadKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <item
                            tag="a"
                            @click.prevent="configureEmail"
                            v-tuto="{
                                key: 'project.prospect.message.email-setting',
                                name: 'Prospect - Message - Paramètre email',
                                body: 'Avant d\'envoyer un message dans une discussion externe, paramétrez ici l\'envoi d\'email dans votre projet.',
                                timeout: 500,
                            }"
                        >
                            <icon class="fa fa-cog" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('prospect.message.email_setting')"
                            ></div>
                        </item>
                        <thread-row
                            v-for="c in filteredThreads"
                            :key="c.id"
                            :thread="c"
                            :count="threadMessagesCount(c)"
                            @click="setThread(c)"
                        />
                    </item-list>
                    <buttons v-if="can('all.project.thread.add')">
                        <a @click.prevent="addThread" v-text="$t('add')"></a>
                    </buttons>
                </div>
            </template>

            <!-- List of messages -->
            <template #2>
                <frame-layout :count="2" :tab="frameTab" class="hc-flex-1">
                    <template
                        #1
                        v-if="prospect || prospectsSelected.length > 0"
                    >
                        <tab-layout
                            :count="2"
                            :tab="selectedProspectsTab"
                            class="hc-flex-1"
                        >
                            <template #1>
                                <div
                                    class="hc-flex-column"
                                    style="height: 100%"
                                    v-if="messageThread"
                                    @dragenter.prevent
                                    @dragover.prevent
                                    @dragleave.prevent
                                    @drop.prevent="dropFiles"
                                >
                                    <item
                                        @click="setThread(null)"
                                        class="bordered"
                                    >
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="messageThread.name"
                                        ></div>
                                        <div
                                            style="padding: 0 5px; color: #777"
                                            v-text="
                                                messageThread.private
                                                    ? 'Interne'
                                                    : 'Partagé'
                                            "
                                        ></div>
                                    </item>

                                    <!-- Selected prospects -->

                                    <div
                                        id="hc-prospect-message-selected-prospects"
                                        :class="
                                            selectedProspectsFolded
                                                ? 'folded'
                                                : ''
                                        "
                                    >
                                        <div
                                            id="hc-prospect-message-selected-prospects-list"
                                        >
                                            <selected-prospect
                                                v-for="prospect in selectedProspects"
                                                :key="prospect.id"
                                                :prospect="prospect"
                                                @remove="removeSelectedProspect"
                                            />
                                        </div>
                                        <icon
                                            tag="a"
                                            :class="[
                                                'fa',
                                                selectedProspectsFolded
                                                    ? 'fa-caret-down'
                                                    : 'fa-caret-up',
                                            ]"
                                            @click.prevent.stop="
                                                selectedProspectsFolded =
                                                    !selectedProspectsFolded
                                            "
                                            size="30"
                                            style="min-width: 30px"
                                        />
                                        <icon
                                            tag="a"
                                            class="fa fa-plus"
                                            @click.prevent.stop="
                                                selectedProspectsTab = 1
                                            "
                                            size="30"
                                            style="min-width: 30px"
                                        />
                                    </div>

                                    <div
                                        style="
                                            display: flex;
                                            flex-direction: column;
                                            overflow: auto;
                                            width: 100%;
                                            height: 100%;
                                            scroll-behavior: smooth;
                                            position: relative;
                                        "
                                        ref="messages"
                                    >
                                        <message-row
                                            v-for="c in messages"
                                            :key="c.id"
                                            :message="c"
                                            @add-user="setWaitingUserMessage(c)"
                                            @edit="message = { ...c }"
                                        />

                                        <loading :loading="fetchingMessages" />
                                    </div>
                                    <form
                                        v-if="!disabled"
                                        class="hc-flex-column"
                                        @submit.prevent="
                                            message.id
                                                ? updateMessage()
                                                : addMessage()
                                        "
                                        style="
                                            padding: 0 10px;
                                            position: relative;
                                        "
                                    >
                                        <template v-if="showTextEditor">
                                            <text-editor
                                                v-model.lazy.enter="
                                                    message.body
                                                "
                                                :min-height="100"
                                                @enter="
                                                    message.id
                                                        ? updateMessage()
                                                        : addMessage()
                                                "
                                                placeholder="Saisir un message ..."
                                                ref="textarea"
                                            />
                                            <div
                                                class="hc-prospect-new-message-attachments"
                                                v-if="
                                                    attachments.length ||
                                                    messageInvoice
                                                "
                                            >
                                                <invoice-attachment
                                                    v-if="messageInvoice"
                                                    :invoice="messageInvoice"
                                                    @remove="removeInvoice"
                                                />
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
                                            <div
                                                class="hc-item-main-content"
                                            ></div>
                                            <icon
                                                tag="a"
                                                class="fa fa-file-alt"
                                                v-tooltip="
                                                    'Templates de message'
                                                "
                                                @click.prevent="
                                                    (frameTab = 1), (tab = 2)
                                                "
                                            />
                                            <icon
                                                tag="label"
                                                class="fa fa-link"
                                                v-tooltip="'Ajouter des PJ'"
                                                ><input
                                                    type="file"
                                                    @change="addAttachments"
                                                    multiple
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
                                </div>
                            </template>

                            <template #2>
                                <select-prospect
                                    :search-fields="['first_name', 'last_name']"
                                    @back="selectedProspectsTab = 0"
                                    @prospect-selected="addSelectedProspect"
                            /></template>
                        </tab-layout>
                    </template>

                    <template #2>
                        <select-prospect
                            @back="tab = 0"
                            @prospect-selected="setMessageProspect"
                        />
                    </template>
                </frame-layout>
            </template>

            <template #3>
                <tab-layout :count="3" :tab="frameTab" class="hc-flex-1">
                    <!-- List of users -->
                    <template #1 v-if="waitingUserMessage">
                        <div class="hc-flex-column" style="height: 100%">
                            <item
                                @click="setWaitingUserMessage(null)"
                                class="bordered"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="messageThread.name"
                                ></div>
                            </item>
                            <div
                                class="hc-flex-column hc-flex-1"
                                style="overflow: auto"
                            >
                                <search v-model="userKeyword" />
                                <item-list class="hc-flex-1" padding="5px">
                                    <user-row
                                        v-for="c in filteredUsers"
                                        :key="c.id"
                                        :user="c"
                                        @click="storeMessageUser(c)"
                                    />
                                </item-list>
                            </div>
                        </div>
                    </template>

                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.message.templates.title')
                                    "
                                ></div>
                            </item>
                            <search v-model="messageTemplateKeyword" />
                            <item-list
                                class="hc-flex-1"
                                style="padding: 16px; gap: 10px"
                            >
                                <template-row
                                    v-for="c in filteredMessageTemplates"
                                    :key="c.id"
                                    :template="c"
                                    @click="setMessageTemplate(c)"
                                    @send="sendMessageTemplate(c)"
                                />
                            </item-list>
                            <buttons
                                v-if="can('all.project.message-template.add')"
                            >
                                <a
                                    @click.prevent="addMessageTemplate"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                </tab-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-prospect-new-message-attachments {
    display: flex;
    flex-direction: row;
    overflow: auto;
    scroll-behavior: smooth;
    overflow-y: hidden;
}

.hc-prospect-new-message-attachments::-webkit-scrollbar {
    display: none;
}

#hc-prospect-message-selected-prospects {
    display: flex;
    flex-direction: row;
    border-bottom: 1px solid #eee;
    padding: 5px 5px 0 5px;
    max-height: 100%;
}

#hc-prospect-message-selected-prospects-list {
    height: auto;
    overflow: auto;
    flex: 1;
}

.folded #hc-prospect-message-selected-prospects-list {
    height: 35px;
    overflow: hidden;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

import {
    SET_PROSPECT,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";
import {
    SET_PROSPECT_MESSAGES,
    SET_PROSPECT_WAITING_USER_MESSAGE,
    FETCH_PROSPECT_MESSAGES,
    ADD_PROSPECT_MESSAGE,
    BULK_PROSPECT_MESSAGE,
    UPDATE_PROSPECT_MESSAGE,
    ADD_PROSPECT_MESSAGE_USER,
    SET_PROSPECT_MESSAGE_THREAD,
    FETCH_PROSPECT_THREADS,
    SET_PROSPECT_MESSAGE_INVOICE,
} from "@/actions/project/prospect/message";
import { FETCH_THREADS } from "@/actions/project/thread";
import { OPEN_MODAL } from "@/actions/modal";
import { FETCH_MESSAGE_TEMPLATES } from "@/actions/project/message-template";

// Components
import MessageRow from "./MessageRow.vue";
import ThreadRow from "./ThreadRow.vue";
import UserRow from "./UserRow.vue";
import TemplateRow from "./TemplateRow.vue";
import InvoiceAttachment from "./InvoiceAttachment.vue";
import UploadingAttachmentRow from "./UploadingAttachmentRow.vue";
import SelectedProspect from "./SelectedProspect.vue";
import SelectProspect from "../select/Select.vue";

export default {
    components: {
        MessageRow,
        ThreadRow,
        UserRow,
        TemplateRow,
        InvoiceAttachment,
        UploadingAttachmentRow,
        SelectedProspect,
        SelectProspect,
    },

    data() {
        return {
            name: "prospect-manage-messages",
            tab: 0,
            frameTab: 0,
            selectedProspectsTab: 0,
            threadKeyword: "",
            userKeyword: "",
            messageTemplateKeyword: "",
            showTextEditor: true,

            bulkMessages: [],
            message: this.newMessage(),

            attachments: [],

            selectedProspects: [],
            selectedProspectsFolded: true,

            addingMessage: false,
            fetchingMessages: false,
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
         * @param {*} category
         */
        setThread(thread) {
            store.commit(SET_PROSPECT_MESSAGE_THREAD, thread);
            this.frameTab = 0;
        },

        /**
         *
         */
        fetchThreads() {
            store.dispatch(FETCH_THREADS);

            if (!this.prospect && this.prospectsSelected.length == 0) {
                this.tab = 1;
                this.frameTab = 1;
            }
        },

        /**
         *
         */
        newMessage() {
            return {
                body: "",
            };
        },

        async fetchSelectedProspects() {
            if (this.prospectsSelected.length == 0) {
                this.selectedProspects = [];
                return;
            }

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify({
                            ids: this.prospectsSelected,
                        }),
                        fields: "first_name,last_name",
                    },
                });
                this.selectedProspects = data.data;
            } finally {
                this.fetchingProspect = false;
            }
        },

        addSelectedProspect(prospect) {
            this.selectedProspects = [prospect, ...this.selectedProspects];
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.selectedProspects.map((p) => p.id)
            );
            this.selectedProspectsTab = 0;
        },

        /**
         *
         */
        removeSelectedProspect(prospect) {
            this.selectedProspects = this.selectedProspects.filter(
                (p) => p.id != prospect.id
            );
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.selectedProspects.map((p) => p.id)
            );
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
         * @param {*} attachment
         */
        removeInvoice() {
            store.commit(SET_PROSPECT_MESSAGE_INVOICE, null);
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

            if (this.prospect) {
                try {
                    await store.dispatch(ADD_PROSPECT_MESSAGE, {
                        thread: this.messageThread.id,
                        params: formData,
                    });
                } finally {
                    this.removeInvoice();
                }
            } else if (this.prospectsSelected.length > 0) {
                this.prospectsSelected.forEach((p) => {
                    formData.append("prospects[]", p);
                });

                try {
                    await store.dispatch(BULK_PROSPECT_MESSAGE, {
                        thread: this.messageThread.id,
                        params: formData,
                    });

                    this.bulkMessages = [...this.bulkMessages, this.message];
                } finally {
                }
            }
            this.addingMessage = false;
            this.attachments = [];
            this.message = this.newMessage();
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
            } finally {
                this.addingMessage = false;
                this.message = this.newMessage();
            }
        },

        /**
         *
         * @param {*} user
         */
        storeMessageUser(user) {
            store.dispatch(ADD_PROSPECT_MESSAGE_USER, {
                message: this.waitingUserMessage,
                user: user,
            });
            this.setWaitingUserMessage(null);
        },

        /**
         *
         */
        addMessageTemplate() {
            store.commit(OPEN_MODAL, "message-template-add");
        },

        /**
         *
         */
        setMessageTemplate(template) {
            this.message.body = this.fields.reduce((carry, field) => {
                if (field.meta) {
                    return carry.replaceAll(
                        `{prospect.meta.${field.slug}}`,
                        this.prospect.meta ? this.prospect.meta[field.slug] : ""
                    );
                } else {
                    return carry.replaceAll(
                        `{prospect.${field.slug}}`,
                        this.prospect[field.slug]
                    );
                }
            }, template.body);
            this.frameTab = 0;
            this.tab = 1;
        },

        /**
         *
         */
        sendMessageTemplate(template) {
            this.setMessageTemplate(template);
            this.addMessage();
        },

        /**
         *
         */
        fetchMessageTemplates() {
            store.dispatch(FETCH_MESSAGE_TEMPLATES);
        },

        /**
         *
         * @param {*} thread
         */
        threadMessagesCount(thread) {
            const t = this.prospectThreads.find(
                (t) => t.thread_id == thread.id
            );
            return t ? t.total : 0;
        },

        /**
         *
         * @param {*} message
         */
        setWaitingUserMessage(message) {
            store.commit(SET_PROSPECT_WAITING_USER_MESSAGE, message);
        },

        /**
         *
         */
        configureEmail() {
            store.commit(OPEN_MODAL, "setting-email");
        },

        /**
         *
         */
        setMessageProspect(prospect) {
            store.commit(SET_PROSPECT, prospect);

            if (this.messageThread) {
                this.tab = 1;
                this.frameTab = 0;
            } else {
                this.tab = 0;
            }
        },

        async fetchProspectThreads() {
            try {
                await store.dispatch(FETCH_PROSPECT_THREADS);

                let thread = this.prospectThreads.find((t) => t.total > 0);

                if (thread) {
                    thread = this.threads.find((t) => t.id == thread.thread_id);

                    if (thread) {
                        this.setThread(thread);
                    }
                }
            } finally {
            }
        },
    },

    watch: {
        async messageThread(newValue) {
            if (newValue) {
                this.tab = 1;

                if (this.prospect) {
                    await store.commit(SET_PROSPECT_MESSAGES, []);

                    this.fetchingMessages = true;

                    try {
                        await store.dispatch(
                            FETCH_PROSPECT_MESSAGES,
                            newValue.id
                        );
                    } finally {
                        this.fetchingMessages = false;
                    }
                } else {
                    this.bulkMessages = [];
                }
            } else {
                this.tab = 0;
            }
        },

        async messageInvoice(newValue) {
            if (newValue) {
                this.message.invoice = newValue.id;
            } else {
                this.message.invoice = null;
            }
        },

        async prospect(newValue) {
            this.removeInvoice();
            if (newValue && this.slideOpen(this.name)) {
                this.fetchProspectThreads();

                if (this.messageThread) {
                    store.dispatch(
                        FETCH_PROSPECT_MESSAGES,
                        this.messageThread.id
                    );
                }
            }
        },

        async prospectMessages(newValue, oldValue) {
            await this.$nextTick();

            /*if (newValue.length != oldValue.length && this.$refs.messages) {
                this.$refs.messages.scrollTop =
                    this.$refs.messages.scrollHeight;
            }*/
        },

        waitingUserMessage(newValue) {
            if (newValue) {
                this.tab = 2;
                this.frameTab = 0;
            } else {
                this.tab = 1;
            }
        },

        async tab(newValue) {
            await this.$nextTick();

            if (newValue == 1) {
                // this.$refs.message.scrollTop = this.$refs.message.scrollHeight;
            } else if (newValue == 2) {
                if (this.frameTab == 1) {
                    this.fetchMessageTemplates();
                }
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "threads",
            "fields",
            "prospect",
            "prospectsSelected",
            "prospectFullName",
            "prospectMessages",
            "users",
            "messageTemplates",
            "messageThread",
            "messageInvoice",
            "prospectThreads",
            "slideOpen",
            "waitingUserMessage",
            "can",
        ]),

        /**
         * title
         */
        title() {
            return this.prospect
                ? this.prospectFullName
                : this.prospectsSelected.length + " prospects";
        },

        /**
         * Do not allow user
         * to edit some field
         * or on certain conditions applied to the prospect
         */
        disabled() {
            return (
                // deleted or archived prospect
                this.prospect &&
                (this.prospect.deleted_at || this.prospect.processed_at)
            );
        },

        /**
         *
         */
        filteredMessageTemplates() {
            const keyword = removeStringAccent(this.messageTemplateKeyword);

            return this.messageTemplates.filter(
                (messageTemplate) =>
                    removeStringAccent(messageTemplate.name).indexOf(keyword) >=
                    0
            );
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
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0 &&
                        (!this.waitingUserMessage ||
                            !this.waitingUserMessage.users ||
                            !this.waitingUserMessage.users.find(
                                (u) => u.id == user.id
                            ))
                )
                .sort((user1, user2) =>
                    user1.pivot &&
                    user2.pivot &&
                    user1.pivot.relevance_message >
                        user2.pivot.relevance_message
                        ? -1
                        : 1
                );
        },

        messages() {
            return this.prospect
                ? [...this.prospectMessages].sort((m1, m2) =>
                      m2.created_at >= m1.created_at ? 1 : -1
                  )
                : this.bulkMessages;
        },
    },
};
</script>
