<template>
    <item tag="label" v-tooltip="calendar.name">
        <icon class="fa fa-calendar" :style="style" />
        <div class="hc-item-main-content" v-text="calendar.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        calendar: {
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
                    id: this.calendar.id,
                    name: this.calendar.name,
                    fillColor: this.calendar.bgcolor,
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
            return this.calendar.id;
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
