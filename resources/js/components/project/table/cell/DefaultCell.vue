<template>
    <label
        v-if="field == 'created_at' || field == 'updated_at'"
        class="hc-default-cell-label"
    >
        <span v-text="label"></span>
    </label>
    <label
        v-else-if="field == 'phone_number' || field == 'mobile_phone_number'"
        class="hc-default-cell-label"
    >
        <input
            type="tel"
            class="hc-default-cell-input"
            v-model.lazy="value"
            @focus="select"
            :disabled="disabled"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProjectInput"
            @keydown.down="focusNextProjectInput"
        />
        <span v-text="label"></span>
    </label>
    <label v-else-if="field == 'email'" class="hc-default-cell-label">
        <input
            type="email"
            class="hc-default-cell-input"
            v-model.lazy="value"
            @focus="select"
            :disabled="disabled"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProjectInput"
            @keydown.down="focusNextProjectInput"
        />
        <span v-text="label"></span>
    </label>
    <label v-else-if="field == 'website_url'" class="hc-default-cell-label">
        <input
            type="text"
            class="hc-default-cell-input"
            v-model.lazy="value"
            @focus="select"
            :disabled="disabled"
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
        v-else
        class="hc-default-cell-input"
        v-model.lazy="value"
        @focus="select"
        :disabled="disabled"
        @keydown.enter="focusNextInput"
        @keydown.up="focusPreviousProjectInput"
        @keydown.down="focusNextProjectInput"
    />
</template>

<script>
import store from "@/store";

import { UPDATE_PROJECT } from "@/actions/project";

export default {
    props: {
        /**
         * Project
         */
        project: {
            type: Object,
        },

        /**
         * Field
         */
        field: {
            type: String,
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
            const currentInput = document.activeElement;
            const inputs = [...document.getElementsByTagName("input")];

            // Get the next input
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
    },

    computed: {
        /**
         * When value is updated
         * Send modification to the API
         */
        value: {
            get() {
                return this.project[this.field];
            },

            set(newValue) {
                // Modification payload
                const update = {
                    slug: this.project.slug,
                    [this.field]: newValue,
                };

                // If the project is a new project
                // that doesn't have yet an ID
                // we simply localy update field
                // The modification will be stacked
                // and will be sent lately
                // after we retrive the real ID of the project
                if (this.project.id <= 0) {
                    store.commit(UPDATE_PROJECT, update);
                    return;
                }

                // else
                store.dispatch(UPDATE_PROJECT, update);
            },
        },

        /**
         * Do not allow user
         * to edit some field
         * or on certain conditions applied to the project
         */
        disabled() {
            return (
                // deleted or archived project
                this.project.deleted_at ||
                this.project.processed_at ||
                // created_at and updated_at fields
                this.field == "created_at" ||
                this.field == "updated_at"
            );
        },

        /**
         * Label that will be shown
         * instead of raw value
         * for some type of field
         */
        label() {
            // created_at and updated_at field
            // as human readable fields
            if (this.field == "created_at" || this.field == "updated_at") {
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
