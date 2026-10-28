<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form class="hc-email-composer" @submit.prevent="send">
                <item tag="button" type="button" class="bordered" :disabled="sending" @click="$emit('back')">
                    <icon class="fa fa-caret-left" />
                    <div class="hc-item-main-content" v-text="draft.category"></div>
                </item>
                <div class="hc-email-composer-fields">
                    <fieldset :disabled="sending">
                        <v-field label="Destinataire" required>
                            <input v-if="!draft.prospects" v-model="draft.to" type="email" required />
                            <textarea v-else :value="draft.to" rows="3" readonly aria-label="Destinataires"></textarea>
                        </v-field>
                        <p v-if="draft.prospects" class="hc-email-help">
                            {{ draft.prospects.length }} destinataire(s) — un e-mail sera envoyé à chaque prospect.
                        </p>
                        <p v-if="draft.prospects && !draft.prospects.length" role="alert" class="hc-email-error">
                            Aucun prospect sélectionné ne possède d'adresse e-mail.
                        </p>
                        <v-field label="Objet" required>
                            <input v-model="draft.subject" required maxlength="255" />
                        </v-field>
                        <v-field label="Message" required>
                            <textarea v-model="draft.body" class="hc-email-body" rows="10" required placeholder="Saisir un e-mail ..."></textarea>
                        </v-field>
                        <div v-if="templateAttachments.length || attachments.length" class="hc-email-attachments">
                            <div v-for="file in templateAttachments" :key="file.id" class="hc-email-attachment">
                                <icon class="fa fa-paperclip" />
                                <span v-text="file.name"></span>
                                <button type="button" :aria-label="'Retirer ' + file.name" @click="removeTemplateAttachment(file)">×</button>
                            </div>
                            <div v-for="(file, index) in attachments" :key="'upload-' + index" class="hc-email-attachment">
                                <icon class="fa fa-paperclip" />
                                <span v-text="file.name"></span>
                                <button type="button" :aria-label="'Retirer ' + file.name" @click="removeAttachment(file)">×</button>
                            </div>
                        </div>
                        <div class="hc-flex-row">
                            <icon tag="button" type="button" class="fa fa-file-alt" title="Modèles d'e-mail" aria-label="Modèles d'e-mail" @click="showTemplates" />
                            <icon tag="label" class="fa fa-paperclip" title="Ajouter des pièces jointes">
                                <input type="file" multiple aria-label="Ajouter des pièces jointes" @change="addAttachments" />
                            </icon>
                            <span class="hc-email-help">10 fichiers maximum, 10 Mo par fichier.</span>
                        </div>
                    </fieldset>
                    <p v-if="sendError" role="alert" class="hc-email-error" v-text="sendError"></p>
                    <p v-if="sent" role="status" class="hc-email-success">E-mail envoyé avec succès.</p>
                </div>
                <buttons>
                    <button :disabled="sending || !canSend" v-text="sending ? 'Envoi en cours...' : $t('send')"></button>
                </buttons>
                <loading :loading="sending" />
            </form>
        </template>
        <template #2>
            <div class="hc-flex-column" style="height: 100%; position: relative">
                <item tag="button" type="button" class="bordered" @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div class="hc-item-main-content">Modèles d'e-mail</div>
                </item>
                <search v-model="templateKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <item v-for="template in filteredTemplates" :key="template.id" tag="a" @click.prevent="setTemplate(template)">
                        <icon class="fa fa-file-alt" />
                        <div class="hc-item-main-content">
                            <div v-text="template.name"></div>
                            <small v-if="template.subject" v-text="template.subject"></small>
                            <small v-if="template.attachments && template.attachments.length"> · {{ template.attachments.length }} pièce(s) jointe(s)</small>
                        </div>
                        <icon v-if="can('all.project.message-template.update')" tag="button" type="button" class="fa fa-cog" title="Modifier le modèle" aria-label="Modifier le modèle" @click.stop.prevent="editTemplate(template)" />
                    </item>
                    <p v-if="!loadingTemplates && !filteredTemplates.length" class="hc-email-help">Aucun modèle disponible.</p>
                    <p v-if="templateError" role="alert" class="hc-email-error" v-text="templateError"></p>
                </item-list>
                <buttons>
                    <a v-if="can('all.project.message-template.add')" @click.prevent="addTemplate" v-text="$t('add')"></a>
                </buttons>
                <loading :loading="loadingTemplates" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectEmailService from "@/apis/project/prospect/email";
import { FETCH_MESSAGE_TEMPLATES, SET_MESSAGE_TEMPLATE } from "@/actions/project/message-template";
import { OPEN_MODAL } from "@/actions/modal";

export default {
    props: {
        initialDraft: { type: Object, required: true },
    },
    emits: ["back"],
    data() {
        return {
            draft: { ...this.initialDraft },
            tab: 0,
            templateKeyword: "",
            templateId: null,
            templateAttachments: [],
            attachments: [],
            sending: false,
            loadingTemplates: false,
            sendError: "",
            templateError: "",
            sent: false,
        };
    },
    computed: {
        ...mapGetters(["project", "messageTemplates", "can"]),
        canSend() {
            return !!this.draft.to.trim() && !!this.draft.subject.trim() && !!this.draft.body.trim()
                && (!this.draft.prospects || this.draft.prospects.length > 0);
        },
        filteredTemplates() {
            const normalize = value => String(value || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            const keyword = normalize(this.templateKeyword);
            return this.messageTemplates.filter(template => normalize(template.name).includes(keyword));
        },
    },
    methods: {
        errorMessage(error, fallback) {
            const errors = error.response?.data?.errors;
            return (errors && Object.values(errors).flat()[0]) || error.response?.data?.message || fallback;
        },
        validAttachments(files) {
            if (files.length > 10 || files.some(file => file.size > 10 * 1024 * 1024)) {
                this.sendError = "Vous pouvez joindre 10 fichiers maximum, de 10 Mo chacun.";
                return false;
            }
            return true;
        },
        addAttachments(event) {
            const files = [...this.attachments, ...event.target.files];
            event.target.value = "";
            if (this.sending || !this.validAttachments([...this.templateAttachments, ...files])) return;
            this.attachments = files;
            this.sendError = "";
            this.sent = false;
        },
        removeAttachment(file) {
            if (this.sending) return;
            this.attachments = this.attachments.filter(item => item !== file);
            this.sendError = "";
        },
        removeTemplateAttachment(file) {
            if (this.sending) return;
            this.templateAttachments = this.templateAttachments.filter(item => item.id !== file.id);
            this.sendError = "";
        },
        async showTemplates() {
            this.tab = 1;
            this.loadingTemplates = true;
            this.templateError = "";
            try {
                await store.dispatch(FETCH_MESSAGE_TEMPLATES);
            } catch (error) {
                this.templateError = this.errorMessage(error, "Impossible de charger les modèles.");
            } finally {
                this.loadingTemplates = false;
            }
        },
        plainText(body) {
            // Shared message templates use the rich text editor; direct e-mails use plain text.
            const element = document.createElement("div");
            element.innerHTML = String(body || "").replace(/<br\s*\/?\s*>/gi, "\n").replace(/<\/(p|div|li|h[1-6])>/gi, "\n");
            element.querySelectorAll("a[href]").forEach(link => {
                const href = link.getAttribute("href");
                if (href && link.textContent.trim() !== href) {
                    link.textContent += ` (${href})`;
                }
            });
            return element.textContent.trim();
        },
        setTemplate(template) {
            const files = [...(template.attachments || [])];
            if (!this.validAttachments([...files, ...this.attachments])) {
                this.tab = 0;
                return;
            }
            this.templateId = template.id;
            this.templateAttachments = files;
            if (template.subject) this.draft.subject = template.subject;
            this.draft.body = this.plainText(template.body);
            this.sendError = "";
            this.sent = false;
            this.tab = 0;
        },
        addTemplate() {
            store.commit(OPEN_MODAL, "message-template-add");
        },
        editTemplate(template) {
            store.commit(SET_MESSAGE_TEMPLATE, template);
            store.commit(OPEN_MODAL, "message-template-update");
        },
        async send() {
            if (this.sending || !this.canSend) return;
            this.sending = true;
            this.sendError = "";
            this.sent = false;
            const payload = new FormData();
            for (const field of ["to", "subject", "body", "category"]) {
                payload.append(field, this.draft[field] || "");
            }
            (this.draft.prospects || []).forEach(id => payload.append("prospects[]", id));
            if (this.templateId) payload.append("template_id", this.templateId);
            this.templateAttachments.forEach(file => payload.append("template_attachment_ids[]", file.id));
            this.attachments.forEach(file => payload.append("attachments[]", file, file.name));
            try {
                if (this.draft.prospects) {
                    await ProspectEmailService.sendBulk(this.project.slug, payload);
                } else {
                    await ProspectEmailService.send(this.project.slug, this.draft.prospect, payload);
                }
                this.draft.body = "";
                this.attachments = [];
                this.templateAttachments = [];
                this.templateId = null;
                this.sent = true;
            } catch (error) {
                this.sendError = this.errorMessage(error, "L'e-mail n'a pas pu être envoyé.");
            } finally {
                this.sending = false;
            }
        },
    },
};
</script>

<style scoped>
.hc-email-composer { display: flex; flex-direction: column; height: 100%; position: relative; }
.hc-email-composer-fields { flex: 1; min-height: 0; overflow-y: auto; padding: 10px; }
.hc-email-composer fieldset { border: 0; padding: 0; margin: 0; min-width: 0; }
.hc-email-composer textarea { width: 100%; resize: vertical; }
.hc-email-composer .hc-email-body { height: 220px; min-height: 150px; overflow-y: auto; }
.hc-email-help { color: #777; font-size: 11px; padding: 5px; white-space: normal; }
.hc-email-error { color: #b42318; padding: 5px; }
.hc-email-success { color: #26834a; padding: 5px; }
.hc-email-attachments { display: flex; flex-direction: column; gap: 4px; padding: 5px 0; }
.hc-email-attachment { display: flex; align-items: center; background: #f5f5f5; border-radius: 5px; }
.hc-email-attachment span { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hc-email-attachment button { background: none; border: 0; padding: 8px; cursor: pointer; }
</style>
