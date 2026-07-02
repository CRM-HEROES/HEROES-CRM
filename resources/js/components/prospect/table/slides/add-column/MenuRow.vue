<template>
    <label class="hc-setting-prospects-table-menu-row" v-tooltip="menu.name">
        <input type="checkbox" :checked="isChecked" @change="change" />
        <i :class="menu.icon"></i>
    </label>
</template>

<style>
.hc-setting-prospects-table-menu-row {
    font-size: 11px;
    height: 36px;
    width: 36px;
    cursor: pointer;
}

.hc-setting-prospects-table-menu-row > input {
    display: none;
}

.hc-setting-prospects-table-menu-row > i {
    display: inline-block;
    width: 100%;
    height: 100%;
    line-height: 36px;
    text-align: center;
    border-radius: 10px;
    background-color: white;
    font-size: 15px;
}

.hc-setting-prospects-table-menu-row > input:not(:checked) + i {
    color: #cccccc !important;
}
</style>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        menu: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        value() {
            return this.menu.key;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },
    },
};
</script>
