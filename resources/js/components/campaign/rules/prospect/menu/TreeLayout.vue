<template>
    <tree-layout class="hc-campaign-rules-bloc" :tabulation="5">
        <template #header>
            <item>
                <icon class="fa fa-list" color="#333333" />
                <div
                    class="hc-item-main-content hc-campaign-rules-bloc-title"
                    v-text="$t('campaign.rule.prospect.menu')"
                ></div>
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <menu-row
                v-for="menu in menus"
                :menu="menu"
                @dragging="dragging"
                @dragged="dragged"
            />
        </template>
    </tree-layout>
</template>

<script>
import { mapGetters } from "vuex";

import MenuRow from "./MenuRow.vue";

export default {
    components: {
        MenuRow,
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
        ...mapGetters(["menus"]),
    },
};
</script>
