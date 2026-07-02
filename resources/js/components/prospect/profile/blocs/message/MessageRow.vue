<template>
    <div class="hc-prospect-message hc-flex-column">
        <div class="hc-prospect-message-header">
            <icon class="fa fa-envelope" />
            <div
                class="hc-prospect-message-author"
                v-if="message.creator"
                v-text="message.creator.name"
            ></div>
            <span
                class="hc-item-main-content hc-prospect-message-date"
                v-text="date"
            ></span>
            <icon
                v-if="canEdit"
                tag="a"
                class="fa fa-pen"
                :size="24"
                v-tooltip="$t('edit')"
                @click.prevent="edit"
            />
        </div>
        <div class="hc-prospect-message-body" v-html="message.body"></div>
        <div
            class="hc-prospect-message-users"
            v-if="message.users && message.users.length > 0"
        >
            <span
                v-for="user in message.users"
                :key="user.id"
                class="hc-tag"
                style="color: white; background-color: #ff8d00"
                >{{ user.name }}
                <i v-if="user.pivot.archived_at" class="fa fa-lock"></i
            ></span>
        </div>
        <div
            class="hc-prospect-message-attachments"
            v-if="message.attachments && message.attachments.length > 0"
        >
            <attachment-row
                v-for="c in message.attachments"
                :key="c.id"
                :attachment="c"
            />
        </div>
    </div>
</template>

<style>
.hc-prospect-message {
    padding: 0px 5px 5px 5px;
    position: relative;
    border-bottom: 1px solid #eee;
}

.hc-prospect-message-header {
    align-items: center;
    display: flex;
    flex-direction: row;
    width: 100%;
    font-size: 12px;
}

.hc-prospect-message-users {
    padding: 5px 10px;
}

.hc-prospect-message .hc-icon {
    font-size: 11px;
}

.hc-prospect-message > * {
    width: 100%;
}

.hc-prospect-message-date {
    color: #777777;
    font-size: 11px;
    text-align: right;
}

.hc-prospect-message-body {
    white-space: normal;
    padding: 0 10px;
}

.hc-prospect-message-attachments {
    width: 100%;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 10px;
    padding: 5px 10px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    REMOVE_PROSPECT_MESSAGE,
    ARCHIVE_PROSPECT_MESSAGE_USER,
} from "@/actions/project/prospect/message";

import AttachmentRow from "./AttachmentRow.vue";

export default {
    components: {
        AttachmentRow,
    },

    props: {
        /**
         *
         */
        message: {
            type: Object,
        },
    },

    data() {
        return {
            removingMessage: false,
        };
    },

    methods: {
        async removeMessage() {
            this.removingMessage = true;

            try {
                await store.dispatch(REMOVE_PROSPECT_MESSAGE, this.message);
            } finally {
                this.removingMessage = false;
            }
        },

        edit() {
            this.$emit("edit");
        },

        addUser() {
            this.$emit("add-user");
        },

        archive() {
            store.dispatch(ARCHIVE_PROSPECT_MESSAGE_USER, {
                message: this.message,
                user: this.user,
            });
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),

        /**
         *
         */
        date() {
            return dayjs(this.message.created_at).fromNow();
        },

        /**
         *
         */
        canEdit() {
            return this.user && this.user.id == this.message.creator_id;
        },

        /**
         *
         */
        waiting() {
            if (!this.user || !this.message.users) {
                return null;
            }

            return this.message.users.find((user) => user.id == this.user.id);
        },
    },
};
</script>
