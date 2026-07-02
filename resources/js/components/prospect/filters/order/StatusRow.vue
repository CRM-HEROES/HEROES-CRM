<template>
    <item tag="label">
        <icon class="fa fa-battery-full" :style="style" />
        <div class="hc-item-main-content" v-text="status.name"></div>
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
import { SET_ORDER_STATUS } from "@/actions/project/order/status";

export default {
    props: {
        modelValue: {
            type: Array,
        },

        status: {
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
            store.commit(OPEN_MODAL, "order-status-update");
            store.commit(SET_ORDER_STATUS, this.status);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        value() {
            return this.status.id;
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
                color: this.status.bgcolor,
            };
        },
    },
};
</script>
