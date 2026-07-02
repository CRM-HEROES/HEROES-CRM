<template>
    <label :class="['hc-default-cell-label', isValid ? '' : 'error']">
        <span v-text="label"></span>
    </label>
</template>

<script>
export default {
    props: {
        /**
         * Order
         */
        order: {
            type: Object,
        },

        /**
         * Field
         */
        field: {
            type: String,
        },
    },

    computed: {
        /**
         * When value is updated
         * Send modification to the API
         */
        value() {
            if (
                this.field == "tax_value" ||
                this.field == "total_including_tax" ||
                this.field == "total_excluding_tax"
            ) {
                return parseFloat(this.order[this.field]).toFixed(2);
            }

            return this.order[this.field];
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

            if (
                this.field == "tax_value" ||
                this.field == "total_including_tax" ||
                this.field == "total_excluding_tax"
            ) {
                return this.value + " " + this.order.currency;
            }

            return this.value;
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

            return true;
        },
    },
};
</script>
