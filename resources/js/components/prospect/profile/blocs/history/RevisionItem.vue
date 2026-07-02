<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.user"
        icon="fa fa-pen"
    >
        <div class="hc-prospect-profile-history-revision">
            <b
                class="hc-prospect-profile-history-revision-field"
                v-if="item.field"
                v-text="item.field + ' : '"
            ></b>
            <div
                class="hc-prospect-profile-history-revision-old-value"
                v-if="item.old_value"
                v-text="item.old_value"
            ></div>
            <i
                v-if="item.old_value && item.new_value"
                class="fa fa-caret-right"
            ></i>
            <div
                class="hc-prospect-profile-history-revision-new-value"
                v-if="item.new_value"
                v-text="item.new_value"
            ></div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-revision {
    display: flex;
    flex-direction: row;
    gap: 8px;
    align-items: center;
}
</style>

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
