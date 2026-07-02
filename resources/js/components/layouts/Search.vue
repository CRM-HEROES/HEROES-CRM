<template>
    <form
        id="hc-global-search"
        :class="inputFocused ? 'focused' : ''"
        @click="focusInput"
        @submit.prevent="search"
    >
        <div id="hc-global-search-input-container">
            <input
                ref="input"
                id="hc-global-search-input"
                :placeholder="$t('search') + ' ...'"
                v-model="debouncedKeyword"
                @focus="open = true"
                @blur="close"
                @keydown="selectAutocomplete"
            />
        </div>
        <icon
            tag="button"
            class="fa fa-search"
            id="hc-global-search-input-icon"
            :size="29"
            :icon-size="12"
            color="#AAAAAA"
        >
            <loading :loading="loadingProspect || loadingUser"
        /></icon>
        <item-list
            id="hc-global-search-result"
            v-if="hasResults"
            @click.stop="open = false"
        >
            <search-row
                v-for="(item, i) in filteredItems"
                :key="item.type + '-' + item.item.id"
                :id="
                    'hc-global-search-result-' + item.type + '-' + item.item.id
                "
                :selected="selected == i"
                :item="item.item"
                :type="item.type"
                :keyword="keyword"
            />
        </item-list>
    </form>
</template>

<style>
#hc-global-search {
    height: 100%;
    width: 240px;
    position: relative;
    padding: 3px 0;
}

#hc-global-search-input-container {
    height: 100%;
    width: 100%;
    position: relative;
}

#hc-global-search-input-icon {
    position: absolute;
    right: 2px;
    top: 6px;
}

#hc-global-search-input {
    height: 100%;
    width: 100%;
    border-radius: 7px;
    border: none;
    padding: 0 10px;
    font-size: 12px;
    background-color: #ebebeb;
}

#hc-global-search-input:focus {
    outline: 2px solid #1e88e5;
}

#hc-global-search-result {
    position: absolute;
    left: 0;
    top: 36px;
    padding: 5px;
    width: 100%;
    height: auto;
    min-height: 38px;
    max-height: 290px;
    overflow: auto;
    z-index: 4;
    border-radius: 3px;
    background: #fff;
    box-shadow: 0px 9px 33px 0px rgba(0, 0, 0, 0.07),
        0px 0px 2px 0px rgba(0, 0, 0, 0.25);
}

#hc-global-search-result > .selected {
    background-color: #12a0f3;
}

#hc-global-search-result > .selected * {
    color: white !important;
}

@media (max-width: 767px) {
    #hc-global-search {
        flex: 1;
        position: unset;
        display: flex;
        width: auto;
        align-items: center;
        justify-content: flex-end;
    }

    #hc-global-search-input-container {
        position: absolute;
        top: 26px;
        z-index: 10;
        padding: 5px;
        left: 0;
        height: 46px;
        visibility: hidden;
        opacity: 0;
        transition: all 100ms ease-out;
    }

    .focused #hc-global-search-input-container {
        top: 46px;
        visibility: visible;
        opacity: 1;
    }

    #hc-global-search-input-icon {
        position: relative;
        top: unset;
        left: unset;
    }

    #hc-global-search-result {
        top: 82px;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import GlobalUserService from "@/apis/user";
import ProjectService from "@/apis/project";
import ProspectService from "@/apis/project/prospect";

import SearchRow from "./search/SearchRow.vue";

// Actions
import {
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

import { OPEN_SLIDE } from "@/actions/slide";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_EVENT } from "@/actions/project/event";

import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";

export default {
    components: {
        SearchRow,
    },

    data() {
        return {
            tmpKeyword: "",
            keyword: "",
            loadingProspect: false,
            loadingUser: false,
            loadingProject: false,
            count: 30,
            page: 1,
            prospects: [],
            projects: [],
            globalUsers: [],
            open: false,
            inputFocused: false,
            prospectCancelTokenSource: null,
            userCancelTokenSource: null,
            projectCancelTokenSource: null,
            selected: -1,

            actions: [
                {
                    id: "create-event",
                    name: this.$t(
                        "global_search.actions.prospect.create_event.name"
                    ),
                    description: this.$t(
                        "global_search.actions.prospect.create_event.description"
                    ),
                    keywords: this.$t(
                        "global_search.actions.prospect.create_event.keywords"
                    ),
                    action: this.actionCreateEvent,
                    icon: "fa fa-calendar-plus",
                },
                {
                    id: "create-order",
                    name: this.$t(
                        "global_search.actions.prospect.create_order.name"
                    ),
                    description: this.$t(
                        "global_search.actions.prospect.create_order.description"
                    ),
                    keywords: this.$t(
                        "global_search.actions.prospect.create_order.keywords"
                    ),
                    action: this.actionCreateOrder,
                    icon: "fa fa-shopping-cart",
                },
                {
                    id: "create-sms",
                    name: this.$t(
                        "global_search.actions.prospect.create_sms.name"
                    ),
                    description: this.$t(
                        "global_search.actions.prospect.create_sms.description"
                    ),
                    keywords: this.$t(
                        "global_search.actions.prospect.create_sms.keywords"
                    ),
                    action: this.actionCreateSms,
                    icon: "fa fa-comments",
                },
                {
                    id: "create-interaction",
                    name: this.$t(
                        "global_search.actions.prospect.create_interaction.name"
                    ),
                    description: this.$t(
                        "global_search.actions.prospect.create_interaction.description"
                    ),
                    keywords: this.$t(
                        "global_search.actions.prospect.create_interaction.keywords"
                    ),
                    action: this.actionCreateInteraction,
                    icon: "fa fa-phone",
                },
                {
                    id: "create-file",
                    name: this.$t(
                        "global_search.actions.prospect.create_file.name"
                    ),
                    description: this.$t(
                        "global_search.actions.prospect.create_file.description"
                    ),
                    keywords: this.$t(
                        "global_search.actions.prospect.create_file.keywords"
                    ),
                    action: this.actionCreateFile,
                    icon: "fa fa-folder",
                },
                {
                    id: "create-message",
                    name: this.$t(
                        "global_search.actions.prospect.create_message.name"
                    ),
                    description: this.$t(
                        "global_search.actions.prospect.create_message.description"
                    ),
                    keywords: this.$t(
                        "global_search.actions.prospect.create_message.keywords"
                    ),
                    action: this.actionCreateMessage,
                    icon: "fa fa-envelope",
                },

                {
                    id: "create-calendar",
                    name: this.$t(
                        "global_search.actions.project.create_calendar.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_calendar.keywords"
                    ),
                    action: this.actionCreateCalendar,
                    icon: "fa fa-calendar icon-green",
                },
                {
                    id: "create-category",
                    name: this.$t(
                        "global_search.actions.project.create_category.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_category.keywords"
                    ),
                    action: this.actionCreateCategory,
                    icon: "fa fa-tags icon-green",
                },
                {
                    id: "create-document",
                    name: this.$t(
                        "global_search.actions.project.create_document.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_document.keywords"
                    ),
                    action: this.actionCreateDocument,
                    icon: "fa fa-file-pdf icon-green",
                },
                {
                    id: "create-folder",
                    name: this.$t(
                        "global_search.actions.project.create_folder.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_folder.keywords"
                    ),
                    action: this.actionCreateFolder,
                    icon: "fa fa-folder icon-blue",
                },
                {
                    id: "create-group",
                    name: this.$t(
                        "global_search.actions.project.create_group.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_group.keywords"
                    ),
                    action: this.actionCreateGroup,
                    icon: "fa fa-users icon-green",
                },
                {
                    id: "create-menu",
                    name: this.$t(
                        "global_search.actions.project.create_menu.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_menu.keywords"
                    ),
                    action: this.actionCreateMenu,
                    icon: "fa fa-list icon-green",
                },
                {
                    id: "create-order-action",
                    name: this.$t(
                        "global_search.actions.project.create_order_action.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_order_action.keywords"
                    ),
                    action: this.actionCreateOrderAction,
                    icon: "fa fa-person-digging icon-green",
                },
                {
                    id: "create-order-step",
                    name: this.$t(
                        "global_search.actions.project.create_order_step.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_order_step.keywords"
                    ),
                    action: this.actionCreateOrderStep,
                    icon: "fa fa-step-forward icon-green",
                },
                {
                    id: "create-pipeline",
                    name: this.$t(
                        "global_search.actions.project.create_pipeline.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_pipeline.keywords"
                    ),
                    action: this.actionCreatePipeline,
                    icon: "fa fa-forward",
                },
                {
                    id: "create-product",
                    name: this.$t(
                        "global_search.actions.project.create_product.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_product.keywords"
                    ),
                    action: this.actionCreateProduct,
                    icon: "fa fa-gift icon-green",
                },
                {
                    id: "create-role",
                    name: this.$t(
                        "global_search.actions.project.create_role.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_role.keywords"
                    ),
                    action: this.actionCreateRole,
                    icon: "fa fa-user-md icon-green",
                },
                {
                    id: "create-thread",
                    name: this.$t(
                        "global_search.actions.project.create_thread.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_thread.keywords"
                    ),
                    action: this.actionCreateThread,
                    icon: "fa fa-envelope icon-green",
                },
                {
                    id: "add-user",
                    name: this.$t(
                        "global_search.actions.project.add_user.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.add_user.keywords"
                    ),
                    action: this.actionAddUser,
                    icon: "fa fa-user-plus icon-green",
                },
                {
                    id: "create-import",
                    name: this.$t(
                        "global_search.actions.project.create_import.name"
                    ),
                    description: this.$t(
                        "global_search.actions.project.create_import.description"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_import.keywords"
                    ),
                    action: this.actionCreateImport,
                    icon: "fa fa-file-download icon-green",
                },

                {
                    id: "api-smsbox",
                    name: this.$t("global_search.actions.api.smsbox.name"),
                    keywords: this.$t(
                        "global_search.actions.api.smsbox.keywords"
                    ),
                    action: this.actionApiSmsbox,
                    logo: '<svg viewBox="0 0 50 40" xmlns="http://www.w3.org/2000/svg"><path d="M42.8,40H7.2c-4,0-7.2-3.2-7.2-7.2V7c0-4,3.2-7.2,7.2-7.2h35.7C46.8-0.2,50,3,50,7v25.8 C50,36.8,46.8,40,42.8,40z" fill="#7d3a96"/><path d="m26.6 34.3h-3.2c-4 0-7.3-3.3-7.3-7.3v-13.4c0-4 3.3-7.3 7.3-7.3h3.2c4 0 7.3 3.3 7.3 7.3v13.4c-0.1 4-3.3 7.3-7.3 7.3z" fill="#fff"/><path d="m27.4 23.4h-4.7c-2.2 0-3.9-1.8-3.9-3.9v-6.4c0-2.2 1.8-3.9 3.9-3.9h4.7c2.2 0 3.9 1.8 3.9 3.9v6.4c0 2.2-1.8 3.9-3.9 3.9z" fill="#7e3b96"/>',
                },
                {
                    id: "api-mtarget",
                    name: this.$t("global_search.actions.api.mtarget.name"),
                    keywords: this.$t(
                        "global_search.actions.api.mtarget.keywords"
                    ),
                    action: this.actionApiMTarget,
                    logo: '<svg viewBox="0 0 50 50"><path style="fill: #11b37d" d="M9.4,47c0-11.9,0-23.9,0.1-35.8c2.9,11.9,5.8,23.9,8.7,35.8h14.3c2.9-11.9,5.8-23.8,8.7-35.7 c0.1,11.9,0.1,23.8,0.1,35.7H50c0-14.8,0-29.7,0-44.5c-5,0-10.1,0-15.1,0.1c-3.2,13-6.3,26-9.5,39c-3.3-13-6.5-26-9.6-39.1 c-5,0-10,0-15.1,0c0,14.8,0,29.7,0,44.5H9.4z"/></svg>',
                },
                {
                    id: "pipedrive",
                    name: this.$t("global_search.actions.api.pipedrive.name"),
                    keywords: this.$t(
                        "global_search.actions.api.pipedrive.keywords"
                    ),
                    action: this.actionApiPipedrive,
                    logo: '<svg viewBox="0 0 32 32"><path d="M27.75,32H4.25C1.9,32,0,30.1,0,27.75V4.25C0,1.9,1.9,0,4.25,0h23.5C30.1,0,32,1.9,32,4.25v23.5C32,30.1,30.1,32,27.75,32z" /><path style="fill:#FFFFFF;" d="M17.12,7.33c-2.01,0-3.17,0.91-3.73,1.52c-0.07-0.54-0.42-1.23-1.81-1.23H8.55v3.14h1.23 c0.2,0,0.27,0.07,0.27,0.27V25.4h3.58v-5.37v-0.41c0.56,0.51,1.62,1.22,3.29,1.22c3.5,0,5.94-2.77,5.94-6.75 C22.89,10.05,20.56,7.33,17.12,7.33 M16.39,17.72c-1.93,0-2.8-1.84-2.8-3.56c0-2.7,1.47-3.66,2.85-3.66c1.69,0,2.84,1.45,2.84,3.63 C19.26,16.62,17.81,17.72,16.39,17.72"/></svg>',
                },
                {
                    id: "api-email",
                    name: this.$t("global_search.actions.api.email.name"),
                    keywords: this.$t(
                        "global_search.actions.api.email.keywords"
                    ),
                    action: this.actionApiEmail,
                    icon: "fa fa-envelope",
                },
                {
                    id: "api-ultramsg",
                    name: this.$t("global_search.actions.api.ultramsg.name"),
                    keywords: this.$t(
                        "global_search.actions.api.ultramsg.keywords"
                    ),
                    action: this.actionApiUltraMsg,
                    logo: '<svg viewBox="0 0 50 50"><path style="fill: #07b49b" d="M0.8,28.2l2.6-2.6C3.8,25.2,4.4,25,4.9,25h10v19.8l-2.6,2.6c-0.4,0.4-0.9,0.6-1.5,0.6h-10V28.2z" /><path style="fill: #07b49b" d="M18,17.4l2.6-2.6c0.4-0.4,0.9-0.6,1.5-0.6h10v30.7l-2.6,2.6C29,47.8,28.5,48,27.9,48H18V17.4z" /><path style="fill: #07b49b" d="M35.1,6l2.6-2.6c0.4-0.4,0.9-0.6,1.5-0.6h10v42l-2.6,2.6c-0.4,0.4-0.9,0.6-1.5,0.6h-10V6z" /></svg>',
                },
                {
                    id: "add-field",
                    name: this.$t(
                        "global_search.actions.project.create_field.name"
                    ),
                    keywords: this.$t(
                        "global_search.actions.project.create_field.keywords"
                    ),
                    action: this.actionAddField,
                    icon: "fa fa-columns",
                },
            ],
        };
    },

    created() {
        this.getQueryFromParam();
    },

    methods: {
        /**
         *
         */
        getQueryFromParam() {
            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);

            searchParams.forEach((value, param) => {
                if (param == "filters") {
                    try {
                        value = JSON.parse(value);
                        if (value.query) {
                            this.keyword = value.query;
                        }
                    } catch (e) {}
                }
            });
        },

        /**
         *
         */
        close() {
            setTimeout(() => {
                this.open = false;
            }, 250);
            this.inputFocused = false;
        },

        /**
         *
         * @param {*} e
         */
        selectAutocomplete(e) {
            if (this.filteredItems.length > 0) {
                // Down
                if (e.keyCode == 40) {
                    e.preventDefault();

                    if (this.selected + 1 < this.filteredItems.length) {
                        this.selected++;
                    } else {
                        this.selected = -1;
                    }

                    return;
                    // Up
                } else if (e.keyCode == 38) {
                    e.preventDefault();

                    if (this.selected <= -1) {
                        this.selected = this.filteredItems.length - 1;
                    } else {
                        this.selected--;
                    }

                    return;
                    // Enter
                }
            }
        },

        /**
         *
         * @param {*} e
         */
        search(e) {
            if (this.keyword.length == 0) {
                store.commit(INIT_PROSPECT_PARAMS);
                store.dispatch(FETCH_PROSPECTS);
                return;
            }

            if (this.selected >= 0) {
                const item = this.filteredItems[this.selected];
                document
                    .getElementById(
                        "hc-global-search-result-" +
                            item.type +
                            "-" +
                            item.item.id
                    )
                    .click();
            } else if (this.$route.name == "prospect") {
                store.commit(SET_PROSPECT_PARAMS, {
                    query: this.keyword,
                });
                store.dispatch(FETCH_PROSPECTS);
            } else {
                this.$router.push({
                    name: "prospect",
                    params: {
                        project: this.project.slug,
                    },
                    query: {
                        filters: JSON.stringify({
                            query: this.keyword,
                        }),
                    },
                });
            }

            // this.keyword = "";
            document.getElementById("hc-global-search-input").blur();
            // this.open = true;

            if (this.prospectCancelTokenSource) {
                this.prospectCancelTokenSource.cancel();
            }

            return;
        },

        /**
         * Scroll to selected item
         */
        scrollToSelectedItem() {
            if (this.filteredItems.length == 0) {
                return;
            }

            const item = this.filteredItems[this.selected];
            const selectedItem = document.getElementById(
                "hc-global-search-result-" + item.type + "-" + item.item.id
            );
            const result = document.getElementById("hc-global-search-result");

            if (result.scrollTop > selectedItem.offsetTop) {
                result.scrollTop = selectedItem.offsetTop - 5;
            } else if (
                selectedItem.offsetTop >
                result.scrollTop +
                    result.offsetHeight -
                    selectedItem.offsetHeight -
                    5
            ) {
                result.scrollTop =
                    selectedItem.offsetTop -
                    result.offsetHeight +
                    selectedItem.offsetHeight +
                    5;
            }
        },

        /**
         *
         */
        focusInput() {
            setTimeout(() => {
                this.$refs.input.focus();
            }, 100);
            this.inputFocused = true;
        },

        /**
         *
         */
        async fetchProspects(loading) {
            if (!this.project) {
                return;
            }

            if (this.keyword.length < 2) {
                this.prospects = [];
                return;
            }

            if (this.prospectCancelTokenSource) {
                this.prospectCancelTokenSource.cancel();
            }

            this.prospectCancelTokenSource = axios.CancelToken.source();

            this.loadingProspect = loading === undefined || loading;
            const filters = {
                query: this.keyword,
            };

            let fields = [
                "id",
                "first_name",
                "last_name",
                ...this.searchableFields.map(
                    (f) => (f.meta ? "meta->" : "") + f.slug
                ),
            ];

            /*fields = fields.filter(
                (value, index, array) => array.indexOf(value) === index
            );*/

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify(filters),
                        fields: fields.join(","),
                        page: this.page,
                        count: this.count,
                    },
                    cancelToken: this.prospectCancelTokenSource.token,
                });
                this.prospects = data.data;
            } finally {
                this.prospectCancelTokenSource = null;
                this.loadingProspect = false;
            }
        },

        /**
         *
         */
        async fetchUsers(loading) {
            if (this.project) {
                return;
            }

            if (!this.can("all")) {
                return;
            }

            if (this.keyword.length < 2) {
                this.globalUsers = [];
                return;
            }

            if (this.userCancelTokenSource) {
                this.userCancelTokenSource.cancel();
            }

            this.userCancelTokenSource = axios.CancelToken.source();

            this.loadingUser = loading === undefined || loading;
            const filters = {
                query: this.keyword,
            };

            try {
                const { data } = await GlobalUserService.get({
                    params: {
                        filters: JSON.stringify(filters),
                        fields: "id,first_name,last_name",
                        page: this.page,
                        count: this.count,
                    },
                    cancelToken: this.userCancelTokenSource.token,
                });
                this.globalUsers = data.data;
            } finally {
                this.userCancelTokenSource = null;
                this.loadingUser = false;
            }
        },

        /**
         *
         */
        async fetchProjects(loading) {
            if (this.project) {
                return;
            }

            if (this.keyword.length < 2) {
                this.projects = [];
                return;
            }

            if (this.projectCancelTokenSource) {
                this.projectCancelTokenSource.cancel();
            }

            this.projectCancelTokenSource = axios.CancelToken.source();

            this.loadingProject = loading === undefined || loading;
            const filters = {
                query: this.keyword,
            };

            try {
                const { data } = await ProjectService.get({
                    params: {
                        filters: JSON.stringify(filters),
                        fields: "id,name",
                        page: this.page,
                        count: this.count,
                    },
                    cancelToken: this.projectCancelTokenSource.token,
                });
                this.projects = data.data;
            } finally {
                this.projectCancelTokenSource = null;
                this.loadingProject = false;
            }
        },

        /**
         *
         */
        filteredActions(keyword) {
            return this.actions.filter(
                (action) =>
                    removeStringAccent(action.keywords).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredCalendars(keyword) {
            return this.can("all.project.calendar.update")
                ? this.calendars.filter(
                      (calendar) =>
                          removeStringAccent(calendar.name).indexOf(keyword) >=
                          0
                  )
                : [];
        },

        /**
         *
         */
        filteredCategories(keyword) {
            return this.can("all.project.calendar.update")
                ? this.categories.filter(
                      (category) =>
                          removeStringAccent(category.name).indexOf(keyword) >=
                          0
                  )
                : [];
        },

        /**
         *
         */
        filteredDocuments(keyword) {
            return this.documents.filter(
                (document) =>
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredFields(keyword) {
            return this.can("all.project.field.update")
                ? this.fields.filter(
                      (field) =>
                          removeStringAccent(field.name).indexOf(keyword) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredFolders(keyword) {
            return this.can("all.project.folder.update")
                ? this.folders.filter(
                      (folder) =>
                          folder.for == "prospect" &&
                          removeStringAccent(folder.name).indexOf(keyword) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredGroups(keyword) {
            return this.can("all.project.group.update")
                ? this.groups.filter(
                      (group) =>
                          removeStringAccent(group.name).indexOf(keyword) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredImports(keyword) {
            return this.imports.filter(
                (prospectImport) =>
                    removeStringAccent(prospectImport.name).indexOf(keyword) >=
                    0
            );
        },

        /**
         *
         */
        filteredLabels(keyword) {
            return this.can("all.project.category.label.update")
                ? this.labels.filter(
                      (label) =>
                          removeStringAccent(label.name).indexOf(keyword) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredMenus(keyword) {
            return this.menus.filter(
                (menu) => removeStringAccent(menu.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredProducts() {
            const keyword = removeStringAccent(this.keyword);

            return this.can("all.project.product.update")
                ? this.products.filter(
                      (product) =>
                          removeStringAccent(product.name).indexOf(keyword) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredQuestionnaires(keyword) {
            return this.can("all.project.questionnaire.update")
                ? this.questionnaires.filter(
                      (questionnaire) =>
                          removeStringAccent(questionnaire.name).indexOf(
                              keyword
                          ) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredRoles(keyword) {
            return this.can("all.project.role.update")
                ? this.roles.filter(
                      (role) =>
                          removeStringAccent(role.name).indexOf(keyword) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredThreads(keyword) {
            return this.can("all.project.thread.update")
                ? this.threads.filter(
                      (thread) =>
                          removeStringAccent(thread.name).indexOf(keyword) >= 0
                  )
                : [];
        },

        /**
         *
         */
        filteredUsers(keyword) {
            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },

        // ACTIONS

        /**
         *
         */
        actionCreateEvent() {
            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(120);

            store.commit(SET_EVENT, {
                prospect: null,
                user: this.$store.state.auth.user,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         *
         */
        actionCreateOrder() {
            store.commit(SET_PROSPECT, null);
            // store.commit(SET_PROSPECT_ORDER, null);
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },

        /**
         *
         */
        actionCreateSms() {
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },

        /**
         *
         */
        actionCreateInteraction() {
            store.commit(SET_INTERACTION_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-interactions");
        },

        /**
         *
         */
        actionCreateFile() {
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-files");
        },

        /**
         *
         */
        actionCreateMessage() {
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         *
         */
        actionCreateCalendar() {
            store.commit(OPEN_MODAL, "calendar-add");
        },

        /**
         *
         */
        actionCreateCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        /**
         *
         */
        actionCreateDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },

        /**
         *
         */
        actionCreateFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },

        /**
         *
         */
        actionCreateGroup() {
            store.commit(OPEN_MODAL, "group-add");
        },

        /**
         *
         */
        actionCreateMenu() {
            store.commit(OPEN_MODAL, "menu-add");
        },

        /**
         *
         */
        actionCreateOrderAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },

        /**
         *
         */
        actionCreateOrderStep() {
            store.commit(OPEN_MODAL, "order-step-add");
        },

        /**
         *
         */
        actionCreatePipeline() {
            store.commit(OPEN_MODAL, "pipeline-add");
        },

        /**
         *
         */
        actionCreateProduct() {
            store.commit(OPEN_MODAL, "product-add");
        },

        /**
         *
         */
        actionCreateRole() {
            store.commit(OPEN_MODAL, "role-add");
        },

        /**
         *
         */
        actionCreateThread() {
            store.commit(OPEN_MODAL, "thread-add");
        },

        /**
         *
         */
        actionAddUser() {
            store.commit(OPEN_MODAL, "user-add");
        },

        /**
         *
         */
        actionCreateImport() {
            store.commit(OPEN_SLIDE, "import");
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         *
         */
        actionApiSmsbox() {
            store.commit(OPEN_MODAL, "setting-smsbox");
        },

        /**
         *
         */
        actionApiMTarget() {
            store.commit(OPEN_MODAL, "setting-mtarget");
        },

        /**
         *
         */
        actionApiPipedrive() {
            store.commit(OPEN_MODAL, "setting-pipedrive");
        },

        /**
         *
         */
        actionApiEmail() {
            store.commit(OPEN_MODAL, "setting-email");
        },

        /**
         *
         */
        actionApiUltraMsg() {
            store.commit(OPEN_MODAL, "setting-ultramsg");
        },

        /**
         *
         */
        actionAddField() {
            store.commit(OPEN_MODAL, "field-add");
        },
    },

    watch: {
        keyword() {
            this.selected = -1;
            this.fetchProspects();
            this.fetchUsers();
            this.fetchProjects();
        },

        async selected() {
            await this.$nextTick();
            if (this.hasResults) {
                this.scrollToSelectedItem();
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "can",
            "calendars",
            "categories",
            "documents",
            "fields",
            "folders",
            "groups",
            "imports",
            "menus",
            "products",
            "menus",
            "questionnaires",
            "roles",
            "threads",
            "users",
        ]),

        /**
         *
         */
        debouncedKeyword: {
            get() {
                return this.tmpKeyword;
            },
            set(value) {
                this.tmpKeyword = value;

                if (this.keywordTimeout !== undefined) {
                    clearTimeout(this.keywordTimeout);
                }

                this.keywordTimeout = setTimeout(() => {
                    this.keyword = value;
                }, 300);
            },
        },

        /**
         *
         */
        hasResults() {
            return (
                this.open &&
                this.keyword.length > 0 &&
                this.filteredItems.length > 0
            );
        },

        /**
         *
         */
        labels() {
            return this.categories.reduce(
                (carry, category) => [
                    ...carry,
                    ...(category.labels
                        ? category.labels.map((label) => ({
                              ...label,
                              category: category,
                          }))
                        : []),
                ],
                []
            );
        },

        /**
         *
         */
        searchableFields() {
            return this.fields.filter(
                (field) =>
                    field.searchable &&
                    (field.meta ||
                        (field.slug != "first_name" &&
                            field.slug != "last_name"))
            );
        },

        /**
         *
         */
        filteredItems() {
            const keyword = removeStringAccent(this.keyword);

            const search = this.project
                ? {
                      menu: this.filteredMenus,
                      label: this.filteredLabels,
                      user: this.filteredUsers,
                      group: this.filteredGroups,
                      calendar: this.filteredCalendars,
                      action: this.filteredActions,
                      category: this.filteredCategories,
                      import: this.filteredImports,
                      product: this.filteredProducts,
                      folder: this.filteredFolders,
                      thread: this.filteredThreads,
                      document: this.filteredDocuments,
                      role: this.filteredRoles,
                      questionnaire: this.filteredQuestionnaires,
                      field: this.filteredFields,
                  }
                : {};

            const result = Object.entries(search)
                .map(([type, fn]) =>
                    fn(keyword).map((item) => ({
                        type,
                        item,
                    }))
                )
                .reduce(
                    (carry, items) => [...items, ...carry],
                    this.project
                        ? this.prospects.map((item) => ({
                              type: "prospect",
                              item,
                          }))
                        : [
                              ...this.globalUsers.map((item) => ({
                                  type: "user",
                                  item,
                              })),
                              ...this.projects.map((item) => ({
                                  type: "project",
                                  item,
                              })),
                          ]
                );

            return result;
        },
    },
};
</script>
