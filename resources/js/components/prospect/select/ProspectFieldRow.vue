<template>
    <item class="hc-prospect-event-field-row">
        <icon class="fa fa-columns" />
        <div class="hc-prospect-event-field-name" v-text="field.name"></div>
        <div class="hc-item-main-content hc-prospect-event-field-input">
            <input
                v-if="
                    field.slug == 'phone_number' ||
                    field.slug == 'mobile_phone_number' ||
                    field.type == 'tel'
                "
                type="tel"
                v-model.lazy="value"
                :placeholder="field.name + ' ...'"
            />
            <input
                v-else-if="field.slug == 'email' || field.type == 'email'"
                type="email"
                v-model.lazy="value"
                :placeholder="field.name + ' ...'"
            />
            <google-map-input
                v-else-if="field.slug == 'street'"
                v-model="value"
                @changed="updateAddress"
            />
            <google-map-input
                v-else-if="field.type == 'address'"
                v-model="value"
            />
            <input v-else-if="type == 'date'" type="date" v-model="value" />
            <input
                v-else-if="type == 'datetime'"
                type="datetime-local"
                v-model="value"
            />
            <textarea
                v-else-if="type == 'long_text'"
                v-model.lazy="value"
            ></textarea>
            <input
                v-else
                type="text"
                v-model.lazy="value"
                :placeholder="field.name + ' ...'"
            />
        </div>
    </item>
</template>

<style>
.hc-prospect-event-field-name {
    width: 100px;
}

.hc-prospect-event-field-input {
    height: 100%;
}

.hc-prospect-event-field-input > input {
    height: 100%;
    padding: 0 15px;
}
</style>

<script>
export default {
    props: {
        /**
         *
         */
        modelValue: {
            type: String,
            default: "",
        },

        field: {
            type: Object,
        },
    },

    data() {
        return {
            value: this.modelValue,
        };
    },

    methods: {
        /**
         *
         * @param {*} address
         */
        updateAddress(address) {
            this.$emit("update-address", address);
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
        value(newValue) {
            this.$emit("update:modelValue", newValue);
        },
    },
};
</script>
