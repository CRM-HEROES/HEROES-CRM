<template>
    <item tag="label">
        <icon class="fa fa-person-digging" :style="style" />
        <div class="hc-item-main-content" v-text="action.name"></div>
        <icon
            v-if="can('all.project.order.action.update')"
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
import { SET_ORDER_ACTION } from "@/actions/project/order/action";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        action: {
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
            store.commit(OPEN_MODAL, "order-action-update");
            store.commit(SET_ORDER_ACTION, this.action);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        value() {
            return this.action.id;
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
                color: this.action.bgcolor,
            };
        },
    },
};
</script>
