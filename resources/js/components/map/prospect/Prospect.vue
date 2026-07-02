<template>
    <div :class="['hc-map-prospect', prospect && prospect.id ? '' : 'hide']">
        <div class="hc-map-prospect-content">
            <template v-if="prospect && prospect.id">
                <div class="hc-map-prospect-header">
                    <icon class="fa fa-user" :size="40"></icon>
                    <router-link
                        :to="{
                            name: 'prospect.show',
                            params: {
                                project: project.slug,
                                prospect: prospect.id,
                            },
                        }"
                        class="hc-map-prospect-title"
                        v-text="prospectFullName"
                    ></router-link>
                    <div
                        class="hc-map-prospect-close hc-flex-column hc-flex-center"
                        @click="close"
                    >
                        &times;
                    </div>
                </div>

                <div class="hc-map-prospect-body hc-flex-column">
                    <search v-model="fieldKeyword" />

                    <item @click="$emit('search', mapProspect)">
                        <icon class="fa fa-search" />
                        <div
                            class="hc-item-main-content hc-map-prospect-bloc-title"
                            v-text="$t('map.prospect.search')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>

                    <div class="hc-map-prospect-body-content hc-flex-1">
                        <!-- Default fields-->
                        <tree-layout
                            class="hc-map-prospect-bloc"
                            :open="true"
                            v-if="defaultFields.length > 0"
                        >
                            <template #header>
                                <item>
                                    <icon
                                        class="fa fa-info-circle"
                                        color="#333333"
                                    />
                                    <div
                                        class="hc-item-main-content hc-map-prospect-bloc-title"
                                        v-text="
                                            $t('map.prospect.default_infos')
                                        "
                                    ></div>
                                    <icon class="fa fa-caret-down" />
                                </item>
                            </template>
                            <template #body>
                                <default-field
                                    v-for="field in defaultFields"
                                    :key="field.attribute"
                                    :field="field"
                                    :prospect="prospect"
                                />
                            </template>
                        </tree-layout>

                        <!-- Meta fields-->
                        <tree-layout
                            class="hc-map-prospect-bloc"
                            v-if="metaFields.length > 0"
                        >
                            <template #header>
                                <item>
                                    <icon
                                        class="fa fa-info-circle"
                                        color="#333333"
                                    />
                                    <div
                                        class="hc-item-main-content hc-map-prospect-bloc-title"
                                        v-text="$t('map.prospect.other_infos')"
                                    ></div>
                                    <icon class="fa fa-caret-down" />
                                </item>
                            </template>
                            <template #body>
                                <meta-field
                                    v-for="field in metaFields"
                                    :key="field.attribute"
                                    :field="field"
                                    :prospect="prospect"
                                />
                            </template>
                        </tree-layout>

                        <!-- Labels -->
                        <tree-layout
                            class="hc-map-prospect-bloc"
                            :open="true"
                            v-if="
                                categoriesWithLabels.length > 0 ||
                                categoriesWithoutLabels.length > 0
                            "
                        >
                            <template #header>
                                <item>
                                    <icon class="fa fa-tags" color="#333333" />
                                    <div
                                        class="hc-item-main-content hc-map-prospect-bloc-title"
                                        v-text="$t('map.prospect.labels')"
                                    ></div>
                                    <icon
                                        tag="a"
                                        class="fa fa-plus"
                                        @click.prevent.stop="manageLabels(null)"
                                    />
                                    <icon class="fa fa-caret-down" />
                                </item>
                            </template>
                            <template #body>
                                <category
                                    v-for="category in categoriesWithLabels"
                                    :key="category.id"
                                    :category="category"
                                    :prospect="prospect"
                                    @click="manageLabels(category)"
                                />
                                <!--category
                                    v-for="category in categoriesWithoutLabels"
                                    :key="category.id"
                                    :category="category"
                                    :prospect="prospect"
                                    @click="manageLabels(category)"
                                /-->
                            </template>
                        </tree-layout>

                        <!-- Events -->
                        <tree-layout class="hc-map-prospect-bloc" :open="true">
                            <template #header>
                                <item>
                                    <icon
                                        class="fa fa-calendar"
                                        color="#333333"
                                    />
                                    <div
                                        class="hc-item-main-content hc-map-prospect-bloc-title"
                                        v-text="$t('map.prospect.appointments')"
                                    ></div>
                                    <icon
                                        tag="a"
                                        class="fa fa-plus"
                                        @click.prevent.stop="manageEvents"
                                    />
                                    <icon class="fa fa-caret-down" />
                                </item>
                            </template>
                            <template #body>
                                <event
                                    v-for="event in prospect.events"
                                    :key="event.id"
                                    :event="event"
                                    :prospect="prospect"
                                />
                            </template>
                        </tree-layout>

                        <!-- Orders -->
                        <tree-layout class="hc-map-prospect-bloc" :open="true">
                            <template #header>
                                <item>
                                    <icon
                                        class="fa fa-shopping-cart"
                                        color="#333333"
                                    />
                                    <div
                                        class="hc-item-main-content hc-map-prospect-bloc-title"
                                        v-text="$t('map.prospect.orders')"
                                    ></div>
                                    <icon
                                        tag="a"
                                        class="fa fa-plus"
                                        @click.prevent.stop="manageOrders"
                                    />
                                    <icon class="fa fa-caret-down" />
                                </item>
                            </template>
                            <template #body>
                                <order
                                    v-for="order in prospect.orders"
                                    :key="order.id"
                                    :order="order"
                                    :prospect="prospect"
                            /></template>
                        </tree-layout>
                    </div>
                </div>

                <div class="hc-map-prospect-footer">
                    <icon
                        tag="a"
                        class="fa fa-calendar icon-purple"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageEvents"
                    />
                    <icon
                        v-if="
                            prospect.phone_number ||
                            prospect.mobile_phone_number
                        "
                        tag="a"
                        class="fa fa-phone icon-orange"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageInteractions"
                    />
                    <icon
                        v-if="prospect.mobile_phone_number"
                        tag="a"
                        class="fa fa-comment icon-purple"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageSms"
                    />
                    <icon
                        tag="a"
                        class="fa fa-envelope icon-green"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageMessages"
                    />
                    <icon
                        tag="a"
                        class="fa fa-folder icon-blue"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageFiles"
                    />
                    <icon
                        tag="a"
                        class="fa fa-shopping-cart icon-cyan"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageOrders"
                    />
                    <icon
                        tag="a"
                        class="fa fa-file-pdf icon-garnet"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent=""
                    />
                    <icon
                        tag="a"
                        class="fa fa-user-plus icon-orange"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageUsers"
                    />
                    <icon
                        tag="a"
                        class="fa fa-users icon-brown"
                        :size="28"
                        color="#333333"
                        :icon-size="12"
                        @click.prevent="manageGroups"
                    />
                </div>
            </template>
        </div>
    </div>
</template>

<style>
.hc-map-prospect {
    height: 100%;
    padding: 0;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
    transition: all 100ms ease-out;
}

.hc-map-prospect.hide {
    visibility: hidden;
    opacity: 0;
    transform: scale(0.95);
    pointer-events: none;
}

.hc-map-prospect-content {
    width: 250px;
    height: 100%;
    background-color: #fffa;
    display: flex;
    flex-direction: column;
    border-radius: 0;
    visibility: visible;
    opacity: 1;
    transform: scale(1);
    box-shadow: 0 2px 3px #0003;
    backdrop-filter: blur(5px);
}

.hc-map-prospect-header {
    border-bottom: 1px solid #eeeeee;
    display: flex;
    flex-direction: row;
    font-size: 13px;
    font-weight: bold;
    height: 40px;
    line-height: 40px;
}

.hc-map-prospect-close {
    height: 30px;
    text-align: center;
    width: 30px;
    font-weight: bold;
    font-size: 15px;
    color: #888888;
    cursor: pointer;
    margin: 5px;
    border-radius: 15px;
}

.hc-map-prospect-close:hover {
    background-color: #eeeeee;
    color: #555555;
}

.hc-map-prospect-title {
    flex: 1;
    font-size: 11px;
    font-weight: 600;
    line-height: 40px;
    padding: 0 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #333333;
    padding-left: 5px;
}

.hc-map-prospect-body {
    flex: 1;
    overflow: hidden;
    background-color: white;
}

.hc-map-prospect-body-content {
    overflow: auto;
}

.hc-map-prospect-bloc {
    width: 100%;
    border-bottom: 1px solid #eeeeee;
}

.hc-map-prospect-bloc-title {
    font-weight: normal;
}

.hc-map-prospect-body .tree-layout-body {
    padding: 0 5px !important;
}

.hc-map-prospect-footer {
    border-top: 1px solid #eeeeee;
    display: flex;
    flex-direction: row;
    font-size: 13px;
    font-weight: bold;
    height: 40px;
    line-height: 40px;
    padding: 5px;
    justify-content: flex-end;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

import { SET_MAP_PROSPECT } from "@/actions/project/map";
import {
    SET_PROSPECT,
    SHOW_PROSPECT,
    SET_PROSPECT_CATEGORY,
} from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { SET_EVENT } from "@/actions/project/event";
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";

import DefaultField from "./DefaultField.vue";
import MetaField from "./MetaField.vue";
import Category from "./Category.vue";
import Event from "./Event.vue";
import Order from "./Order.vue";

export default {
    components: {
        DefaultField,
        MetaField,
        Category,
        Event,
        Order,
    },

    data() {
        return {
            fieldKeyword: "",
        };
    },

    methods: {
        /**
         * Close prospect info popup
         */
        close() {
            store.commit(SET_MAP_PROSPECT, null);
            store.commit(SET_PROSPECT, null);
        },

        /**
         * Manage prospect files
         * See: @/components/prospect/file/Slide.vue
         */
        manageFiles() {
            store.commit(OPEN_SLIDE, "prospect-manage-files");
            store.commit(SET_PROSPECT, this.prospect);
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages() {
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
            store.commit(SET_PROSPECT, this.prospect);
        },

        /**
         * Manage prospect orders
         * See: @/components/prospect/order/Slide.vue
         */
        manageOrders() {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
            store.commit(SET_PROSPECT, this.prospect);
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
         * Manage prospect events
         * See: @/components/prospect/event/Slide.vue
         */
        manageEvents() {
            if (this.disabled || this.prospect.id <= 0) {
                return;
            }

            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(90);

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, {
                prospect: this.prospect,
                user: this.$store.state.auth.user,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });
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
         * Manage prospect labels
         * See: @/components/prospect/label/Slide.vue
         */
        manageLabels(category) {
            store.commit(OPEN_SLIDE, "prospect-manage-labels");
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_PROSPECT_CATEGORY, category);
        },
    },

    watch: {
        /**
         *
         * @param {*} e
         */
        async mapProspect(newValue) {
            store.commit(SET_PROSPECT, newValue);
            // this.prospect = newValue;

            if (!newValue) {
                return;
            }

            store.dispatch(SHOW_PROSPECT, newValue.id);

            /*const { data } = await ProspectService.show(
                this.project.slug,
                newValue.id
            );
            this.prospect = data;*/
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospect",
            "mapProspect",
            "fields",
            "categories",
        ]),

        /**
         * Get current prospect full name
         *
         * @param {*} state
         * @returns
         */
        prospectFullName() {
            return this.prospect
                ? [this.prospect.first_name, this.prospect.last_name]
                      .filter((n) => n)
                      .join(" ")
                : "";
        },

        defaultFields() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.fields.filter(
                (field) =>
                    field.for == "prospect" &&
                    !field.meta &&
                    removeStringAccent(field.name).indexOf(keyword) >= 0
            );
        },

        metaFields() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.fields.filter(
                (field) =>
                    field.for == "prospect" &&
                    field.meta &&
                    removeStringAccent(field.name).indexOf(keyword) >= 0
            );
        },

        categoriesWithLabels() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    removeStringAccent(category.name).indexOf(keyword) >= 0 &&
                    this.prospect.labels &&
                    this.prospect.labels.find(
                        (label) => label.category_id == category.id
                    )
            );
        },

        categoriesWithoutLabels() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    removeStringAccent(category.name).indexOf(keyword) >= 0 &&
                    this.prospect.labels &&
                    !this.prospect.labels.find(
                        (label) => label.category_id == category.id
                    )
            );
        },
    },
};
</script>
