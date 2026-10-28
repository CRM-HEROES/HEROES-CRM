<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="messageTemplateToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="messageTemplateToUpdate.name"
                    required
            /></v-field>
            <v-field label="Objet de l'e-mail" v-slot="{ label }">
                <input v-model="messageTemplateToUpdate.subject" :placeholder="label + ' ...'" maxlength="255" />
            </v-field>
            <text-editor
                style="padding: 5px"
                ref="body"
                required
                v-model.lazy="messageTemplateToUpdate.body"
                :placeholder="$t('content')"
            />
            <template-attachments
                v-model:attachments="messageTemplateToUpdate.attachments"
                :disabled="updatingMessageTemplate || removingMessageTemplate"
            />
            <p v-if="saveError" role="alert" class="hc-message-template-error" v-text="saveError"></p>
        </item-list>
        <buttons>
            <button
                v-if="can('all.project.message-template.delete')"
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button :disabled="updatingMessageTemplate || removingMessageTemplate" v-text="$t('update')"></button>
        </buttons>
        <loading
            :loading="updatingMessageTemplate || removingMessageTemplate"
        />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import TemplateAttachments from "../Attachments.vue";

// Actions
import {
    UPDATE_MESSAGE_TEMPLATE,
    REMOVE_MESSAGE_TEMPLATE,
} from "@/actions/project/message-template";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    components: { TemplateAttachments },
    data() {
        return {
            updatingMessageTemplate: false,
            removingMessageTemplate: false,
            fetchingMessageTemplate: false,
            messageTemplateToUpdate: this.copyTemplate(this.messageTemplate),
            saveError: "",
        };
    },

    methods: {
        copyTemplate(template) {
            return template ? { ...template, attachments: [...(template.attachments || [])] } : null;
        },
        /**
         *
         */
        async update() {
            if (this.updatingMessageTemplate || this.removingMessageTemplate) return;
            this.updatingMessageTemplate = true;
            this.saveError = "";

            try {
                await store.dispatch(
                    UPDATE_MESSAGE_TEMPLATE,
                    this.messageTemplateToUpdate
                );
                store.dispatch(FETCH_PROSPECTS);
                store.commit(CLOSE_MODAL);
            } catch (error) {
                this.saveError = Object.values(error.response?.data?.errors || {}).flat()[0]
                    || error.response?.data?.message || "Impossible d'enregistrer le modèle.";
            } finally {
                this.updatingMessageTemplate = false;
            }
        },

        /**
         *
         */
        remove() {
            if (this.updatingMessageTemplate || this.removingMessageTemplate) return;
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingMessageTemplate = true;
                this.saveError = "";

                try {
                    await store.dispatch(
                        REMOVE_MESSAGE_TEMPLATE,
                        this.messageTemplateToUpdate.id
                    );
                    store.dispatch(FETCH_PROSPECTS);
                    store.commit(CLOSE_MODAL);
                } catch (error) {
                    this.saveError = error.response?.data?.message || "Impossible de supprimer le modèle.";
                } finally {
                    this.removingMessageTemplate = false;
                }
            });
        },
    },

    watch: {
        async messageTemplate(newValue) {
            this.messageTemplateToUpdate = this.copyTemplate(newValue);
            this.saveError = "";
        },
    },

    computed: {
        ...mapGetters(["messageTemplate", "fields", "can"]),
    },
};
</script>
