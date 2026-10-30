<template>
    <label-item
        v-for="assignment in addedAssignments"
        :key="assignment.name"
        :item="assignment"
    />
    <history-row
        v-if="showRevision"
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
import { mapGetters } from "vuex";
import { addedLabelNames } from "@/utils/prospect-label-history";
// Components
import HistoryRow from "./Row.vue";
import LabelItem from "./LabelItem.vue";

export default {
    props: {
        item: {
            type: Object,
        },
    },

    components: {
        HistoryRow,
        LabelItem,
    },

    computed: {
        ...mapGetters(["categories"]),

        addedAssignments() {
            if (!(this.item.key || "").startsWith("category->")) return [];

            const categoryId = this.item.key.slice("category->".length);
            const category = this.categories.find((category) => category.id == categoryId);
            const labels = (category && category.labels) || [];

            return addedLabelNames(this.item, labels).map((name) => ({
                ...labels.find((label) => label.name === name),
                id: this.item.id,
                category_id: categoryId,
                field: this.item.field,
                name,
                created_at: this.item.created_at,
                creator: this.item.user,
                creator_id: this.item.user_id,
            }));
        },

        showRevision() {
            // Legacy baselines preserve an assignment, not a known before/after.
            return this.item.old_value !== null || !this.addedAssignments.length;
        },
    },
};
</script>
