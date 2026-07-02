<template>
    <item class="hc-campaign-rule-menu-row">
        <icon class="fa fa-tag" icon-size="11" :size="30" :style="style" />
        <div class="hc-item-main-content" v-text="menu.name"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="30"
            @mousedown.stop
            @click.prevent.stop="edit"
        />
    </item>
</template>

<style>
.hc-campaign-rule-menu-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import store from "@/store";

import CampaignTemplate from "@/components/campaign/components/Template.vue";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_MENU } from "@/actions/project/menu";

export default {
    components: {
        CampaignTemplate,
    },

    props: {
        menu: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "menu-update");
            store.commit(SET_MENU, this.menu);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.menu.bgcolor,
            };
        },
    },
};
</script>
