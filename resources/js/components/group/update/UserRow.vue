<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserGroupService from "@/apis/project/user/group";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        group: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addGroupUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeGroupUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addGroupUser() {
            UserGroupService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.group.id]
            );
        },

        removeGroupUser() {
            UserGroupService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.group.id]
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
