<template>
    <item-list
        style="width: 100%; border-bottom: 1px solid #ddd; position: relative"
    >
        <label class="hc-campaign-item-value-row">
            <div
                class="hc-campaign-item-value-row-label"
                v-text="'Produit'"
            ></div>
            <div class="hc-campaign-item-value-row-select">
                <span v-text="product.name"></span>
            </div>
        </label>
        <label class="hc-campaign-item-value-row">
            <div
                class="hc-campaign-item-value-row-label"
                v-text="'Quantité'"
            ></div>
            <div class="hc-campaign-item-value-row-select">
                <input v-model.lazy="quantity" />
            </div>
        </label>

        <icon
            tag="a"
            class="fa fa-times icon-red"
            size="28"
            icon-size="14"
            style="
                position: absolute;
                right: 0;
                top: 0;
                background-color: white;
            "
            @click.prevent="remove"
        />
    </item-list>
</template>

<style></style>

<script>
export default {
    props: {
        value: {
            type: Object,
        },
        product: {
            type: Object,
        },
    },

    data() {
        return {
            currentProduct: this.value,
        };
    },

    watch: {
        value(newValue) {
            this.currentProduct = newValue;
        },
    },

    methods: {
        remove() {
            this.$emit("remove", this.product.id);
        },
    },

    computed: {
        ...Object.fromEntries(
            ["quantity"].map((key) => [
                key,
                {
                    // Get selected field CSS value
                    get() {
                        return this.currentProduct[key]
                            ? this.currentProduct[key]
                            : "";
                    },
                    // Set selected field CSS value
                    set(value) {
                        this.currentProduct[key] = value;
                        this.$emit(
                            "updated",
                            this.product.id,
                            this.currentProduct
                        );
                    },
                },
            ])
        ),
    },
};
</script>
