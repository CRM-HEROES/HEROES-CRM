<template>
    <item tag="label">
        <icon class="fa fa-gift" />
        <div class="hc-item-main-content" v-text="product.name"></div>
        <icon
            v-if="can('all.project.order.product.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_PRODUCT } from "@/actions/project/product";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        product: {
            type: Object,
        },
    },

    methods: {
        change(order) {
            let isChecked = order.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },

        /**
         *
         */
        edit() {
            store.commit(OPEN_MODAL, "product-update");
            store.commit(SET_PRODUCT, this.product);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        value() {
            return this.product.id;
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
