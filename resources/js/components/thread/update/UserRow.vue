<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserThreadService from "@/apis/project/user/thread";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        thread: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addThreadUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeThreadUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addThreadUser() {
            UserThreadService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.thread.id]
            );
        },

        removeThreadUser() {
            UserThreadService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.thread.id]
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
