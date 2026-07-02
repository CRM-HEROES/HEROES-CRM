<template>
    <tree-layout :tabulation="5" class="hc-campaign-rules-bloc">
        <template #header>
            <item>
                <icon class="fa fa-tags" color="#333333" />
                <div
                    class="hc-item-main-content hc-campaign-rules-bloc-title"
                    v-text="$t('campaign.rule.prospect.tag')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-plus"
                    @click.prevent.stop="createCategory"
                />
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <category-row
                v-for="category in categories"
                :key="category.id"
                :category="category"
                @dragging="dragging"
                @dragged="dragged"
            />
        </template>
    </tree-layout>
</template>

<script>
import { mapGetters } from "vuex";

import CategoryRow from "./CategoryRow.vue";

export default {
    components: {
        CategoryRow,
    },

    methods: {
        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
        },
    },

    computed: {
        // Store
        ...mapGetters(["categories"]),
    },
};
</script>
