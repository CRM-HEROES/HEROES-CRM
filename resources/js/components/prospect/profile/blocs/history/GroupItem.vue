<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="user"
        icon="fa fa-users"
    >
        <div class="hc-prospect-profile-history-group">
            <b v-text="'Affecté au groupe : '"></b>
            <div v-text="item.name"></div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-group {
    display: flex;
    flex-direction: row;
    gap: 8px;
}
</style>

<script>
import { mapGetters } from "vuex";

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
        ...mapGetters(["users"]),

        user() {
            if (!this.item.creator_id) {
                return null;
            }

            return this.users.find((user) => user.id == this.item.creator_id);
        },
    },
};
</script>
