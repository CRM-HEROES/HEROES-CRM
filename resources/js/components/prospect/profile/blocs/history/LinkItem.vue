<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.creator"
        icon="fa fa-external-link"
    >
        Link
    </history-row>
</template>

<style></style>

<script>
// Components
import HistoryRow from "./Row.vue";

export default {
    props: {
        item: {
            type: Object,
        },
    },

    components: {
        HistoryRow,
    },
};
</script>
