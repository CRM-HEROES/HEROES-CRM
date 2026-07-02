<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserOrderStepService from "@/apis/project/user/order-step";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        orderStep: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addOrderStepUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeOrderStepUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addOrderStepUser() {
            UserOrderStepService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.orderStep.id]
            );
        },

        removeOrderStepUser() {
            UserOrderStepService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.orderStep.id]
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
