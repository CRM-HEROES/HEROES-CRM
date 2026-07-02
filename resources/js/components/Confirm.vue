<template>
    <modal
        name="confirm"
        :title="title"
        @open="focusOkButton"
        @closed="cancelCallback ? cancelCallback() : null"
    >
        <div class="hc-flex-column" style="height: 100%">
            <div
                class="hc-flex-1"
                style="padding: 15px; font-size: 13px"
                v-html="message"
            ></div>
            <buttons>
                <button
                    v-text="$t('cancel')"
                    style="background-color: #eee; color: #000 !important"
                    @click.prevent="cancel"
                ></button>
                <button
                    v-text="$t('ok')"
                    ref="buttonOk"
                    @click.prevent="ok"
                ></button>
            </buttons>
        </div>
    </modal>
</template>

<script>
import EventBus from "@/utils/event-bus";
import { mapGetters } from "vuex";
import store from "@/store";

import { CLOSE_MODAL, OPEN_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            message: "",
            title: this.project ? this.project.name : this.$t("app_name"),
            okButton: this.$t("ok"),
            cancelButton: this.$t("cancel"),
            okCallback: null,
            cancelCallback: null,
        };
    },

    created() {
        EventBus.on(
            "confirm",
            (
                message,
                okCallback,
                cancelCallback,
                title,
                okButton,
                cancelButton
            ) => {
                this.confirm(
                    message,
                    okCallback,
                    cancelCallback,
                    title,
                    okButton,
                    cancelButton
                );
            }
        );
    },

    methods: {
        confirm(
            message,
            okCallback,
            cancelCallback,
            title,
            okButton,
            cancelButton
        ) {
            this.reset();

            this.message = message;

            if (okCallback) {
                this.okCallback = okCallback;
            }

            if (cancelCallback) {
                this.cancelCallback = cancelCallback;
            }

            if (title) {
                this.title = title;
            }

            if (okButton) {
                this.okButton = okButton;
            }

            if (cancelButton) {
                this.cancelButton = cancelButton;
            }

            store.commit(OPEN_MODAL, "confirm");
        },

        reset() {
            this.title = this.project ? this.project.name : this.$t("app_name");
            this.okButton = this.$t("ok");
            this.cancelButton = this.$t("cancel");
            this.okCallback = null;
        },

        ok() {
            if (this.okCallback) {
                this.okCallback();
            }

            store.commit(CLOSE_MODAL);
        },

        cancel() {
            store.commit(CLOSE_MODAL);
        },

        focusOkButton() {
            setTimeout(() => {
                this.buttonOk.focus();
            }, 150);
        },
    },

    computed: {
        ...mapGetters(["project"]),

        buttonOk: function () {
            return this.$refs.buttonOk;
        },
    },
};
</script>
