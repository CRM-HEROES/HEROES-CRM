<template>
    <span v-if="content" class="hc-tip">
        <button ref="button" type="button" class="hc-tip-button" :class="{ open }" :title="$t('workflow.tip.what')" @click.stop.prevent="toggle">i</button>
        <span v-if="open" class="hc-tip-pop" role="tooltip" :style="position" @click.stop v-text="content"></span>
    </span>
</template>

<style>
.hc-tip {
    display: inline-block;
    margin-left: 4px;
    vertical-align: middle;
}

/* Two classes: stronger than the rules of the buttons around it (menus, toolbars) */
.hc-tip > .hc-tip-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    width: 16px;
    min-width: 16px;
    height: 16px;
    min-height: 16px;
    margin: 0;
    padding: 0;
    color: #3a49c9;
    background: #eceefd;
    border: 0;
    border-radius: 50%;
    box-shadow: none;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 11px;
    font-style: italic;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 0;
    text-transform: none;
    cursor: pointer;
}

.hc-tip > .hc-tip-button:hover,
.hc-tip > .hc-tip-button.open {
    color: #fff;
    background: #4b5ce8;
}

.hc-tip-pop {
    position: fixed;
    z-index: 2000;
    width: 250px;
    padding: 10px 12px;
    color: #2b2f3a;
    background: #fff;
    border: 1px solid #d5d9e4;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(30, 35, 60, 0.22);
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    letter-spacing: 0;
    line-height: 1.45;
    text-align: left;
    text-transform: none;
    white-space: normal;
}
</style>

<script>
// One bubble at a time: opening another one closes the previous
let opened = null;

/**
 * A small "i" button that explains, in a bubble, what the field or the button
 * next to it does. Give it the text (text) or the key of a translation (k);
 * without text there is no button at all.
 */
export default {
    props: {
        text: { type: String, default: "" },
        k: { type: String, default: "" },
    },

    data() {
        return { open: false, position: {} };
    },

    computed: {
        content() {
            if (this.text) {
                return this.text;
            }

            return this.k && this.$te(this.k) ? this.$t(this.k) : "";
        },
    },

    beforeUnmount() {
        this.close();
    },

    methods: {
        toggle() {
            this.open ? this.close() : this.show();
        },

        show() {
            if (opened && opened !== this) {
                opened.close();
            }

            const rect = this.$refs.button.getBoundingClientRect();
            const width = 250;

            this.position = {
                left: Math.max(8, Math.min(rect.left - 12, window.innerWidth - width - 8)) + "px",
                top: rect.bottom + 8 + "px",
            };
            this.open = true;
            opened = this;

            document.addEventListener("click", this.close);
            document.addEventListener("keydown", this.onKey);
            window.addEventListener("scroll", this.close, true);
        },

        close() {
            this.open = false;

            if (opened === this) {
                opened = null;
            }

            document.removeEventListener("click", this.close);
            document.removeEventListener("keydown", this.onKey);
            window.removeEventListener("scroll", this.close, true);
        },

        onKey(event) {
            if (event.key === "Escape") {
                this.close();
            }
        },
    },
};
</script>
