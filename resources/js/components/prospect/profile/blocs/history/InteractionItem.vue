<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="item.creator"
        icon="fa fa-phone"
    >
        <div class="hc-prospect-profile-history-label">
            <b
                class="hc-prospect-profile-history-label-category"
                v-text="'Appelé sur : '"
            ></b>
            <div
                class="hc-prospect-profile-history-label-name"
                v-text="source"
            ></div>
        </div>
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

    computed: {
        source() {
            if (this.item.source == "telephone") {
                return "Téléphone";
            }

            if (this.item.source == "ringover") {
                return "Ringover";
            }

            if (this.item.source == "aircall") {
                return "Aircall";
            }

            return this.item.source;
        },
    },
};
</script>
