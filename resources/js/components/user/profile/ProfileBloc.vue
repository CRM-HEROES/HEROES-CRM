<template>
    <info-bloc v-if="bloc.type == 'field'" :bloc="bloc" :open="open" />
    <calendar-bloc
        v-else-if="bloc.type == 'calendar'"
        :bloc="bloc"
        :open="open"
    />
    <category-bloc
        v-else-if="bloc.type == 'category'"
        :bloc="bloc"
        :open="open"
    />
    <document-bloc
        v-else-if="bloc.type == 'document'"
        :bloc="bloc"
        :open="open"
    />
    <export-bloc v-else-if="bloc.type == 'export'" :bloc="bloc" :open="open" />
    <file-bloc
        v-else-if="
            bloc.type.indexOf('folder->') == 0 &&
            folders.find(
                (folder) =>
                    folder.for == 'user' &&
                    folder.id == bloc.type.replace('folder->', '')
            )
        "
        :bloc="bloc"
        :open="open"
        :folder="
            folders.find(
                (folder) =>
                    folder.for == 'user' &&
                    folder.id == bloc.type.replace('folder->', '')
            )
        "
    />
    <folder-bloc v-else-if="bloc.type == 'folder'" :bloc="bloc" :open="open" />
    <group-bloc v-else-if="bloc.type == 'group'" :bloc="bloc" :open="open" />
    <import-bloc v-else-if="bloc.type == 'import'" :bloc="bloc" :open="open" />
    <menu-bloc v-else-if="bloc.type == 'menu'" :bloc="bloc" :open="open" />
    <order-step-bloc
        v-else-if="bloc.type == 'order-step'"
        :bloc="bloc"
        :open="open"
    />
    <questionnaire-bloc
        v-else-if="bloc.type == 'questionnaire'"
        :bloc="bloc"
        :open="open"
    />
    <role-bloc v-else-if="bloc.type == 'role'" :bloc="bloc" :open="open" />
    <thread-bloc v-else-if="bloc.type == 'thread'" :bloc="bloc" :open="open" />
    <user-bloc v-else-if="bloc.type == 'user'" :bloc="bloc" :open="open" />
    <vehicle-position-bloc
        v-else-if="bloc.type == 'vehicle-position'"
        :bloc="bloc"
        :open="open"
    />
</template>

<script>
import { mapGetters } from "vuex";

import CalendarBloc from "./blocs/calendar/Bloc.vue";
import CategoryBloc from "./blocs/category/Bloc.vue";
import DocumentBloc from "./blocs/document/Bloc.vue";
import ExportBloc from "./blocs/export/Bloc.vue";
import FileBloc from "./blocs/file/Bloc.vue";
import FolderBloc from "./blocs/folder/Bloc.vue";
import GroupBloc from "./blocs/group/Bloc.vue";
import ImportBloc from "./blocs/import/Bloc.vue";
import InfoBloc from "./blocs/info/Bloc.vue";
import MenuBloc from "./blocs/menu/Bloc.vue";
import OrderStepBloc from "./blocs/order-step/Bloc.vue";
import QuestionnaireBloc from "./blocs/questionnaire/Bloc.vue";
import RoleBloc from "./blocs/role/Bloc.vue";
import ThreadBloc from "./blocs/thread/Bloc.vue";
import UserBloc from "./blocs/user/Bloc.vue";
import VehiclePositionBloc from "./blocs/vehicle-position/Bloc.vue";

export default {
    components: {
        CalendarBloc,
        CategoryBloc,
        DocumentBloc,
        ExportBloc,
        FileBloc,
        FolderBloc,
        GroupBloc,
        ImportBloc,
        InfoBloc,
        MenuBloc,
        OrderStepBloc,
        QuestionnaireBloc,
        RoleBloc,
        ThreadBloc,
        UserBloc,
        VehiclePositionBloc,
    },

    props: {
        bloc: {
            type: Object,
        },
        open: {
            type: Boolean,
        },
    },

    computed: {
        ...mapGetters([
            "prospect",
            "categories",
            "threads",
            "folders",
            "pipelines",
        ]),
    },
};
</script>
