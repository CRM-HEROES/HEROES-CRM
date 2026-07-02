<template>
    <slide
        :name="name"
        :title="$t('user.profile.setting.title')"
        icon="fa fa-cog"
        style="width: 300px"
        @open="getSetting"
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
                                    $t('user.profile.setting.column_i', {
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
                                            projectUserSettingsUserProfile[
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
                                        group="user-profile-setting-bloc"
                                        @end="setBlocColumn"
                                    >
                                        <template #item="{ element, index }">
                                            <tree-layout
                                                class="hc-user-profile-setting-bloc"
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
                                                        group="user-profile-setting-field"
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
                                            $t('user.profile.setting.new_bloc')
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
                            v-text="$t('user.profile.setting.new_bloc')"
                        ></div>
                    </item>
                    <item-list
                        class="hc-flex-1"
                        padding="5px"
                        style="overflow: auto"
                    >
                        <item tag="a" @click.prevent="addFieldBloc">
                            <icon class="fa fa-id-card" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('user.profile.setting.fields')"
                            ></div>
                            <icon class="fa fa-caret-plus" />
                        </item>

                        <add-bloc-row
                            v-for="bloc in blocs"
                            :key="bloc.key"
                            :bloc="bloc"
                            @add-bloc="addNewBloc"
                        />
                    </item-list>
                </div>
            </template>

            <template #3>
                <frame-layout :count="3" :tab="blocTab">
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
                                        $t('user.profile.setting.edit_bloc')
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
                                            projectUserSettingsUserProfile[
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
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('user.profile.setting.fields')"
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
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-user-profile-setting-bloc {
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
import {
    GET_PROJECT_USER_SETTING,
    UPDATE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";
import { ADD_USER_FOLDER } from "@/actions/project/user/folder";

// Components
import AddBlocRow from "./AddBlocRow.vue";
import FieldRow from "./FieldRow.vue";
import FolderRow from "./FolderRow.vue";

export default {
    components: { AddBlocRow, FieldRow, FolderRow },

    data() {
        return {
            name: "user-profile-setting",
            tab: 0,
            columnTab: 0,
            blocTab: 0,
            columnsCount: 4,
            fieldKeyword: "",
            folderKeyword: "",
            newBloc: {
                name: "",
            },
            bloc: null,
            blocs: [
                {
                    key: "calendar",
                    name: this.$t("user.profile.blocs.calendars"),
                    icon: "fa fa-calendar icon-purple",
                },
                {
                    key: "category",
                    name: this.$t("user.profile.blocs.categories"),
                    icon: "fa fa-tags icon-purple",
                },
                {
                    key: "document",
                    name: this.$t("user.profile.blocs.documents"),
                    icon: "fa fa-file-pdf icon-garnet",
                },
                {
                    key: "export",
                    name: this.$t("user.profile.blocs.exports"),
                    icon: "fa fa-file-upload icon-green",
                },
                {
                    key: "folder",
                    name: this.$t("user.profile.blocs.folders"),
                    icon: "fa fa-folder icon-blue",
                },
                {
                    key: "file",
                    icon: "fa fa-file",
                    name: this.$t("user.profile.setting.files"),
                    action: this.selectFileBloc,
                    group: true,
                },
                {
                    key: "group",
                    name: this.$t("user.profile.blocs.groups"),
                    icon: "fa fa-users icon-brown",
                },
                {
                    key: "import",
                    name: this.$t("user.profile.blocs.imports"),
                    icon: "fa fa-file-download icon-green",
                },
                {
                    key: "menu",
                    name: this.$t("user.profile.blocs.menus"),
                    icon: "fa fa-list icon-red",
                },
                {
                    key: "order-action",
                    name: this.$t("user.profile.blocs.order_actions"),
                    icon: "fa fa-check icon-cyan",
                },
                {
                    key: "order-step",
                    name: this.$t("user.profile.blocs.order_steps"),
                    icon: "fa fa-step-forward icon-cyan",
                },
                {
                    key: "questionnaire",
                    name: this.$t("user.profile.blocs.questionnaires"),
                    icon: "fa fa-clipboard icon-brown",
                },
                {
                    key: "role",
                    name: this.$t("user.profile.blocs.roles"),
                    icon: "fa fa-user-tie icon-orange",
                },
                {
                    key: "thread",
                    name: this.$t("user.profile.blocs.threads"),
                    icon: "fa fa-envelope icon-green",
                },
                {
                    key: "user",
                    name: this.$t("user.profile.blocs.users"),
                    icon: "fa fa-user icon-brown",
                },
                {
                    key: "vehicle-position",
                    name: "Position de véhicule",
                    icon: "fa fa-car icon-brown",
                },
            ],
            movable: deviceType() == "desktop",
        };
    },

    methods: {
        /**
         * Get user columns
         */
        getSetting() {
            store.dispatch(GET_PROJECT_USER_SETTING, { key: "user-profile" });
        },

        getField(slug) {
            const meta = slug.indexOf("meta->") == 0;
            slug = slug.replace("meta->", "");

            const field = this.fields.find(
                (f) => f.for == "user" && f.slug == slug && f.meta == meta
            );

            return field;
        },

        blocsAt(column) {
            return this.projectUserSettingsUserProfile.filter(
                (bloc) => bloc.column == column
            );
        },

        setBlocColumn(e) {
            let newSettings = [...this.projectUserSettingsUserProfile];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "user-profile",
                value: newSettings,
            });
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
        addBloc(bloc) {
            let newSettings = [...this.projectUserSettingsUserProfile];
            newSettings[this.columnTab] = [
                ...newSettings[this.columnTab],
                bloc,
            ];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "user-profile",
                value: newSettings,
            });

            this.tab = 0;
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
            let newSettings = [...this.projectUserSettingsUserProfile];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "user-profile",
                value: newSettings,
            });

            this.tab = 0;
        },

        /**
         *
         * @param {*} bloc
         */
        removeBloc(column, index) {
            let newSettings = [...this.projectUserSettingsUserProfile];
            newSettings[this.columnTab] = newSettings[column].filter(
                (b, i) => i != index
            );

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "user-profile",
                value: newSettings,
            });
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
        addNewBloc(bloc) {
            if (bloc.group) {
                bloc.action();
            } else {
                this.addBloc({
                    type: bloc.key,
                    name: bloc.name,
                });
            }
        },

        /**
         *
         */
        selectBlocField(column, index) {
            this.bloc = {
                column,
                index,
            };

            this.blocTab = 1;
            this.tab = 2;
        },

        /**
         *
         */
        addBlocField(field) {
            let newSettings = [...this.projectUserSettingsUserProfile];
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
                key: "user-profile",
                value: newSettings,
            });

            // this.tab = 0;
        },

        /**
         *
         */
        removeBlocField(column, index, field) {
            let newSettings = [...this.projectUserSettingsUserProfile];
            const items = Array.isArray(newSettings[column][index].items)
                ? newSettings[column][index].items
                : [];

            newSettings[column][index].items = items.filter((f) => f != field);

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "user-profile",
                value: newSettings,
            });
        },

        getBlocIcon(bloc) {
            const b = this.blocs.find((b) => b.key == bloc);

            if (b) {
                return b.icon;
            }

            return "fa-columns";
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "fields",
            "folders",
            "projectUserSettingsUserProfile",
            "can",
        ]),

        /**
         *
         */
        selectedFields() {
            return this.projectUserSettingsUserProfile.reduce(
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
                    field.for == "user" &&
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
                    folder.for == "user" &&
                    removeStringAccent(folder.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
./BlowRow.vue
