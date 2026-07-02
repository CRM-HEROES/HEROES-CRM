<template>
    <bloc icon="fa fa-users" :name="$t('project.profile.blocs.products')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addProduct" />
            <icon v-if="products.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="products.length > 0"
            >
                <product-row
                    v-for="(product, i) in products"
                    :key="product.id"
                    :product="product"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import ProductRow from "./ProductRow.vue";

export default {
    components: {
        Bloc,
        ProductRow,
    },

    methods: {
        /**
         * Add product
         * See: @/components/product/add/Modal.vue
         */
        addProduct() {
            store.commit(OPEN_MODAL, "product-add");
        },
    },

    computed: {
        ...mapGetters(["project", "products"]),
    },
};
</script>
