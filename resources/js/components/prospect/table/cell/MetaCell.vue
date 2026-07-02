<template>
    <label
        :class="[
            'hc-default-cell-label',
            'align-' + align,
            isValid ? '' : 'error',
            formatClass,
        ]"
    >
        <google-map-input
            v-if="type == 'address'"
            class="hc-default-cell-input"
            :disabled="disabled"
            v-model.lazy="value"
            @changed="updateAddress"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProspectInput"
            @keydown.down="focusNextProspectInput"
            :placeholder="placeholder"
        />
        <textarea
            v-else-if="type == 'long_text'"
            class="hc-default-cell-input"
            :disabled="disabled"
            v-model.lazy="value"
            @focus="select($event), autoHeight($event)"
            @blur="initHeight($event)"
            @change="update"
            :placeholder="placeholder"
        ></textarea>
        <input
            v-else
            :type="inputType"
            :class="['hc-default-cell-input', value ? '' : 'empty']"
            v-model.lazy="value"
            :disabled="disabled"
            @focus="select"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProspectInput"
            @keydown.down="focusNextProspectInput"
            :placeholder="placeholder"
        />

        <span v-text="label"></span>

        <a
            v-if="type == 'url' && value"
            target="_blank"
            :href="value"
            class="fa fa-external-link"
        ></a>
    </label>
</template>

<script>
import store from "@/store";

import { UPDATE_PROSPECT } from "@/actions/project/prospect";

export default {
    props: {
        /**
         * Prospect
         */
        prospect: {
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

        /**
         * align
         */
        align: {
            type: String,
            default: "left",
        },

        /**
         * Default value
         */
        defaultValue: {
            type: String,
        },

        /**
         * Required
         */
        required: {
            type: Boolean,
        },

        /**
         * Format
         */
        format: {
            type: String,
        },

        /**
         * Placeholder
         */
        placeholder: {
            type: String,
            default: "",
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
        focusPreviousProspectInput() {
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
        focusNextProspectInput() {
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
            e.target.style.height = `${Math.max(e.target.scrollHeight, 22)}px`;
        },

        /**
         * @param {*} e
         */
        initHeight(e) {
            e.target.style.height = "0";
            e.target.style.width = `100%`;
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
        value: {
            get() {
                return this.prospect.meta
                    ? this.type == "number"
                        ? parseInt(this.prospect.meta[this.field])
                        : this.prospect.meta[this.field]
                    : "";
            },

            set(newValue) {
                // Modification payload
                const update = {
                    id: this.prospect.id,
                    meta: {
                        [this.field]: newValue,
                    },
                };

                // If the prospect is a new prospect
                // that doesn't have yet an ID
                // we simply locally update field
                // The modification will be stacked
                // and will be sent lately
                // after we retrieve the real ID of the prospect
                if (this.prospect.id <= 0) {
                    store.commit(UPDATE_PROSPECT, update);
                    return;
                }

                // else
                store.dispatch(UPDATE_PROSPECT, update);
            },
        },

        /**
         * Do not allow user
         * to edit
         * on certain conditions applied to the prospect
         */
        disabled() {
            return this.prospect.deleted_at || this.prospect.processed_at;
        },

        /**
         * Check if data has good format
         * for its type
         */
        isValid() {
            if (this.type == "email") {
                return !this.value || hcEmailPattern.test(this.value);
            }

            if (this.required && !this.value && !this.defaultValue) {
                return false;
            }

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
                const date = new Date(this.value);

                if (date == "Invalid date") {
                    return this.value;
                }

                return dayjs(date).format("DD/MM/YYYY");
                // For datetime field
            } else if (this.type == "datetime" && this.value) {
                const date = new Date(this.value);

                if (date == "Invalid date") {
                    return this.value;
                }

                return dayjs(date).format("DD/MM/YYYY HH:mm:ss");
            }

            return this.value ? this.value : this.defaultValue;
        },

        inputType() {
            return this.type == "url"
                ? "text"
                : this.type == "datetime"
                ? "datetime-local"
                : this.type;
        },

        /**
         */
        formatClass() {
            if (this.format == "capitalize") {
                return "capitalize";
            } else if (this.format == "uppercase") {
                return "uppercase";
            } else if (this.format == "lowercase") {
                return "lowercase";
            } else {
                return "";
            }
        },
    },
};
</script>
