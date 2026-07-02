<template>
    <action-row
        v-if="type == 'action'"
        :id="id"
        :class="selectedClass"
        :action="item"
    />
    <calendar-row
        v-if="type == 'calendar'"
        :id="id"
        :class="selectedClass"
        :calendar="item"
    />
    <category-row
        v-else-if="type == 'category'"
        :id="id"
        :class="selectedClass"
        :category="item"
    />
    <document-row
        v-else-if="type == 'document'"
        :id="id"
        :class="selectedClass"
        :document="item"
    />
    <field-row
        v-else-if="type == 'field'"
        :id="id"
        :class="selectedClass"
        :field="item"
    />
    <folder-row
        v-else-if="type == 'folder'"
        :id="id"
        :class="selectedClass"
        :folder="item"
    />
    <group-row
        v-else-if="type == 'group'"
        :id="id"
        :class="selectedClass"
        :group="item"
    />
    <import-row
        v-else-if="type == 'import'"
        :id="id"
        :class="selectedClass"
        :prospect-import="item"
    />
    <label-row
        v-else-if="type == 'label'"
        :id="id"
        :class="selectedClass"
        :label="item"
    />
    <menu-row
        v-else-if="type == 'menu'"
        :id="id"
        :class="selectedClass"
        :menu="item"
    />
    <product-row
        v-else-if="type == 'product'"
        :id="id"
        :class="selectedClass"
        :product="item"
    />
    <project-row
        v-else-if="type == 'project'"
        :id="id"
        :class="selectedClass"
        :project="item"
    />
    <questionnaire-row
        v-else-if="type == 'questionnaire'"
        :id="id"
        :class="selectedClass"
        :questionnaire="item"
    />
    <role-row
        v-else-if="type == 'role'"
        :id="id"
        :class="selectedClass"
        :role="item"
    />
    <thread-row
        v-else-if="type == 'thread'"
        :id="id"
        :class="selectedClass"
        :thread="item"
    />
    <user-row
        v-else-if="type == 'user'"
        :id="id"
        :class="selectedClass"
        :user="item"
    />
    <prospect-row
        v-else-if="type == 'prospect'"
        :id="id"
        :class="selectedClass"
        :prospect="item"
        :keyword="keyword"
    />
</template>

<style>
.hc-search-item {
    align-items: start;
}

.hc-search-title {
    font-weight: 700;
    line-height: 28px;
    font-size: 11px;
}

.hc-search-description {
    color: #888888;
    font-size: 11px;
    margin-top: 0px;
    margin-bottom: 5px;
    white-space: normal;
    line-height: 16px;
}
</style>

<script>
import ActionRow from "./ActionRow.vue";
import CalendarRow from "./CalendarRow.vue";
import CategoryRow from "./CategoryRow.vue";
import DocumentRow from "./DocumentRow.vue";
import FieldRow from "./FieldRow.vue";
import FolderRow from "./FolderRow.vue";
import GroupRow from "./GroupRow.vue";
import ImportRow from "./ImportRow.vue";
import LabelRow from "./LabelRow.vue";
import MenuRow from "./MenuRow.vue";
import ProductRow from "./ProductRow.vue";
import ProjectRow from "./ProjectRow.vue";
import ProspectRow from "./ProspectRow.vue";
import QuestionnaireRow from "./QuestionnaireRow.vue";
import RoleRow from "./RoleRow.vue";
import ThreadRow from "./ThreadRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        ActionRow,
        CalendarRow,
        CategoryRow,
        UserRow,
        DocumentRow,
        FieldRow,
        FolderRow,
        GroupRow,
        ImportRow,
        LabelRow,
        MenuRow,
        ProductRow,
        ProjectRow,
        ProspectRow,
        QuestionnaireRow,
        RoleRow,
        ThreadRow,
    },

    props: {
        keyword: {
            type: String,
        },
        item: {
            type: Object,
        },
        id: {
            type: String,
        },
        selected: {
            type: Boolean,
            default: false,
        },
        type: {
            type: String,
        },
    },

    computed: {
        selectedClass() {
            return this.selected ? "selected" : "";
        },
    },
};
</script>
