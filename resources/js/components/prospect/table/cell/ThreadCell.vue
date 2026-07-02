<template>
    <label :class="'hc-thread-cell-label' + (isFocused ? ' focused' : '')">
        <span
            class="hc-thread-cell-count"
            v-if="messages.length > 1"
            v-text="messages.length"
        ></span>
        <div class="hc-thread-cell-resume">
            <i v-if="waiting" class="fa fa-user-plus hc-thread-cell-waiting"></i
            ><span v-html="resume"></span>
        </div>
        <div class="hc-thread-cell-threads" v-if="!disabled">
            <div class="hc-thread-cell-new-message">
                <autocomplete
                    ref="textarea"
                    v-model="message.body"
                    :placeholder="
                        $t('prospect.table.cell.thread.enter_message')
                    "
                    :auto-height="true"
                    :strategies="strategies"
                    @click.stop
                    @keydown.enter="addMessage"
                    @selected="addWaitingUser"
                    @focus="isFocused = true"
                    @blur="isFocused = false"
                />
                <icon
                    tag="a"
                    class="fa fa-user-plus hc-thread-cell-message-waiting-user"
                    :size="26"
                    v-tooltip="'Attendre retour ...'"
                    @click.prevent.stop="tab = 2"
                />
                <icon
                    tag="a"
                    class="fa fa-file-alt hc-thread-cell-message-template"
                    :size="26"
                    v-tooltip="'Templates de message'"
                    @click.prevent.stop="tab = 1"
                />
                <icon
                    tag="a"
                    class="fa fa-align-left hc-thread-cell-view-thread"
                    :size="26"
                    v-tooltip="$t('prospect.table.cell.thread.view_thread')"
                />
                <icon
                    tag="a"
                    class="fa fa-paper-plane icon-blue hc-thread-cell-send-thread"
                    :size="26"
                    v-tooltip="$t('prospect.table.cell.thread.send')"
                    @click.prevent.stop="addMessage"
                />
                <loading :loading="addingMessage" />
            </div>
            <div
                class="hc-thread-cell-waiting-users"
                v-if="waitingUsers.length > 0"
                @click.stop
            >
                <tag
                    v-for="user in waitingUsers"
                    :text="user.name + ' ×'"
                    color="#FFFFFF"
                    bgcolor="#ff8d00"
                    @click="removeWaitingUser(user)"
                />
            </div>

            <tab-layout :count="3" :tab="tab">
                <template #1>
                    <div class="hc-thread-cell-messages">
                        <message-row
                            v-for="m in messages"
                            :key="m.id"
                            :prospect="prospect"
                            :message="m"
                            @edit="editMessage(m)"
                            @edit-user="editMessageUser(m)"
                            @remove="removeMessage(m)"
                        />
                    </div>
                </template>

                <template #2>
                    <item-list>
                        <item class="bordered" @click.prevent.stop="tab = 0">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('back')"
                            ></div>
                        </item>
                        <template-row
                            v-for="c in messageTemplates"
                            :key="c.id"
                            :template="c"
                            @click.stop="setMessageTemplate(c)"
                            @send="sendMessageTemplate(c)"
                        />
                    </item-list>
                </template>

                <template #3>
                    <item-list>
                        <item class="bordered" @click.prevent.stop="tab = 0">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('back')"
                            ></div>
                        </item>
                        <message-user-row
                            v-for="user in users"
                            :key="user.id"
                            :user="user"
                            @click.stop="addWaitingUser(user)"
                        />
                    </item-list>
                </template>
            </tab-layout>
        </div>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import dayjs from "dayjs";

import {
    ADD_PROSPECT_MESSAGE,
    UPDATE_PROSPECT_MESSAGE,
    ADD_PROSPECT_MESSAGE_USER,
} from "@/actions/project/prospect/message";
import { SET_PROSPECT } from "@/actions/project/prospect";
import { FETCH_MESSAGE_TEMPLATES } from "@/actions/project/message-template";

// Components
import MessageRow from "./MessageRow.vue";
import TemplateRow from "./TemplateRow.vue";
import MessageUserRow from "./MessageUserRow.vue";

export default {
    components: {
        MessageRow,
        TemplateRow,
        MessageUserRow,
    },

    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },

        /**
         * Thread
         */
        thread: {
            type: Object,
        },
    },

    data() {
        return {
            tab: 0,
            addingMessage: false,
            message: {
                body: "",
            },
            waitingUsers: [],
            isFocused: false,

            strategies: [
                {
                    match: /@(\w*)$/,
                    index: 1,
                    search: (query, callback) => {
                        query = removeStringAccent(query);

                        callback(
                            this.users.filter(
                                (user) =>
                                    removeStringAccent(user.name).indexOf(
                                        query
                                    ) >= 0
                            )
                        );
                    },
                    replace(user) {
                        return `${user.name} `;
                    },
                    template(user) {
                        return user.name;
                    },
                },
            ],
        };
    },

    methods: {
        /**
         *
         * @param {*} e
         */
        addMessage(e) {
            if (e.shiftKey) {
                return;
            }

            if (!this.message.body || this.message.body.match(/@\w*$/)) {
                e.preventDefault();
                return;
            }

            e.target.blur();
            this.sendMessage().then(() => {
                e.target.focus();
            });
        },

        /**
         *
         */
        async sendMessage() {
            this.addingMessage = true;
            store.commit(SET_PROSPECT, this.prospect);

            try {
                if (this.message.id) {
                    const { id, body, thread_id } = this.message;
                    await store.dispatch(UPDATE_PROSPECT_MESSAGE, {
                        id,
                        body: body
                            .replace(/\r\n/g, "<br />")
                            .replace(/\n/g, "<br />"),
                        thread_id,
                    });
                } else {
                    await store.dispatch(ADD_PROSPECT_MESSAGE, {
                        thread: this.thread,
                        params: {
                            body: this.message.body
                                .replace(/\r\n/g, "<br />")
                                .replace(/\n/g, "<br />"),
                            users: this.waitingUsers.map((u) => u.id),
                        },
                    });
                }
            } finally {
                this.addingMessage = false;
                this.message = { body: "" };
                this.waitingUsers = [];
            }
        },

        addWaitingUser(user) {
            if (this.message.id) {
                store.dispatch(ADD_PROSPECT_MESSAGE_USER, {
                    message: this.message,
                    user: user,
                });
                this.message = { body: "" };
            } else {
                const users = [...this.waitingUsers, user];
                this.waitingUsers = users.filter(
                    (u, i) => users.findIndex((u2) => u2.id == u.id) == i
                );
            }
            this.tab = 0;
        },

        removeWaitingUser(user) {
            this.waitingUsers = this.waitingUsers.filter(
                (u) => u.id != user.id
            );
        },

        /**
         *
         */
        setMessageTemplate(template) {
            this.message.body = template.body;
            this.tab = 0;
        },

        /**
         *
         */
        sendMessageTemplate(template) {
            this.message.body = template.body;
            this.sendMessage();
            this.tab = 0;
        },

        /**
         *
         */
        fetchMessageTemplates() {
            store.dispatch(FETCH_MESSAGE_TEMPLATES);
        },

        /**
         *
         * @param {*} m
         */
        editMessage(m) {
            this.message = m;
            this.message.body = this.message.body
                .replace(/<br\s*>/g, "\n")
                .replace(/<br\s*\/>/g, "\n");
            this.$refs.textarea.focus();
        },

        /**
         *
         * @param {*} m
         */
        editMessageUser(m) {
            this.message = m;
            this.tab = 2;
        },
    },

    watch: {
        async tab(newValue) {
            if (newValue == 1 && this.messageTemplates.length == 0) {
                this.fetchMessageTemplates();
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters(["users", "messageTemplates", "users"]),

        /**
         * List if messages
         * associated to the given thread
         */
        messages() {
            if (!Array.isArray(this.prospect.messages)) {
                return [];
            }

            const messages = this.prospect.messages.filter(
                (m) => m.thread_id == this.thread
            );

            messages.sort((m1, m2) => (m2.created_at > m1.created_at ? 1 : -1));

            return messages;
        },

        /**
         *
         */
        resume() {
            if (this.messages.length == 0) {
                return "";
            }

            const message = this.messages[0];

            const tempElement = document.createElement("div");
            tempElement.innerHTML = message.body;

            const textContent =
                tempElement.textContent || tempElement.innerText;

            return (
                "<b>" +
                dayjs(new Date(message.created_at)).format("DD/MM") +
                "</b>" +
                " " +
                textContent.trim()
            );
        },

        /**
         *
         */
        waiting() {
            return (
                this.user &&
                this.messages.find(
                    (message) =>
                        message.users &&
                        message.users.find(
                            (user) =>
                                user.id == this.user.id &&
                                !(user.pivot && user.pivot.archived_at)
                        )
                )
            );
        },

        /**
         * Do not allow user
         * to edit some field
         * or on certain conditions applied to the prospect
         */
        disabled() {
            return (
                // deleted or archived prospect
                this.prospect.deleted_at || this.prospect.processed_at
            );
        },
    },
};
</script>
