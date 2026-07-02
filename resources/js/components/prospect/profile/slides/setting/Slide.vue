<template>
    <slide
        :name="name"
        :title="$t('prospect.profile.setting.title')"
        icon="fa fa-cog"
        style="width: 320px"
        @open="getSetting(), (tab = 0), (columnTab = 0)"
    >
        <tab-layout :count="3" :tab="tab" style="height: 100%">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <item style="border-bottom: 1px solid #eee">
                        <div class="hc-item-main-content hc-flex-column"></div>

                        <!-- Movable -->
                        <icon
                            :class="[
                                'fa',
                                'fa-arrows',
                                movable ? 'icon-blue' : 'icon-grey',
                            ]"
                            @click.prevent.stop="movable = !movable"
                        />
                    </item>
                    <tab
                        :count="columnsCount"
                        @tab:change="(t) => (columnTab = t)"
                        :tab="columnTab"
                    >
                        <template v-for="i in columnsCount" v-slot:[i]>
                            <div
                                v-text="
                                    $t('prospect.profile.setting.column_i', {
                                        i: i,
                                    })
                                "
                                @dragenter="columnTab = i - 1"
                            ></div>
                        </template>
                    </tab>
                    <tab-layout
                        :count="columnsCount"
                        :tab="columnTab"
                        class="hc-flex-1"
                    >
                        <!-- Default and meta fields -->
                        <template v-for="i in columnsCount" v-slot:[i]>
                            <div class="hc-flex-column" style="height: 100%">
                                <div class="hc-flex-1" style="overflow: auto">
                                    <draggable
                                        tag="item-list"
                                        :list="
                                            projectUserSettingsProspectProfile[
                                                i - 1
                                            ]
                                        "
                                        style="
                                            padding: 10px;
                                            gap: 10px;
                                            background-color: #f8fafc;
                                            height: auto;
                                            min-height: 100%;
                                        "
                                        :disabled="!movable"
                                        :component-data="{ padding: '5px' }"
                                        group="prospect-profile-setting-bloc"
                                        @end="setBlocColumn"
                                    >
                                        <template #item="{ element, index }">
                                            <tree-layout
                                                class="hc-prospect-profile-setting-bloc"
                                                :tabulation="0"
                                                :open="true"
                                            >
                                                <template #header>
                                                    <item>
                                                        <icon
                                                            :class="[
                                                                'fa',
                                                                getBlocIcon(
                                                                    element
                                                                ),
                                                            ]"
                                                        />
                                                        <div
                                                            class="hc-item-main-content"
                                                            v-text="
                                                                element.name
                                                            "
                                                        ></div>
                                                        <icon
                                                            v-if="
                                                                element.type ==
                                                                'field'
                                                            "
                                                            class="fa fa-plus"
                                                            tag="a"
                                                            @click.prevent.stop="
                                                                selectBlocField(
                                                                    i - 1,
                                                                    index
                                                                )
                                                            "
                                                        />
                                                        <icon
                                                            class="fa fa-cog"
                                                            tag="a"
                                                            @click.prevent.stop="
                                                                editBloc(
                                                                    i - 1,
                                                                    index
                                                                )
                                                            "
                                                        />
                                                        <icon
                                                            class="fa fa-times"
                                                            tag="a"
                                                            @click.prevent.stop="
                                                                removeBloc(
                                                                    i - 1,
                                                                    index
                                                                )
                                                            "
                                                        />
                                                        <icon
                                                            class="fa fa-caret-down"
                                                        />
                                                    </item>
                                                </template>
                                                <template
                                                    v-if="
                                                        element.type == 'field'
                                                    "
                                                    #body
                                                >
                                                    <draggable
                                                        :disabled="!movable"
                                                        tag="item-list"
                                                        :list="element.items"
                                                        group="prospect-profile-setting-field"
                                                        @end="setBlocColumn"
                                                    >
                                                        <template
                                                            #item="{ element }"
                                                        >
                                                            <item
                                                                v-if="
                                                                    getField(
                                                                        element
                                                                    )
                                                                "
                                                            >
                                                                <icon
                                                                    class="fa fa-columns"
                                                                />
                                                                <div
                                                                    class="hc-item-main-content"
                                                                    v-text="
                                                                        getField(
                                                                            element
                                                                        ).name
                                                                    "
                                                                ></div>
                                                                <icon
                                                                    class="fa fa-times"
                                                                    @click.prevent.stop="
                                                                        removeBlocField(
                                                                            i -
                                                                                1,
                                                                            index,
                                                                            element
                                                                        )
                                                                    "
                                                                />
                                                            </item>
                                                        </template>
                                                    </draggable>
                                                </template>
                                            </tree-layout>
                                        </template>
                                    </draggable>
                                </div>
                                <buttons>
                                    <a
                                        @click.prevent="tab = 1"
                                        v-text="
                                            $t(
                                                'prospect.profile.setting.new_bloc'
                                            )
                                        "
                                    ></a>
                                </buttons>
                            </div>
                        </template>
                    </tab-layout>
                </div>
            </template>

            <template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 0" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('prospect.profile.setting.new_bloc')"
                        ></div>
                    </item>
                    <search v-model="blocTypeKeyword" />
                    <item-list
                        class="hc-flex-1"
                        padding="5px"
                        style="overflow: auto"
                    >
                        <template
                            v-for="blocType in filteredBlocTypes"
                            :key="blocType.key"
                        >
                            <item
                                v-if="!findBloc(blocType.key)"
                                tag="a"
                                :key="blocType.key"
                                @click.prevent="blocType.action"
                            >
                                <icon :class="blocType.icon" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="blocType.name"
                                ></div>
                                <icon
                                    :class="[
                                        'fa',
                                        blocType.group
                                            ? 'fa-caret-right'
                                            : 'fa-plus',
                                    ]"
                                />
                            </item>
                        </template>
                    </item-list>
                </div>
            </template>

            <template #3>
                <frame-layout :count="6" :tab="blocTab">
                    <template #1>
                        <form
                            v-if="bloc"
                            class="hc-flex-column"
                            style="height: 100%"
                            @submit.prevent="updateBloc"
                        >
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.profile.setting.edit_bloc')
                                    "
                                ></div>
                            </item>
                            <item-list
                                gap="5px"
                                class="hc-flex-1"
                                padding="12px"
                            >
                                <v-field :label="$t('name')" required v-slot="p"
                                    ><input
                                        required
                                        :placeholder="p.label + ' ...'"
                                        v-model="
                                            projectUserSettingsProspectProfile[
                                                bloc.column
                                            ][bloc.index].name
                                        "
                                /></v-field>
                            </item-list>
                            <buttons>
                                <button v-text="$t('update')"></button>
                            </buttons>
                        </form>
                    </template>

                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.profile.setting.messages')
                                    "
                                ></div>
                            </item>
                            <search v-model="threadKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <thread-row
                                    v-for="c in filteredThreads"
                                    :key="c.id"
                                    :thread="c"
                                    @click="addMessageBloc(c)"
                                />
                            </item-list>
                            <buttons v-if="can('all.project.thread.add')">
                                <a
                                    @click.prevent="addThread"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>

                    <template #3>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.profile.setting.files')
                                    "
                                ></div>
                            </item>
                            <search v-model="folderKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <folder-row
                                    v-for="c in filteredFolders"
                                    :key="c.id"
                                    :folder="c"
                                    @click="addFolderBloc(c)"
                                />
                            </item-list>
                            <buttons v-if="can('all.project.folder.add')">
                                <a
                                    @click.prevent="addFolder"
                                    v-text="$t('add')"
                                ></a>
                            </buttons></div
                    ></template>

                    <template #4>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.profile.setting.filters')
                                    "
                                ></div>
                            </item>
                            <search v-model="categoryKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <category-row
                                    v-for="c in filteredCategories"
                                    :key="c.id"
                                    :category="c"
                                    @click="addCategoryBloc(c)"
                                />
                            </item-list>
                            <buttons v-if="can('all.project.category.add')">
                                <a
                                    @click.prevent="addCategory"
                                    v-text="$t('add')"
                                ></a>
                            </buttons></div
                    ></template>

                    <template #5>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.profile.setting.fields')
                                    "
                                ></div>
                            </item>
                            <search v-model="fieldKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <field-row
                                    v-for="c in filteredFields"
                                    :key="c.id"
                                    :field="c"
                                    @click="addBlocField(c)"
                                />
                            </item-list>
                            <buttons v-if="can('all.project.field.add')">
                                <a
                                    @click.prevent="addField"
                                    v-text="$t('add')"
                                ></a>
                            </buttons></div
                    ></template>

                    <template #6>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('prospect.profile.setting.pipelines')
                                    "
                                ></div>
                            </item>
                            <search v-model="pipelineKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <pipeline-row
                                    v-for="c in filteredPipelines"
                                    :key="c.id"
                                    :pipeline="c"
                                    @click="addPipelineBloc(c)"
                                />
                            </item-list>
                            <buttons
                                v-if="true || can('all.project.pipeline.add')"
                            >
                                <a
                                    @click.prevent="addPipeline"
                                    v-text="$t('add')"
                                ></a>
                            </buttons></div
                    ></template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-prospect-profile-setting-bloc {
    width: 100%;
    background: white;
    border: 1px solid #eee;
    border-radius: 5px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import {
    SET_PROSPECT_PROFILE_SETTING_COLUMN_TAB,
    SET_PROSPECT_PROFILE_SETTING_BLOC_TAB,
} from "@/actions/project/prospect";
import {
    GET_PROJECT_USER_SETTING,
    UPDATE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";
import { ADD_USER_FOLDER } from "@/actions/project/user/folder";
import { ADD_USER_THREAD } from "@/actions/project/user/thread";
import { ADD_USER_CATEGORY } from "@/actions/project/user/category";

// Components
import CategoryRow from "./CategoryRow.vue";
import FieldRow from "./FieldRow.vue";
import FolderRow from "./FolderRow.vue";
import ThreadRow from "./ThreadRow.vue";
import PipelineRow from "./PipelineRow.vue";

export default {
    components: { CategoryRow, FieldRow, FolderRow, ThreadRow, PipelineRow },

    data() {
        return {
            name: "prospect-profile-setting",
            blocTab: 0,
            columnsCount: 4,
            categoryKeyword: "",
            fieldKeyword: "",
            folderKeyword: "",
            threadKeyword: "",
            blocTypeKeyword: "",
            pipelineKeyword: "",
            newBloc: {
                name: "",
            },
            bloc: null,
            movable: deviceType() == "desktop",
            projectUserSettingsProspectProfile: [],
            blocTypes: [
                {
                    key: "field",
                    icon: "fa fa-id-card",
                    name: this.$t("prospect.profile.setting.fields"),
                    action: this.addFieldBloc,
                },
                {
                    key: "message",
                    icon: "fa fa-envelope",
                    name: this.$t("prospect.profile.setting.messages"),
                    action: this.selectMessageBloc,
                    group: true,
                },
                {
                    key: "folder",
                    icon: "fa fa-folder",
                    name: this.$t("prospect.profile.setting.files"),
                    action: this.selectFileBloc,
                    group: true,
                },
                {
                    key: "category",
                    icon: "fa fa-tags",
                    name: this.$t("prospect.profile.setting.filters"),
                    action: this.selectCategoryBloc,
                    group: true,
                },
                {
                    key: "pipeline",
                    icon: "fa fa-forward",
                    name: this.$t("prospect.profile.setting.pipelines"),
                    action: this.selectPipelineBloc,
                    group: true,
                },
                {
                    key: "order",
                    icon: "fa fa-shopping-cart",
                    name: this.$t("prospect.profile.setting.orders"),
                    action: this.addOrderBloc,
                },
                {
                    key: "label",
                    icon: "fa fa-tag",
                    name: this.$t("prospect.profile.setting.labels"),
                    action: this.addLabelBloc,
                },
                {
                    key: "link",
                    icon: "fa fa-external-link",
                    name: this.$t("prospect.profile.setting.external_links"),
                    action: this.addLinkBloc,
                },
                {
                    key: "event",
                    icon: "fa fa-calendar",
                    name: this.$t("prospect.profile.setting.events"),
                    action: this.addEventBloc,
                },
                {
                    key: "history",
                    icon: "fa fa-clock",
                    name: this.$t("prospect.profile.setting.history"),
                    action: this.addHistoryBloc,
                },
                {
                    key: "interaction",
                    icon: "fa fa-phone",
                    name: this.$t("prospect.profile.setting.calls"),
                    action: this.addInteractionBloc,
                },
                {
                    key: "sms",
                    icon: "fa fa-comment",
                    name: this.$t("prospect.profile.setting.sms"),
                    action: this.addSmsBloc,
                },
                {
                    key: "document",
                    icon: "fa fa-file-pdf",
                    name: this.$t("prospect.profile.setting.documents"),
                    action: this.addDocumentBloc,
                },
                {
                    key: "user",
                    icon: "fa fa-user",
                    name: this.$t("prospect.profile.setting.affected_users"),
                    action: this.addUserBloc,
                },
                {
                    key: "group",
                    icon: "fa fa-users",
                    name: this.$t("prospect.profile.setting.affected_groups"),
                    action: this.addGroupBloc,
                },
                {
                    key: "map",
                    icon: "fa fa-map-marker",
                    name: this.$t("prospect.profile.setting.map"),
                    action: this.addMapBloc,
                },
            ],
        };
    },

    methods: {
        /**
         * Get user columns
         */
        async getSetting() {
            this.projectUserSettingsProspectProfile = await store.dispatch(
                GET_PROJECT_USER_SETTING,
                {
                    key: "prospect-profile",
                    user: this.user.id,
                }
            );
        },

        getField(slug) {
            const meta = slug.indexOf("meta->") == 0;
            slug = slug.replace("meta->", "");

            const field = this.fields.find(
                (f) => f.slug == slug && f.meta == meta
            );

            return field;
        },

        blocsAt(column) {
            return this.projectUserSettingsProspectProfile.filter(
                (bloc) => bloc.column == column
            );
        },

        setBlocColumn(e) {
            let newSettings = [...this.projectUserSettingsProspectProfile];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
                user: this.user.id,
            });
        },

        /**
         *
         */
        addThread() {
            store.commit(OPEN_MODAL, "thread-add");
        },

        /**
         *
         */
        addPipeline() {
            store.commit(OPEN_MODAL, "pipeline-add");
        },

        /**
         *
         */
        addField() {
            store.commit(OPEN_MODAL, "field-add");
        },

        /**
         *
         */
        addFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },

        /**
         *
         */
        addCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        /**
         *
         * @param {*} bloc
         */
        addBloc(bloc) {
            let newSettings = [...this.projectUserSettingsProspectProfile];
            newSettings[this.columnTab] = [
                ...newSettings[this.columnTab],
                bloc,
            ];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
                user: this.user.id,
            });

            this.tab = 0;
            this.projectUserSettingsProspectProfile = newSettings;
        },

        /**
         *
         * @param {*} bloc
         */
        editBloc(column, index) {
            this.bloc = {
                column,
                index,
            };

            this.blocTab = 0;
            this.tab = 2;
        },

        updateBloc() {
            let newSettings = [...this.projectUserSettingsProspectProfile];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
                user: this.user.id,
            });

            this.tab = 0;
        },

        /**
         *
         * @param {*} bloc
         */
        removeBloc(column, index) {
            let newSettings = [...this.projectUserSettingsProspectProfile];
            newSettings[this.columnTab] = newSettings[column].filter(
                (b, i) => i != index
            );

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
                user: this.user.id,
            });

            this.projectUserSettingsProspectProfile = newSettings;
        },

        /**
         *
         * @param {*} bloc
         */
        selectMessageBloc() {
            this.tab = 2;
            this.blocTab = 1;
        },

        /**
         *
         * @param {*} bloc
         */
        selectFileBloc() {
            this.tab = 2;
            this.blocTab = 2;
        },

        /**
         *
         * @param {*} bloc
         */
        selectCategoryBloc() {
            this.tab = 2;
            this.blocTab = 3;
        },

        /**
         *
         * @param {*} bloc
         */
        selectPipelineBloc() {
            this.tab = 2;
            this.blocTab = 5;
        },

        /**
         *
         * @param {*} bloc
         */
        addFieldBloc() {
            this.addBloc({
                type: "field",
                name: "Information",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addMessageBloc(thread) {
            this.addBloc({
                type: "thread->" + thread.id,
                name: thread.name,
            });
            store.dispatch(ADD_USER_THREAD, thread);
        },

        /**
         *
         * @param {*} bloc
         */
        addFolderBloc(folder) {
            this.addBloc({
                type: "folder->" + folder.id,
                name: folder.name,
            });
            store.dispatch(ADD_USER_FOLDER, folder);
        },

        /**
         *
         * @param {*} bloc
         */
        addPipelineBloc(pipeline) {
            this.addBloc({
                type: "pipeline->" + pipeline.id,
                name: pipeline.name,
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addCategoryBloc(category) {
            this.addBloc({
                type: "category->" + category.id,
                name: category.name,
            });
            store.dispatch(ADD_USER_CATEGORY, category);
        },

        /**
         *
         * @param {*} bloc
         */
        addOrderBloc() {
            this.addBloc({
                type: "order",
                name: "Commandes",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addLabelBloc() {
            this.addBloc({
                type: "label",
                name: "Filtres actifs",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addLinkBloc() {
            this.addBloc({
                type: "link",
                name: "Liens externes",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addEventBloc() {
            this.addBloc({
                type: "event",
                name: "RDV",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addHistoryBloc() {
            this.addBloc({
                type: "history",
                name: "Historiques",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addInteractionBloc() {
            this.addBloc({
                type: "interaction",
                name: "Appel",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addSmsBloc() {
            this.addBloc({
                type: "sms",
                name: "SMS",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addDocumentBloc() {
            this.addBloc({
                type: "document",
                name: "Document",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addUserBloc() {
            this.addBloc({
                type: "user",
                name: "Utilisateurs affectés",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addGroupBloc() {
            this.addBloc({
                type: "group",
                name: "Groupes affectés",
            });
        },

        /**
         *
         * @param {*} bloc
         */
        addMapBloc() {
            this.addBloc({
                type: "map",
                name: "MAP",
            });
        },

        /**
         *
         */
        selectBlocField(column, index) {
            this.bloc = {
                column,
                index,
            };

            this.blocTab = 4;
            this.tab = 2;
        },

        /**
         *
         */
        addBlocField(field) {
            let newSettings = [...this.projectUserSettingsProspectProfile];
            const items = Array.isArray(
                newSettings[this.bloc.column][this.bloc.index].items
            )
                ? newSettings[this.bloc.column][this.bloc.index].items
                : [];

            newSettings[this.bloc.column][this.bloc.index].items = [
                ...items,
                field.meta ? "meta->" + field.slug : field.slug,
            ];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
                user: this.user.id,
            });

            // this.tab = 0;
            this.projectUserSettingsProspectProfile = newSettings;
        },

        /**
         *
         */
        removeBlocField(column, index, field) {
            let newSettings = [...this.projectUserSettingsProspectProfile];
            const items = Array.isArray(newSettings[column][index].items)
                ? newSettings[column][index].items
                : [];

            newSettings[column][index].items = items.filter((f) => f != field);

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospect-profile",
                value: newSettings,
                user: this.user.id,
            });

            this.projectUserSettingsProspectProfile = newSettings;
        },

        getBlocIcon(bloc) {
            if (bloc.type.indexOf("thread->") == 0) {
                return "fa-envelope";
            } else if (bloc.type.indexOf("folder->") == 0) {
                return "fa-folder";
            } else if (bloc.type.indexOf("category->") == 0) {
                return "fa-tags";
            } else if (bloc.type == "order") {
                return "fa-shopping-cart";
            } else if (bloc.type == "link") {
                return "fa-external-link";
            } else if (bloc.type == "event") {
                return "fa-calendar";
            } else if (bloc.type == "interaction") {
                return "fa-phone";
            } else if (bloc.type == "sms") {
                return "fa-comment";
            } else if (bloc.type == "document") {
                return "fa-file-pdf";
            } else if (bloc.type == "user") {
                return "fa-user";
            } else if (bloc.type == "group") {
                return "fa-users";
            } else if (bloc.type == "history") {
                return "fa-clock";
            }

            return "fa-columns";
        },

        findBloc(type) {
            if (type == "field") {
                return false;
            }

            for (let i in this.projectUserSettingsProspectProfile) {
                for (let b in this.projectUserSettingsProspectProfile[i]) {
                    if (
                        this.projectUserSettingsProspectProfile[i][b].type ==
                        type
                    ) {
                        return true;
                    }
                }
            }

            return false;
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "fields",
            "categories",
            "folders",
            "threads",
            "pipelines",
            "prospectProfileSettingColumnTab",
            "prospectProfileSettingBlocTab",
            "user",
            "can",
        ]),

        /**
         *
         */
        columnTab: {
            get() {
                return this.prospectProfileSettingColumnTab;
            },
            set(value) {
                store.commit(SET_PROSPECT_PROFILE_SETTING_COLUMN_TAB, value);
            },
        },

        /**
         *
         */
        tab: {
            get() {
                return this.prospectProfileSettingBlocTab;
            },
            set(value) {
                store.commit(SET_PROSPECT_PROFILE_SETTING_BLOC_TAB, value);
            },
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    removeStringAccent(category.name).indexOf(keyword) >= 0 &&
                    !this.findBloc("category->" + category.id)
            );
        },

        /**
         *
         */
        selectedFields() {
            return this.projectUserSettingsProspectProfile.reduce(
                (carry, column) => [
                    ...carry,
                    ...column.reduce(
                        (carry2, bloc) => [
                            ...carry2,
                            ...(bloc.type == "field" &&
                            Array.isArray(bloc.items)
                                ? bloc.items
                                : []),
                        ],
                        []
                    ),
                ],
                []
            );
        },

        /**
         *
         */
        filteredFields() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.fields.filter(
                (field) =>
                    removeStringAccent(field.name).indexOf(keyword) >= 0 &&
                    field.for == "prospect" &&
                    !this.selectedFields.find(
                        (f) =>
                            f ==
                            (field.meta ? "meta->" + field.slug : field.slug)
                    )
            );
        },

        /**
         *
         */
        filteredFolders() {
            const keyword = removeStringAccent(this.folderKeyword);

            return this.folders.filter(
                (folder) =>
                    folder.for == "prospect" &&
                    removeStringAccent(folder.name).indexOf(keyword) >= 0 &&
                    !this.findBloc("folder->" + folder.id)
            );
        },

        /**
         *
         */
        filteredThreads() {
            const keyword = removeStringAccent(this.threadKeyword);

            return this.threads.filter(
                (thread) =>
                    removeStringAccent(thread.name).indexOf(keyword) >= 0 &&
                    !this.findBloc("thread->" + thread.id)
            );
        },

        /**
         *
         */
        filteredPipelines() {
            const keyword = removeStringAccent(this.pipelineKeyword);

            return this.pipelines.filter(
                (pipeline) =>
                    removeStringAccent(pipeline.name).indexOf(keyword) >= 0 &&
                    !this.findBloc("pipeline->" + pipeline.id)
            );
        },

        /**
         *
         */
        filteredBlocTypes() {
            const keyword = removeStringAccent(this.blocTypeKeyword);

            return this.blocTypes.filter(
                (blocType) =>
                    removeStringAccent(blocType.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
