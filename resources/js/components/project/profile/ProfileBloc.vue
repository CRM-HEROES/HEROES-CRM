<template>
    <info-bloc
        v-if="bloc.type == 'info-profile'"
        :bloc="{
            name: $t('project.profile.blocs.profile'),
            items: ['name', 'email', 'phone_number'],
        }"
    />
    <info-bloc
        v-else-if="bloc.type == 'info-contacts'"
        :bloc="{
            name: $t('project.profile.blocs.contacts'),
            items: [
                'street',
                'street_bis',
                'postal_code',
                'city',
                'county',
                'state',
                'country',
            ],
        }"
    />
    <info-bloc
        v-else-if="bloc.type == 'info-other'"
        :bloc="{
            name: 'Autres',
            items: ['naf', 'siret', 'capital', 'num_tva_intra'],
        }"
    />
    <api-bloc v-else-if="bloc.type == 'api'" />
    <calendar-bloc v-else-if="bloc.type == 'calendar'" />
    <category-bloc v-else-if="bloc.type == 'category'" />
    <document-bloc v-else-if="bloc.type == 'document'" />
    <field-bloc v-else-if="bloc.type == 'field'" />
    <folder-bloc v-else-if="bloc.type == 'folder'" />
    <group-bloc v-else-if="bloc.type == 'group'" />
    <import-bloc v-else-if="bloc.type == 'import'" />
    <line-bloc v-else-if="bloc.type == 'line'" />
    <menu-bloc v-else-if="bloc.type == 'menu'" />
    <order-action-bloc v-else-if="bloc.type == 'order-action'" />
    <order-status-bloc v-else-if="bloc.type == 'order-status'" />
    <order-step-bloc v-else-if="bloc.type == 'order-step'" />
    <product-bloc v-else-if="bloc.type == 'product'" />
    <role-bloc v-else-if="bloc.type == 'role'" />
    <thread-bloc v-else-if="bloc.type == 'thread'" />
    <user-bloc v-else-if="bloc.type == 'user'" />
</template>

<script>
import ApiBloc from "./blocs/api/Bloc.vue";
import CalendarBloc from "./blocs/calendar/Bloc.vue";
import CategoryBloc from "./blocs/category/Bloc.vue";
import DocumentBloc from "./blocs/document/Bloc.vue";
import FieldBloc from "./blocs/field/Bloc.vue";
import FolderBloc from "./blocs/folder/Bloc.vue";
import GroupBloc from "./blocs/group/Bloc.vue";
import ImportBloc from "./blocs/import/Bloc.vue";
import InfoBloc from "./blocs/info/Bloc.vue";
import LineBloc from "./blocs/line/Bloc.vue";
import MenuBloc from "./blocs/menu/Bloc.vue";
import OrderActionBloc from "./blocs/order-action/Bloc.vue";
import OrderStatusBloc from "./blocs/order-status/Bloc.vue";
import OrderStepBloc from "./blocs/order-step/Bloc.vue";
import ProductBloc from "./blocs/product/Bloc.vue";
import RoleBloc from "./blocs/role/Bloc.vue";
import ThreadBloc from "./blocs/thread/Bloc.vue";
import UserBloc from "./blocs/user/Bloc.vue";

export default {
    components: {
        ApiBloc,
        CalendarBloc,
        CategoryBloc,
        DocumentBloc,
        FieldBloc,
        FolderBloc,
        GroupBloc,
        ImportBloc,
        InfoBloc,
        LineBloc,
        MenuBloc,
        OrderActionBloc,
        OrderStatusBloc,
        OrderStepBloc,
        ProductBloc,
        RoleBloc,
        ThreadBloc,
        UserBloc,
    },

    props: {
        bloc: {
            type: Object,
        },
    },
};
</script>
