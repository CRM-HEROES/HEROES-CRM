<template>
    <component
        :is="tag"
        :style="style"
        :class="['hc-table-footer-cell', isFormulable ? 'formulable' : '']"
    >
        <template v-if="isFormulable">
            <select
                v-model="formula"
                :id="'hc-table-footer-cell-' + column.id"
                @change="$event.target.blur()"
            >
                <option :value="''">Formule ...</option>
                <option
                    v-for="formula in formulas"
                    :key="formula.key"
                    :value="formula.key"
                    v-text="formula.name"
                ></option>
            </select>
            <label
                v-text="value"
                :for="'hc-table-footer-cell-' + column.id"
            ></label>
        </template>
    </component>
</template>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        /**
         * HTML tag
         * Default <td>
         * When the column is fixed
         * we use <div> as tag
         */
        tag: {
            type: String,
            default: "td",
        },

        /**
         * Column
         */
        column: {
            type: Array,
        },
    },

    data() {
        return {
            formula: "",
            formulas: [
                {
                    key: "sum",
                    name: "Somme",
                    formula: (values) =>
                        values
                            .reduce(
                                (carry, value) =>
                                    carry +
                                    (isNaN(parseFloat(value))
                                        ? 0
                                        : parseFloat(value)),
                                0
                            )
                            .toFixed(2),
                },
                {
                    key: "average",
                    name: "Moyenne",
                    formula: (values) => {
                        values = values.filter(
                            (value) => !isNaN(parseFloat(value))
                        );

                        return (
                            values.reduce(
                                (carry, value) => carry + parseFloat(value),
                                0
                            ) / values.length
                        ).toFixed(2);
                    },
                },
            ],
        };
    },

    computed: {
        ...mapGetters(["fields", "orders", "commissions"]),

        /**
         * Define the width of the column
         */
        style() {
            const width = this.column.width ? this.column.width : 120;

            return {
                maxWidth: `${width}px`,
                minWidth: `${width}px`,
            };
        },

        isFormulable() {
            return this.values !== null;
        },

        values() {
            if (
                this.column.category == "default" &&
                (this.column.id == "tax_value" ||
                    this.column.id == "total_including_tax" ||
                    this.column.id == "total_excluding_tax")
            ) {
                return this.orders
                    .map((order) =>
                        order[this.column.id]
                            ? order[this.column.id]
                            : undefined
                    )
                    .filter((value) => value !== undefined);
            } else if (this.column.category == "total_commissions") {
                return this.orders
                    .map((order) =>
                        order.actions
                            .map((action) => {
                                const commissions = this.commissions.filter(
                                    (commission) =>
                                        commission.action_id == action.id &&
                                        commission.user_id ==
                                            action.pivot.user_id &&
                                        order.products.find(
                                            (product) =>
                                                product.id ==
                                                commission.product_id
                                        )
                                );

                                return commissions.reduce(
                                    (carry, commission) =>
                                        carry + commission.absolute_value,
                                    0
                                );
                            })
                            .reduce(
                                (carry, commission) => carry + commission,
                                0
                            )
                    )
                    .filter((value) => value !== undefined);
            }

            return null;
        },

        value() {
            const formula = this.formulas.find(
                (formula) => formula.key == this.formula
            );
            return formula ? formula.formula(this.values) : "";
        },
    },
};
</script>
