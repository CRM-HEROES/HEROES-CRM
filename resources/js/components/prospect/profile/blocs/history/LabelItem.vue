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
                v-if="category || item.field"
                v-text="(category ? category.name : item.field) + ' : '"
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
            if (this.item.creator) return this.item.creator;

            const creatorId = this.item.pivot
                ? this.item.pivot.creator_id : this.item.creator_id;
            if (!creatorId) {
                return null;
            }

            return this.users.find((user) => user.id == creatorId);
        },
    },
};
</script>
