<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox
            :model-value="isChecked || disabled"
            :disabled="disabled"
            @change="change"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserLabelService from "@/apis/project/user/label";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        label: {
            type: Object,
        },

        disabled: {
            type: Boolean,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addLabelUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeLabelUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addLabelUser() {
            UserLabelService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.label.id]
            );
        },

        removeLabelUser() {
            UserLabelService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.label.id]
            );
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        value() {
            return this.user.id;
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
