<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import ProspectFileService from "@/apis/project/prospect/file.js";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        file: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addFileUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeFileUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addFileUser() {
            ProspectFileService.bulkUpdate(
                this.project.slug,
                this.prospect.id,
                this.folder.id,
                [this.user.id],
                [this.file.id]
            );
        },

        removeFileUser() {
            ProspectFileService.bulkDestroy(
                this.project.slug,
                this.prospect.id,
                this.folder.id,
                [this.user.id],
                [this.file.id]
            );
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "folder"]),

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
