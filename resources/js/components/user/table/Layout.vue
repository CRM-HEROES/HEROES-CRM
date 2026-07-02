<template>
    <div id="users-table">
        <div id="users-table-body">
            <user-table />
        </div>
        <card-menus />
        <!--add-column-modal /-->

        <filter-user-calendar-slide />
        <filter-user-category-slide />
        <filter-user-document-slide />
        <filter-user-folder-slide />
        <filter-user-group-slide />
        <filter-user-menu-slide />
        <filter-user-order-action-slide />
        <filter-user-order-step-slide />
        <filter-user-role-slide />
        <filter-user-thread-slide />
        <filter-user-user-slide />

        <manage-user-bulk-calendars-modal />
        <manage-user-bulk-categories-modal />
        <manage-user-bulk-documents-modal />
        <manage-user-bulk-folders-modal />
        <manage-user-bulk-groups-modal />
        <manage-user-bulk-menus-modal />
        <manage-user-bulk-order-actions-modal />
        <manage-user-bulk-order-steps-modal />
        <manage-user-bulk-roles-modal />
        <manage-user-bulk-threads-modal />
        <manage-user-bulk-users-modal />

        <add-column-modal />
        <prospects-table-columns-slide />
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
// import AddColumnModal from "./modals/add-column/Modal.vue";

import FilterUserCalendarSlide from "@/components/user/filters/calendar/Slide.vue";
import FilterUserCategorySlide from "@/components/user/filters/category/Slide.vue";
import FilterUserDocumentSlide from "@/components/user/filters/document/Slide.vue";
import FilterUserFolderSlide from "@/components/user/filters/folder/Slide.vue";
import FilterUserGroupSlide from "@/components/user/filters/group/Slide.vue";
import FilterUserMenuSlide from "@/components/user/filters/menu/Slide.vue";
import FilterUserOrderActionSlide from "@/components/user/filters/order-action/Slide.vue";
import FilterUserOrderStepSlide from "@/components/user/filters/order-step/Slide.vue";
import FilterUserRoleSlide from "@/components/user/filters/role/Slide.vue";
import FilterUserThreadSlide from "@/components/user/filters/thread/Slide.vue";
import FilterUserUserSlide from "@/components/user/filters/user/Slide.vue";

import ManageUserBulkCalendarsModal from "@/components/user/bulk/calendar/Modal.vue";
import ManageUserBulkCategoriesModal from "@/components/user/bulk/category/Modal.vue";
import ManageUserBulkDocumentsModal from "@/components/user/bulk/document/Modal.vue";
import ManageUserBulkFoldersModal from "@/components/user/bulk/folder/Modal.vue";
import ManageUserBulkGroupsModal from "@/components/user/bulk/group/Modal.vue";
import ManageUserBulkMenusModal from "@/components/user/bulk/menu/Modal.vue";
import ManageUserBulkOrderActionsModal from "@/components/user/bulk/order-action/Modal.vue";
import ManageUserBulkOrderStepsModal from "@/components/user/bulk/order-step/Modal.vue";
import ManageUserBulkRolesModal from "@/components/user/bulk/role/Modal.vue";
import ManageUserBulkThreadsModal from "@/components/user/bulk/thread/Modal.vue";
import ManageUserBulkUsersModal from "@/components/user/bulk/user/Modal.vue";
import AddColumnModal from "./modals/add-column/Modal.vue";
import ProspectsTableColumnsSlide from "@/components/prospect/table/slides/add-column/Slide.vue";

export default {
    components: {
        CardMenus,
        UserTable,
        AddColumnModal,
        ProspectsTableColumnsSlide,

        FilterUserCalendarSlide,
        FilterUserCategorySlide,
        FilterUserDocumentSlide,
        FilterUserFolderSlide,
        FilterUserGroupSlide,
        FilterUserMenuSlide,
        FilterUserOrderActionSlide,
        FilterUserOrderStepSlide,
        FilterUserRoleSlide,
        FilterUserThreadSlide,
        FilterUserUserSlide,

        ManageUserBulkCalendarsModal,
        ManageUserBulkCategoriesModal,
        ManageUserBulkDocumentsModal,
        ManageUserBulkFoldersModal,
        ManageUserBulkGroupsModal,
        ManageUserBulkMenusModal,
        ManageUserBulkOrderActionsModal,
        ManageUserBulkOrderStepsModal,
        ManageUserBulkRolesModal,
        ManageUserBulkThreadsModal,
        ManageUserBulkUsersModal,
    },
};
</script>
