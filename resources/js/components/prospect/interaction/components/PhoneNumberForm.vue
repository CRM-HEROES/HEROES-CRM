<template>
    <form
        class="hc-flex-column"
        :style="formStyle"
        @submit.prevent="$emit('submit')"
    >
        <item @click="$emit('back')" class="bordered">
            <icon class="fa fa-caret-left" />
            <div class="hc-item-main-content" v-text="$t(titleKey)"></div>
        </item>
        <item-list padding="12px" style="height: auto">
            <v-field :label="$t('field.prospect.phone_number')"
                ><input type="tel" v-model.lazy="value"
            /></v-field>
        </item-list>
        <buttons>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading :loading="loading" />
    </form>
</template>

<script>
export default {
    props: {
        titleKey: {
            type: String,
            required: true,
        },

        modelValue: {
            type: [String, Number],
            default: null,
        },

        loading: {
            type: Boolean,
            default: false,
        },

        relative: {
            type: Boolean,
            default: false,
        },
    },

    emits: ["back", "submit", "update:modelValue"],

    computed: {
        value: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit("update:modelValue", value);
            },
        },

        formStyle() {
            return this.relative
                ? "height: 100%; position: relative"
                : "height: 100%";
        },
    },
};
</script>
