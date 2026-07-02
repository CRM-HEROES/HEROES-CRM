<template>
    <label
        v-if="type == 'date' || type == 'email' || type == 'tel'"
        class="hc-meta-cell-label"
    >
        <input
            class="hc-meta-cell-input"
            :type="type"
            :disabled="disabled"
            v-model.lazy="value"
            @focus="select"
            @change="update"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProjectInput"
            @keydown.down="focusNextProjectInput"
        />
        <span v-text="label"></span>
    </label>
    <label v-else-if="type == 'url'" class="hc-meta-cell-label">
        <input
            type="text"
            class="hc-meta-cell-input"
            v-model.lazy="value"
            :disabled="disabled"
            @focus="select"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProjectInput"
            @keydown.down="focusNextProjectInput"
        />
        <span v-text="label"></span>
        <a
            v-if="value"
            target="_blank"
            :href="value"
            class="fa fa-external-link"
        ></a>
    </label>
    <input
        v-else-if="type == 'text' || type == 'number'"
        class="hc-meta-cell-input"
        :type="type"
        :disabled="disabled"
        v-model.lazy="value"
        @focus="select"
        @change="update"
        @keydown.enter="focusNextInput"
        @keydown.up="focusPreviousProjectInput"
        @keydown.down="focusNextProjectInput"
    />
    <input
        v-else-if="type == 'datetime'"
        type="datetime-local"
        :disabled="disabled"
        v-model.lazy="value"
        @focus="select"
        @change="update"
        @keydown.enter="focusNextInput"
        @keydown.up="focusPreviousProjectInput"
        @keydown.down="focusNextProjectInput"
    />
    <google-map-input
        v-else-if="type == 'address'"
        class="hc-meta-cell-input"
        :disabled="disabled"
        v-model.lazy="value"
        @changed="updateAddress"
        @keydown.enter="focusNextInput"
        @keydown.up="focusPreviousProjectInput"
        @keydown.down="focusNextProjectInput"
    />
    <textarea
        v-else-if="type == 'long_text'"
        class="hc-meta-cell-input"
        :disabled="disabled"
        v-model.lazy="value"
        @focus="select($event), autoHeight($event)"
        @blur="initHeight($event)"
        @change="update"
    ></textarea>
</template>

<script>
import store from "@/store";

export default {
    props: {
        /**
         * Project
         */
        USER: {
            type: Object,
        },

        /**
         * Field name
         */
        field: {
            type: String,
        },

        /**
         * Field type
         */
        type: {
            type: String,
            default: "text",
        },
    },

    methods: {
        /**
         * Select input content
         *
         * @param {*} e
         */
        select(e) {
            // e.target.select();
        },

        /**
         * When we tap ENTER
         * we focus the next input
         */
        focusNextInput() {
            // Get list of inputs in the document
            const inputs = [...document.getElementsByTagName("input")];

            // Get the next input
            const currentInput = document.activeElement;
            const currentInputIndex = inputs.indexOf(currentInput);
            const nextInputIndex = (currentInputIndex + 1) % inputs.length;
            const input = inputs[nextInputIndex];

            // Focus the next input
            if (input) {
                input.focus();
            }
        },

        /**
         */
        focusPreviousProjectInput() {
            // Get list of inputs in the document
            const currentInput = document.activeElement;
            let parentNode = currentInput;

            while (parentNode && parentNode.tagName !== "TR") {
                parentNode = parentNode.parentNode;
            }

            // Get the next input
            let previousParentNode = parentNode.previousElementSibling;

            if (previousParentNode) {
                const inputs = [...parentNode.getElementsByTagName("input")];
                const prevInputs = [
                    ...previousParentNode.getElementsByTagName("input"),
                ];
                const currentInputIndex = inputs.indexOf(currentInput);

                const input = prevInputs[currentInputIndex];

                // Focus the next input
                if (input) {
                    input.focus();
                }
            }
        },
        /**
         */
        focusNextProjectInput() {
            // Get list of inputs in the document
            const currentInput = document.activeElement;
            let parentNode = currentInput;

            while (parentNode && parentNode.tagName !== "TR") {
                parentNode = parentNode.parentNode;
            }

            // Get the next input
            let nextParentNode = parentNode.nextElementSibling;

            if (nextParentNode) {
                const inputs = [...parentNode.getElementsByTagName("input")];
                const nextInputs = [
                    ...nextParentNode.getElementsByTagName("input"),
                ];
                const currentInputIndex = inputs.indexOf(currentInput);

                const input = nextInputs[currentInputIndex];

                // Focus the next input
                if (input) {
                    input.focus();
                }
            }
        },

        /**
         * @param {*} e
         */
        autoHeight(e) {
            e.target.style.height = "auto";
            e.target.style.height = `${e.target.scrollHeight}px`;
        },

        /**
         * @param {*} e
         */
        initHeight(e) {
            e.target.style.height = "100%";
        },

        /**
         * Update address
         * @param {*} e
         */
        updateAddress(address) {
            this.value = address.place.formatted_address;
        },
    },

    computed: {
        /**
         * When value is updated
         * Send modification to the API
         */
        value() {
            return this.USER.meta ? this.USER.meta[this.field] : "";
        },

        /**
         * Do not allow user
         * to edit
         * on certain conditions applied to the USER
         */
        disabled() {
            return true;
        },

        /**
         * Label that will be shown
         * instead of raw value
         * for some type of field
         */
        label() {
            // For date field
            if (this.type == "date" && this.value) {
                const date = dayjs(new Date(this.value)).format("DD/MM/YYYY");

                if (date == "Invalid date") {
                    return this.value;
                }

                return date;

                // For datetime field
            } else if (this.type == "datetime" && this.value) {
                const date = dayjs(new Date(this.value)).format(
                    "DD/MM/YYYY HH:mm:ss"
                );

                if (date == "Invalid date") {
                    return this.value;
                }

                return date;
            }

            return this.value;
        },
    },
};
</script>
