<template>
    <!-- Safe by construction: the text is escaped before anything else (utils/markdown.js) -->
    <div class="hc-md" v-html="html"></div>
</template>

<style>
.hc-md {
    line-height: 1.55;
    overflow-wrap: anywhere;
}

.hc-md > :first-child {
    margin-top: 0;
}

.hc-md > :last-child {
    margin-bottom: 0;
}

.hc-md h1,
.hc-md h2,
.hc-md h3,
.hc-md h4,
.hc-md h5,
.hc-md h6 {
    margin: 1.1em 0 0.4em;
    line-height: 1.25;
}

.hc-md h1 { font-size: 1.45em; }
.hc-md h2 { font-size: 1.25em; }
.hc-md h3 { font-size: 1.1em; }
.hc-md h4,
.hc-md h5,
.hc-md h6 { font-size: 1em; }

.hc-md p,
.hc-md ul,
.hc-md ol,
.hc-md blockquote,
.hc-md pre {
    margin: 0.5em 0;
}

.hc-md ul,
.hc-md ol {
    padding-left: 1.4em;
}

.hc-md li {
    margin: 0.15em 0;
}

.hc-md blockquote {
    padding: 0.2em 0.9em;
    border-left: 3px solid #ccc;
    color: #555;
}

.hc-md code {
    padding: 0.1em 0.35em;
    border-radius: 4px;
    background: #f0f0f2;
    font-size: 0.9em;
}

.hc-md pre {
    padding: 10px 12px;
    border-radius: 6px;
    background: #f0f0f2;
    overflow-x: auto;
}

.hc-md pre code {
    padding: 0;
    background: transparent;
}

.hc-md hr {
    border: 0;
    border-top: 1px solid #ddd;
    margin: 1em 0;
}

.hc-md a {
    color: #0b78b8;
    text-decoration: underline;
}

.hc-md .hc-md-table {
    overflow-x: auto;
    margin: 0.6em 0;
}

.hc-md table {
    border-collapse: collapse;
    min-width: 50%;
}

.hc-md th,
.hc-md td {
    padding: 5px 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.hc-md th {
    background: #f4f4f6;
}
</style>

<script>
import { renderMarkdown } from "@/utils/markdown";

export default {
    props: {
        text: { type: String, default: "" },
    },

    computed: {
        html() {
            return renderMarkdown(this.text);
        },
    },
};
</script>
