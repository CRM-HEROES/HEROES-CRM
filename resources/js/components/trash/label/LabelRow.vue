<template>
    <item class="hc-trash-label" tag="label">
        <icon class="fa fa-tag" :color="label.bgcolor" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-flex-row">
                <span class="hc-trash-label-name" v-text="label.name"></span>
                <span
                    class="hc-trash-label-category"
                    v-text="
                        label.category ? ' (' + label.category.name + ')' : ''
                    "
                    :style="{
                        color: label.category.color,
                        backgroundColor: label.category.bgcolor,
                    }"
                ></span>
            </div>
            <span class="hc-trash-prospect-date" v-text="date"></span>
        </div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-trash-label {
    padding: 2px 0 !important;
}

.hc-trash-label-category {
    font-size: 11px;
    line-height: 20px;
    padding: 0 3px;
    border-radius: 2px;
    display: inline-block;
}

.hc-trash-label-name {
    color: #000000;
    white-space: normal;
    flex: 1;
}

.hc-trash-label-date {
    color: #777777;
    font-size: 11px;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        label: {
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
            return "Supprimé " + dayjs(this.label.deleted_at).fromNow();
        },

        /**
         *
         */
        value() {
            return this.label.id;
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
