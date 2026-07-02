<template>
    <label
        :class="[
            'hc-default-cell-label',
            'align-' + align,
            isValid ? '' : 'error',
            field == 'date_of_birth' ? 'hc-date-of-birth-cell-label' : '',
            formatClass,
        ]"
    >
        <input
            v-if="field != 'created_at' && field != 'updated_at'"
            :type="inputType"
            class="hc-default-cell-input"
            v-model.lazy="value"
            @focus="select"
            :disabled="disabled"
            @keydown.enter="focusNextInput"
            @keydown.up="focusPreviousProspectInput"
            @keydown.down="focusNextProspectInput"
            :placeholder="placeholder"
        />

        <span v-text="label"></span>

        <a
            v-if="field == 'website_url' && value"
            target="_blank"
            :href="value"
            class="fa fa-external-link"
        ></a>

        <div v-if="field == 'date_of_birth'" class="hc-date-of-birth-cell-age">
            <select v-model="age">
                <option v-for="i in 91" :value="i + 9" v-text="i + 9"></option>
            </select>
            <span v-text="age"></span>
        </div>
    </label>
</template>

<style>
.hc-date-of-birth-cell-label {
    white-space: nowrap;
}

.hc-date-of-birth-cell-label > span {
    width: calc(100% - 50px);
    display: inline-block;
    pointer-events: none;
}

.hc-date-of-birth-cell-label > .hc-default-cell-input {
    width: calc(100% - 50px);
    display: inline-block;
    position: absolute;
    height: 100%;
    opacity: 0;
}

.hc-date-of-birth-cell-label > .hc-default-cell-input:focus {
    opacity: 1;
}

.hc-date-of-birth-cell-label
    > .hc-default-cell-input::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}

.hc-date-of-birth-cell-age {
    display: inline-block;
    width: 50px;
    height: 100%;
    border-left: 1px solid #f5f5f5;
    position: relative;
}

.hc-date-of-birth-cell-age > select {
    width: 100%;
    height: 100%;
    border: none;
    background: none;
    cursor: pointer;
}

.hc-date-of-birth-cell-age > span {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 100%;
    background-color: white;
    pointer-events: none;
    line-height: 21px;
    padding-left: 5px;
    padding-right: 20px;
    text-align: right;
}

.hc-date-of-birth-cell-age:hover > span {
    outline: 2px solid #1e88e5;
    z-index: 2;
}

.hc-date-of-birth-cell-age > span:after {
    content: "";
    position: absolute;
    right: 5px;
    top: 8px;
    width: 0;
    height: 0;
    border-radius: 2px;
    border-top: 5px solid #555555;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    pointer-events: none;
}

.hc-date-of-birth-cell-label:hover > .hc-date-of-birth-cell-age {
    right: 0;
    position: absolute;
}
</style>

<script>
import store from "@/store";

import { UPDATE_PROSPECT } from "@/actions/project/prospect";

const hcEmailPattern = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;

export default {
    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },

        /**
         * Field
         */
        field: {
            type: String,
        },

        /**
         * Default value
         */
        defaultValue: {
            type: String,
        },

        /**
         * align
         */
        align: {
            type: String,
            default: "left",
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

    data() {
        return {
            showAges: false,
        };
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
    },

    computed: {
        /**
         * When value is updated
         * Send modification to the API
         */
        value: {
            get() {
                return this.prospect[this.field];
            },

            async set(newValue) {
                // Modification payload
                const update = {
                    id: this.prospect.id,
                    [this.field]: newValue,
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
                const oldValue = this.prospect[this.field];
                try {
                    await store.dispatch(UPDATE_PROSPECT, update);
                } catch (e) {
                    store.commit(UPDATE_PROSPECT, {
                        ...update,
                        [this.field]: oldValue,
                    });
                }
            },
        },

        /**
         * Do not allow user
         * to edit some field
         * or on certain conditions applied to the prospect
         */
        disabled() {
            return (
                // deleted or archived prospect
                this.prospect.deleted_at ||
                this.prospect.processed_at ||
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
                const date = new Date(this.value);

                if (date == "Invalid date") {
                    return this.value;
                }

                return dayjs(date).format("DD/MM/YYYY HH:mm:ss");
            }

            // Date of birth
            if (this.field == "date_of_birth" && this.value) {
                const date = new Date(this.value);

                if (date == "Invalid date") {
                    return this.value;
                }

                return dayjs(date).format("DD/MM/YYYY");
            }

            return this.value ? this.value : this.defaultValue;
        },

        inputType() {
            if (
                this.field == "phone_number" ||
                this.field == "mobile_phone_number"
            ) {
                return "tel";
            }

            if (this.field == "email") {
                return "email";
            }

            if (this.field == "date_of_birth") {
                return "date";
            }

            return "text";
        },

        /**
         * Check if data has good format
         * for its type
         */
        isValid() {
            if (this.field == "email") {
                return !this.value || hcEmailPattern.test(this.value);
            }

            if (this.required && !this.value && !this.defaultValue) {
                return false;
            }

            return true;
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

        /**
         * Get age from date of birth
         */
        age: {
            get() {
                if (this.field != "date_of_birth") {
                    return null;
                }

                if (!this.value) {
                    return null;
                }

                let ageDifMs = new Date() - new Date(this.value);
                let ageDate = new Date(ageDifMs);
                return Math.abs(ageDate.getUTCFullYear() - 1970);
            },
            set(value) {
                let date;

                if (this.value) {
                    date = new Date(this.value);
                    date.setFullYear(new Date().getFullYear());
                } else {
                    date = new Date();
                }

                date.setFullYear(date.getFullYear() - value);

                this.value = dateToString(date).substring(0, 10);
            },
        },
    },
};
</script>
