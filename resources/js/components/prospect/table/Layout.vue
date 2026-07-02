<template>
    <div id="prospects-table">
        <div id="prospects-table-body">
            <prospect-menus />
            <prospect-table />
        </div>

        <prospect-table-footer />
        <add-column-slide />
        <ocr-modal />
    </div>
</template>

<style>
#prospects-table {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    border-top: 1px solid #c0c0c0;
}
#prospects-table-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: hidden;
}
</style>

<script>
import ProspectMenus from "./menu/Menus.vue";
import ProspectTableFooter from "./CardMenus.vue";
import ProspectTable from "./Table.vue";
import AddColumnSlide from "./slides/add-column/Slide.vue";
import OcrModal from "@/components/ocr/Modal.vue";

export default {
    components: {
        ProspectMenus,
        ProspectTableFooter,
        ProspectTable,
        AddColumnSlide,
        OcrModal,
    },
};
</script>
