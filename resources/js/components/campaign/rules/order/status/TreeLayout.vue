<template>
    <tree-layout class="hc-campaign-rules-bloc" :tabulation="5">
        <template #header>
            <item>
                <icon class="fa fa-shopping-cart" color="#333333" />
                <div
                    class="hc-item-main-content hc-campaign-rules-bloc-title"
                    v-text="$t('campaign.rule.order.status')"
                ></div>
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <status-row
                v-for="status in orderStatuses"
                :status="status"
                @dragging="dragging"
                @dragged="dragged"
            />
        </template>
    </tree-layout>
</template>

<script>
import { mapGetters } from "vuex";

import StatusRow from "./StatusRow.vue";

export default {
    components: {
        StatusRow,
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
        ...mapGetters(["orderStatuses"]),
    },
};
</script>
