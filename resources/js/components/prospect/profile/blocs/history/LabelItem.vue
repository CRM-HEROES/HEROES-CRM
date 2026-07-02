<template>
    <history-row
        :item="item"
        :date="item.created_at"
        :user="user"
        icon="fa fa-tag"
        :color="item.color"
        :bgcolor="item.bgcolor"
    >
        <div class="hc-prospect-profile-history-label">
            <b
                class="hc-prospect-profile-history-label-category"
                v-if="category"
                v-text="category.name + ' : '"
            ></b>
            <div
                class="hc-prospect-profile-history-label-name"
                v-text="item.name"
            ></div>
        </div>
    </history-row>
</template>

<style>
.hc-prospect-profile-history-label {
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
        ...mapGetters(["categories", "users"]),

        category() {
            return this.categories.find(
                (category) => category.id == this.item.category_id
            );
        },

        user() {
            if (!this.item.creator_id) {
                return null;
            }

            return this.users.find((user) => user.id == this.item.creator_id);
        },
    },
};
</script>
