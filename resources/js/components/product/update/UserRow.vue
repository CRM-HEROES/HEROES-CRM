<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import UserProductService from "@/apis/project/user/product";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
            type: Object,
        },

        product: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
                this.addProductUser();
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
                this.removeProductUser();
            }
            this.$emit("update:modelValue", newValue);
        },

        addProductUser() {
            UserProductService.bulkUpdate(
                this.project.slug,
                [this.user.id],
                [this.product.id]
            );
        },

        removeProductUser() {
            UserProductService.bulkDestroy(
                this.project.slug,
                [this.user.id],
                [this.product.id]
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
