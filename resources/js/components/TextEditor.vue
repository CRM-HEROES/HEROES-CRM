<template>
    <div class="hc-text-editor">
        <div class="hc-text-editor-buttons">
            <div>
                <template v-for="(button, key) in definedButtons" :key="key">
                    <select v-if="key == 'font'"></select>
                    <input
                        :title="button.name"
                        type="color"
                        v-if="key == 'color'"
                        @blur="button.action"
                    />
                    <input
                        :title="button.name"
                        type="color"
                        v-else-if="key == 'backgroundColor'"
                        value="#FFFFFF"
                        @blur="button.action"
                    />
                    <button
                        v-else
                        :title="button.name"
                        :class="[button.active ? 'active' : '']"
                        @click.prevent="button.action"
                    >
                        <i :class="button.icon"></i>
                    </button>
                </template>
            </div>
        </div>
        <div
            class="hc-text-editor-area"
            contenteditable=""
            ref="textarea"
            @input="input"
            @focus="focus"
            @blur="blur"
            @keydown.enter="keyEnter"
            :placeholder="placeholder"
            :style="style"
            :required="required"
        ></div>
    </div>
</template>

<style>
.hc-text-editor {
    width: 100%;
}

.hc-text-editor-buttons {
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: center;
}

.hc-text-editor-buttons > div {
    max-width: 100%;
    display: flex;
    flex-direction: row;
    gap: 4px;
    padding: 3px 0;
}

.hc-text-editor-buttons > div > button {
    width: 24px;
    height: 24px;
    background: none;
    border: none;
    line-height: 24px;
    text-align: center;
    position: relative;
    border-radius: 7px;
    outline: 1px solid #eee;
    font-size: 11px;
    color: #000000;
}

.hc-text-editor-buttons > div > button.active {
    background-color: #8641a6;
    color: white;
}

.hc-text-editor-buttons > div > input[type="color"] {
    width: 24px;
    height: 24px;
    padding: 0;
    border-radius: 3px;
    border: none;
    overflow: hidden;
}

.hc-text-editor-area {
    overflow: auto;
    width: 100%;
    min-height: 40px;
    max-height: 200px;
    border: 1px solid #ddd;
    padding: 10px;
    position: relative;
    border-radius: 5px;
    white-space: normal;
    font-size: 12px;
}

.hc-text-editor-area:empty:before {
    position: absolute;
    content: attr(placeholder);
    top: 10px;
    left: 10px;
    color: #999999;
}

.hc-text-editor-area:focus {
    outline: 2px solid #12a0f3;
}
</style>

<script>
export default {
    props: {
        modelValue: {
            type: String,
            default: "",
        },

        modelModifiers: {
            default: () => ({}),
        },

        /**
         * Buttons
         */
        buttons: {
            type: Array,
            default: [
                "bold",
                "italic",
                "underline",
                "unorderedList",
                "orderedList",
                "alignLeft",
                "alignCenter",
                "alignRight",
                "color",
                "backgroundColor",
            ],
        },

        /**
         * Placeholder
         */
        placeholder: {
            type: String,
            default: "",
        },

        required: {
            type: Boolean,
            default: false,
        },

        /**
         * Height
         */
        height: {
            type: String,
            default: null,
        },

        /**
         * Min height
         */
        minHeight: {
            type: Number,
            default: null,
        },
    },

    data() {
        return {
            currentValue: this.modelValue,
            availableButtons: {
                bold: {
                    icon: "fa fa-bold",
                    name: "Gras",
                    active: false,
                    action: () => document.execCommand("bold", false, null),
                    check: () => document.queryCommandState("bold"),
                },
                italic: {
                    icon: "fa fa-italic",
                    name: "Italic",
                    active: false,
                    action: () => document.execCommand("italic", false, null),
                    check: () => document.queryCommandState("italic"),
                },
                underline: {
                    icon: "fa fa-underline",
                    name: "Sous-lignée",
                    active: false,
                    action: () =>
                        document.execCommand("underline", false, null),
                    check: () => document.queryCommandState("underline"),
                },
                unorderedList: {
                    icon: "fa fa-list-ul",
                    name: "Liste non ordonnée",
                    active: false,
                    action: () =>
                        document.execCommand(
                            "insertUnorderedList",
                            false,
                            null
                        ),
                    check: () =>
                        document.queryCommandState("insertUnorderedList"),
                },
                orderedList: {
                    icon: "fa fa-list-ul",
                    name: "Liste ordonnée",
                    active: false,
                    action: () =>
                        document.execCommand("insertOrderedList", false, null),
                    check: () =>
                        document.queryCommandState("insertOrderedList"),
                },
                alignLeft: {
                    icon: "fa fa-align-left",
                    name: "Aligner à gauche",
                    active: false,
                    action: () =>
                        document.execCommand("justifyLeft", false, null),
                    check: () => document.queryCommandState("justifyLeft"),
                },
                alignCenter: {
                    icon: "fa fa-align-center",
                    name: "Centrer",
                    active: false,
                    action: () =>
                        document.execCommand("justifyCenter", false, null),
                    check: () => document.queryCommandState("justifyCenter"),
                },
                alignRight: {
                    icon: "fa fa-align-right",
                    name: "Aligner à droite",
                    active: false,
                    action: () =>
                        document.execCommand("justifyRight", false, null),
                    check: () => document.queryCommandState("justifyRight"),
                },
                color: {
                    name: "Couleur de texte",
                    action: (e) => {
                        const spanElement = document.createElement("span");
                        spanElement.style.color = e.target.value;

                        // Get the current selection and wrap it with the <span> element
                        const selection = window.getSelection();
                        const range = selection.getRangeAt(0);
                        range.surroundContents(spanElement);
                        this.input(e);
                    },
                    check: () => document.queryCommandState("justifyRight"),
                },
                backgroundColor: {
                    name: "Couleur de fond",
                    action: (e) => {
                        const spanElement = document.createElement("span");
                        spanElement.style.backgroundColor = e.target.value;

                        // Get the current selection and wrap it with the <span> element
                        const selection = window.getSelection();
                        if (selection.rangeCount > 0) {
                            const range = selection.getRangeAt(0);
                            range.surroundContents(spanElement);
                            this.input(e);
                        }
                    },
                    check: () => document.queryCommandState("justifyRight"),
                },
            },
        };
    },

    mounted() {
        document.addEventListener("selectionchange", this.checkActiveButtons);
        this.updateInnerHTML();
    },

    methods: {
        checkActiveButtons() {
            this.availableButtons = Object.fromEntries(
                Object.entries(this.availableButtons).map(([key, value]) => [
                    key,
                    {
                        ...value,
                        active:
                            value.check() &&
                            document.activeElement == this.$refs.textarea,
                    },
                ])
            );
        },

        input() {
            if (!this.modelModifiers.lazy) {
                this.$emit("update:modelValue", this.$refs.textarea.innerHTML);
            }
        },

        focus() {
            this.currentValue = this.modelValue;

            let p = this.$refs.textarea,
                s = window.getSelection(),
                r = document.createRange();
            r.setStart(p, 0);
            r.setEnd(p, 0);
            s.removeAllRanges();
            s.addRange(r);
        },

        blur(e) {
            if (this.currentValue != this.$refs.textarea.innerHTML) {
                if (this.modelModifiers.lazy) {
                    this.$emit(
                        "update:modelValue",
                        this.$refs.textarea.innerHTML
                    );
                }

                this.$emit("change", e);
            }
        },

        keyEnter(e) {
            if (!e.shiftKey && this.modelModifiers.enter) {
                this.$refs.textarea.blur();

                this.$emit("enter", e);
            }
        },

        getCaretPosition() {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const preSelectionRange = range.cloneRange();
                preSelectionRange.selectNodeContents(this.$refs.textarea);
                preSelectionRange.setEnd(
                    range.startContainer,
                    range.startOffset
                );
                const caretOffset = preSelectionRange.toString().length;
                return caretOffset;
            }
            return 0;
        },

        setCaretPosition(caretOffset) {
            const range = document.createRange();
            const selection = window.getSelection();
            range.setStart(this.$refs.textarea, caretOffset);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        },

        updateInnerHTML() {
            if (this.modelValue !== this.$refs.textarea.innerHTML) {
                this.$refs.textarea.innerHTML = this.modelValue;
                this.setCaretPosition(this.modelValue ? 1 : 0);
            }
        },
    },

    watch: {
        checkActiveButtons() {
            this.availableButtons = Object.fromEntries(
                Object.entries(this.availableButtons).map(([key, value]) => [
                    key,
                    {
                        ...value,
                        active:
                            value.check() &&
                            document.activeElement == this.$refs.textarea,
                    },
                ])
            );
        },

        modelValue() {
            this.updateInnerHTML();
        },
    },

    computed: {
        /**
         * Defined buttons
         */
        definedButtons() {
            return Object.fromEntries(
                Object.entries(this.availableButtons).filter(
                    ([key]) => this.buttons.indexOf(key) >= 0
                )
            );
        },

        /**
         *
         */
        style() {
            const style = {};

            if (this.height) {
                style.height = this.height;
            }

            if (this.minHeight) {
                style.minHeight = this.minHeight + "px";
            }

            return style;
        },
    },
};
</script>
