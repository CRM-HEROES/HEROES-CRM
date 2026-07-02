<template>
    <div id="users-table">
        <div id="users-table-body">
            <user-table />
        </div>
        <card-menus />
        <user-project-slide />

        <add-column-modal />
        <user-filter-project-slide />
    </div>
</template>

<style>
#users-table {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    border-top: 1px solid #c0c0c0;
}
#users-table-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: hidden;
}
</style>

<script>
import CardMenus from "./CardMenus.vue";
import UserTable from "./Table.vue";
import UserProjectSlide from "../project/Slide";
import UserFilterProjectSlide from "../filters/project/Slide";
import AddColumnModal from "./modals/add-column/Modal.vue";

export default {
    components: {
        CardMenus,
        UserTable,
        UserProjectSlide,
        UserFilterProjectSlide,
        AddColumnModal,
    },
};
</script>
