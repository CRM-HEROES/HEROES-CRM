<template>
    <campaign-template :field="field">
        <item class="hc-campaign-menu-row">
            <icon class="fa fa-list" icon-size="11" :size="30" :style="style" />
            <div class="hc-item-main-content" v-text="menu.name"></div>
            <icon
                class="fa fa-arrows"
                icon-size="11"
                :size="30"
                color="#CCCCCC"
            />
        </item>
    </campaign-template>
</template>

<style>
.hc-campaign-menu-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: {
                name: this.menu.name,
                rule: this.menu.filters,
                category: "rule",
                style: {
                    color: this.menu.color,
                    backgroundColor: this.menu.bgcolor,
                },
            },
        };
    },

    props: {
        menu: {
            type: Object,
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
