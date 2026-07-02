<template>
    <label class="hc-trash-message hc-flex-column">
        <div class="hc-trash-message-header">
            <icon
                v-if="message.sent"
                class="fa fa-check-double icon-green"
                title="Envoyé"
            />
            <icon
                v-else-if="message.error"
                class="fa fa-exclamation-triangle icon-red"
            />
            <icon v-else class="fa fa-envelope" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-trash-message-prospect"
                    v-if="message.prospect"
                    v-text="
                        [
                            message.prospect.first_name,
                            message.prospect.last_name,
                        ]
                            .filter((n) => n)
                            .join(' ')
                    "
                ></div>
                <span class="hc-trash-message-date" v-text="date"></span>
            </div>
            <checkbox :model-value="isChecked" @change="change" />
        </div>
        <div class="hc-trash-message-body" v-html="message.plain_text"></div>
        <div
            v-if="message.error"
            class="hc-trash-message-error"
            v-html="message.error"
        ></div>
        <div
            class="hc-trash-message-users"
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
            class="hc-trash-message-attachments"
            v-if="message.attachments && message.attachments.length > 0"
        >
            <attachment-row
                v-for="c in message.attachments"
                :key="c.id"
                :attachment="c"
            />
        </div>
    </label>
</template>

<style>
.hc-trash-message {
    padding: 5px 0px 5px 0px;
    position: relative;
    border-top: 1px solid #e5e5e5;
    cursor: pointer;
}

.hc-trash-message:hover {
    background-color: #f5f5f5;
}

.hc-trash-message-header {
    align-items: center;
    display: flex;
    flex-direction: row;
    width: 100%;
    font-size: 12px;
}

.hc-trash-message-prospect {
    font-weight: 500;
}

.hc-trash-message-header > .hc-checkbox {
    margin-right: 5px;
}

.hc-trash-message-users {
    padding: 5px 10px;
}

.hc-trash-message > * {
    width: 100%;
}

.hc-trash-message-date {
    color: #777777;
    font-size: 11px;
}

.hc-trash-message-body {
    white-space: normal;
    padding: 0 10px;
    word-break: break-all;
}

.hc-trash-message-error {
    white-space: normal;
    color: #b75959;
    font-size: 12px;
    padding: 0 10px;
    word-break: break-all;
}

.hc-trash-message-attachments {
    width: 100%;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 10px;
    padding: 5px 10px;
}
</style>

<script>
import AttachmentRow from "./AttachmentRow.vue";

export default {
    components: {
        AttachmentRow,
    },

    props: {
        modelValue: {
            type: Array,
        },

        /**
         *
         */
        message: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        date() {
            return "Supprimé " + dayjs(this.message.deleted_at).fromNow();
        },

        /**
         *
         */
        value() {
            return this.message.id;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },
    },
};
</script>
