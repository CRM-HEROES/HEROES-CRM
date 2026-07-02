<template>
    <div id="orders-table">
        <div id="orders-table-body">
            <order-table />
        </div>

        <order-table-footer />
        <add-column-modal />

        <filter-order-action-slide />
        <filter-order-product-slide />
        <filter-order-step-slide />
        <filter-order-status-slide />
    </div>
</template>

<style>
#orders-table {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    border-top: 1px solid #c0c0c0;
}
#orders-table-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: hidden;
}
</style>

<script>
import OrderTableFooter from "./CardMenus.vue";
import OrderTable from "./Table.vue";
import AddColumnModal from "./modals/add-column/Modal.vue";

import FilterOrderActionSlide from "@/components/order/filters/action/Slide.vue";
import FilterOrderProductSlide from "@/components/order/filters/product/Slide.vue";
import FilterOrderStatusSlide from "@/components/order/filters/status/Slide.vue";
import FilterOrderStepSlide from "@/components/order/filters/step/Slide.vue";

export default {
    components: {
        OrderTableFooter,
        OrderTable,
        AddColumnModal,

        FilterOrderActionSlide,
        FilterOrderProductSlide,
        FilterOrderStatusSlide,
        FilterOrderStepSlide,
    },
};
</script>
