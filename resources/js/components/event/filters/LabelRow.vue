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
            const newValue = { ...this.modelValue };
            const category = this.label.category_id;
            const selected = Array.isArray(newValue[category]) ? [...newValue[category]] : [];
            const index = selected.findIndex((id) => String(id) === String(this.value));
            if (event.target.checked && index === -1) selected.push(this.value);
            if (!event.target.checked && index !== -1) selected.splice(index, 1);

            if (selected.length) newValue[category] = selected;
            else delete newValue[category];
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
                Array.isArray(this.modelValue?.[this.label.category_id]) &&
                this.modelValue[this.label.category_id].some((id) => String(id) === String(this.value))
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
