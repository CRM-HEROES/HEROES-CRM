<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserMenuService from "@/apis/project/user/menu";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        menu: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addMenuUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeMenuUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addMenuUser() {
            UserMenuService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.menu.id]
            );
        },

        removeMenuUser() {
            UserMenuService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.menu.id]
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
