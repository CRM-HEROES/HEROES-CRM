<template>
    <textarea
        class="hc-autocomplete-textarea"
        v-model="value"
        @focus="setCaretCoordinates(), (lastValue = $event.target.value)"
        @input="setCaretCoordinates(), updateTextBeforeAfterCaret()"
        @change.prevent="change"
        @blur.prevent="blur"
        @keydown="selectAutocomplete"
        @keydown.enter="selectAutocomplete"
        @keyup="updateTextBeforeAfterCaret(), updateAutocomplete()"
        ref="textarea"
    ></textarea>
</template>

<style>
.hc-autocomplete {
    position: relative;
}

.hc-autocomplete-textarea {
    resize: auto;
    width: 100%;
    border: none;
}

.hc-autocomplete-dropdown {
    background-color: white;
    border-radius: 3px;
    box-shadow: 0 10px 20px -5px #0006;
    list-style: none;
    margin: 0;
    overflow: hidden;
    padding: 0;
    position: fixed;
    transition: all 50ms ease-out;
    width: 200px;
    z-index: 1000;
    font-size: 12px;
}

.hc-autocomplete-dropdown.hidden {
    visibility: hidden;
}

.hc-autocomplete-dropdown > li {
    padding: 2px 10px;
}

.hc-autocomplete-dropdown > li:hover {
    background-color: #eee;
    cursor: pointer;
}

.hc-autocomplete-dropdown > li.selected {
    background-color: #1e88e5;
    color: white;
}
</style>

<script>
import { ref } from "vue";

window.hcAutocompleteDropdown = null;

export default {
    props: {
        /**
         *
         */
        modelValue: {
            type: String,
            default: "",
        },

        /**
         *
         */
        autoHeight: {
            type: Boolean,
            default: false,
        },

        /**
         *
         */
        strategies: {
            type: Array,
            default: [],
        },
    },

    data() {
        return {
            value: this.modelValue,
            lastValue: this.modelValue,
            caretCoordinates: null,
            textBeforeCaret: "",
            textAfterCaret: "",
            autocomplete: [],
            selected: 0,
            hovered: null,
        };
    },

    mounted() {
        if (!window.hcAutocompleteDropdown) {
            window.hcAutocompleteDropdown = document.createElement("ul");
            window.hcAutocompleteDropdown.setAttribute(
                "class",
                "hc-autocomplete-dropdown"
            );
            document.body.appendChild(window.hcAutocompleteDropdown);
            window.hcAutocompleteDropdown.addEventListener(
                "mouseleave",
                (e) => {
                    this.hovered = null;
                }
            );
        }
    },

    methods: {
        focus() {
            this.$refs.textarea.focus();
        },

        /**
         *
         * @param {*} event
         */
        change(event) {},

        /**
         *
         * @param {*} event
         */
        blur(event) {
            if (this.hovered === null) {
                this.$emit("blured", event);

                if (this.value !== this.lastValue) {
                    this.$emit("changed", event);
                }
            }
        },

        /**
         *
         */
        setCaretCoordinates() {
            // caret coordinate
            this.caretCoordinates = getCaretCoordinates(
                this.textarea,
                this.textarea.selectionEnd
            );
        },

        /**
         *
         */
        updateTextBeforeAfterCaret() {
            // text to match
            this.textBeforeCaret = this.value.substring(
                0,
                this.textarea.selectionStart
            );
            this.textAfterCaret = this.value.substring(
                this.textarea.selectionStart
            );
        },

        /**
         *
         * @param {*} e
         */
        selectAutocomplete(e) {
            if (this.autocomplete.length > 0) {
                // Down
                if (e.keyCode == 40) {
                    e.preventDefault();

                    if (this.selected + 1 < this.autocomplete.length) {
                        this.selected++;
                    } else {
                        this.selected = 0;
                    }

                    return;
                    // Up
                } else if (e.keyCode == 38) {
                    e.preventDefault();

                    if (this.selected <= 0) {
                        this.selected = this.autocomplete.length - 1;
                    } else {
                        this.selected--;
                    }

                    return;
                    // Tab
                } else if (e.keyCode == 9 || e.keyCode == 13) {
                    if (this.selected >= 0) {
                        e.preventDefault();
                        this.replaceAutocomplete(
                            this.autocomplete[this.selected]
                        );
                        return;
                    }
                }
            }

            this.selected = 0;
        },

        /**
         *
         * @param {*} e
         */
        async replaceAutocomplete(autocomplete) {
            this.hovered = null;
            const lastValue = this.value;
            this.value = autocomplete.replace + this.textAfterCaret;
            await this.$nextTick();
            this.textarea.selectionStart = this.textarea.selectionEnd =
                this.value.length - this.textAfterCaret.length;
            this.setCaretCoordinates();
            this.updateTextBeforeAfterCaret();
            this.updateAutocomplete();
            this.focus();
            this.lastValue = lastValue;
            this.$emit("selected", autocomplete.item);
        },

        /**
         *
         * @param {*} value
         */
        updateAutocomplete() {
            const autocomplete = ref([]);
            this.autocomplete = autocomplete;

            this.strategies.forEach((strategy) => {
                const matches = this.textBeforeCaret.match(strategy.match);
                const index = strategy.index ?? 0;

                if (matches && matches.length >= index) {
                    const query = matches[index];
                    const callback = async (ac) => {
                        autocomplete.value = [
                            ...autocomplete.value.filter(
                                (a) => a.callback != callback
                            ),
                            ...ac.map((s) => ({
                                item: s,
                                value: strategy.template
                                    ? strategy.template(s, query)
                                    : s,
                                replace: this.textBeforeCaret.replace(
                                    strategy.match,
                                    strategy.replace ? strategy.replace(s) : s
                                ),
                                callback: callback,
                            })),
                        ];
                    };

                    strategy.search(query, callback);
                }
            });
        },
    },

    watch: {
        /**
         *
         */
        modelValue(newValue) {
            this.value = newValue;
        },

        /**
         *
         */
        async value(newValue, oldValue) {
            this.$emit("update:modelValue", newValue);

            if (this.autoHeight) {
                await this.$nextTick();

                if (newValue.length != oldValue.length) {
                    this.$refs.textarea.style.height = 0;
                    this.$refs.textarea.style.height =
                        this.$refs.textarea.scrollHeight + 2 + "px";
                }
            }
        },

        /**
         *
         */
        caretCoordinates(newValue) {
            const rect = this.textarea.getBoundingClientRect();
            window.hcAutocompleteDropdown.style.left = `${
                rect.left + newValue.left
            }px`;
            window.hcAutocompleteDropdown.style.top = `${
                rect.top +
                newValue.top +
                newValue.height -
                this.textarea.scrollTop
            }px`;
        },

        /**
         *
         */
        textBeforeCaret() {
            this.updateAutocomplete();
        },

        /**
         *
         */
        showDropdown(newValue) {
            if (newValue) {
                window.hcAutocompleteDropdown.classList.remove("hidden");
            } else {
                window.hcAutocompleteDropdown.classList.add("hidden");
            }
        },

        /**
         *
         */
        autocomplete(newValue) {
            this.hovered = null;
            window.hcAutocompleteDropdown.innerHTML = "";
            newValue.forEach((ac, i) => {
                let li = document.createElement("li");
                li.innerHTML = ac.value;
                li.setAttribute("class", i == this.selected ? "selected" : "");
                li.addEventListener("click", (e) => {
                    this.selected = i;
                    this.replaceAutocomplete(this.autocomplete[this.selected]);
                });
                li.addEventListener("mouseenter", (e) => {
                    this.hovered = i;
                    for (let j in window.hcAutocompleteDropdown.childNodes) {
                        let child = window.hcAutocompleteDropdown.childNodes[j];
                        if (child.setAttribute) {
                            child.setAttribute(
                                "class",
                                j == this.selected ? "selected" : ""
                            );
                        }
                    }
                });
                window.hcAutocompleteDropdown.appendChild(li);
            });
        },
    },

    computed: {
        /**
         *
         */
        showDropdown() {
            return this.caretCoordinates && this.autocomplete.length > 0;
        },

        /**
         *
         */
        textarea() {
            return this.$refs.textarea;
        },

        /**
         *
         *
        dropdown() {
            return this.$refs.dropdown;
        },*/
    },
};

// Thanks: https://github.com/component/textarea-caret-position
(function () {
    function e(b, e, f) {
        if (!h)
            throw Error(
                "textarea-caret-position#getCaretCoordinates should only be called in a browser"
            );
        if ((f = (f && f.debug) || !1)) {
            var a = document.querySelector(
                "#input-textarea-caret-position-mirror-div"
            );
            a && a.parentNode.removeChild(a);
        }
        a = document.createElement("div");
        a.id = "input-textarea-caret-position-mirror-div";
        document.body.appendChild(a);
        const c = a.style,
            d = window.getComputedStyle
                ? window.getComputedStyle(b)
                : b.currentStyle,
            k = "INPUT" === b.nodeName;
        c.whiteSpace = "pre-wrap";
        k || (c.wordWrap = "break-word");
        c.position = "absolute";
        f || (c.visibility = "hidden");
        l.forEach(function (a) {
            k && "lineHeight" === a ? (c.lineHeight = d.height) : (c[a] = d[a]);
        });
        m
            ? b.scrollHeight > parseInt(d.height) && (c.overflowY = "scroll")
            : (c.overflow = "hidden");
        a.textContent = b.value.substring(0, e);
        k && (a.textContent = a.textContent.replace(/\s/g, "\u00a0"));
        const g = document.createElement("span");
        g.textContent = b.value.substring(e) || ".";
        a.appendChild(g);
        b = {
            top: g.offsetTop + parseInt(d.borderTopWidth),
            left: g.offsetLeft + parseInt(d.borderLeftWidth),
            height:
                d.lineHeight == "normal"
                    ? parseInt(d.fontSize) * 1.2
                    : parseInt(d.lineHeight),
        };
        f ? (g.style.backgroundColor = "#aaa") : document.body.removeChild(a);
        return b;
    }
    const l = [
            "direction",
            "boxSizing",
            "width",
            "height",
            "overflowX",
            "overflowY",
            "borderTopWidth",
            "borderRightWidth",
            "borderBottomWidth",
            "borderLeftWidth",
            "borderStyle",
            "paddingTop",
            "paddingRight",
            "paddingBottom",
            "paddingLeft",
            "fontStyle",
            "fontVariant",
            "fontWeight",
            "fontStretch",
            "fontSize",
            "fontSizeAdjust",
            "lineHeight",
            "fontFamily",
            "textAlign",
            "textTransform",
            "textIndent",
            "textDecoration",
            "letterSpacing",
            "wordSpacing",
            "tabSize",
            "MozTabSize",
        ],
        h = "undefined" !== typeof window,
        m = h && null != window.mozInnerScreenX;
    "undefined" != typeof module && "undefined" != typeof module.exports
        ? (module.exports = e)
        : h && (window.getCaretCoordinates = e);
})();
</script>
