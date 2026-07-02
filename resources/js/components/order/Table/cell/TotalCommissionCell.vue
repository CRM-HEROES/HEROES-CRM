<template>
    <label
        :class="[
            'hc-default-cell-label',
            'hc-default-cell-order-total-commission',
            isValid ? '' : 'error',
        ]"
    >
        <span v-text="label"></span>
    </label>
</template>

<style>
.hc-default-cell-order-total-commission {
    text-align: right;
    font-weight: 600;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        /**
         * Order
         */
        order: {
            type: Object,
        },

        /**
         * Field
         */
        field: {
            type: String,
        },
    },

    computed: {
        ...mapGetters(["commissions", "projectUserSetting"]),

        /**
         *
         */
        decimal() {
            return this.projectUserSetting("orders-table.decimal", 2);
        },

        /**
         */
        label() {
            const total = this.order.actions
                .map((action) => {
                    const commissions = this.commissions.filter(
                        (commission) =>
                            commission.action_id == action.id &&
                            commission.user_id == action.pivot.user_id &&
                            this.order.products.find(
                                (product) => product.id == commission.product_id
                            )
                    );

                    return commissions.reduce(
                        (carry, commission) =>
                            carry + commission.absolute_value,
                        0
                    );
                })
                .reduce((carry, commission) => carry + commission, 0)
                .toFixed(parseInt(this.decimal));

            return total + " " + this.order.currency;
        },

        inputType() {
            if (
                this.field == "phone_number" ||
                this.field == "mobile_phone_number"
            ) {
                return "tel";
            }

            if (this.field == "email") {
                return "email";
            }

            return "text";
        },

        /**
         * Check if data has good format
         * for its type
         */
        isValid() {
            if (this.field == "email") {
                return !this.value || hcEmailPattern.test(this.value);
            }

            return true;
        },
    },
};
</script>
