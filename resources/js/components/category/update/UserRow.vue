<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserCategoryService from "@/apis/project/user/category";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
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
                this.addCategoryUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeCategoryUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addCategoryUser() {
            UserCategoryService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.category.id]
            );
        },

        removeCategoryUser() {
            UserCategoryService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.category.id]
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
