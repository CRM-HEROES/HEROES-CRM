<template>
    <tr
        :id="'prospect-' + prospect.id"
        :class="[
            prospect.deleted_at ? 'deleted' : '',
            prospect.processed_at ? 'processed' : '',
            isSelected ? 'selected' : '',
            prospect.id == viewedProspect ? 'viewed' : '',
        ]"
    >
        <td class="fixed">
            <!-- Select prospect -->
            <label class="hc-table-selector">
                <input
                    type="checkbox"
                    v-model="selected"
                    :value="prospect.id"
                    @click="toggleSelectedProspect"
                />
                <span></span>
            </label>

            <div class="hc-table-row-options">
                <template
                    v-for="menu in projectUserSettingsProspectsTableMenus"
                >
                    <!-- View prospect -->
                    <router-link
                        v-if="menu == 'view'"
                        class="hc-table-prospect-option-view"
                        :to="{
                            name: 'prospect.show',
                            params: {
                                project: this.project.slug,
                                prospect: this.prospect.id,
                            },
                        }"
                        @click="viewProspect"
                        ><i class="fa fa-eye"></i
                    ></router-link>

                    <!-- Document -->
                    <a
                        v-else-if="menu == 'document' && canFile"
                        @click.prevent="manageDocuments"
                        ><i class="fa fa-file-pdf"></i
                    ></a>

                    <!-- Duplicate -->
                    <a
                        v-else-if="menu == 'duplicate'"
                        @click.prevent="duplicate"
                    >
                        <i class="fa fa-copy"></i>
                        <!--loading :loading="duplicating" /-->
                    </a>

                    <!-- File -->
                    <a
                        v-else-if="menu == 'file' && canFile"
                        :class="[
                            isOptionFiltered('file') ? 'filtered' : '',
                            prospect.files && prospect.files.length > 0
                                ? 'has-items'
                                : '',
                        ]"
                        @click.prevent="manageFiles"
                        ><i class="fa fa-folder"></i
                    ></a>

                    <!-- Message -->
                    <a
                        v-else-if="menu == 'message' && canMessage"
                        :class="[
                            isOptionFiltered('message') ? 'filtered' : '',
                            prospect.messages && prospect.messages.length > 0
                                ? 'has-items'
                                : '',
                        ]"
                        @click.prevent="manageMessages"
                        ><i class="fa fa-envelope"></i
                    ></a>

                    <!-- Order -->
                    <a
                        v-else-if="
                            menu == 'order' && can('all.prospect.order.view')
                        "
                        :class="[
                            isOptionFiltered('order') ? 'filtered' : '',
                            prospect.orders && prospect.orders.length > 0
                                ? 'has-items'
                                : '',
                        ]"
                        @click.prevent="manageOrders"
                        ><i class="fa fa-shopping-cart"></i
                    ></a>

                    <!-- Interaction -->
                    <a
                        v-else-if="
                            menu == 'interaction' &&
                            can('all.prospect.interaction.view')
                        "
                        :class="[
                            isOptionFiltered('interaction') ? 'filtered' : '',
                            prospect.interactions &&
                            prospect.interactions.length > 0
                                ? 'has-items'
                                : '',
                        ]"
                        ><i
                            @click.stop="manageInteractions"
                            class="fa fa-phone"
                            v-if="
                                prospect.phone_number ||
                                prospect.mobile_phone_number
                            "
                        ></i
                    ></a>

                    <!-- SMS -->
                    <a
                        v-else-if="
                            menu == 'sms' && can('all.prospect.sms.view')
                        "
                        :class="[
                            isOptionFiltered('sms') ? 'filtered' : '',
                            prospect.sms && prospect.sms.length > 0
                                ? 'has-items'
                                : '',
                        ]"
                        ><i
                            @click.prevent="manageSms"
                            class="fa fa-comment"
                            v-if="prospect.mobile_phone_number"
                        ></i
                    ></a>

                    <!-- Questionnaire -->
                    <a
                        v-else-if="menu == 'questionnaire'"
                        :class="[
                            isOptionFiltered('questionnaire') ? 'filtered' : '',
                        ]"
                        @click.prevent="manageQuestionnaires"
                        ><i class="fa fa-clipboard"></i
                    ></a>

                    <!-- User -->
                    <a
                        v-else-if="menu == 'user'"
                        :class="[isOptionFiltered('user') ? 'filtered' : '']"
                        @click.prevent="manageUsers"
                        ><i class="fa fa-user-plus"></i
                    ></a>

                    <!-- Group -->
                    <a
                        v-else-if="menu == 'group'"
                        :class="[isOptionFiltered('group') ? 'filtered' : '']"
                        @click.prevent="manageGroups"
                        ><i class="fa fa-users"></i
                    ></a>

                    <!-- Revision -->
                    <a
                        v-else-if="menu == 'revision'"
                        @click.prevent="showRevisions"
                        ><i class="fa fa-pen"></i
                    ></a>
                </template>
            </div>

            <!-- List of fixed columns -->
            <div class="hc-table-fixed-cells">
                <cell
                    v-for="column in fixedColumns"
                    tag="div"
                    :key="prospect.id + '-' + column.key"
                    :prospect="prospect"
                    :column="column"
                />
            </div>
        </td>

        <!-- List of none fixed columns -->
        <cell
            v-for="column in notFixedColumns"
            :key="prospect.id + '-' + column.key"
            :prospect="prospect"
            :column="column"
        />
    </tr>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_SELECTED_PROSPECTS,
    TOGGLE_SELECTED_PROSPECTS,
    SET_VIEWED_PROSPECT,
} from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { SET_PROSPECT, DUPLICATE_PROSPECT } from "@/actions/project/prospect";
import { OPEN_SLIDE, OPEN_LEFT_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";

// Components
import Cell from "./Cell.vue";

export default {
    components: {
        Cell,
    },

    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },

        /**
         * List of fixed columns
         */
        fixedColumns: {
            type: Array,
        },

        /**
         * List of none fixed columns
         */
        notFixedColumns: {
            type: Array,
        },

        /**
         * Index of the row in the prospects table
         * we need it when we use SHIFT
         * to select multiple prospects
         * See: toggleSelectedProspect
         */
        index: {
            type: Number,
        },
    },

    methods: {
        /**
         * Handle SHIFT
         * when clicking checkbox
         *
         * @param {*} event
         */
        toggleSelectedProspect(event) {
            const index = this.index;
            const shift = event.shiftKey;
            const checked = event.currentTarget.checked;

            store.commit(TOGGLE_SELECTED_PROSPECTS, { index, shift, checked });
        },

        /**
         *
         */
        viewProspect() {
            store.commit(SET_VIEWED_PROSPECT, this.prospect.id);
        },

        /**
         * Manage prospect documents
         * See: @/components/prospect/document/Slide.vue
         */
        manageDocuments() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-documents");
        },

        /**
         * Manage prospect files
         * See: @/components/prospect/file/Slide.vue
         */
        manageFiles() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-files");
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         * Manage prospect orders
         * See: @/components/prospect/order/Slide.vue
         */
        manageOrders() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },

        /**
         * Manage prospect interactions
         * See: @/components/prospect/interaction/Slide.vue
         */
        manageInteractions() {
            store.commit(UPDATE_SELECTED_PROSPECTS, []);
            store.commit(SET_INTERACTION_PROSPECT, this.prospect);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
        },

        /**
         * Manage prospect sms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageSms() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },

        /**
         * Manage prospect forms
         * See: @/components/prospect/questionnaire/Slide.vue
         */
        manageQuestionnaires() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-questionnaires");
        },

        /**
         * Manage prospect users
         * See: @/components/prospect/user/Slide.vue
         */
        manageUsers() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-users");
        },

        /**
         * Manage prospect groups
         * See: @/components/prospect/group/Slide.vue
         */
        manageGroups() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-groups");
        },

        /**
         *
         */
        showRevisions() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-revisions");
        },

        /**
         *
         */
        duplicate() {
            hcConfirm(
                "Voulez-vous vraiment dupliquer ce prospect ?",
                async () => {
                    try {
                        store.dispatch(DUPLICATE_PROSPECT, this.prospect.id);
                    } finally {
                    }
                }
            );
        },

        /**
         * Check if option is filtered
         * @param {*} option
         */
        isOptionFiltered(option) {
            // By files
            if (option == "file") {
                return (
                    this.prospectsParamExists("withFiles") ||
                    this.prospectsParamExists("withoutFiles")
                );
                // By messages
            } else if (option == "message") {
                return (
                    this.prospectsParamExists("withMessages") ||
                    this.prospectsParamExists("withoutMessages")
                );
                // By orders
            } else if (option == "order") {
                return (
                    this.prospectsParamExists("withOrders") ||
                    this.prospectsParamExists("withoutOrders")
                );
                // By interactions
            } else if (option == "interaction") {
                return (
                    this.prospectsParamExists("withInteractions") ||
                    this.prospectsParamExists("withoutInteractions")
                );
                // By sms
            } else if (option == "sms") {
                return (
                    this.prospectsParamExists("withSms") ||
                    this.prospectsParamExists("withoutSms")
                );
                // By questionnaires
            } else if (option == "questionnaire") {
                return (
                    this.prospectsParamExists("withQuestionnaires") ||
                    this.prospectsParamExists("withoutQuestionnaires")
                );
                // By users
            } else if (option == "user") {
                return (
                    this.prospectsParamExists("withUsers") ||
                    this.prospectsParamExists("withoutUsers")
                );
                // By groups
            } else if (option == "group") {
                return (
                    this.prospectsParamExists("withGroups") ||
                    this.prospectsParamExists("withoutGroups")
                );
            }

            return false;
        },
    },

    computed: {
        /**
         * Select a row
         */
        selected: {
            get() {
                return this.prospectsSelected;
            },
            set(value) {
                store.commit(UPDATE_SELECTED_PROSPECTS, value);
            },
        },

        /**
         * Is row selected
         */
        isSelected() {
            return this.prospectsSelected.indexOf(this.prospect.id) >= 0;
        },

        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "viewedProspect",
            "prospectsSelected",
            "prospectsParamExists",
            "projectUserSettingsProspectsTableMenus",
            "can",
            "canMessage",
            "canFile",
        ]),
    },
};
</script>
