<template>
    <tree-layout class="hc-campaign-rules-bloc" :tabulation="5">
        <template #header>
            <item>
                <icon class="fa fa-person-digging" color="#333333" />
                <div
                    class="hc-item-main-content hc-campaign-rules-bloc-title"
                    v-text="$t('campaign.rule.order.action')"
                ></div>
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <action-row
                v-for="action in orderActions"
                :action="action"
                @dragging="dragging"
                @dragged="dragged"
            />
        </template>
    </tree-layout>
</template>

<script>
import { mapGetters } from "vuex";

import ActionRow from "./ActionRow.vue";

export default {
    components: {
        ActionRow,
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
        ...mapGetters(["orderActions"]),
    },
};
</script>
