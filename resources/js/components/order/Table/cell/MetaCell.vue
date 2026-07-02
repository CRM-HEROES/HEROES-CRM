<template>
    <label class="hc-default-cell-label">
        <google-map-input
            v-if="type == 'address'"
            class="hc-default-cell-input"
            :disabled="disabled"
            v-model.lazy="value"
            @changed="updateAddress"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProspectInput"
            @keydown.down="focusNextProspectInput"
        />
        <textarea
            v-else-if="type == 'long_text'"
            class="hc-default-cell-input"
            :disabled="disabled"
            v-model.lazy="value"
            @focus="select($event), autoHeight($event)"
            @blur="initHeight($event)"
            @change="update"
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
import { mapGetters } from "vuex";
import store from "@/store";

import ProspectOrderService from "@/apis/project/prospect/order";

import { UPDATE_PROSPECT_ORDER } from "@/actions/project/prospect/order";

export default {
    props: {
        /**
         * Order
         */
        order: {
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
            e.target.style.height = `${e.target.scrollHeight}px`;
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
        ...mapGetters(["project"]),

        /**
         * When value is updated
         * Send modification to the API
         */
        value: {
            get() {
                return this.order.meta ? this.order.meta[this.field] : "";
            },

            set(newValue) {
                const update = {
                    meta: {
                        [this.field]: newValue,
                    },
                };

                store.commit(UPDATE_PROSPECT_ORDER, {
                    id: this.order.id,
                    ...update,
                });
                ProspectOrderService.update(
                    this.project.slug,
                    this.order.prospect_id,
                    this.order.id,
                    update
                );
            },
        },

        /**
         * Do not allow user
         * to edit
         * on certain conditions applied to the order
         */
        disabled() {
            return this.order.deleted_at || this.order.processed_at;
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

        inputType() {
            return this.type == "url"
                ? "text"
                : this.type == "datetime"
                ? "datetime-local"
                : this.type;
        },
    },
};
</script>
