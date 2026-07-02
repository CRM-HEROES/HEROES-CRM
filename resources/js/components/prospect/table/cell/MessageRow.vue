<template>
    <div class="hc-thread-cell-row">
        <div class="hc-thread-cell-row-header">
            <icon
                v-if="waiting"
                :class="[
                    'hc-thread-cell-row-waiting',
                    'fa',
                    waiting.pivot && waiting.pivot.archived_at
                        ? 'fa-lock'
                        : 'fa-user-plus',
                ]"
                @click.prevent.stop="archive"
                :size="20"
            ></icon>
            <i class="fa fa-envelope"></i>
            <span
                class="hc-thread-cell-row-header-title"
                v-text="header"
            ></span>
            <icon
                class="fa fa-times"
                v-tooltip="$t('delete')"
                size="16"
                icon-size="12"
                @click.prevent.stop="remove"
            />
            <icon
                class="fa fa-user-plus"
                size="16"
                icon-size="10"
                @click.prevent.stop="editUser"
            />
            <icon
                class="fa fa-pen"
                v-tooltip="$t('edit')"
                size="16"
                icon-size="10"
                @click.prevent.stop="edit"
            />
        </div>
        <div class="hc-thread-cell-row-body" v-html="body"></div>
        <loading :loading="removingMessage" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import dayjs from "dayjs";

import { SET_PROSPECT } from "@/actions/project/prospect";
import {
    REMOVE_PROSPECT_MESSAGE,
    ARCHIVE_PROSPECT_MESSAGE_USER,
} from "@/actions/project/prospect/message";

export default {
    props: {
        /**
         *
         */
        prospect: {
            type: Object,
        },

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
        archive() {
            store.commit(SET_PROSPECT, this.prospect);
            store.dispatch(ARCHIVE_PROSPECT_MESSAGE_USER, {
                message: this.message,
                user: this.user,
            });
        },

        edit() {
            this.$emit("edit", this.message);
        },

        editUser() {
            this.$emit("edit-user", this.message);
        },

        async remove() {
            this.removingMessage = true;
            store.commit(SET_PROSPECT, this.prospect);

            try {
                await store.dispatch(REMOVE_PROSPECT_MESSAGE, this.message);
            } finally {
                this.removingMessage = false;
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),

        /**
         */
        header() {
            return (
                (this.message.creator ? this.message.creator.name : "") +
                " le " +
                dayjs(new Date(this.message.created_at)).format("DD/MM/YYYY") +
                " à " +
                dayjs(new Date(this.message.created_at)).format("HH:mm")
            );
        },

        /**
         *
         */
        body() {
            return this.message.body;
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
