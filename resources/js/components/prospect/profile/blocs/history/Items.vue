<template>
    <div class="hc-prospect-history-items">
        <template v-for="item in items" :key="item.type + '-' + item.id">
            <event-item v-if="item.type == 'event'" :item="item" />
            <file-item v-else-if="item.type == 'file'" :item="item" />
            <group-item v-else-if="item.type == 'group'" :item="item" />
            <interaction-item
                v-else-if="item.type == 'interaction'"
                :item="item"
            />
            <label-item v-else-if="item.type == 'label'" :item="item" />
            <link-item v-else-if="item.type == 'link'" :item="item" />
            <message-item v-else-if="item.type == 'message'" :item="item" />
            <order-item v-else-if="item.type == 'order'" :item="item" />
            <revision-item v-else-if="item.type == 'revision'" :item="item" />
            <sms-item v-else-if="item.type == 'sms'" :item="item" />
            <user-item v-else-if="item.type == 'user'" :item="item" />
        </template>
    </div>
</template>

<script>
import EventItem from "./EventItem";
import FileItem from "./FileItem";
import GroupItem from "./GroupItem";
import InteractionItem from "./InteractionItem";
import LabelItem from "./LabelItem";
import LinkItem from "./LinkItem";
import MessageItem from "./MessageItem";
import OrderItem from "./OrderItem";
import RevisionItem from "./RevisionItem";
import SmsItem from "./SmsItem";
import UserItem from "./UserItem";

export default {
    props: {
        items: {
            type: Array,
            default: () => [],
        },
    },

    components: {
        EventItem,
        FileItem,
        GroupItem,
        InteractionItem,
        LabelItem,
        LinkItem,
        MessageItem,
        OrderItem,
        RevisionItem,
        SmsItem,
        UserItem,
    },
};
</script>
