<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeMessageTemplate"
    >
        <div class="hc-flex-column hc-flex-1" style="overflow-y: auto">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    ref="name"
                    :placeholder="label + ' ...'"
                    v-model="messageTemplate.name"
                    required
            /></v-field>
            <v-field label="Objet de l'e-mail" v-slot="{ label }">
                <input v-model="messageTemplate.subject" :placeholder="label + ' ...'" maxlength="255" />
            </v-field>
            <text-editor
                style="padding: 5px"
                ref="body"
                required
                v-model.lazy="messageTemplate.body"
                :placeholder="$t('content')"
            />
            <template-attachments v-model:attachments="messageTemplate.attachments" :disabled="addingMessageTemplate" />
            <p v-if="saveError" role="alert" class="hc-message-template-error" v-text="saveError"></p>
        </div>
        <buttons>
            <button :disabled="addingMessageTemplate" v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingMessageTemplate" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import TemplateAttachments from "../Attachments.vue";

// Actions
import { ADD_MESSAGE_TEMPLATE } from "@/actions/project/message-template";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    components: { TemplateAttachments },
    data() {
        return {
            messageTemplate: this.newMessageTemplate(),
            addingMessageTemplate: false,
            saveError: "",

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
                subject: "",
                attachments: [],
            };
        },

        /**
         *
         */
        async storeMessageTemplate() {
            if (this.addingMessageTemplate) return;
            if (!this.messageTemplate.name) {
                this.$refs.name.focus();
                return;
            }

            if (!this.messageTemplate.body) {
                this.$refs.body.focus();
                return;
            }

            this.addingMessageTemplate = true;
            this.saveError = "";

            try {
                await store.dispatch(
                    ADD_MESSAGE_TEMPLATE,
                    this.messageTemplate
                );
                this.messageTemplate = this.newMessageTemplate();
                store.commit(CLOSE_MODAL);
            } catch (error) {
                this.saveError = Object.values(error.response?.data?.errors || {}).flat()[0]
                    || error.response?.data?.message || "Impossible d'enregistrer le modèle.";
            } finally {
                this.addingMessageTemplate = false;
            }
        },
    },

    computed: {
        ...mapGetters(["fields"]),
    },
};
</script>
