<template>
    <movable
        :classes="[
            'hc-campaign-action',
            isNew ? 'is-new' : '',
            selected ? 'selected' : '',
            removing ? 'removing' : '',
        ]"
        :style="action.style"
        :zoom="campaignZoom"
        :rotatable="false"
        @change="update"
        @changing="change"
    >
        <!--
            Field content
        -->
        <div
            class="hc-campaign-action-content"
            :style="contentStyle"
            ref="content"
            @mouseup="addRelation"
        >
            <span v-text="action.name"></span>
        </div>

        <!--
            Field remove
        -->
        <div class="hc-campaign-action-remove" @mousedown.stop="remove">
            &times;
        </div>
    </movable>
</template>

<style>
.hc-campaign-action {
    outline: 1px dashed #7939b8;
    line-height: 18px;
    background-color: white;
    font-size: 11px;
}

.hc-campaign-action-content {
    padding: 5px;
}

.hc-campaign-action.selected {
    outline: 2px solid #7939b8;
}

.hc-campaign-action.removing {
    transition: transform ease-out 70ms, opacity ease-out 70ms;
    transform: scale(0.85);
    opacity: 0;
}

.hc-campaign-action.is-new {
    box-shadow: 0 4px 8px #0005;
    transform: scale(1.005);
    z-index: 1000;
}

.hc-campaign-action.is-new {
    position: fixed;
}

.hc-campaign-action:hover {
    outline: 2px solid #7939b8;
}

.hc-campaign-action-remove {
    background-color: #7939b8;
    border-radius: 50%;
    color: white;
    height: 22px;
    font-size: 12px;
    left: -15px;
    line-height: 24px;
    opacity: 0;
    position: absolute;
    text-align: center;
    top: -15px;
    transform: scale(0.7);
    transition: opacity ease-out 100ms, transform ease-out 100ms;
    width: 22px;
}

.hc-campaign-action:hover > .hc-campaign-action-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-campaign-action.is-new .hc-movable-h-resize,
.hc-campaign-action.is-new .hc-movable-w-resize,
.hc-campaign-action.is-new .hc-movable-wh-resize,
.hc-campaign-action.is-new .hc-movable-rotate,
.hc-campaign-action.is-new .hc-movable-remove {
    display: none;
}
</style>

<script>
import Movable from "@/components/Movable.vue";

import store from "@/store";
import {
    UPDATE_CAMPAIGN_ACTION,
    REMOVE_CAMPAIGN_ACTION,
    ADD_CAMPAIGN_CAMPAIGN_ACTION,
} from "@/actions/project/campaign";
import { mapGetters } from "vuex";

export default {
    components: {
        Movable,
    },

    props: {
        /**
         *
         */
        action: {
            type: Object,
        },

        /**
         * Is action selected
         */
        selected: {
            type: Boolean,
            default: false,
        },

        /**
         * Is action selecting
         */
        isNew: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            removing: false,
        };
    },

    methods: {
        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removing = true;

                try {
                    await store.dispatch(
                        REMOVE_CAMPAIGN_ACTION,
                        this.action.id
                    );
                } finally {
                    this.removing = false;
                }
            });
        },

        /**
         *
         */
        selecting(event) {
            this.$emit("selected", event, this.action);
        },

        async addRelation() {
            if (this.campaign) {
                await store.dispatch(ADD_CAMPAIGN_CAMPAIGN_ACTION, {
                    campaign: this.campaign,
                    action: this.action,
                });
            }
        },

        /**
         *
         */
        change(style) {
            this.action.style = {
                ...this.action.style,
                ...style,
            };
        },

        /**
         *
         * @param {*} style
         */
        update(style) {
            store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                id: this.action.id,
                style,
            });
        },
    },

    computed: {
        /**
         * Content style
         */
        contentStyle() {
            return Object.fromEntries(
                Object.entries(this.action.style).filter(
                    ([key]) =>
                        key != "left" &&
                        key != "top" &&
                        key != "width" &&
                        key != "height" &&
                        key != "transform"
                )
            );
        },

        // Store
        ...mapGetters(["campaignZoom", "campaign"]),
    },
};
</script>
