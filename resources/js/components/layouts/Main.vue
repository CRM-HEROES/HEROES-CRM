<template>
    <div id="hc-main-layout" @keydown="keydown">
        <layout-header />
        <div id="hc-main-layout-body">
            <router-view></router-view>
        </div>

        <!-- To do: Move it in global layout -->

        <setting-slide />
        <add-global-user-modal v-if="can('all')" />

        <template v-if="project">
            <event-reminder />

            <manage-pipeline-labels-slide />

            <manage-prospect-documents-slide />
            <manage-prospect-events-slide v-if="canEvent" />
            <manage-prospect-files-slide v-if="canFile" />
            <manage-prospect-groups-slide v-if="canGroup" />
            <manage-prospect-interactions-slide
                v-if="can('all.prospect.interaction.view')"
            />
            <manage-prospect-labels-slide />
            <manage-prospect-logs-slide />
            <manage-prospect-messages-slide v-if="canMessage" />
            <manage-prospect-orders-slide
                v-if="can('all.prospect.order.view')"
            />
            <manage-prospect-revisions-slide />
            <manage-prospect-sms-slide v-if="can('all.prospect.sms.view')" />
            <manage-prospect-questionnaires-slide />
            <manage-prospect-users-slide v-if="canUser" />

            <manage-user-actions-slide />
            <manage-user-calendars-slide />
            <manage-user-categories-slide />
            <manage-user-documents-slide />
            <manage-user-duplicate-slide />
            <manage-user-files-slide />
            <manage-user-folders-slide />
            <manage-user-groups-slide />
            <manage-user-menus-slide />
            <manage-user-order-actions-slide />
            <manage-user-order-steps-slide />
            <manage-user-questionnaires-slide />
            <manage-user-permissions-slide />
            <manage-user-roles-slide />
            <manage-user-threads-slide />
            <manage-user-users-slide />
            <manage-user-vehicles-slide />
            <manage-users-slide />

            <manage-role-calendars-slide />
            <manage-role-categories-slide />
            <manage-role-documents-slide />
            <manage-role-duplicate-slide />
            <manage-role-folders-slide />
            <manage-role-order-actions-slide />
            <manage-role-order-steps-slide />
            <manage-role-permissions-slide />
            <manage-role-questionnaires-slide />
            <manage-role-threads-slide />
            <manage-role-users-slide />
            <manage-roles-slide />

            <add-calendar-modal v-if="can('all.project.calendar.add')" />
            <add-category-modal v-if="can('all.project.category.add')" />
            <add-event-description-template-modal
                v-if="can('all.project.event-description-template.add')"
            />
            <add-field-modal v-if="can('all.project.field.add')" />
            <add-document-modal v-if="can('all.project.document.add')" />
            <add-folder-modal v-if="can('all.project.folder.add')" />
            <add-group-modal v-if="can('all.project.group.add')" />
            <add-import-modal v-if="can('all.prospect.import')" />
            <add-label-modal v-if="can('all.project.category.label.add')" />
            <add-menu-modal />
            <add-message-template-modal
                v-if="can('all.project.message-template.add')"
            />
            <add-order-action-modal
                v-if="can('all.project.order.action.add')"
            />
            <add-order-status-modal
                v-if="can('all.project.order.status.add')"
            />
            <add-order-step-modal v-if="can('all.project.order.step.add')" />
            <add-pipeline-modal />
            <add-product-modal v-if="can('all.project.order.product.add')" />
            <add-prospect-link-modal />
            <add-questionnaire-modal
                v-if="can('all.project.questionnaire.add')"
            />
            <add-questionnaire-section-modal
                v-if="can('all.project.questionnaire.add')"
            />
            <add-questionnaire-question-modal
                v-if="can('all.project.questionnaire.add')"
            />
            <add-questionnaire-option-modal
                v-if="can('all.project.questionnaire.add')"
            />
            <add-role-modal v-if="can('all.project.role.add')" />
            <add-sms-template-modal
                v-if="can('all.project.sms-template.add')"
            />
            <add-thread-modal v-if="can('all.project.thread.add')" />
            <add-user-modal v-if="can('all.project.user.add')" />
            <add-vehicle-modal v-if="can('all.project.vehicle.add')" />

            <add-user-vehicle-modal />
            <update-user-vehicle-modal />

            <category-slide />
            <document-slide />
            <duplicate-slide />
            <import-slide v-if="can('all.prospect.import')" />
            <questionnaire-slide />

            <update-calendar-modal v-if="can('all.project.calendar.update')" />
            <update-category-modal v-if="can('all.project.calendar.update')" />
            <update-commission-modal
                v-if="can('all.project.user.commission.add')"
            />
            <update-document-modal v-if="can('all.project.calendar.update')" />
            <update-event-description-template-modal
                v-if="can('all.project.event-description-template.update')"
            />
            <update-field-modal v-if="can('all.project.field.update')" />
            <update-folder-modal v-if="can('all.project.folder.update')" />
            <update-file-modal />
            <update-import-modal />
            <update-group-modal v-if="can('all.project.group.update')" />
            <update-label-modal
                v-if="can('all.project.category.label.update')"
            />
            <update-menu-modal />
            <update-message-template-modal
                v-if="can('all.project.message-template.update')"
            />
            <update-order-action-modal
                v-if="can('all.project.order.action.update')"
            />
            <update-order-status-modal
                v-if="can('all.project.order.status.update')"
            />
            <update-order-step-modal
                v-if="can('all.project.order.step.update')"
            />
            <update-pipeline-modal />
            <update-product-modal
                v-if="can('all.project.order.product.update')"
            />
            <update-prospect-link-modal />
            <update-questionnaire-modal
                v-if="can('all.project.questionnaire.update')"
            />
            <update-questionnaire-section-modal
                v-if="can('all.project.questionnaire.update')"
            />
            <update-questionnaire-question-modal
                v-if="can('all.project.questionnaire.update')"
            />
            <update-questionnaire-option-modal
                v-if="can('all.project.questionnaire.update')"
            />
            <update-role-modal v-if="can('all.project.role.update')" />
            <update-sms-template-modal
                v-if="can('all.project.sms-template.update')"
            />
            <update-thread-modal v-if="can('all.project.thread.update')" />
            <export-modal v-if="can('all.prospect.export')" />

            <filter-prospect-event-slide />
            <filter-prospect-field-slide />
            <filter-prospect-file-slide />
            <filter-prospect-created-slide />
            <filter-prospect-creator-slide />
            <filter-prospect-group-slide />
            <filter-prospect-import-slide />
            <filter-prospect-interaction-slide />
            <filter-prospect-label-slide />
            <filter-prospect-message-slide />
            <filter-prospect-thread-slide />
            <filter-prospect-order-slide />
            <filter-prospect-pipedrive-account-slide />
            <filter-prospect-sms-slide />
            <filter-prospect-user-slide />
            <filter-prospect-all-slide />

            <manage-prospect-bulk-labels-modal />
            <manage-prospect-bulk-users-modal />
            <manage-prospect-bulk-groups-modal />
            <manage-prospect-bulk-field-modal />
            <manage-prospect-bulk-project-modal />

            <protected-content>
                <setting-email-modal />
                <setting-smsbox-modal />
                <setting-ultramsg-modal />
                <setting-m-target-modal />
                <setting-pipedrive-modal />
                <setting-ringover-modal />
            </protected-content>

            <menu-icon-slide />
            <trash-slide />

            <api-pappers-slide />
        </template>

        <layout-footer v-if="impersonating" />

        <manage-user-sessions-slide />
        <manage-locale-slide />
        <manage-tutorial-slide />
        <confirm />
        <flash />
        <tooltip />
        <tutorial-tooltip />
        <loading :loading="changing" />
    </div>
</template>

<style>
#hc-main-layout {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
}

#hc-main-layout-body {
    flex: 1;
    width: 100%;
    overflow: hidden;
    position: relative;
}
</style>

<script>
import "dayjs/locale/en";
import "dayjs/locale/fr";

import { mapActions, mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_PERMISSIONS } from "@/actions/permission";
import { CLOSE_MODALS } from "@/actions/modal";
import { CLOSE_SLIDES, OPEN_SLIDE } from "@/actions/slide";

import EventReminder from "@/components/event/Reminder.vue";

// Slides and Modals
import SettingSlide from "./setting/Slide.vue";

import AddGlobalUserModal from "@/components/global/user/add/Modal.vue";

import ManagePipelineLabelsSlide from "@/components/pipeline/label/Slide.vue";

import ManageProspectDocumentsSlide from "@/components/prospect/document/Slide.vue";
import ManageProspectEventsSlide from "@/components/prospect/event/Slide.vue";
import ManageProspectFilesSlide from "@/components/prospect/file/Slide.vue";
import ManageProspectGroupsSlide from "@/components/prospect/group/Slide.vue";
import ManageProspectInteractionsSlide from "@/components/prospect/interaction/Slide.vue";
import ManageProspectLabelsSlide from "@/components/prospect/label/Slide.vue";
import ManageProspectLogsSlide from "@/components/prospect/log/Slide.vue";
import ManageProspectMessagesSlide from "@/components/prospect/message/Slide.vue";
import ManageProspectOrdersSlide from "@/components/prospect/order/Slide.vue";
import ManageProspectQuestionnairesSlide from "@/components/prospect/questionnaire/Slide.vue";
import ManageProspectRevisionsSlide from "@/components/prospect/revision/Slide.vue";
import ManageProspectSmsSlide from "@/components/prospect/sms/Slide.vue";
import ManageProspectUsersSlide from "@/components/prospect/user/Slide.vue";

import ManageLocaleSlide from "@/components/locale/Slide.vue";
import ManageTutorialSlide from "@/components/tutorial/Slide.vue";

import ManageUserActionsSlide from "@/components/user/action/Slide.vue";
import ManageUserCalendarsSlide from "@/components/user/calendar/Slide.vue";
import ManageUserCategoriesSlide from "@/components/user/category/Slide.vue";
import ManageUserDocumentsSlide from "@/components/user/document/Slide.vue";
import ManageUserDuplicateSlide from "@/components/user/duplicate/Slide.vue";
import ManageUserFilesSlide from "@/components/user/file/Slide.vue";
import ManageUserFoldersSlide from "@/components/user/folder/Slide.vue";
import ManageUserGroupsSlide from "@/components/user/group/Slide.vue";
import ManageUserMenusSlide from "@/components/user/menu/Slide.vue";
import ManageUserOrderActionsSlide from "@/components/user/order-action/Slide.vue";
import ManageUserOrderStepsSlide from "@/components/user/order-step/Slide.vue";
import ManageUserPermissionsSlide from "@/components/user/permission/Slide.vue";
import ManageUserQuestionnairesSlide from "@/components/user/questionnaire/Slide.vue";
import ManageUserRolesSlide from "@/components/user/role/Slide.vue";
import ManageUserSessionsSlide from "@/components/user/session/Slide.vue";
import ManageUserThreadsSlide from "@/components/user/thread/Slide.vue";
import ManageUserUsersSlide from "@/components/user/user/Slide.vue";
import ManageUserVehiclesSlide from "@/components/user/vehicle/list/Slide.vue";
import ManageUsersSlide from "@/components/user/list/Slide.vue";

import ManageRoleCalendarsSlide from "@/components/role/calendar/Slide.vue";
import ManageRoleCategoriesSlide from "@/components/role/category/Slide.vue";
import ManageRoleDocumentsSlide from "@/components/role/document/Slide.vue";
import ManageRoleDuplicateSlide from "@/components/role/duplicate/Slide.vue";
import ManageRoleFoldersSlide from "@/components/role/folder/Slide.vue";
import ManageRoleOrderActionsSlide from "@/components/role/order-action/Slide.vue";
import ManageRoleOrderStepsSlide from "@/components/role/order-step/Slide.vue";
import ManageRolePermissionsSlide from "@/components/role/permission/Slide.vue";
import ManageRoleQuestionnairesSlide from "@/components/role/questionnaire/Slide.vue";
import ManageRoleThreadsSlide from "@/components/role/thread/Slide.vue";
import ManageRoleUsersSlide from "@/components/role/user/Slide.vue";
import ManageRolesSlide from "@/components/role/list/Slide.vue";

import AddCalendarModal from "@/components/calendar/add/Modal.vue";
import AddCategoryModal from "@/components/category/add/Modal.vue";
import AddDocumentModal from "@/components/document/add/Modal.vue";
import AddEventDescriptionTemplateModal from "@/components/event-description-template/add/Modal.vue";
import AddFieldModal from "@/components/field/add/Modal.vue";
import AddFolderModal from "@/components/folder/add/Modal.vue";
import AddGroupModal from "@/components/group/add/Modal.vue";
import AddImportModal from "@/components/import/add/Modal.vue";
import AddLabelModal from "@/components/label/add/Modal.vue";
import AddMenuModal from "@/components/menu/add/Modal.vue";
import AddMessageTemplateModal from "@/components/message-template/add/Modal.vue";
import AddOrderActionModal from "@/components/order/action/add/Modal.vue";
import AddOrderStatusModal from "@/components/order/status/add/Modal.vue";
import AddOrderStepModal from "@/components/order/step/add/Modal.vue";
import AddPipelineModal from "@/components/pipeline/add/Modal.vue";
import AddProductModal from "@/components/product/add/Modal.vue";
import AddProspectLinkModal from "@/components/prospect/link/add/Modal.vue";
import AddQuestionnaireModal from "@/components/questionnaire/questionnaire/add/Modal.vue";
import AddQuestionnaireSectionModal from "@/components/questionnaire/section/add/Modal.vue";
import AddQuestionnaireQuestionModal from "@/components/questionnaire/question/add/Modal.vue";
import AddQuestionnaireOptionModal from "@/components/questionnaire/option/add/Modal.vue";
import AddRoleModal from "@/components/role/add/Modal.vue";
import AddSmsTemplateModal from "@/components/sms-template/add/Modal.vue";
import AddThreadModal from "@/components/thread/add/Modal.vue";
import AddUserModal from "@/components/user/add/Modal.vue";
import AddVehicleModal from "@/components/vehicle/add/Modal.vue";

import AddUserVehicleModal from "@/components/user/vehicle/add/Modal.vue";
import UpdateUserVehicleModal from "@/components/user/vehicle/update/Modal.vue";

import CategorySlide from "@/components/category/list/Slide.vue";
import DocumentSlide from "@/components/document/list/Slide.vue";
import DuplicateSlide from "@/components/duplicate/Slide.vue";
import ImportSlide from "@/components/import/process/Slide.vue";
import QuestionnaireSlide from "@/components/questionnaire/Slide.vue";

import UpdateCalendarModal from "@/components/calendar/update/Modal.vue";
import UpdateCategoryModal from "@/components/category/update/Modal.vue";
import UpdateCommissionModal from "@/components/commission/Modal.vue";
import UpdateDocumentModal from "@/components/document/update/Modal.vue";
import UpdateEventDescriptionTemplateModal from "@/components/event-description-template/update/Modal.vue";
import UpdateFieldModal from "@/components/field/update/Modal.vue";
import UpdateFileModal from "@/components/file/update/Modal.vue";
import UpdateFolderModal from "@/components/folder/update/Modal.vue";
import UpdateGroupModal from "@/components/group/update/Modal.vue";
import UpdateImportModal from "@/components/import/update/Modal.vue";
import UpdateLabelModal from "@/components/label/update/Modal.vue";
import UpdateMenuModal from "@/components/menu/update/Modal.vue";
import UpdateMessageTemplateModal from "@/components/message-template/update/Modal.vue";
import UpdateOrderActionModal from "@/components/order/action/update/Modal.vue";
import UpdateOrderStatusModal from "@/components/order/status/update/Modal.vue";
import UpdateOrderStepModal from "@/components/order/step/update/Modal.vue";
import UpdatePipelineModal from "@/components/pipeline/update/Modal.vue";
import UpdateProductModal from "@/components/product/update/Modal.vue";
import UpdateProspectLinkModal from "@/components/prospect/link/update/Modal.vue";
import UpdateQuestionnaireModal from "@/components/questionnaire/questionnaire/update/Modal.vue";
import UpdateQuestionnaireSectionModal from "@/components/questionnaire/section/update/Modal.vue";
import UpdateQuestionnaireQuestionModal from "@/components/questionnaire/question/update/Modal.vue";
import UpdateQuestionnaireOptionModal from "@/components/questionnaire/option/update/Modal.vue";
import UpdateRoleModal from "@/components/role/update/Modal.vue";
import UpdateSmsTemplateModal from "@/components/sms-template/update/Modal.vue";
import UpdateThreadModal from "@/components/thread/update/Modal.vue";

import ExportModal from "@/components/prospect/table/modals/export/Modal.vue";

import FilterProspectEventSlide from "@/components/prospect/filters/event/Slide.vue";
import FilterProspectFieldSlide from "@/components/prospect/filters/field/Slide.vue";
import FilterProspectFileSlide from "@/components/prospect/filters/file/Slide.vue";
import FilterProspectCreatedSlide from "@/components/prospect/filters/created/Slide.vue";
import FilterProspectCreatorSlide from "@/components/prospect/filters/creator/Slide.vue";
import FilterProspectGroupSlide from "@/components/prospect/filters/group/Slide.vue";
import FilterProspectImportSlide from "@/components/prospect/filters/import/Slide.vue";
import FilterProspectInteractionSlide from "@/components/prospect/filters/interaction/Slide.vue";
import FilterProspectLabelSlide from "@/components/prospect/filters/label/Slide.vue";
import FilterProspectMessageSlide from "@/components/prospect/filters/message/Slide.vue";
import FilterProspectOrderSlide from "@/components/prospect/filters/order/Slide.vue";
import FilterProspectThreadSlide from "@/components/prospect/filters/thread/Slide.vue";
import FilterProspectPipedriveAccountSlide from "@/components/prospect/filters/pipedrive-account/Slide.vue";
import FilterProspectSmsSlide from "@/components/prospect/filters/sms/Slide.vue";
import FilterProspectUserSlide from "@/components/prospect/filters/user/Slide.vue";
import FilterProspectAllSlide from "@/components/prospect/filters/all/Slide.vue";

import ManageProspectBulkGroupsModal from "@/components/prospect/bulk/group/Modal.vue";
import ManageProspectBulkLabelsModal from "@/components/prospect/bulk/label/Modal.vue";
import ManageProspectBulkUsersModal from "@/components/prospect/bulk/user/Modal.vue";
import ManageProspectBulkFieldModal from "@/components/prospect/bulk/field/Modal.vue";
import ManageProspectBulkProjectModal from "@/components/prospect/bulk/project/Modal.vue";

import SettingEmailModal from "@/components/settings/Email.vue";
import SettingSmsboxModal from "@/components/settings/Smsbox.vue";
import SettingRingoverModal from "@/components/settings/Ringover.vue";
import SettingUltramsgModal from "@/components/settings/Ultramsg.vue";
import SettingMTargetModal from "@/components/settings/MTarget.vue";
import SettingPipedriveModal from "@/components/settings/Pipedrive.vue";

import MenuIconSlide from "@/components/menu-icon/Slide.vue";
import TrashSlide from "@/components/trash/Slide.vue";

import ApiPappersSlide from "@/components/api/pappers/Slide.vue";

import LayoutHeader from "./Header.vue";
import LayoutFooter from "./Footer.vue";

import Confirm from "@/components/Confirm.vue";

export default {
    name: "main",

    components: {
        LayoutHeader,
        LayoutFooter,

        EventReminder,

        SettingSlide,

        AddGlobalUserModal,

        ManagePipelineLabelsSlide,

        ManageProspectDocumentsSlide,
        ManageProspectEventsSlide,
        ManageProspectFilesSlide,
        ManageProspectGroupsSlide,
        ManageProspectInteractionsSlide,
        ManageProspectLabelsSlide,
        ManageProspectLogsSlide,
        ManageProspectMessagesSlide,
        ManageProspectOrdersSlide,
        ManageRolePermissionsSlide,
        ManageProspectQuestionnairesSlide,
        ManageProspectRevisionsSlide,
        ManageProspectSmsSlide,
        ManageProspectUsersSlide,

        ManageLocaleSlide,
        ManageTutorialSlide,

        ManageUserActionsSlide,
        ManageUserCalendarsSlide,
        ManageUserCategoriesSlide,
        ManageUserDocumentsSlide,
        ManageUserDuplicateSlide,
        ManageUserFilesSlide,
        ManageUserFoldersSlide,
        ManageUserGroupsSlide,
        ManageUserMenusSlide,
        ManageUserOrderActionsSlide,
        ManageUserOrderStepsSlide,
        ManageUserPermissionsSlide,
        ManageUserQuestionnairesSlide,
        ManageUserRolesSlide,
        ManageUserSessionsSlide,
        ManageUserThreadsSlide,
        ManageUserUsersSlide,
        ManageUserVehiclesSlide,
        ManageUsersSlide,

        ManageRoleCalendarsSlide,
        ManageRoleCategoriesSlide,
        ManageRoleDocumentsSlide,
        ManageRoleDuplicateSlide,
        ManageRoleFoldersSlide,
        ManageRoleOrderActionsSlide,
        ManageRoleOrderStepsSlide,
        ManageRoleQuestionnairesSlide,
        ManageRoleThreadsSlide,
        ManageRoleUsersSlide,
        ManageRolesSlide,

        AddCalendarModal,
        AddCategoryModal,
        AddDocumentModal,
        AddEventDescriptionTemplateModal,
        AddFolderModal,
        AddFieldModal,
        AddGroupModal,
        AddImportModal,
        AddLabelModal,
        AddMenuModal,
        AddMessageTemplateModal,
        AddOrderActionModal,
        AddOrderStatusModal,
        AddOrderStepModal,
        AddPipelineModal,
        AddProductModal,
        AddProspectLinkModal,
        AddQuestionnaireModal,
        AddQuestionnaireSectionModal,
        AddQuestionnaireQuestionModal,
        AddQuestionnaireOptionModal,
        AddRoleModal,
        AddSmsTemplateModal,
        AddThreadModal,
        AddUserModal,
        AddVehicleModal,

        AddUserVehicleModal,
        UpdateUserVehicleModal,

        CategorySlide,
        DocumentSlide,
        DuplicateSlide,
        ImportSlide,
        QuestionnaireSlide,

        UpdateCalendarModal,
        UpdateCategoryModal,
        UpdateCommissionModal,
        UpdateDocumentModal,
        UpdateEventDescriptionTemplateModal,
        UpdateFieldModal,
        UpdateFileModal,
        UpdateFolderModal,
        UpdateGroupModal,
        UpdateImportModal,
        UpdateLabelModal,
        UpdateMenuModal,
        UpdateMessageTemplateModal,
        UpdateOrderActionModal,
        UpdateOrderStatusModal,
        UpdateOrderStepModal,
        UpdatePipelineModal,
        UpdateProductModal,
        UpdateProspectLinkModal,
        UpdateQuestionnaireModal,
        UpdateQuestionnaireSectionModal,
        UpdateQuestionnaireQuestionModal,
        UpdateQuestionnaireOptionModal,
        UpdateRoleModal,
        UpdateSmsTemplateModal,
        UpdateThreadModal,

        ExportModal,

        FilterProspectEventSlide,
        FilterProspectFieldSlide,
        FilterProspectFileSlide,
        FilterProspectCreatedSlide,
        FilterProspectCreatorSlide,
        FilterProspectGroupSlide,
        FilterProspectImportSlide,
        FilterProspectInteractionSlide,
        FilterProspectLabelSlide,
        FilterProspectMessageSlide,
        FilterProspectOrderSlide,
        FilterProspectThreadSlide,
        FilterProspectPipedriveAccountSlide,
        FilterProspectSmsSlide,
        FilterProspectUserSlide,
        FilterProspectAllSlide,

        ManageProspectBulkLabelsModal,
        ManageProspectBulkUsersModal,
        ManageProspectBulkGroupsModal,
        ManageProspectBulkFieldModal,
        ManageProspectBulkProjectModal,

        SettingEmailModal,
        SettingSmsboxModal,
        SettingRingoverModal,
        SettingUltramsgModal,
        SettingMTargetModal,
        SettingPipedriveModal,

        MenuIconSlide,
        TrashSlide,

        ApiPappersSlide,

        Confirm,
    },

    data() {
        return {
            user: this.$store.state.auth.user,
        };
    },

    mounted() {
        document.addEventListener("keydown", this.keydown);
        store.dispatch(FETCH_PERMISSIONS, this.project);
        dayjs.locale(this.locale);
    },

    methods: {
        ...mapActions({
            signOut: "auth/logout",
        }),
        async logout() {
            await this.signOut();
        },
        keydown(e) {
            if (e.ctrlKey) {
                // Search
                if (e.key === "f") {
                    e.preventDefault();
                    const searchBox = document.getElementById(
                        "hc-global-search-input"
                    );
                    searchBox.focus();
                    searchBox.select();
                    store.commit(CLOSE_MODALS);
                    store.commit(CLOSE_SLIDES);
                }
            }
        },
    },

    watch: {
        projectSlug() {
            store.dispatch(FETCH_PERMISSIONS, this.project);
        },
    },

    computed: {
        ...mapGetters("auth", ["impersonating"]),
        ...mapGetters("route", ["changing"]),
        ...mapGetters([
            "project",
            "can",
            "canMessage",
            "canFile",
            "canEvent",
            "canGroup",
            "canUser",
            "locale",
        ]),

        projectSlug() {
            return this.project ? this.project.slug : null;
        },
    },
};
</script>
