<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.creator"
        icon="fa fa-shopping-cart"
    >
        <div class="hc-prospect-profile-history-order">
            <b v-text="'Devis : '"></b>
            <div v-text="item.name"></div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-order {
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
