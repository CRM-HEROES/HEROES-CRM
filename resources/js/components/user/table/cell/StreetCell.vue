<template>
    <google-map-input
        class="hc-default-cell-input"
        v-model="value"
        :disabled="disabled"
        :placeholder="placeholder"
    />
</template>

<script>
export default {
    props: {
        /**
         * Project
         */
        user: {
            type: Object,
        },

        /**
         * Field name (street)
         */
        field: {
            type: String,
        },

        /**
         * disabled
         */
        disabled: {
            type: Boolean,
            default: false,
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
            updatingAddress: false,
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
    },

    computed: {
        /**
         * When value is updated
         * Send modification to the API
         */
        value() {
            return this.user[this.field];
        },
    },
};
</script>
