<template>
    <div class="hc-wfe-editor" :class="{ disabled: readOnly }">
        <div class="hc-wfe-editor-tabs">
            <button type="button" :class="{ active: mode === 'edit' }" @click="setMode('edit')" v-text="$t('workflow.email.tab_edit')"></button>
            <button type="button" :class="{ active: mode === 'design' }" @click="setMode('design')" v-text="$t('workflow.email.tab_design')"></button>
            <button type="button" :class="{ active: mode === 'preview' }" @click="setMode('preview')" v-text="$t('workflow.email.tab_preview')"></button>
        </div>

        <!-- Toolbar: the buttons keep the selection of the text (mousedown is not taken by the button) -->
        <div v-if="mode === 'edit' && !readOnly" class="hc-wfe-editor-toolbar">
            <select :value="''" :title="$t('workflow.email.format')" @change="applyBlock($event)" @mousedown="remember">
                <option value="" v-text="$t('workflow.email.format')"></option>
                <option value="p" v-text="$t('workflow.email.paragraph')"></option>
                <option value="h1" v-text="$t('workflow.email.title1')"></option>
                <option value="h2" v-text="$t('workflow.email.title2')"></option>
                <option value="h3" v-text="$t('workflow.email.title3')"></option>
            </select>
            <span class="hc-wfe-editor-sep"></span>
            <button type="button" :title="$t('workflow.email.bold')" @mousedown.prevent="run('bold')"><b>B</b></button>
            <button type="button" :title="$t('workflow.email.italic')" @mousedown.prevent="run('italic')"><i>I</i></button>
            <button type="button" :title="$t('workflow.email.underline')" @mousedown.prevent="run('underline')"><u>U</u></button>
            <button type="button" :title="$t('workflow.email.strike')" @mousedown.prevent="run('strikeThrough')"><s>S</s></button>
            <span class="hc-wfe-editor-sep"></span>
            <label class="hc-wfe-editor-color" :title="$t('workflow.email.color')" @mousedown="remember">
                <span class="hc-wfe-editor-color-a" :style="{ borderBottomColor: textColor }">A</span>
                <input type="color" v-model="textColor" @input="color('foreColor', textColor)" />
            </label>
            <label class="hc-wfe-editor-color" :title="$t('workflow.email.highlight')" @mousedown="remember">
                <span class="hc-wfe-editor-color-a" :style="{ background: highlightColor, color: '#222' }">A</span>
                <input type="color" v-model="highlightColor" @input="color('hiliteColor', highlightColor)" />
            </label>
            <span class="hc-wfe-editor-sep"></span>
            <button type="button" :title="$t('workflow.email.align_left')" @mousedown.prevent="run('justifyLeft')"><i class="fa fa-align-left"></i></button>
            <button type="button" :title="$t('workflow.email.align_center')" @mousedown.prevent="run('justifyCenter')"><i class="fa fa-align-center"></i></button>
            <button type="button" :title="$t('workflow.email.align_right')" @mousedown.prevent="run('justifyRight')"><i class="fa fa-align-right"></i></button>
            <span class="hc-wfe-editor-sep"></span>
            <button type="button" :title="$t('workflow.email.bullets')" @mousedown.prevent="run('insertUnorderedList')"><i class="fa fa-list-ul"></i></button>
            <button type="button" :title="$t('workflow.email.numbers')" @mousedown.prevent="run('insertOrderedList')"><i class="fa fa-list-ol"></i></button>
            <span class="hc-wfe-editor-sep"></span>
            <button type="button" :title="$t('workflow.email.link')" @mousedown.prevent="addLink"><i class="fa fa-link"></i></button>
            <button type="button" :title="$t('workflow.email.unlink')" @mousedown.prevent="run('unlink')"><i class="fa fa-chain-broken"></i></button>
            <button type="button" :title="$t('workflow.email.image')" @mousedown.prevent="addImage"><i class="fa fa-picture-o"></i></button>
            <span class="hc-wfe-editor-sep"></span>
            <button type="button" :title="$t('workflow.email.undo')" @mousedown.prevent="run('undo')"><i class="fa fa-undo"></i></button>
            <button type="button" :title="$t('workflow.email.redo')" @mousedown.prevent="run('redo')"><i class="fa fa-repeat"></i></button>
            <button type="button" :title="$t('workflow.email.clear')" @mousedown.prevent="run('removeFormat')"><i class="fa fa-eraser"></i></button>
        </div>

        <!-- Writing -->
        <div
            v-show="mode === 'edit'"
            ref="area"
            class="hc-wfe-editor-area"
            :contenteditable="!readOnly"
            :data-placeholder="$t('workflow.email.placeholder')"
            @input="onInput"
            @blur="remember"
            @keyup="remember"
            @mouseup="remember"
            @paste="onPaste"
        ></div>

        <!-- The look of the email, chosen graphically -->
        <div v-if="mode === 'design'" class="hc-wfd">
            <div v-if="!design" class="hc-wfd-standard">
                <strong v-text="$t('workflow.email.design_standard')"></strong>
                <p v-text="$t('workflow.email.design_standard_help')"></p>
                <button v-if="!readOnly" type="button" class="hc-wfd-primary" @click="customize" v-text="$t('workflow.email.design_customize')"></button>
            </div>

            <template v-else>
                <div class="hc-wfd-group" v-text="$t('workflow.email.design_style')"></div>
                <div class="hc-wfd-styles">
                    <button
                        v-for="style in styles"
                        :key="style"
                        type="button"
                        class="hc-wfd-style"
                        :class="[style, { on: design.style === style }]"
                        :disabled="readOnly"
                        @click="change({ style })"
                    >
                        <span class="hc-wfd-thumb">
                            <span class="hc-wfd-thumb-band" :style="{ background: design.header_color }"></span>
                            <span class="hc-wfd-thumb-line"></span>
                            <span class="hc-wfd-thumb-line short"></span>
                        </span>
                        <strong v-text="$t('workflow.email.style.' + style)"></strong>
                        <small v-text="$t('workflow.email.style_help.' + style)"></small>
                    </button>
                </div>

                <div class="hc-wfd-group" v-text="$t('workflow.email.design_colors')"></div>
                <div class="hc-wfd-palettes">
                    <button
                        v-for="palette in palettes"
                        :key="palette.key"
                        type="button"
                        class="hc-wfd-palette"
                        :class="{ on: design.header_color === palette.header_color && design.accent_color === palette.accent_color }"
                        :title="$t('workflow.email.palette.' + palette.key)"
                        :disabled="readOnly"
                        @click="change({ header_color: palette.header_color, accent_color: palette.accent_color })"
                    >
                        <span :style="{ background: palette.header_color }"></span>
                        <span :style="{ background: palette.accent_color }"></span>
                    </button>
                </div>
                <div class="hc-wfd-colors">
                    <label v-for="key in colorKeys" :key="key" class="hc-wfd-color">
                        <input type="color" :value="design[key]" :disabled="readOnly" @input="change({ [key]: $event.target.value })" />
                        <span v-text="$t('workflow.email.color.' + key)"></span>
                        <code v-text="design[key]"></code>
                    </label>
                </div>

                <div class="hc-wfd-group" v-text="$t('workflow.email.design_text')"></div>
                <div class="hc-wfd-row">
                    <label>
                        <span v-text="$t('workflow.email.font')"></span>
                        <select :value="design.font" :disabled="readOnly" @change="change({ font: $event.target.value })">
                            <option v-for="font in fonts" :key="font" :value="font" :style="{ fontFamily: fontCss[font] }" v-text="$t('workflow.email.fonts.' + font)"></option>
                        </select>
                    </label>
                </div>

                <div class="hc-wfd-group" v-text="$t('workflow.email.design_parts')"></div>
                <label class="hc-wfd-toggle">
                    <input type="checkbox" :checked="design.logo === 'project'" :disabled="readOnly" @change="change({ logo: $event.target.checked ? 'project' : 'none' })" />
                    <span v-text="$t('workflow.email.logo')"></span>
                </label>
                <label v-if="design.style === 'branded'" class="hc-wfd-toggle">
                    <input type="checkbox" :checked="design.show_title" :disabled="readOnly" @change="change({ show_title: $event.target.checked })" />
                    <span v-text="$t('workflow.email.show_title')"></span>
                </label>
                <label class="hc-wfd-toggle">
                    <input type="checkbox" :checked="design.show_signature" :disabled="readOnly" @change="change({ show_signature: $event.target.checked })" />
                    <span v-text="$t('workflow.email.show_signature')"></span>
                </label>
                <label class="hc-wfd-toggle">
                    <input type="checkbox" :checked="design.show_footer" :disabled="readOnly" @change="change({ show_footer: $event.target.checked })" />
                    <span v-text="$t('workflow.email.show_footer')"></span>
                </label>

                <div class="hc-wfd-group" v-text="$t('workflow.email.design_wording')"></div>
                <div class="hc-wfd-texts">
                    <label v-for="key in textKeys" :key="key" v-show="showsText(key)">
                        <span v-text="$t('workflow.email.text.' + key)"></span>
                        <input type="text" :value="design[key]" maxlength="255" :disabled="readOnly" @input="change({ [key]: $event.target.value })" />
                    </label>
                </div>

                <button v-if="!readOnly" type="button" class="hc-wfd-reset" @click="$emit('update:design', null)" v-text="$t('workflow.email.design_reset')"></button>
            </template>
        </div>

        <!-- As the prospect will see it: the same drawing as the email sent -->
        <div v-if="mode === 'preview' || mode === 'design'" class="hc-wfe-editor-preview">
            <div v-if="previewError" class="hc-wff-warn" v-text="previewError"></div>
            <p v-if="previewLoading" class="hc-wff-count" v-text="$t('workflow.loading')"></p>
            <!-- A frame without any right (sandbox): what the email contains cannot run here -->
            <iframe class="hc-wfe-mail-frame" :class="{ small: mode === 'design' }" sandbox="" :srcdoc="previewHtml || previewDocument" :title="$t('workflow.email.tab_preview')"></iframe>
            <p class="hc-wfe-editor-note" v-text="$t('workflow.email.preview_note')"></p>
        </div>

        <!-- Variables: inserted where the cursor is -->
        <div v-if="mode === 'edit' && !readOnly" class="hc-wff-variables">
            <span v-text="$t('workflow.variables')"></span>
            <button v-for="variable in variableList" :key="variable" type="button" @mousedown.prevent="insert(variable)" v-text="variable"></button>
        </div>
    </div>
</template>

<style>
.hc-wfe-editor {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
}

.hc-wfe-editor-tabs {
    display: flex;
    gap: 4px;
}

.hc-wfe-editor .hc-wfe-editor-tabs button {
    padding: 4px 12px;
    color: #5d6272;
    background: #f0f1f6;
    border: 0;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.hc-wfe-editor .hc-wfe-editor-tabs button.active {
    color: #fff;
    background: #4b5ce8;
}

.hc-wfe-editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 2px;
    padding: 4px;
    background: #f6f7fb;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
}

.hc-wfe-editor-toolbar button,
.hc-wfe-editor-color {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    width: 28px;
    min-width: 28px;
    height: 28px;
    margin: 0;
    padding: 0;
    color: #2b2f3a;
    background: transparent;
    border: 0;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
}

.hc-wfe-editor-toolbar button:hover,
.hc-wfe-editor-color:hover {
    background: #e3e6f3;
}

.hc-wfe-editor-toolbar select {
    width: auto;
    max-width: 120px;
    padding: 4px 6px;
    font-size: 12px;
}

.hc-wfe-editor-sep {
    width: 1px;
    height: 18px;
    margin: 0 3px;
    background: #d5d9e4;
}

.hc-wfe-editor-color {
    position: relative;
}

.hc-wfe-editor-color input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    opacity: 0;
    cursor: pointer;
}

.hc-wfe-editor-color-a {
    padding: 0 4px;
    border-bottom: 3px solid #222;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.1;
}

.hc-wfe-editor-area {
    min-height: 180px;
    max-height: 420px;
    padding: 12px;
    overflow: auto;
    color: #222;
    background: #fff;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    line-height: 1.5;
}

.hc-wfe-editor-area:focus {
    border-color: #5b6cf0;
    box-shadow: 0 0 0 3px rgba(91, 108, 240, 0.15);
    outline: 0;
}

.hc-wfe-editor-area:empty::before {
    color: #9aa0b0;
    content: attr(data-placeholder);
}

.hc-wfe-editor-area h1 {
    margin: 0 0 8px;
    font-size: 22px;
}

.hc-wfe-editor-area h2 {
    margin: 0 0 8px;
    font-size: 18px;
}

.hc-wfe-editor-area h3 {
    margin: 0 0 8px;
    font-size: 15px;
}

.hc-wfe-editor-area p {
    margin: 0 0 8px;
}

.hc-wfe-editor-area img {
    max-width: 100%;
}

.hc-wfe-editor-preview {
    padding: 12px;
    background: #eef0f6;
    border-radius: 8px;
}

.hc-wfd {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    background: #f6f7fb;
    border: 1px solid #e3e6ef;
    border-radius: 10px;
}

.hc-wfd-group {
    margin-top: 6px;
    color: #3a49c9;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.hc-wfd-standard p {
    margin: 4px 0 10px;
    color: #5d6272;
    font-size: 12px;
}

.hc-wfd .hc-wfd-primary,
.hc-wfd .hc-wfd-reset {
    align-self: flex-start;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.hc-wfd .hc-wfd-primary {
    color: #fff;
    background: #4b5ce8;
    border: 0;
}

.hc-wfd .hc-wfd-reset {
    margin-top: 8px;
    color: #5d6272;
    background: #fff;
    border: 1px solid #d5d9e4;
}

.hc-wfd-styles {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.hc-wfd .hc-wfd-style {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 8px;
    text-align: left;
    background: #fff;
    border: 2px solid #e3e6ef;
    border-radius: 10px;
    cursor: pointer;
}

.hc-wfd .hc-wfd-style.on {
    border-color: #4b5ce8;
    box-shadow: 0 0 0 3px rgba(75, 92, 232, 0.15);
}

.hc-wfd-style strong {
    font-size: 13px;
}

.hc-wfd-style small {
    color: #7a7f90;
    font-size: 11px;
    line-height: 1.3;
}

.hc-wfd-thumb {
    display: flex;
    flex-direction: column;
    gap: 4px;
    height: 64px;
    padding: 0 0 6px;
    overflow: hidden;
    background: #f3f6f8;
    border-radius: 6px;
}

.hc-wfd-thumb-band {
    height: 16px;
}

.hc-wfd-style.simple .hc-wfd-thumb {
    background: #fff;
    border: 1px solid #e3e6ef;
}

.hc-wfd-style.simple .hc-wfd-thumb-band {
    display: none;
}

.hc-wfd-thumb-line {
    height: 5px;
    margin: 0 8px;
    background: #cfd3de;
    border-radius: 3px;
}

.hc-wfd-thumb-line.short {
    width: 55%;
}

.hc-wfd-palettes {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.hc-wfd .hc-wfd-palette {
    display: flex;
    overflow: hidden;
    width: 44px;
    height: 26px;
    padding: 0;
    background: transparent;
    border: 2px solid #fff;
    border-radius: 13px;
    box-shadow: 0 0 0 1px #d5d9e4;
    cursor: pointer;
}

.hc-wfd .hc-wfd-palette.on {
    box-shadow: 0 0 0 2px #4b5ce8;
}

.hc-wfd-palette span {
    flex: 1;
}

.hc-wfd-colors {
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
}

.hc-wfd-color {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 12px;
}

.hc-wfd-color input[type="color"] {
    flex: none;
    width: 30px;
    height: 26px;
    padding: 0;
    background: transparent;
    border: 1px solid #d5d9e4;
    border-radius: 6px;
    cursor: pointer;
}

.hc-wfd-color span {
    flex: 1;
}

.hc-wfd-color code {
    color: #7a7f90;
    font-size: 11px;
}

.hc-wfd-row label,
.hc-wfd-texts label {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin: 0;
    color: #5d6272;
    font-size: 11px;
}

.hc-wfd-texts {
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
}

.hc-wfd-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 13px;
    cursor: pointer;
}

.hc-wfe-mail-frame.small {
    height: 340px;
}

.hc-wfe-mail-frame {
    display: block;
    width: 100%;
    height: 420px;
    background: #eef0f6;
    border: 0;
    border-radius: 8px;
}

.hc-wfe-editor-note {
    margin: 8px 0 0;
    color: #7a7f90;
    font-size: 11px;
    text-align: center;
}

.hc-wfe-editor.disabled .hc-wfe-editor-area {
    background: #f6f7fb;
}
</style>

<script>
import { mapGetters } from "vuex";
import WorkflowService from "@/apis/project/workflow";
import { COLOR_KEYS, FONTS, PALETTES, STYLES, TEXT_KEYS, defaultDesign, escapeHtml, hasContent, safeUrl, withSamples } from "@/utils/workflow-email";

const FONT_CSS = {
    arial: "Arial, Helvetica, sans-serif",
    verdana: "Verdana, Geneva, sans-serif",
    tahoma: "Tahoma, Geneva, sans-serif",
    trebuchet: "'Trebuchet MS', Helvetica, sans-serif",
    georgia: "Georgia, 'Times New Roman', serif",
    times: "'Times New Roman', Times, serif",
};
const PREVIEW_DELAY = 500;

/**
 * The email is written as in a word processor. What is stored is the HTML
 * (cleaned again on the server before it is sent).
 */
export default {
    props: {
        modelValue: { type: String, default: "" },
        // { prospect: [...], user: [...], project: [...] }
        variables: { type: Object, default: () => ({}) },
        // The subject, shown in the preview
        subject: { type: String, default: "" },
        // The look of the email (null: the usual look of the CRM)
        design: { type: Object, default: null },
        projectName: { type: String, default: "" },
        readOnly: { type: Boolean, default: false },
    },

    emits: ["update:modelValue", "update:design"],

    data() {
        return {
            mode: "edit",
            textColor: "#c0341d",
            highlightColor: "#fff3a3",
            range: null,
            previewHtml: "",
            previewLoading: false,
            previewError: "",
            previewTimer: null,
            previewRequest: 0,
            styles: STYLES,
            palettes: PALETTES,
            fonts: FONTS,
            fontCss: FONT_CSS,
            colorKeys: COLOR_KEYS,
            textKeys: TEXT_KEYS,
        };
    },

    computed: {
        ...mapGetters(["project"]),

        variableList() {
            return Object.entries(this.variables).flatMap(([group, names]) => names.map((name) => `{{${group}.${name}}}`));
        },

        /**
         * The email as the prospect will get it, with examples in place of the variables
         */
        previewDocument() {
            const body = hasContent(this.modelValue)
                ? withSamples(this.modelValue, this.projectName)
                : `<p style="color:#9aa0b0">${escapeHtml(this.$t("workflow.email.placeholder"))}</p>`;
            const subject = this.subject
                ? `<div class="subject"><small>${escapeHtml(this.$t("workflow.email.subject_label"))}</small><strong>${withSamples(escapeHtml(this.subject), this.projectName)}</strong></div>`
                : "";

            return `<!doctype html><html><head><meta charset="utf-8"><style>
body{margin:0;padding:12px;background:#eef0f6;font-family:Arial,Helvetica,sans-serif}
.mail{max-width:560px;margin:0 auto;overflow:hidden;background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(30,35,60,.12)}
.subject{display:flex;flex-direction:column;gap:2px;padding:10px 16px;background:#fafbfd;border-bottom:1px solid #e3e6ef;font-size:13px}
.subject small{color:#8a8fa0}
.head{padding:14px 16px;color:#fff;background:#4b5ce8;font-size:15px;font-weight:700}
.body{padding:18px 16px;color:#222;font-size:14px;line-height:1.5;text-align:left;overflow-wrap:anywhere}
.body img{max-width:100%}
.foot{padding:10px 16px;color:#8a8fa0;background:#fafbfd;border-top:1px solid #e3e6ef;font-size:11px;text-align:center}
</style></head><body><div class="mail">${subject}<div class="head">${escapeHtml(this.projectName || this.$t("workflow.email.your_project"))}</div><div class="body">${body}</div><div class="foot">${escapeHtml(this.$t("workflow.email.preview_foot"))}</div></div></body></html>`;
        },
    },

    beforeUnmount() {
        clearTimeout(this.previewTimer);
    },

    watch: {
        // The drawing follows what is written and chosen (once the typing stops)
        design: { deep: true, handler: "queuePreview" },
        subject: "queuePreview",
        mode: "queuePreview",

        // The text changed from outside (the HTML view, another step): show it, keeping the cursor otherwise
        modelValue(value) {
            this.queuePreview();

            const area = this.$refs.area;

            if (area && area.innerHTML !== (value || "")) {
                area.innerHTML = value || "";
            }
        },
    },

    mounted() {
        this.$refs.area.innerHTML = this.modelValue || "";
        // Colours as CSS (style="color: ...") rather than <font>, which mail clients render badly
        try {
            document.execCommand("styleWithCSS", false, true);
        } catch (e) {
            // not supported: the formatting still works
        }
    },

    methods: {
        /**
         * The design chosen, changed by a few values
         */
        change(patch) {
            this.$emit("update:design", { ...this.design, ...patch });
        },

        customize() {
            this.$emit("update:design", defaultDesign(this.project ? this.project.name : ""));
        },

        // Not every text matters to every style
        showsText(key) {
            return this.design.style === "branded" || !["brand_label", "category"].includes(key);
        },

        queuePreview() {
            clearTimeout(this.previewTimer);

            if (this.mode !== "preview" && this.mode !== "design") {
                return;
            }

            if (!this.design) {
                this.previewHtml = "";
                this.previewError = "";
                return;
            }

            this.previewTimer = setTimeout(() => this.loadPreview(), PREVIEW_DELAY);
        },

        /**
         * The email drawn by the server, with the same view as the email sent
         */
        async loadPreview() {
            const request = ++this.previewRequest;
            this.previewLoading = true;
            this.previewError = "";

            try {
                const { data } = await WorkflowService.emailPreview(this.project.slug, {
                    subject: withSamples(this.subject, this.projectName).replace(/&amp;|&lt;|&gt;|&quot;|&#39;/g, (e) => ({ "&amp;": "&", "&lt;": "<", "&gt;": ">", "&quot;": '"', "&#39;": "'" }[e])),
                    body: hasContent(this.modelValue) ? withSamples(this.modelValue, this.projectName) : "",
                    design: this.design,
                });

                // A slower answer to an older question is not the one wanted
                if (request === this.previewRequest) {
                    this.previewHtml = data.html;
                }
            } catch (e) {
                if (request === this.previewRequest) {
                    this.previewHtml = "";
                    this.previewError = this.$t("workflow.email.preview_error");
                }
            } finally {
                if (request === this.previewRequest) {
                    this.previewLoading = false;
                }
            }
        },

        setMode(mode) {
            this.mode = mode;

            if (mode === "edit") {
                this.$nextTick(() => {
                    this.$refs.area.innerHTML = this.modelValue || "";
                });
            }
        },

        onInput() {
            this.$emit("update:modelValue", this.cleanup(this.$refs.area.innerHTML));
        },

        /**
         * An empty editor is an empty text (not "<br>")
         */
        cleanup(html) {
            return hasContent(html) ? html : "";
        },

        /**
         * Pasted text keeps its paragraphs but not the styles of where it comes from
         */
        onPaste(event) {
            const text = event.clipboardData && event.clipboardData.getData("text/plain");

            if (text === undefined || text === null || this.readOnly) {
                return;
            }

            event.preventDefault();
            this.restore();
            document.execCommand("insertText", false, text);
            this.onInput();
        },

        // The selection of the text, kept while the focus is on a button or a list

        remember() {
            const selection = window.getSelection();

            if (selection && selection.rangeCount && this.$refs.area && this.$refs.area.contains(selection.anchorNode)) {
                this.range = selection.getRangeAt(0).cloneRange();
            }
        },

        restore() {
            const area = this.$refs.area;

            area.focus();

            if (this.range) {
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(this.range);
            }
        },

        run(command, value = null) {
            if (this.readOnly) {
                return;
            }

            this.restore();
            document.execCommand(command, false, value);
            this.remember();
            this.onInput();
        },

        color(command, value) {
            this.run(command, value);
        },

        applyBlock(event) {
            const tag = event.target.value;
            event.target.value = "";

            if (tag) {
                this.run("formatBlock", `<${tag}>`);
            }
        },

        addLink() {
            const url = safeUrl(window.prompt(this.$t("workflow.email.link_ask"), "https://"));

            if (!url) {
                return;
            }

            this.restore();

            // Nothing selected: the address is the text of the link
            if (window.getSelection().isCollapsed) {
                document.execCommand("insertHTML", false, `<a href="${url}">${url}</a>`);
            } else {
                document.execCommand("createLink", false, url);
            }

            this.remember();
            this.onInput();
        },

        addImage() {
            const url = safeUrl(window.prompt(this.$t("workflow.email.image_ask"), "https://"), { image: true });

            if (url) {
                this.run("insertImage", url);
            }
        },

        /**
         * A variable at the cursor
         */
        insert(variable) {
            this.run("insertText", variable);
        },
    },
};
</script>
