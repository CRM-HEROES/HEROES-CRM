<template>
    <item tag="label">
        <icon class="fa fa-step-forward" :style="style" />
        <div class="hc-item-main-content" v-text="step.name"></div>
        <icon
            v-if="can('all.project.order.status.update')"
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
import { SET_ORDER_STEP } from "@/actions/project/order/step";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        step: {
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
            store.commit(OPEN_MODAL, "order-step-update");
            store.commit(SET_ORDER_STEP, this.step);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        value() {
            return this.step.id;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },

        /**
         *
         */
        style() {
            return {
                color: this.step.bgcolor,
            };
        },
    },
};
</script>
