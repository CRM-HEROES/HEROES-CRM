<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeMessageTemplate"
    >
        <div class="hc-flex-column">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    ref="name"
                    :placeholder="label + ' ...'"
                    v-model="messageTemplate.name"
                    required
            /></v-field>
            <text-editor
                style="padding: 5px"
                ref="body"
                required
                v-model.lazy="messageTemplate.body"
                :placeholder="$t('content')"
            />
        </div>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingMessageTemplate" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_MESSAGE_TEMPLATE } from "@/actions/project/message-template";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            messageTemplate: this.newMessageTemplate(),
            addingMessageTemplate: false,

            strategies: [
                {
                    match: /(\w*)$/,
                    index: 1,
                    search: (query, callback) => {
                        query = removeStringAccent(query);

                        callback(
                            this.fields.filter(
                                (f) =>
                                    f.for == "prospect" &&
                                    removeStringAccent(f.name).indexOf(query) >=
                                        0
                            )
                        );
                    },
                    replace(field) {
                        return (
                            (field.meta
                                ? `{prospect.meta.${field.slug}}`
                                : `{prospect.${field.slug}}`) + " "
                        );
                    },
                    template(field) {
                        return field.name;
                    },
                },
            ],
        };
    },

    methods: {
        /**
         *
         */
        newMessageTemplate() {
            return {
                name: "",
                body: "",
            };
        },

        /**
         *
         */
        async storeMessageTemplate() {
            if (!this.messageTemplate.name) {
                this.$refs.name.focus();
                return;
            }

            if (!this.messageTemplate.body) {
                this.$refs.body.focus();
                return;
            }

            this.addingMessageTemplate = true;

            try {
                await store.dispatch(
                    ADD_MESSAGE_TEMPLATE,
                    this.messageTemplate
                );
            } finally {
                this.addingMessageTemplate = false;
                this.messageTemplate = this.newMessageTemplate();
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["fields"]),
    },
};
</script>
