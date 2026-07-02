<template>
    <campaign-template :field="field">
        <item class="hc-campaign-rule-label-row">
            <icon class="fa fa-tag" icon-size="11" :size="30" :style="style" />
            <div class="hc-item-main-content" v-text="label.name"></div>
            <icon
                v-if="can('all.project.category.label.update')"
                tag="a"
                class="fa fa-cog hc-show-on-hover"
                :size="30"
                @mousedown.stop
                @click.prevent.stop="edit"
            />
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
.hc-campaign-rule-label-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import CampaignTemplate from "@/components/campaign/components/Template.vue";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    components: {
        CampaignTemplate,
    },

    data() {
        return {
            field: {
                action: "prospect-detach-label",
                name:
                    "Detacher filtre: " +
                    this.category.name +
                    " - " +
                    this.label.name,
                category: "action",
                value: {
                    label: this.label.id,
                },
                style: {
                    color: this.label.color,
                    backgroundColor: this.label.bgcolor,
                },
            },
        };
    },

    props: {
        category: {
            type: Object,
        },
        label: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },
    },
};
</script>
