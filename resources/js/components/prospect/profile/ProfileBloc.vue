<template>
    <field-bloc v-if="bloc.type == 'field'" :bloc="bloc" :open="open" />
    <message-bloc
        v-else-if="
            bloc.type.indexOf('thread->') == 0 &&
            threads.find(
                (thread) => thread.id == bloc.type.replace('thread->', '')
            )
        "
        :bloc="bloc"
        :thread="
            threads.find(
                (thread) => thread.id == bloc.type.replace('thread->', '')
            )
        "
        :open="open"
    />
    <file-bloc
        v-else-if="
            bloc.type.indexOf('folder->') == 0 &&
            folders.find(
                (folder) => folder.id == bloc.type.replace('folder->', '')
            )
        "
        :bloc="bloc"
        :folder="
            folders.find(
                (folder) => folder.id == bloc.type.replace('folder->', '')
            )
        "
        :open="open"
    />
    <category-bloc
        v-else-if="
            bloc.type.indexOf('category->') == 0 &&
            categories.find(
                (category) => category.id == bloc.type.replace('category->', '')
            )
        "
        :bloc="bloc"
        :category="
            categories.find(
                (category) => category.id == bloc.type.replace('category->', '')
            )
        "
        :open="open"
    />
    <pipeline-bloc
        v-else-if="
            bloc.type.indexOf('pipeline->') == 0 &&
            pipelines.find(
                (pipeline) => pipeline.id == bloc.type.replace('pipeline->', '')
            )
        "
        :bloc="bloc"
        :pipeline="
            pipelines.find(
                (pipeline) => pipeline.id == bloc.type.replace('pipeline->', '')
            )
        "
        :open="open"
    />
    <history-bloc
        v-else-if="bloc.type == 'history'"
        :bloc="bloc"
        :open="open"
    />
    <order-bloc v-else-if="bloc.type == 'order'" :bloc="bloc" :open="open" />
    <link-bloc v-else-if="bloc.type == 'link'" :bloc="bloc" :open="open" />
    <event-bloc v-else-if="bloc.type == 'event'" :bloc="bloc" :open="open" />
    <interaction-bloc
        v-else-if="bloc.type == 'interaction'"
        :bloc="bloc"
        :open="open"
    />
    <sms-bloc v-else-if="bloc.type == 'sms'" :bloc="bloc" :open="open" />
    <document-bloc
        v-else-if="bloc.type == 'document'"
        :bloc="bloc"
        :open="open"
    />
    <user-bloc v-else-if="bloc.type == 'user'" :bloc="bloc" :open="open" />
    <group-bloc v-else-if="bloc.type == 'group'" :bloc="bloc" :open="open" />
    <label-bloc v-else-if="bloc.type == 'label'" :bloc="bloc" :open="open" />
    <map-bloc
        v-else-if="
            bloc.type == 'map' && prospect.latitude && prospect.longitude
        "
        :bloc="bloc"
        :open="open"
    />
</template>

<script>
import { mapGetters } from "vuex";

import CategoryBloc from "./blocs/category/Bloc.vue";
import DocumentBloc from "./blocs/document/Bloc.vue";
import EventBloc from "./blocs/event/Bloc.vue";
import FieldBloc from "./blocs/field/Bloc.vue";
import FileBloc from "./blocs/file/Bloc.vue";
import GroupBloc from "./blocs/group/Bloc.vue";
import HistoryBloc from "./blocs/history/Bloc.vue";
import InteractionBloc from "./blocs/interaction/Bloc.vue";
import LabelBloc from "./blocs/label/Bloc.vue";
import LinkBloc from "./blocs/link/Bloc.vue";
import MapBloc from "./blocs/map/Bloc.vue";
import MessageBloc from "./blocs/message/Bloc.vue";
import OrderBloc from "./blocs/order/Bloc.vue";
import PipelineBloc from "./blocs/pipeline/Bloc.vue";
import SmsBloc from "./blocs/sms/Bloc.vue";
import UserBloc from "./blocs/user/Bloc.vue";

export default {
    components: {
        CategoryBloc,
        DocumentBloc,
        EventBloc,
        FieldBloc,
        FileBloc,
        GroupBloc,
        HistoryBloc,
        InteractionBloc,
        LabelBloc,
        LinkBloc,
        MapBloc,
        MessageBloc,
        OrderBloc,
        PipelineBloc,
        SmsBloc,
        UserBloc,
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
