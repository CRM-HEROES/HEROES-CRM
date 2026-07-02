<template>
    <component :is="tag" :class="[isFiltered ? 'filtered' : '']" :style="style">
        <street-cell
            v-if="column.key == 'street'"
            :prospect="prospect"
            :field="column.key"
            :default-value="column.default_value"
            :required="column.required"
            :format="column.format"
            :align="column.align ? column.align : 'left'"
        />
        <default-cell
            v-else-if="category == 'default'"
            :prospect="prospect"
            :field="column.key"
            :default-value="column.default_value"
            :required="column.required"
            :format="column.format"
            :align="column.align ? column.align : 'left'"
        />
        <meta-cell
            v-else-if="category == 'meta'"
            :prospect="prospect"
            :field="column.id"
            :type="column.type"
            :default-value="column.default_value"
            :required="column.required"
            :format="column.format"
            :align="column.align ? column.align : 'left'"
        />
        <relation-cell
            v-else-if="category == 'category'"
            :prospect="prospect"
            :items="categoryLabels(column.id)"
            @click="manageLabels"
        />
        <thread-cell
            v-else-if="category == 'thread'"
            :prospect="prospect"
            :thread="column.id"
            @click="manageMessages(column.id)"
        />
        <event-cell
            v-if="category == 'events'"
            :prospect="prospect"
            :calendar="column.id != 'events' ? column.id : null"
            @click="manageEvents"
        />
        <event-field-cell
            v-if="category == 'event-field'"
            :prospect="prospect"
            :field="column.id"
        />
        <event-field-cell
            v-if="category == 'event-meta-field'"
            :prospect="prospect"
            :field="column.id"
            :meta="true"
        />
        <interaction-cell
            v-else-if="category == 'interactions'"
            :prospect="prospect"
            @click="manageInteractions"
        />
        <sms-cell
            v-else-if="category == 'sms'"
            :prospect="prospect"
            @click="manageSms"
        />
        <relation-cell
            v-else-if="category == 'users'"
            :prospect="prospect"
            :items="prospect.users ? prospect.users : []"
            @click="manageUsers"
        />
        <relation-cell
            v-else-if="category == 'groups'"
            :prospect="prospect"
            :items="prospect.groups ? prospect.groups : []"
            @click="manageGroups"
        />
        <relation-cell
            v-else-if="category == 'orders'"
            :prospect="prospect"
            :items="
                prospect.orders
                    ? prospect.orders.map((order) => ({
                          ...(order.status ? order.status : {}),
                          ...order,
                      }))
                    : []
            "
            :item-clicked="showOrder"
            @click="manageOrders"
        />
        <relation-cell
            v-else-if="category == 'import'"
            :prospect="prospect"
            :items="prospect.import ? [prospect.import] : []"
        />
        <relation-cell
            v-else-if="category == 'pipedrive-accounts'"
            :prospect="prospect"
            :items="
                prospect.pipedrive_accounts ? prospect.pipedrive_accounts : []
            "
        />
        <relation-cell
            v-else-if="category == 'creator'"
            :prospect="prospect"
            :items="prospect.creator ? [prospect.creator] : []"
        />
    </component>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import {
    SET_PROSPECT,
    SET_PROSPECT_CATEGORY,
} from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { SET_EVENT } from "@/actions/project/event";

import { SET_PROSPECT_MESSAGE_THREAD } from "@/actions/project/prospect/message";
import {
    SHOW_PROSPECT_ORDER,
    SET_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";

// Components
import DefaultCell from "./cell/DefaultCell.vue";
import EventCell from "./cell/EventCell.vue";
import EventFieldCell from "./cell/EventFieldCell.vue";
import InteractionCell from "./cell/InteractionCell.vue";
import MetaCell from "./cell/MetaCell.vue";
import RelationCell from "./cell/RelationCell.vue";
import SmsCell from "./cell/SmsCell.vue";
import StreetCell from "./cell/StreetCell.vue";
import ThreadCell from "./cell/ThreadCell.vue";

export default {
    components: {
        DefaultCell,
        EventCell,
        EventFieldCell,
        InteractionCell,
        MetaCell,
        RelationCell,
        SmsCell,
        StreetCell,
        ThreadCell,
    },

    props: {
        /**
         * HTML tag
         * Default <td>
         * When the column is fixed
         * we use <div> as tag
         */
        tag: {
            type: String,
            default: "td",
        },

        /**
         * Column
         */
        column: {
            type: Array,
        },

        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },
    },

    methods: {
        /**
         * Associated prospect labels
         * that belongs to the given category
         *
         * @param {*} categoryId
         */
        categoryLabels(categoryId) {
            if (!this.prospect.labels || this.prospect.id <= 0) {
                return [];
            }

            return this.prospect.labels.filter(
                (label) => label.category_id == categoryId
            );
        },

        /**
         * Manage prospect labels
         * See: @/components/prospect/label/Slide.vue
         */
        manageLabels() {
            if (this.disabled || this.prospect.id <= 0) {
                return;
            }

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(
                SET_PROSPECT_CATEGORY,
                this.categories.find((c) => c.id == this.column.id)
            );
            store.commit(OPEN_SLIDE, "prospect-manage-labels");
        },

        /**
         * Manage prospect events
         * See: @/components/prospect/event/Slide.vue
         */
        manageEvents() {
            if (!this.canEvent) {
                flashWarning({
                    title: "RDV",
                    body: "Aucun type de RDV ne vous a été attribué, veuillez en parler avec votre administrateur.",
                });
                return;
            }

            if (this.disabled || this.prospect.id <= 0) {
                return;
            }

            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(120);

            const event = {
                prospect: this.prospect,
                // user: this.$store.state.auth.user,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            };

            if (this.column.id != "events") {
                event.calendar_id = this.column.id;
            }

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, event);
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         * Manage prospect interactions
         * See: @/components/prospect/interaction/Slide.vue
         */
        manageInteractions() {
            store.commit(SET_INTERACTION_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-interactions");
        },

        /**
         * Manage prospect sms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageSms() {
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
            store.commit(SET_PROSPECT, this.prospect);
        },

        /**
         * Manage prospect users
         * See: @/components/prospect/user/Slide.vue
         */
        manageUsers() {
            if (this.disabled || this.prospect.id <= 0) {
                return;
            }

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-users");
        },

        /**
         * Manage prospect groups
         * See: @/components/prospect/group/Slide.vue
         */
        manageGroups() {
            if (this.disabled || this.prospect.id <= 0) {
                return;
            }

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-groups");
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages(threadId) {
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(
                SET_PROSPECT_MESSAGE_THREAD,
                this.threads.find((thread) => thread.id == threadId)
            );
        },

        /**
         *
         */
        manageOrders() {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_PROSPECT_ORDER, null);
        },

        /**
         *
         * @param {*} order
         */
        showOrder(order) {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");

            store.commit(SET_PROSPECT, this.prospect);
            store.dispatch(SHOW_PROSPECT_ORDER, order.id);
        },
    },

    computed: {
        ...mapGetters([
            "categories",
            "prospectsParamExists",
            "threads",
            "canEvent",
        ]),

        /**
         * Do not allow user
         * to edit field
         * on certain conditions applied to the prospect
         */
        disabled() {
            return this.prospect.deleted_at || this.prospect.processed_at;
        },

        /**
         * Define the width of the column
         */
        style() {
            const width = this.column.width ? this.column.width : 120;

            return {
                maxWidth: `${width}px`,
                minWidth: `${width}px`,
            };
        },

        /**
         *
         */
        category() {
            return this.column.category;
        },

        /**
         * Check if column is filtered
         */
        isFiltered() {
            switch (this.category) {
                case "users":
                    return (
                        this.prospectsParamExists("withUsers") ||
                        this.prospectsParamExists("withoutUsers")
                    );
                case "groups":
                    return (
                        this.prospectsParamExists("withGroups") ||
                        this.prospectsParamExists("withoutGroups")
                    );
                case "orders":
                    return (
                        this.prospectsParamExists("withOrders") ||
                        this.prospectsParamExists("withoutOrders")
                    );
                case "import":
                    return (
                        this.prospectsParamExists("withImports") ||
                        this.prospectsParamExists("withoutImports")
                    );
                case "events":
                    return (
                        this.prospectsParamExists("withEvents") ||
                        this.prospectsParamExists("withoutEvents")
                    );
                case "interactions":
                    return (
                        this.prospectsParamExists("withInteractions") ||
                        this.prospectsParamExists("withoutInteractions")
                    );
                case "sms":
                    return (
                        this.prospectsParamExists("withSms") ||
                        this.prospectsParamExists("withoutSms")
                    );
                case "category":
                    return (
                        this.prospectsParamExists(
                            "withCategory_" + this.column.id
                        ) ||
                        this.prospectsParamExists(
                            "withoutCategory_" + this.column.id
                        )
                    );
                case "thread":
                    return (
                        this.prospectsParamExists("thread_" + this.column.id) ||
                        this.prospectsParamExists(
                            "withMessages",
                            this.column.id
                        )
                    );
                case "meta":
                    return (
                        this.prospectsParamExists("meta_" + this.column.id) ||
                        this.prospectsParamExists("withMeta_" + this.column.id)
                    );
                default:
                    return (
                        this.prospectsParamExists("field_" + this.column.key) ||
                        this.prospectsParamExists(
                            "withField_" + this.column.key
                        )
                    );
            }
        },
    },
};
</script>
