<template>
    <item class="hc-trash-category" tag="label">
        <icon class="fa fa-tags" :color="category.bgcolor" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-trash-category-name" v-text="category.name"></div>
            <span class="hc-trash-category-date" v-text="date"></span>
        </div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-trash-category {
    padding: 2px 0 !important;
}

.hc-trash-category-name {
    color: #000000;
    white-space: normal;
}

.hc-trash-category-date {
    color: #777777;
    font-size: 11px;
}
</style>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        category: {
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
        date() {
            return "Supprimé " + dayjs(this.category.deleted_at).fromNow();
        },

        /**
         *
         */
        value() {
            return this.category.id;
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
