<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form class="hc-email-composer" @submit.prevent="send">
                <header class="hc-email-compose-header">
                    <div class="hc-email-compose-title">
                        <span v-text="$t(previewMode ? 'email_composer.title.preview' : 'email_composer.title.new')"></span>
                        <small v-if="draft.category" class="hc-email-compose-category" v-text="draft.category"></small>
                    </div>
                    <button type="button" class="hc-email-compose-close" :title="$t('email_composer.back')" :aria-label="$t('email_composer.back')" :disabled="sending" @click="$emit('back')">
                        <icon class="fa fa-times" />
                    </button>
                </header>
                <div v-if="previewMode" class="hc-email-preview-scroll">
                    <div class="hc-email-preview">
                        <div class="hc-email-preview-card">
                            <div class="hc-email-preview-brand">
                                <strong v-text="project.name"></strong>
                                <span>HEROES CRM</span>
                            </div>
                            <div class="hc-email-preview-content">
                                <div class="hc-email-preview-category" v-text="draft.category || $t('email_composer.preview.message')"></div>
                                <h1 v-text="draft.subject || $t('email_composer.preview.subject')"></h1>
                                <div class="hc-email-preview-body" v-text="draft.body || $t('email_composer.preview.body')"></div>
                                <div class="hc-email-preview-signature">
                                    <span v-text="$t('email_composer.preview.closing')"></span><br />
                                    <strong v-text="project.name"></strong>
                                </div>
                            </div>
                        </div>
                        <div class="hc-email-preview-footer">
                            <div v-text="$t('email_composer.preview.sent_by', { project: project.name })"></div>
                            <div v-text="$t('email_composer.preview.rights', { year: currentYear, project: project.name })"></div>
                        </div>
                        <p v-if="hasProspectVariables" class="hc-email-preview-note" v-text="$t('email_composer.preview.variables_note')"></p>
                    </div>
                    <p v-if="sendError" role="alert" class="hc-email-error" v-text="sendError"></p>
                </div>
                <div v-else class="hc-email-composer-fields">
                    <fieldset :disabled="sending">
                        <div class="hc-email-recipient-row">
                            <label class="hc-email-recipient-label" for="hc-email-recipient" v-text="$t('email_composer.recipient.label')"></label>
                            <input v-if="!draft.prospects" id="hc-email-recipient" v-model="draft.to" class="hc-email-recipient-input" type="email" required :placeholder="$t('email_composer.recipient.placeholder')" :aria-label="$t('email_composer.recipient.label')" />
                            <div v-else class="hc-email-recipient-list" role="group" :aria-label="$t('email_composer.recipient.label_plural')" v-text="draft.to"></div>
                        </div>
                        <p v-if="draft.prospects" class="hc-email-help hc-email-recipient-help" v-text="$t('email_composer.recipient.bulk_count', { count: draft.prospects.length })"></p>
                        <p v-if="draft.prospects && !draft.prospects.length" role="alert" class="hc-email-error" v-text="$t('email_composer.recipient.none_with_email')"></p>
                        <div class="hc-email-subject-row">
                            <input v-model="draft.subject" class="hc-email-subject" required maxlength="255" :placeholder="$t('email_composer.subject.placeholder')" :aria-label="$t('email_composer.subject.placeholder')" />
                        </div>
                        <textarea v-model="draft.body" class="hc-email-body" rows="10" required :placeholder="$t('email_composer.body.placeholder')" :aria-label="$t('email_composer.body.label')"></textarea>
                        <div v-if="templateAttachments.length || attachments.length" class="hc-email-attachments">
                            <div v-for="file in templateAttachments" :key="file.id" class="hc-email-attachment">
                                <icon class="fa fa-paperclip" />
                                <span v-text="file.name"></span>
                                <button type="button" :aria-label="$t('email_composer.attachment.remove', { name: file.name })" @click="removeTemplateAttachment(file)"><icon class="fa fa-times" /></button>
                            </div>
                            <div v-for="(file, index) in attachments" :key="'upload-' + index" class="hc-email-attachment">
                                <icon class="fa fa-paperclip" />
                                <span v-text="file.name"></span>
                                <button type="button" :aria-label="$t('email_composer.attachment.remove', { name: file.name })" @click="removeAttachment(file)"><icon class="fa fa-times" /></button>
                            </div>
                        </div>
                    </fieldset>
                    <p v-if="sendError" role="alert" class="hc-email-error" v-text="sendError"></p>
                    <p v-if="sent" role="status" class="hc-email-success" v-text="$t('email_composer.status.sent')"></p>
                </div>
                <footer class="hc-email-compose-footer">
                    <button class="hc-email-send" :disabled="sending || !canSend" v-text="sending ? $t('email_composer.status.sending') : $t('email_composer.status.send')"></button>
                    <button v-if="!previewMode" type="button" class="hc-email-toolbar-button" :title="$t('email_composer.title.preview')" :aria-label="$t('email_composer.title.preview')" :disabled="sending" @click="previewMode = true">
                        <icon class="fa fa-eye" />
                    </button>
                    <button v-else type="button" class="hc-email-toolbar-button" :title="$t('email_composer.toolbar.edit')" :aria-label="$t('email_composer.toolbar.edit')" :disabled="sending" @click="previewMode = false">
                        <icon class="fa fa-pencil-alt" />
                    </button>
                    <button v-if="!previewMode" type="button" class="hc-email-toolbar-button" :title="$t('email_composer.templates.title')" :aria-label="$t('email_composer.templates.title')" :disabled="sending" @click="showTemplates">
                        <icon class="fa fa-file-alt" />
                    </button>
                    <label v-if="!previewMode" class="hc-email-toolbar-button hc-email-attach-button" :title="$t('email_composer.attachment.add_help')" :aria-label="$t('email_composer.attachment.add')">
                        <icon class="fa fa-paperclip" />
                        <input type="file" multiple :aria-label="$t('email_composer.attachment.add')" :disabled="sending" @change="addAttachments" />
                    </label>
                    <span v-if="!previewMode" class="hc-email-help hc-email-attachment-help" v-text="$t('email_composer.attachment.limits')"></span>
                </footer>
                <loading :loading="sending" />
            </form>
        </template>
        <template #2>
            <div class="hc-flex-column" style="height: 100%; position: relative">
                <item tag="button" type="button" class="bordered" @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div class="hc-item-main-content" v-text="$t('email_composer.templates.title')"></div>
                </item>
                <search v-model="templateKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <item v-for="template in filteredTemplates" :key="template.id" tag="a" @click.prevent="setTemplate(template)">
                        <icon class="fa fa-file-alt" />
                        <div class="hc-item-main-content">
                            <div v-text="template.name"></div>
                            <small v-if="template.subject" v-text="template.subject"></small>
                            <small v-if="template.attachments && template.attachments.length"> · {{ $t('email_composer.templates.attachment_count', { count: template.attachments.length }) }}</small>
                        </div>
                        <icon v-if="can('all.project.message-template.update')" tag="button" type="button" class="fa fa-cog" :title="$t('email_composer.templates.edit')" :aria-label="$t('email_composer.templates.edit')" @click.stop.prevent="editTemplate(template)" />
                    </item>
                    <p v-if="!loadingTemplates && !filteredTemplates.length" class="hc-email-help" v-text="$t('email_composer.templates.empty')"></p>
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
            previewMode: false,
            currentYear: new Date().getFullYear(),
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
        hasProspectVariables() {
            return /\{prospect\.[\w.-]+\}/u.test(String(this.draft.body || ""));
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
                this.sendError = this.$t("email_composer.attachment.invalid");
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
                this.templateError = this.errorMessage(error, this.$t("email_composer.templates.load_error"));
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
            this.previewMode = false;
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
                this.previewMode = false;
            } catch (error) {
                this.sendError = this.errorMessage(error, this.$t("email_composer.send_error"));
            } finally {
                this.sending = false;
            }
        },
    },
};
</script>

<style scoped>
.hc-email-composer {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 0;
    position: relative;
    overflow: hidden;
    color: #202124;
    background: #fff;
    border: 1px solid #dadce0;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(60, 64, 67, 0.18);
    font-family: Arial, sans-serif;
}
.hc-email-compose-header {
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 0 10px 0 14px;
    background: #f2f6fc;
    color: #202124;
}
.hc-email-compose-title { display: flex; align-items: center; gap: 9px; min-width: 0; font-size: 14px; font-weight: 600; }
.hc-email-compose-category {
    max-width: 125px;
    overflow: hidden;
    padding: 3px 8px;
    border-radius: 12px;
    background: #e8f0fe;
    color: #1967d2;
    font-size: 10px;
    font-weight: 400;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.hc-email-compose-close, .hc-email-toolbar-button {
    display: inline-flex;
    flex: 0 0 32px;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #5f6368;
    cursor: pointer;
}
.hc-email-compose-close:hover, .hc-email-toolbar-button:hover { background: rgba(60, 64, 67, 0.08); color: #202124; }
.hc-email-compose-close:focus-visible, .hc-email-toolbar-button:focus-visible, .hc-email-send:focus-visible { outline: 2px solid #1a73e8; outline-offset: 2px; }
.hc-email-compose-close:disabled, .hc-email-toolbar-button:disabled { cursor: default; opacity: 0.5; }
.hc-email-composer-fields { flex: 1; min-height: 0; overflow-y: auto; padding: 0 16px 12px; }
.hc-email-composer fieldset { display: flex; flex-direction: column; border: 0; padding: 0; margin: 0; min-width: 0; }
.hc-email-recipient-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    min-height: 42px;
    padding: 10px 0 8px;
    border-bottom: 1px solid #e0e0e0;
}
.hc-email-recipient-row:focus-within { border-color: #1a73e8; }
.hc-email-recipient-label { flex: 0 0 18px; padding-top: 3px; color: #5f6368; font-size: 13px; }
.hc-email-recipient-input, .hc-email-recipient-list, .hc-email-subject {
    flex: 1;
    min-width: 0;
    padding: 2px 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: #202124;
    font: 13px/1.5 Arial, sans-serif;
}
.hc-email-recipient-list { overflow-wrap: anywhere; }
.hc-email-recipient-input::placeholder, .hc-email-subject::placeholder, .hc-email-body::placeholder { color: #80868b; opacity: 1; }
.hc-email-subject-row { display: flex; min-height: 42px; border-bottom: 1px solid #e0e0e0; }
.hc-email-subject-row:focus-within { border-color: #1a73e8; }
.hc-email-composer .hc-email-body {
    width: 100%;
    min-height: 180px;
    height: 220px;
    padding: 13px 0;
    border: 0;
    outline: 0;
    resize: vertical;
    overflow-y: auto;
    background: transparent;
    color: #202124;
    font: 13px/1.55 Arial, sans-serif;
}
.hc-email-recipient-help { margin: 5px 0 2px 30px; padding: 0; color: #5f6368; font-size: 11px; line-height: 1.4; }
.hc-email-help { color: #5f6368; font-size: 10px; white-space: normal; }
.hc-email-error, .hc-email-success { margin: 8px 0 0; padding: 8px 10px; border-radius: 4px; font-size: 12px; line-height: 1.4; }
.hc-email-error { background: #fce8e6; color: #b3261e; }
.hc-email-success { background: #e6f4ea; color: #137333; }
.hc-email-attachments { display: flex; flex-direction: column; gap: 6px; padding: 0 0 10px; }
.hc-email-attachment {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 36px;
    padding: 2px 4px 2px 9px;
    border: 1px solid #dadce0;
    border-radius: 4px;
    background: #f8f9fa;
    color: #5f6368;
    font-size: 12px;
}
.hc-email-attachment > span { flex: 1; min-width: 0; overflow: hidden; color: #3c4043; text-overflow: ellipsis; white-space: nowrap; }
.hc-email-attachment button { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; padding: 0; border: 0; border-radius: 50%; background: transparent; color: #5f6368; cursor: pointer; }
.hc-email-attachment button:hover { background: #e8eaed; color: #202124; }
.hc-email-compose-footer { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; padding: 9px 12px 12px; }
.hc-email-send { min-height: 36px; margin-right: 5px; padding: 0 20px; border: 0; border-radius: 18px; background: #0b57d0; color: #fff; font-size: 13px; font-weight: 500; cursor: pointer; }
.hc-email-send:hover:not(:disabled) { background: #0842a0; box-shadow: 0 1px 2px rgba(60, 64, 67, 0.3); }
.hc-email-send:disabled { background: #a8c7fa; cursor: default; }
.hc-email-attach-button { position: relative; overflow: hidden; cursor: pointer; }
.hc-email-attach-button:focus-within { outline: 2px solid #1a73e8; outline-offset: 2px; }
.hc-email-attach-button input { position: absolute; width: 1px; height: 1px; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); clip-path: inset(50%); white-space: nowrap; }
.hc-email-attachment-help { margin-left: 4px; }
.hc-email-preview-scroll { flex: 1; min-height: 0; overflow-y: auto; padding: 14px 9px 20px; background: #f3f6f8; }
.hc-email-preview { width: 100%; max-width: 600px; margin: 0 auto; color: #24323d; font-family: Arial, Helvetica, sans-serif; }
.hc-email-preview-card { overflow: hidden; border-radius: 8px; background: #fff; box-shadow: 0 8px 24px rgba(22, 59, 82, 0.1); }
.hc-email-preview-brand { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 50px; padding: 12px 17px; background: #163b52; color: #fff; }
.hc-email-preview-brand strong { min-width: 0; overflow-wrap: anywhere; font-size: 16px; }
.hc-email-preview-brand span { flex: 0 0 auto; color: #b9d6df; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; }
.hc-email-preview-content { padding: 22px 20px 18px; }
.hc-email-preview-category { margin-bottom: 10px; color: #4a8796; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
.hc-email-preview-content h1 { margin: 0 0 18px; color: #163b52; font-size: 21px; font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; }
.hc-email-preview-body { min-height: 28px; padding-top: 17px; border-top: 1px solid #e7edf0; color: #42515b; font-size: 13px; line-height: 1.65; white-space: pre-wrap; overflow-wrap: anywhere; }
.hc-email-preview-signature { margin-top: 22px; padding-top: 16px; border-top: 1px solid #e7edf0; color: #6d7b83; font-size: 12px; line-height: 1.6; }
.hc-email-preview-signature strong { color: #163b52; }
.hc-email-preview-footer { padding: 16px 8px 0; color: #84929a; font-size: 10px; line-height: 1.6; text-align: center; }
.hc-email-preview-note { margin: 12px 0 0; padding: 9px 10px; border-radius: 4px; background: #e8f0fe; color: #3c4043; font-size: 11px; line-height: 1.4; }
@media (max-width: 360px) {
    .hc-email-composer-fields { padding-right: 12px; padding-left: 12px; }
    .hc-email-compose-footer { padding-right: 8px; padding-left: 8px; }
    .hc-email-attachment-help { flex-basis: 100%; margin-left: 4px; }
    .hc-email-preview-content { padding-right: 16px; padding-left: 16px; }
}
</style>
