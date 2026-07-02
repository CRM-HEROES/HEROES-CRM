<template>
    <item
        tag="label"
        v-tooltip="label.name"
        class="hc-prospects-table-filter-label"
    >
        <icon class="fa fa-tag" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <icon
            v-if="can('all.project.category.label.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
            :size="30"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-prospects-table-filter-label {
    height: 34px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    props: {
        modelValue: {
            type: Object,
        },

        label: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = { ...this.modelValue };
            if (isChecked) {
                if (!Array.isArray(newValue[this.label.category_id])) {
                    newValue[this.label.category_id] = [];
                }
                newValue[this.label.category_id].push(this.value);
            } else {
                if (Array.isArray(newValue[this.label.category_id])) {
                    newValue[this.label.category_id].splice(
                        newValue[this.label.category_id].indexOf(this.value),
                        1
                    );
                }

                if (newValue[this.label.category_id].length == 0) {
                    delete newValue[this.label.category_id];
                }
            }
            this.$emit("update:modelValue", newValue);
        },

        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["can"]),

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
            return (
                Array.isArray(this.modelValue[this.label.category_id]) &&
                this.modelValue[this.label.category_id].includes(this.value)
            );
        },

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },
    },
};
</script>
