<template>
    <item class="hc-order-field-row">
        <icon class="fa fa-info" />
        <div class="hc-order-field-name" v-text="field.name"></div>
        <div class="hc-item-main-content hc-order-field-input">
            <input
                v-if="field.type == 'tel'"
                type="tel"
                v-model.lazy="value"
                :placeholder="field.name + ' ...'"
            />
            <input
                v-else-if="field.type == 'email'"
                type="email"
                v-model.lazy="value"
                :placeholder="field.name + ' ...'"
            />
            <google-map-input
                v-else-if="field.type == 'address'"
                v-model="value"
            />
            <input
                v-else-if="field.slug == 'date' || field.type == 'date'"
                type="date"
                v-model="value"
                :disabled="disabled"
            />
            <input
                v-else-if="type == 'datetime'"
                type="datetime-local"
                v-model.lazy="value"
            />
            <textarea
                v-else-if="
                    field.slug == 'description' || field.type == 'long_text'
                "
                v-model.lazy="value"
                :disabled="disabled"
            ></textarea>
            <input
                v-else
                type="text"
                v-model.lazy="value"
                :placeholder="field.name + ' ...'"
                :disabled="disabled"
            />
        </div>
        <icon
            tag="a"
            class="fa fa-times icon-red"
            @click.prevent="removeField"
        />
        <loading :loading="removingField" />
    </item>
</template>

<style>
.hc-order-field-name {
    width: 100px;
}

.hc-order-field-input {
    height: 100%;
}

.hc-order-field-input > input,
.hc-order-field-input > textarea {
    width: 100%;
    height: 100%;
    padding: 0 15px;
}
</style>

<script>
import store from "@/store";

import { REMOVE_FIELD } from "@/actions/project/field";

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
            removingField: false,
        };
    },

    methods: {
        removeField() {
            hcConfirm(
                this.$t("prospect.order.tabs.fields.remove_confirm"),
                async () => {
                    this.removingField = true;

                    try {
                        await store.dispatch(REMOVE_FIELD, this.field.id);
                    } finally {
                        this.removingField = false;
                    }
                }
            );
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

    computed: {
        disabled() {
            return (
                !this.field.meta &&
                this.field.slug != "name" &&
                this.field.slug != "description"
            );
        },
    },
};
</script>
