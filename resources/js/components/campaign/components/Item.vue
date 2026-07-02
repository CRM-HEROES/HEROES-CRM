<template>
    <movable
        :style="item.style"
        :zoom="campaignZoom"
        :rotatable="false"
        :classes="['hc-campaign-item', removing ? 'removing' : '']"
        @change="updateStyle"
        @changing="change"
    >
        <div
            v-if="!item.action || item.action == 'empty'"
            class="hc-campaign-item-search"
            @change.stop
        >
            <input
                ref="search"
                :placeholder="$t('search')"
                v-model="keyword"
                @focus="open = true"
                @blur="close"
                @mousedown.stop
            />
            <item-list v-if="hasResults" class="hc-campaign-item-search-result">
                <available-item
                    v-for="(item, i) in filteredItems"
                    :key="item.type + '-' + item.key"
                    :id="
                        'hc-campaign-item-search-result-' +
                        item.type +
                        '-' +
                        item.id
                    "
                    :selected="selected == i"
                    :item="item"
                    @click.stop.prevent="setAction(item)"
                />
            </item-list>
        </div>
        <template v-else="actionItem">
            <item-list>
                <item class="bordered">
                    <icon :class="actionItem.icon" icon-size="14" size="30" />
                    <div
                        class="hc-item-main-content"
                        v-text="actionItem.name"
                    ></div>
                    <icon
                        @mousedown.stop="remove"
                        class="fa fa-times icon-red"
                        icon-size="11"
                        size="30"
                    />
                </item>
            </item-list>
            <item-list @mousedown.stop @mouseup.stop @change.stop>
                <sms-action
                    v-if="item.action == 'prospect-sms'"
                    :action="item"
                />
                <message-action
                    v-else-if="item.action == 'prospect-message'"
                    :action="item"
                />
                <event-action
                    v-else-if="item.action == 'prospect-event'"
                    :action="item"
                />
                <attach-label-action
                    v-else-if="item.action == 'prospect-attach-label'"
                    :action="item"
                />
                <detach-label-action
                    v-else-if="item.action == 'prospect-detach-label'"
                    :action="item"
                />
                <attach-user-action
                    v-else-if="item.action == 'prospect-attach-user'"
                    :action="item"
                />
                <detach-user-action
                    v-else-if="item.action == 'prospect-detach-user'"
                    :action="item"
                />
                <attach-group-action
                    v-else-if="item.action == 'prospect-attach-group'"
                    :action="item"
                />
                <detach-group-action
                    v-else-if="item.action == 'prospect-detach-group'"
                    :action="item"
                />
                <file-document-action
                    v-else-if="item.action == 'prospect-file-document'"
                    :action="item"
                />
                <message-document-action
                    v-else-if="item.action == 'prospect-message-document'"
                    :action="item"
                />
                <order-action
                    v-else-if="item.action == 'prospect-order'"
                    :action="item"
                />
            </item-list>
        </template>
    </movable>
</template>

<style>
.hc-campaign-item {
    outline: 1px solid #00000022;
    box-shadow: 0 4px 12px -6px #0005;
    background-color: white;
    display: flex;
    flex-direction: column;
    border-radius: 2px;
}

.hc-campaign-item.removing {
    transition: transform ease-out 70ms, opacity ease-out 70ms;
    transform: scale(0.85);
    opacity: 0;
}

.hc-campaign-item:hover {
    outline: 2px solid #7939b8;
}

.hc-campaign-item-remove {
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

.hc-campaign-item:hover > .hc-campaign-item-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-campaign-item-search {
    width: 100%;
    height: 30px;
    position: relative;
}

.hc-campaign-item-search > input {
    width: 100%;
    height: 100%;
    border: none;
    padding: 0 15px;
}

.hc-campaign-item-search-result {
    position: absolute;
    width: 100%;
    height: auto;
    top: 30px;
    border: 1px solid #ddd;
    background-color: #fff;
    max-height: 288px;
}

.hc-campaign-item-search-result > .selected {
    background-color: #12a0f3;
}

.hc-campaign-item-search-result > .selected * {
    color: white !important;
}

.hc-campaign-item-value-row {
    width: 100%;
    display: flex;
    flex-direction: row;
    position: relative;
    min-height: 30px;
    align-items: center;
    padding: 2px 4px;
}

.hc-campaign-item-value-row-label {
    font-weight: 600;
    flex: 1;
    padding-left: 4px;
    font-size: 11px;
}

.hc-campaign-item-value-row-select {
    position: relative;
    padding-right: 20px;
    height: 26px;
    line-height: 26px;
}

.hc-campaign-item-value-row-select:after {
    border-left: 4px solid transparent;
    border-radius: 2px;
    border-right: 4px solid transparent;
    border-top: 5px solid #888888;
    content: "";
    height: 0;
    pointer-events: none;
    position: absolute;
    right: 7px;
    top: 12px;
    width: 0;
}

.hc-campaign-item-value-row-select > * {
    -webkit-appearance: none;
    color: #0ea0cf;
    border: none;
    padding: 0 8px;
    height: 100%;
    width: 100%;
    overflow: hidden;
    text-align: right;
    max-width: 140px;
    height: 26px;
}

.hc-campaign-item-value-row-select > *:focus {
    text-align: left;
}

.hc-campaign-item-value-row-select > select {
    border-radius: 4px;
}

.hc-campaign-item-value-row-select > select > option {
    color: #000000;
    background-color: white;
}

.hc-campaign-item-value-row > textarea {
    width: 100%;
    height: 70px;
    border-radius: 4px;
    border: 1px solid #ddd;
    padding: 8px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import Movable from "@/components/Movable.vue";
import AvailableItem from "./AvailableItem.vue";
import SmsAction from "./action/Sms.vue";
import MessageAction from "./action/Message.vue";
import EventAction from "./action/Event.vue";
import AttachLabelAction from "./action/AttachLabel.vue";
import DetachLabelAction from "./action/DetachLabel.vue";
import AttachUserAction from "./action/AttachUser.vue";
import DetachUserAction from "./action/DetachUser.vue";
import AttachGroupAction from "./action/AttachGroup.vue";
import DetachGroupAction from "./action/DetachGroup.vue";
import FileDocumentAction from "./action/FileDocument.vue";
import MessageDocumentAction from "./action/MessageDocument.vue";
import OrderAction from "./action/Order.vue";

import {
    UPDATE_CAMPAIGN_ACTION,
    REMOVE_CAMPAIGN_ACTION,
} from "@/actions/project/campaign";

export default {
    components: {
        Movable,
        AvailableItem,

        SmsAction,
        MessageAction,
        EventAction,
        AttachLabelAction,
        DetachLabelAction,
        AttachUserAction,
        DetachUserAction,
        AttachGroupAction,
        DetachGroupAction,
        FileDocumentAction,
        MessageDocumentAction,
        OrderAction,
    },

    props: {
        /**
         *
         */
        item: {
            type: Object,
            default: null,
        },

        /**
         *
         */
        type: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            removing: false,
            open: false,
            keyword: "",

            actions: [
                {
                    key: "prospect-attach-group",
                    name: "Affecter à un groupe",
                    icon: "fa fa-users icon-green",
                },
                {
                    key: "prospect-attach-label",
                    name: "Affecter à un filtre",
                    icon: "fa fa-tags icon-purple",
                },
                {
                    key: "prospect-attach-user",
                    name: "Affecter à un utilisateur",
                    icon: "fa fa-user-plus icon-green",
                },
                {
                    key: "prospect-delete",
                    name: "Supprimer le prospect",
                    icon: "fa fa-trash icon-red",
                },
                {
                    key: "prospect-detach-group",
                    name: "Détacher du groupe ...",
                    icon: "fa fa-users",
                },
                {
                    key: "prospect-detach-label",
                    name: "Détacher du filtre ...",
                    icon: "fa fa-tags icon-red",
                },
                {
                    key: "prospect-detach-user",
                    name: "Détacher de l'utilisateur ...",
                    icon: "fa fa-user icon-red",
                },
                {
                    key: "prospect-event",
                    name: "Créer un RDV",
                    icon: "fa fa-calendar icon-purple",
                },
                {
                    key: "prospect-get-lat-lng",
                    name: "Récupérer la latitude et longitude",
                    icon: "fa fa-map-marker icon-blue",
                },
                {
                    key: "prospect-file-document",
                    name: "Générer le document X dans le dossier ...",
                    icon: "fa fa-file-pdf icon-garnet",
                },
                {
                    key: "prospect-message",
                    name: "Envoyer un message dans le canal de discussion ...",
                    icon: "fa fa-envelope icon-green",
                },
                {
                    key: "prospect-message-document",
                    name: "Envoyer le document X dans le canal de discussion ...",
                    icon: "fa fa-file-pdf icon-garnet",
                },
                {
                    key: "prospect-not-processed",
                    name: "Ne pas archiver le prospect",
                    icon: "fa fa-unlock icon-blue",
                },
                {
                    key: "prospect-order",
                    name: "Générer le devis ...",
                    icon: "fa fa-shopping-bag icon-cyan",
                },
                {
                    key: "prospect-processed",
                    name: "Archiver le prospect",
                    icon: "fa fa-lock icon-orange",
                },
                {
                    key: "prospect-sms",
                    name: "Envoyer un SMS",
                    icon: "fa fa-comments icon-purple",
                },
            ],
        };
    },

    mounted() {
        if (this.$refs.search) {
            this.$refs.search.focus();
        }
    },

    methods: {
        /**
         * Remove current campaign
         */
        remove() {
            if (this.item.id > 0) {
                store.dispatch(REMOVE_CAMPAIGN_ACTION, this.item.id);
            }
        },

        /**
         *
         */
        close() {
            setTimeout(() => {
                //this.open = false;
            }, 250);
        },

        setAction(action) {
            store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                id: this.item.id,
                name: action.name,
                action: action.key,
            });
        },

        /**
         *
         */
        change(style) {
            this.item.style = {
                ...this.item.style,
                ...style,
            };
        },

        /**
         * Update action
         * css styles
         */
        updateStyle(style) {
            store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                id: this.item.id,
                style,
            });
        },
    },

    computed: {
        // Store
        ...mapGetters(["campaignZoom"]),

        availableItems() {
            return [
                ...this.actions.map((action) => ({
                    ...action,
                    type: "action",
                })),
            ];
        },

        filteredItems() {
            const keyword = removeStringAccent(this.keyword);

            return this.availableItems.filter(
                (item) => removeStringAccent(item.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        hasResults() {
            return this.open && this.filteredItems.length > 0;
        },

        /**
         *
         */
        actionItem() {
            return this.actions.find(
                (action) => action.key == this.item.action
            );
        },
    },
};
</script>
