<template>
    <event-row v-if="type == 'event'" :event="item" />
    <field-row v-else-if="type == 'field'" :field="item" />
    <file-row v-else-if="type == 'file'" :file="item" />
    <label-row v-else-if="type == 'label'" :label="item" />
    <link-row v-else-if="type == 'link'" :link="item" />
    <order-row v-else-if="type == 'order'" :order="item" />
</template>

<script>
// Components
import EventRow from "./EventRow.vue";
import FieldRow from "./FieldRow.vue";
import FileRow from "./FileRow.vue";
import LabelRow from "./LabelRow.vue";
import LinkRow from "./LinkRow.vue";
import OrderRow from "./OrderRow.vue";

export default {
    components: {
        EventRow,
        FieldRow,
        FileRow,
        LabelRow,
        LinkRow,
        OrderRow,
    },

    props: {
        type: {
            type: String,
        },
        item: {
            type: Object,
        },
    },
};
</script>
