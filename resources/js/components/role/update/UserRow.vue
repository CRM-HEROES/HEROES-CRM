<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserRoleService from "@/apis/project/user/role";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        role: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addRoleUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeRoleUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addRoleUser() {
            UserRoleService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.role.id]
            );
        },

        removeRoleUser() {
            UserRoleService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.role.id]
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
