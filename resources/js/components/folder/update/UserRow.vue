<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserFolderService from "@/apis/project/user/folder";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        folder: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addFolderUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeFolderUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addFolderUser() {
            UserFolderService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.folder.id]
            );
        },

        removeFolderUser() {
            UserFolderService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.folder.id]
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
