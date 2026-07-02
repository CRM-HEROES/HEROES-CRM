<template>
    <item tag="label" v-tooltip="folder.name">
        <icon class="fa fa-folder" :style="style" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        folder: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push({
                    id: this.folder.id,
                    name: this.folder.name,
                    fillColor: this.folder.bgcolor,
                });
            } else {
                newValue.splice(this.index, 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        value() {
            return this.folder.id;
        },

        /**
         *
         */
        index() {
            return this.modelValue.findIndex((v) => v.id == this.value);
        },

        /**
         *
         */
        isChecked() {
            return this.index >= 0;
        },
    },
};
</script>
