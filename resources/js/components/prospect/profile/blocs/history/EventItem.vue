<template>
    <history-row
        :item="item"
        :date="item.started_at"
        :user="item.user"
        icon="fa fa-calendar"
        :color="item.calendar.color"
        :bgcolor="item.calendar.bgcolor"
    >
        <div class="hc-prospect-profile-history-event">
            <b
                class="hc-prospect-profile-history-event-calendar"
                v-if="item.calendar"
                v-text="item.calendar.name + ' : '"
            ></b>
            <div
                class="hc-prospect-profile-history-event-location"
                v-if="item.location"
                v-text="item.location"
            ></div>
            <div
                class="hc-prospect-profile-history-event-location"
                v-if="item.description"
                v-html="item.description"
            ></div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-event {
    display: flex;
    flex-direction: row;
    gap: 8px;
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
