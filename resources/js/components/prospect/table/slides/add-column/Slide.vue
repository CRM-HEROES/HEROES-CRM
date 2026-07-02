<template>
    <slide
        :title="$t('prospect.table.column.manage', { project: project.name })"
        :name="name"
        :width="380"
        @open="fetchProspectsTableSetting(), fetchProspectsTableMenusSetting()"
    >
        <tab-layout :count="2" :tab="importTab">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="keyword" />

                    <!-- Shown menus setting -->

                    <draggable
                        tag="div"
                        style="
                            border-bottom: 1px solid #ddd;
                            align-items: center;
                            justify-content: center;
                            display: flex;
                            flex-direction: row;
                            width: 100%;
                            font-size: 12px;
                            position: relative;
                        "
                        :list="menus"
                        item-key="key"
                        v-tuto="{
                            key: 'project.prospect.table.setting.menus',
                            name: 'Tableau de prospects - Paramètres - Menus',
                            body: 'Choisir les menus qui s\'affichent sur chaque ligne de prospects dans votre tableau.',
                            timeout: 300,
                        }"
                        group="menu"
                        @end="menuMoved"
                    >
                        <template #item="{ element }">
                            <menu-row :menu="element" v-model="settingMenus" />
                        </template>
                    </draggable>

                    <!-- Columns -->

                    <item-list class="hc-flex-1">
                        <!-- Fixed columns -->

                        <draggable
                            tag="item-list"
                            :disabled="!movable"
                            :list="availableColumns.filter((c) => c.fixed)"
                            item-key="columnId"
                            style="
                                border-bottom: 1px solid #1e6ee5;
                                height: auto;
                                overflow: visible;
                            "
                            group="fixed-columns"
                            @end="fixedColumnMoved"
                        >
                            <template #item="{ element }">
                                <column-row
                                    :column="element"
                                    :existing-column="existingColumn(element)"
                                    @add="addColumn"
                                    @remove="removeColumn"
                                    @toggle-pin="togglePinColumn"
                                    @toggle-align="toggleAlign"
                                />
                            </template>
                        </draggable>

                        <!-- Not fixed columns -->

                        <draggable
                            tag="item-list"
                            :disabled="!movable"
                            :list="
                                availableColumns.filter(
                                    (c) => c.fixed === false
                                )
                            "
                            item-key="columnId"
                            style="
                                border-bottom: 1px solid #1e6ee5;
                                height: auto;
                                overflow: visible;
                            "
                            group="not-fixed-columns"
                            @end="notFixedColumnMoved"
                        >
                            <template #item="{ element }">
                                <column-row
                                    :column="element"
                                    :existing-column="existingColumn(element)"
                                    @add="addColumn"
                                    @remove="removeColumn"
                                    @toggle-pin="togglePinColumn"
                                    @toggle-align="toggleAlign"
                                />
                            </template>
                        </draggable>

                        <!-- Not added columns -->

                        <column-row
                            v-for="element in availableColumns.filter(
                                (c) => c.fixed === null
                            )"
                            :column="element"
                            @add="addColumn"
                        />
                    </item-list>

                    <buttons v-if="can('all.project.category.label.add')">
                        <a
                            @click.prevent.stop="
                                (importTab = 1), (frameTab = 2)
                            "
                            v-tooltip="$t('field.add.title')"
                            v-text="$t('field.add.title')"
                        ></a>
                        <a
                            @click.prevent.stop="
                                (importTab = 1), (frameTab = 1)
                            "
                            v-tooltip="$t('label.add.title')"
                            v-text="$t('label.add.title')"
                        ></a>
                    </buttons>

                    <item
                        @click="importTab = 0"
                        style="border-bottom: 1px solid #eee"
                    >
                        <div class="hc-item-main-content hc-flex-column"></div>

                        <!-- Re init -->
                        <icon
                            class="fa fa-trash icon-red"
                            icon-size="15"
                            @click.prevent.stop="
                                setProspectsTableSetting([], false)
                            "
                        />

                        <!-- Columns duplicate -->
                        <icon
                            class="fa fa-copy icon-blue"
                            icon-size="15"
                            @click.prevent.stop="
                                (importTab = 1), (frameTab = 3)
                            "
                        />

                        <!-- Columns from import -->
                        <icon
                            class="fa fa-file-download icon-green"
                            icon-size="15"
                            @click.prevent.stop="
                                (importTab = 1), (frameTab = 0)
                            "
                            v-tooltip="$t('prospect.table.column.import.use')"
                            v-tuto="{
                                key: 'project.prospect.table.setting.columns.import',
                                name: $t(
                                    'prospect.table.column.import.tooltip.name'
                                ),
                                body: $t(
                                    'prospect.table.column.import.tooltip.body'
                                ),
                                timeout: 300,
                            }"
                        />

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

                    <loading
                        :loading="
                            fetchingProspectsTableSetting ||
                            fetchingProspectsTableMenusSetting
                        "
                    />
                </div>
            </template>

            <template #2>
                <frame-layout :count="4" :tab="frameTab" class="hc-flex-1">
                    <template #1>
                        <div
                            class="hc-flex-column"
                            style="height: 100%; position: relative"
                        >
                            <item
                                @click="importTab = 0"
                                style="border-bottom: 1px solid #eee"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                    v-text="
                                        'Utiliser les colonnes de l\'import ...'
                                    "
                                ></div>
                            </item>
                            <search v-model="importKeyword" />
                            <div
                                class="hc-flex-1"
                                style="position: relative; overflow: hidden"
                            >
                                <item-list padding="12px" style="height: 100%">
                                    <import-row
                                        v-for="c in filteredImports"
                                        :key="c.id"
                                        :prospect-import="c"
                                        @click="
                                            setProspectsTableSettingFromImportColumns(
                                                c
                                            )
                                        "
                                    />
                                </item-list>
                                <loading :loading="settingProspectsTable" />
                            </div>
                        </div>
                    </template>

                    <template #2>
                        <form
                            class="hc-flex-column"
                            @submit.prevent="storeCategory"
                        >
                            <item
                                @click="importTab = 0"
                                style="border-bottom: 1px solid #eee"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                    v-text="$t('label.add.title')"
                                ></div>
                            </item>
                            <item-list class="hc-flex-1" gap="5px">
                                <v-field
                                    :label="$t('name')"
                                    required
                                    v-slot="{ label }"
                                    ><input
                                        required
                                        :placeholder="label + ' ...'"
                                        v-model="category.name"
                                /></v-field>
                                <v-field :label="$t('description')" v-slot="p">
                                    <textarea
                                        v-model="category.description"
                                        :placeholder="p.label + ' ...'"
                                    ></textarea>
                                </v-field>
                                <v-field :label="$t('for')" required>
                                    <select v-model="category.for">
                                        <option
                                            value="prospect"
                                            v-text="$t('prospects')"
                                        ></option>
                                        <option
                                            value="user"
                                            v-text="$t('users')"
                                        ></option>
                                        <option
                                            value="order"
                                            v-text="$t('orders')"
                                        ></option>
                                        <option
                                            value="product"
                                            v-text="$t('products')"
                                        ></option>
                                    </select>
                                </v-field>
                                <v-field :label="$t('color')"
                                    ><input
                                        type="color"
                                        v-model="category.color"
                                /></v-field>
                                <color-palette v-model="category.color" />
                                <v-field :label="$t('bgcolor')"
                                    ><input
                                        type="color"
                                        v-model="category.bgcolor"
                                /></v-field>
                                <color-palette v-model="category.bgcolor" />
                            </item-list>
                            <buttons>
                                <button v-text="$t('add')"></button>
                            </buttons>
                            <loading :loading="addingCategory" />
                        </form>
                    </template>

                    <template #3>
                        <form
                            class="hc-flex-column"
                            @submit.prevent="storeField"
                        >
                            <item class="bordered" @click="importTab = 0">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('field.add.title')"
                                ></div>
                            </item>
                            <item-list
                                gap="5px"
                                class="hc-flex-1"
                                padding="10px 0"
                            >
                                <v-field :label="$t('name')" required v-slot="p"
                                    ><input
                                        ref="fieldName"
                                        required
                                        :placeholder="p.label + ' ...'"
                                        v-model="field.name"
                                /></v-field>
                                <v-field :label="$t('description')" v-slot="p">
                                    <textarea
                                        v-model="field.description"
                                        :placeholder="p.label + ' ...'"
                                    ></textarea>
                                </v-field>
                                <v-field :label="$t('type')" required>
                                    <select v-model="field.type">
                                        <option
                                            value="text"
                                            v-text="$t('field.types.text')"
                                        ></option>
                                        <option
                                            value="long_text"
                                            v-text="$t('field.types.long_text')"
                                        ></option>
                                        <option
                                            value="number"
                                            v-text="$t('field.types.number')"
                                        ></option>
                                        <option
                                            value="email"
                                            v-text="$t('field.types.email')"
                                        ></option>
                                        <option
                                            value="date"
                                            v-text="$t('field.types.date')"
                                        ></option>
                                        <option
                                            value="datetime"
                                            v-text="$t('field.types.datetime')"
                                        ></option>
                                        <option
                                            value="address"
                                            v-text="$t('field.types.address')"
                                        ></option>
                                        <option
                                            value="url"
                                            v-text="$t('field.types.url')"
                                        ></option>
                                        <option
                                            value="tel"
                                            v-text="$t('field.types.tel')"
                                        ></option>
                                    </select>
                                </v-field>
                                <item tag="label" style="padding-right: 5px">
                                    <icon class="fa fa-search" :style="style" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t('field.add_to_global_search')
                                        "
                                    ></div>
                                    <checkbox v-model="field.searchable" />
                                </item>
                            </item-list>
                            <buttons>
                                <button v-text="$t('add')"></button>
                            </buttons>
                            <loading :loading="addingField" />
                        </form>
                    </template>

                    <template #4>
                        <div class="hc-flex-column" style="height: 100%">
                            <item
                                @click="importTab = 0"
                                style="border-bottom: 1px solid #eee"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                    v-text="$t('back')"
                                ></div>
                            </item>
                            <tab
                                :count="2"
                                @tab:change="
                                    (t) => {
                                        duplicateOriginTab = t;
                                    }
                                "
                            >
                                <template #1>{{
                                    $t("user.duplicate.from")
                                }}</template>
                                <template #2>{{
                                    $t("user.duplicate.for")
                                }}</template>
                            </tab>
                            <tab-layout
                                :count="2"
                                :tab="duplicateOriginTab"
                                class="hc-flex-1"
                            >
                                <template #1>
                                    <div
                                        class="hc-flex-column"
                                        style="height: 100%"
                                    >
                                        <search v-model="userKeyword" />
                                        <item-list
                                            class="hc-flex-1"
                                            padding="5px"
                                        >
                                            <user-row
                                                v-for="user in filteredUsers"
                                                :key="user.id"
                                                :user="user"
                                                v-model="duplicateFor"
                                            />
                                        </item-list>
                                        <buttons>
                                            <button
                                                @click.prevent="
                                                    duplicateProfile
                                                "
                                                v-text="'Dupliquer'"
                                                :disabled="
                                                    duplicateFor.length == 0
                                                "
                                            ></button>
                                        </buttons>
                                        <loading :loading="duplicating" />
                                    </div>
                                </template>

                                <template #2>
                                    <div
                                        class="hc-flex-column"
                                        style="height: 100%"
                                    >
                                        <search v-model="userKeyword" />
                                        <item-list
                                            class="hc-flex-1"
                                            padding="5px"
                                        >
                                            <user-from-row
                                                v-for="user in filteredUsers"
                                                :key="user.id"
                                                :user="user"
                                                @click.prevent="
                                                    (userFrom = user),
                                                        duplicateProfile()
                                                "
                                            />
                                        </item-list>
                                        <loading :loading="duplicating" />
                                    </div>
                                </template>
                            </tab-layout>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import UserDuplicateService from "@/apis/project/user/duplicate";
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SHOW_IMPORT } from "@/actions/project/import";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import {
    GET_PROJECT_USER_SETTING,
    UPDATE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";
import { SHOW_PROJECT } from "@/actions/project";
import { ADD_CATEGORY } from "@/actions/project/category";
import { ADD_FIELD } from "@/actions/project/field";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import ColumnRow from "./ColumnRow.vue";
import CategoryRow from "./CategoryRow.vue";
import ImportRow from "./ImportRow.vue";
import MenuRow from "./MenuRow.vue";
import UserFromRow from "./UserFromRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        ColumnRow,
        CategoryRow,
        ImportRow,
        MenuRow,
        UserFromRow,
        UserRow,
    },

    data() {
        return {
            name: "prospects-table-columns-setting",
            frameTab: 0,
            importTab: 0,

            // keyword search
            keyword: "",
            importKeyword: "",
            userKeyword: "",

            category: this.newCategory(),
            addingCategory: false,

            field: this.newField(),
            addingField: false,

            movable: deviceType() == "desktop",

            prospectsTableSetting: [],
            prospectsTableMenusSetting: [],

            duplicateOriginTab: 0,

            settingProspectsTable: false,
            fetchingProspectsTableSetting: false,
            fetchingProspectsTableMenusSetting: false,

            userFrom: null,
            duplicateFor: [],
            duplicating: false,
        };
    },

    methods: {
        async fetchProspectsTableSetting() {
            this.fetchingProspectsTableSetting = true;

            try {
                this.prospectsTableSetting = await store.dispatch(
                    GET_PROJECT_USER_SETTING,
                    {
                        key: "prospects-table",
                        user: this.user.id,
                    }
                );

                if (typeof this.prospectsTableSetting === "object") {
                    this.prospectsTableSetting = Object.values(
                        this.prospectsTableSetting
                    );
                }
            } finally {
                this.fetchingProspectsTableSetting = false;
            }
        },

        async fetchProspectsTableMenusSetting() {
            this.fetchingProspectsTableMenusSetting = true;

            try {
                this.prospectsTableMenusSetting = await store.dispatch(
                    GET_PROJECT_USER_SETTING,
                    {
                        key: "prospects-table.menus",
                        user: this.user.id,
                    }
                );
            } finally {
                this.fetchingProspectsTableMenusSetting = false;
            }
        },

        async setProspectsTableSetting(settings, refetchProspects) {
            this.prospectsTableSetting = settings;
            await store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospects-table",
                value: settings,
                user: this.user.id,
            });

            if (refetchProspects) {
                store.dispatch(SHOW_PROJECT, this.project.slug);
                store.dispatch(FETCH_PROSPECTS);
            }
        },

        async setProspectsTableSettingFromImportColumns(prospectImport) {
            this.settingProspectsTable = true;

            try {
                prospectImport = await store.dispatch(
                    SHOW_IMPORT,
                    prospectImport.id
                );

                const settings = prospectImport.mapping
                    .filter((s) => s)
                    .map((s) => ({
                        key: s,
                    }));

                this.setProspectsTableSetting(settings, true);
            } finally {
                this.settingProspectsTable = false;
            }
        },

        menuMoved(e) {
            let settings = this.settingMenus;
            let newSettings;

            const oldIndex = e.oldDraggableIndex;
            const newIndex = e.newDraggableIndex;

            if (oldIndex < newIndex) {
                newSettings = [
                    ...settings.slice(0, oldIndex),
                    ...settings.slice(oldIndex + 1, newIndex + 1),
                    settings[oldIndex],
                    ...settings.slice(newIndex + 1),
                ];
            } else if (oldIndex > newIndex) {
                newSettings = [
                    ...settings.slice(0, newIndex),
                    settings[oldIndex],
                    ...settings.slice(newIndex, oldIndex),
                    ...settings.slice(oldIndex + 1),
                ];
            }

            this.settingMenus = newSettings;
        },

        /**
         *
         * @param {*} e
         */
        fixedColumnMoved(e) {
            this.columnMoved(e, true);
        },

        /**
         *
         * @param {*} e
         */
        notFixedColumnMoved(e) {
            this.columnMoved(e, false);
        },

        /**
         *
         * @param {*} e
         * @param {*} fixed
         */
        columnMoved(e, fixed) {
            let settings = this.prospectsTableSetting.filter((c) =>
                this.columns.find((column) => column.columnId == c.key)
            );
            let newSettings;

            const oldIndex = fixed
                ? this.availableColumns.filter((c) => c.fixed)[
                      e.oldDraggableIndex
                  ].index
                : this.availableColumns.filter((c) => !c.fixed)[
                      e.oldDraggableIndex
                  ].index;
            const newIndex = fixed
                ? this.availableColumns.filter((c) => c.fixed)[
                      e.newDraggableIndex
                  ].index
                : this.availableColumns.filter((c) => !c.fixed)[
                      e.newDraggableIndex
                  ].index;

            if (oldIndex < newIndex) {
                newSettings = [
                    ...settings.slice(0, oldIndex),
                    ...settings.slice(oldIndex + 1, newIndex + 1),
                    settings[oldIndex],
                    ...settings.slice(newIndex + 1),
                ];
            } else if (oldIndex > newIndex) {
                newSettings = [
                    ...settings.slice(0, newIndex),
                    settings[oldIndex],
                    ...settings.slice(newIndex, oldIndex),
                    ...settings.slice(oldIndex + 1),
                ];
            }

            if (newSettings) {
                this.setProspectsTableSetting(newSettings);
            }
        },

        /**
         *
         */
        newCategory() {
            return {
                name: "",
                description: "",
                for: "prospect",
                color: "#FFFFFF",
                bgcolor: "#000000",
            };
        },

        /**
         *
         */
        async storeCategory() {
            this.addingCategory = true;

            try {
                const data = await store.dispatch(ADD_CATEGORY, this.category);

                const settings = [
                    ...this.prospectsTableSetting,
                    { key: "category->" + data.id },
                ];

                this.setProspectsTableSetting(settings, true);
            } finally {
                this.category = this.newCategory();
                this.addingCategory = false;
                this.importTab = 0;
            }
        },

        /**
         *
         */
        newField() {
            return {
                name: "",
                description: "",
                for: "prospect",
                meta: true,
                type: "text",
                searchable: false,
            };
        },

        /**
         * Store new field
         */
        async storeField() {
            this.addingField = true;

            try {
                const field = await store.dispatch(ADD_FIELD, this.field);
                const column = {
                    columnId: "meta->" + field.slug,
                    headerId: "hc-prospects-table-header-meta-" + field.slug,
                };

                this.addColumn(column);
                store.commit(CLOSE_SLIDE);
            } finally {
                this.field = this.newField();
                this.addingField = false;
                this.importTab = 0;
            }
        },

        /**
         */
        existingColumn(column) {
            return this.prospectsTableSetting.find(
                (c) => c.key == column.columnId
            );
        },

        /**
         * When we're inside the prospects table
         * Let scroll to the column
         * corresponding to the first filter
         * from the menu
         */
        scrollToCorrespondingColumn(column) {
            const header = document.getElementById(column.headerId);

            if (!header) {
                return;
            }

            header.scrollIntoView();
        },

        /**
         * Add column
         * to the prospects table
         * for the current user
         */
        async addColumn(c) {
            // Get user prospects table settings
            let newSettings = this.prospectsTableSetting;

            const index = newSettings.findIndex(
                (column) => column.key === c.columnId
            );

            if (index >= 0) {
                delete newSettings[index].hidden;
            } else {
                // Add new column
                newSettings.push({
                    key: c.columnId,
                });
            }

            this.prospectsTableSetting = newSettings;

            try {
                // Update setting
                this.setProspectsTableSetting(newSettings, true);

                await this.$nextTick();
                this.scrollToCorrespondingColumn(c);
            } finally {
            }
        },

        /**
         * Remove column
         * from the prospects table
         * for the current user
         */
        async removeColumn(c) {
            // Get user prospects table settings
            let newSettings = this.prospectsTableSetting.map((column) =>
                column.key === c.columnId ? { ...column, hidden: true } : column
            );

            // Update setting
            this.setProspectsTableSetting(newSettings);
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn(column) {
            let settings = this.prospectsTableSetting;
            const count = settings.filter((c) => c.fixed).length;
            const index = column.index;

            let newSettings;

            // Do not fix the column
            if (column.fixed) {
                delete settings[index].fixed;
                // Put the column
                // At the start
                // of the list of none fixed columns
                newSettings = [
                    ...settings.slice(0, index),
                    ...settings.slice(index + 1, count),
                    settings[index],
                    ...settings.slice(count),
                ];
                // Fix the column
            } else {
                settings[index].fixed = true;
                // Put the column
                // At the end
                // of the list of fixed columns
                newSettings = [
                    ...settings.slice(0, count),
                    settings[index],
                    ...settings.slice(count, index),
                    ...settings.slice(index + 1),
                ];
            }

            // Update user prospects table setting
            this.setProspectsTableSetting(newSettings);
        },

        /**
         * Toggle align to left, center or right
         */
        toggleAlign(column) {
            const align =
                column.align == "center"
                    ? "right"
                    : column.align == "right"
                    ? "left"
                    : "center";

            const newSettings = this.prospectsTableSetting.map((c) =>
                c.key == column.columnId ? { ...c, align } : c
            );

            // Update user prospects table setting
            this.setProspectsTableSetting(newSettings);
        },

        async duplicateProfile() {
            this.duplicating = true;

            try {
                const { data } = await UserDuplicateService.duplicate(
                    this.project.slug,
                    this.duplicateOriginTab == 0
                        ? this.currentUser.id
                        : this.userFrom.id,
                    {
                        duplicatables: ["setting->prospects-table"],
                        users:
                            this.duplicateOriginTab == 0
                                ? this.duplicateFor
                                : [this.currentUser.id],
                    }
                );

                if (this.duplicateOriginTab == 1) {
                    store.dispatch(SHOW_USER, this.currentUser.id);
                }
            } finally {
                this.duplicating = false;
                this.importTab = 0;
            }
        },
    },

    watch: {
        user(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                this.fetchProspectsTableSetting();
                this.fetchProspectsTableMenusSetting();
            }
        },
    },

    computed: {
        ...mapGetters("auth", {
            currentUser: "user",
        }),

        ...mapGetters([
            "project",
            "imports",
            "fields",
            "calendars",
            "categories",
            "threads",
            "user",
            "slideOpen",
            "can",
            "users",
        ]),

        settingMenus: {
            get() {
                return this.prospectsTableMenusSetting;
            },
            set(value) {
                this.prospectsTableMenusSetting = value;

                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "prospects-table.menus",
                    value: value,
                    user: this.user.id,
                });
            },
        },

        menus() {
            return [
                {
                    key: "view",
                    name: "Voir le prospect",
                    icon: "fa fa-eye",
                },
                {
                    key: "document",
                    name: "Documents",
                    icon: "fa fa-file-pdf icon-garnet",
                },
                {
                    key: "file",
                    name: "Fichiers",
                    icon: "fa fa-folder icon-blue",
                },
                {
                    key: "message",
                    name: "Messages",
                    icon: "fa fa-envelope icon-green",
                },
                {
                    key: "order",
                    name: "Devis",
                    icon: "fa fa-shopping-cart icon-cyan",
                },
                {
                    key: "interaction",
                    name: "Appel",
                    icon: "fa fa-phone icon-orange",
                },
                {
                    key: "sms",
                    name: "SMS",
                    icon: "fa fa-comment icon-purple",
                },
                {
                    key: "questionnaire",
                    name: "Questionnaires",
                    icon: "fa fa-clipboard icon-garnet",
                },
                {
                    key: "user",
                    name: "Utilisateurs affectés",
                    icon: "fa fa-user-plus icon-purple",
                },
                {
                    key: "group",
                    name: "Groupes affectés",
                    icon: "fa fa-users icon-brown",
                },
                {
                    key: "revision",
                    name: "Historique de modifications",
                    icon: "fa fa-pen icon-blue",
                },
                {
                    key: "duplicate",
                    name: "Duplicate",
                    icon: "fa fa-copy icon-blue",
                },
            ]
                .map((menu) => {
                    const index = this.settingMenus.indexOf(menu.key);
                    return {
                        ...menu,
                        order: index < 0 ? 1000 : index,
                    };
                })
                .sort((a, b) => (a.order > b.order ? 1 : -1));
        },

        eventFields() {
            return [
                "name",
                "description",
                "location",
                "drop_off_location",
                "started_hour",
                "ended_hour",
                "started_date",
                "ended_date",
                "calendar",
                "user",
                "vehicle",
                "prospect",
                "order",
            ];
        },

        columns() {
            return [
                // Events
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.appointments"),
                    icon: "fa fa-calendar icon-purple",
                    columnId: "events",
                    headerId: "hc-prospects-table-header-events-events",
                },
                // Interactions
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.calls"),
                    icon: "fa fa-phone icon-orange",
                    columnId: "interactions",
                    headerId:
                        "hc-prospects-table-header-interactions-interactions",
                },
                // Sms
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.sms"),
                    icon: "fa fa-comments icon-purple",
                    columnId: "sms",
                    headerId: "hc-prospects-table-header-sms-sms",
                },
                // Users
                {
                    type: "users",
                    name: this.$t("prospect.table.column.others.users"),
                    icon: "fa fa-user icon-brown",
                    columnId: "users",
                    headerId: "hc-prospects-table-header-users-users",
                },
                // Groups
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.groups"),
                    icon: "fa fa-users icon-brown",
                    columnId: "groups",
                    headerId: "hc-prospects-table-header-groups-groups",
                },
                // Order
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.orders"),
                    icon: "fa fa-shopping-cart icon-cyan",
                    columnId: "orders",
                    headerId: "hc-prospects-table-header-orders-orders",
                },
                // Creator
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.creator"),
                    icon: "fa fa-user icon-brown",
                    columnId: "creator",
                    headerId: "hc-prospects-table-header-creator-creator",
                },
                // Import
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.import"),
                    icon: "fa fa-file-download icon-brown",
                    columnId: "import",
                    headerId: "hc-prospects-table-header-import-import",
                },
                // Pipedrive
                {
                    type: "other",
                    name: "Pipedrive",
                    icon: "fa fa-share-alt icon-brown",
                    columnId: "pipedrive-accounts",
                    headerId:
                        "hc-prospects-table-header-pipedrive-accounts-pipedrive-accounts",
                },
                // Default fields
                ...this.fields
                    .filter((field) => field.for == "prospect" && !field.meta)
                    .map((field) => ({
                        type: "default-field",
                        name: field.name,
                        icon: "fa fa-columns",
                        columnId: field.slug,
                        headerId:
                            "hc-prospects-table-header-default-" + field.slug,
                    })),
                // Meta fields
                ...this.fields
                    .filter((field) => field.for == "prospect" && field.meta)
                    .map((field) => ({
                        type: "meta-field",
                        name: field.name,
                        icon: "fa fa-plus-square",
                        columnId: "meta->" + field.slug,
                        headerId:
                            "hc-prospects-table-header-meta-" + field.slug,
                    })),
                // Categories
                ...this.categories.map((category) => ({
                    type: "category",
                    icon: "fa fa-tags",
                    iconColor: category.bgcolor,
                    columnId: "category->" + category.id,
                    headerId:
                        "hc-prospects-table-header-category-" + category.id,
                    ...category,
                })),
                // Threads
                ...this.threads.map((thread) => ({
                    type: "thread",
                    name: thread.name,
                    icon: thread.private
                        ? "fa fa-envelope"
                        : "fa fa-envelope-open",
                    iconColor: thread.bgcolor,
                    columnId: "thread->" + thread.id,
                    headerId: "'hc-prospects-table-header-thread-" + thread.id,
                })),
                // Calendar
                ...this.calendars.map((calendar) => ({
                    type: "calendar",
                    icon: "fa fa-calendar",
                    iconColor: calendar.bgcolor,
                    columnId: "calendar->" + calendar.id,
                    headerId:
                        "hc-prospects-table-header-calendar-" + calendar.id,
                    ...calendar,
                })),
                // Event field
                ...this.eventFields.map((field) => ({
                    type: "event-field",
                    icon: "fa fa-calendar",
                    columnId: "event->" + field,
                    headerId: "hc-prospects-table-header-event-field-" + field,
                    name: this.$t(
                        "prospect.table.column.event.fields." + field
                    ),
                })),
                ...this.fields
                    .filter((field) => field.for == "event" && field.meta)
                    .map((field) => ({
                        type: "event-meta-field",
                        icon: "fa fa-calendar",
                        columnId: "event->meta->" + field.slug,
                        headerId:
                            "hc-prospects-table-header-event-meta-field-" +
                            field.slug,
                        name: field.name,
                    })),
            ]
                .map((column, i) => {
                    const index = this.prospectsTableSetting.findIndex(
                        (c) => c.key == column.columnId
                    );
                    const c = this.prospectsTableSetting.find(
                        (c) => c.key == column.columnId
                    );

                    return {
                        ...column,
                        index: index >= 0 ? index : 1000 + i,
                        fixed: c ? (c.fixed ? true : false) : null,
                        align: c ? (c.align ? c.align : "left") : null,
                    };
                })
                .sort((column1, column2) => {
                    return !column1.fixed && column2.fixed
                        ? 1
                        : column1.fixed && !column2.fixed
                        ? -1
                        : column1.index > column2.index
                        ? 1
                        : -1;
                })
                .map((column, index) => ({
                    ...column,
                    index,
                }));
        },

        /**
         *
         */
        availableColumns() {
            const keyword = removeStringAccent(this.keyword);

            return this.columns.filter(
                (column) =>
                    removeStringAccent(column.name).indexOf(keyword) >= 0 ||
                    (column.type == "category" &&
                        column.labels.find(
                            (label) =>
                                removeStringAccent(label.name).indexOf(
                                    keyword
                                ) >= 0
                        ))
            );
        },

        /**
         *
         */
        filteredImports() {
            const keyword = removeStringAccent(this.importKeyword);

            return this.imports.filter(
                (prospectImport) =>
                    removeStringAccent(prospectImport.name).indexOf(keyword) >=
                    0
            );
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) =>
                    user.id != this.user.id &&
                    removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
