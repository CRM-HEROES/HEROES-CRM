<template>
    <modal
        :title="$t('order.table.column.new')"
        name="orders-table-add-column"
        :width="380"
    >
        <tab-layout :count="2" :tab="importTab">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="keyword" />

                    <!--draggable
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
                            key: 'project.order.table.setting.menus',
                            name: 'Tableau de orders - Paramètres - Menus',
                            body: 'Choisir les menus qui s\'affichent sur chaque ligne de orders dans votre tableau.',
                            timeout: 300,
                        }"
                        @end="menuMoved"
                    >
                        <template #item="{ element }">
                            <menu-row :menu="element" v-model="settingMenus" />
                        </template>
                    </draggable-->

                    <item>
                        <icon class="fa fa-cog" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('order.table.setting.decimal')"
                        ></div>
                        <input
                            v-model.lazy="orderTableDecimal"
                            type="number"
                            style="
                                width: 50px;
                                background-color: #f5f4f5;
                                border: none;
                                border-radius: 5px;
                                padding: 0 10px;
                                height: 30px;
                            "
                        />
                    </item>

                    <item-list class="hc-flex-1">
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
                            @end="fixedColumnMoved"
                        >
                            <template #item="{ element }">
                                <column-row :column="element" />
                            </template>
                        </draggable>
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
                            @end="notFixedColumnMoved"
                        >
                            <template #item="{ element }">
                                <column-row :column="element" />
                            </template>
                        </draggable>
                        <column-row
                            v-for="element in availableColumns.filter(
                                (c) => c.fixed === null
                            )"
                            :column="element"
                        />
                    </item-list>

                    <buttons v-if="can('all.project.category.label.add')">
                        <a
                            @click.prevent.stop="
                                (importTab = 1), (frameTab = 1)
                            "
                            v-tooltip="'Créer une colonne de champ ...'"
                            v-text="$t('field.add.title')"
                        ></a>
                        <a
                            @click.prevent.stop="
                                (importTab = 1), (frameTab = 0)
                            "
                            v-tooltip="'Créer une colonne de filtres ...'"
                            v-text="$t('label.add.title')"
                        ></a>
                    </buttons>

                    <item
                        @click="importTab = 0"
                        style="border-bottom: 1px solid #eee"
                    >
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
                </div>
            </template>

            <template #2>
                <frame-layout :count="1" :tab="frameTab" class="hc-flex-1">
                    <template #1>
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
                                            value="order"
                                            v-text="$t('orders')"
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

                    <template #2>
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
                </frame-layout>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SHOW_IMPORT } from "@/actions/project/import";
import { FETCH_ORDERS } from "@/actions/project/order";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { SHOW_PROJECT } from "@/actions/project";
import { ADD_CATEGORY } from "@/actions/project/category";
import { ADD_FIELD } from "@/actions/project/field";

// Components
import ColumnRow from "./ColumnRow.vue";
import CategoryRow from "./CategoryRow.vue";
import MenuRow from "./MenuRow.vue";

export default {
    components: {
        ColumnRow,
        CategoryRow,
        MenuRow,
    },

    data() {
        return {
            frameTab: 0,
            importTab: 0,

            // keyword search
            keyword: "",
            importKeyword: "",

            category: this.newCategory(),
            addingCategory: false,

            field: this.newField(),
            addingField: false,

            movable: deviceType() == "desktop",
            settingOrdersTable: false,
        };
    },

    methods: {
        async setOrdersTableSetting(orderImport) {
            this.settingOrdersTable = true;

            try {
                orderImport = await store.dispatch(SHOW_IMPORT, orderImport.id);

                const settings = orderImport.mapping
                    .filter((s) => s)
                    .map((s) => ({
                        key: s,
                    }));

                await store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table",
                    value: settings,
                });
                store.dispatch(SHOW_PROJECT, this.project.slug);
                store.dispatch(FETCH_ORDERS);
            } finally {
                this.settingOrdersTable = false;
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
            let settings = this.projectUserSettingsOrdersTable.filter((c) =>
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
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table",
                    value: newSettings,
                });
            }
        },

        /**
         *
         */
        newCategory() {
            return {
                name: "",
                description: "",
                for: "order",
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
                    ...this.projectUserSettingsOrdersTable,
                    { key: "category->" + data.id },
                ];

                await store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table",
                    value: settings,
                });
                store.dispatch(SHOW_PROJECT, this.project.slug);
                store.dispatch(FETCH_ORDERS);
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
                for: "order",
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
                const data = await store.dispatch(ADD_FIELD, this.field);

                const settings = [
                    ...this.projectUserSettingsOrdersTable,
                    { key: "meta->" + data.slug },
                ];

                await store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table",
                    value: settings,
                });
                store.dispatch(SHOW_PROJECT, this.project.slug);
                store.dispatch(FETCH_ORDERS);
            } finally {
                this.field = this.newField();
                this.addingField = false;
                this.importTab = 0;
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "imports",
            "fields",
            "categories",
            "threads",
            "projectUserSetting",
            "projectUserSettingsOrdersTable",
            "projectUserSettingsOrdersTableHas",
            "projectUserSettingsOrdersTableMenus",
            "can",
        ]),

        orderTableDecimal: {
            get() {
                return this.projectUserSetting("orders-table.decimal", 2);
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table.decimal",
                    value: value,
                });
            },
        },

        settingMenus: {
            get() {
                return this.projectUserSettingsOrdersTableMenus;
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table.menus",
                    value: value,
                });
            },
        },

        menus() {
            return [
                {
                    key: "view",
                    name: "Voir le devis",
                    icon: "fa fa-eye",
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

        columns() {
            return [
                // Total commission
                {
                    type: "other",
                    name: this.$t(
                        "order.table.column.others.total_commissions"
                    ),
                    icon: "fa fa-money-bill icon-green",
                    columnId: "total_commissions",
                    headerId:
                        "hc-orders-table-header-total_commissions-total_commissions",
                },
                // Prospect
                {
                    type: "other",
                    name: this.$t("order.table.column.others.prospect"),
                    icon: "fa fa-user icon-purple",
                    columnId: "prospect",
                    headerId: "hc-orders-table-header-prospect-prospect",
                },
                // Products
                {
                    type: "other",
                    name: this.$t("order.table.column.others.products"),
                    icon: "fa fa-gift icon-brown",
                    columnId: "products",
                    headerId: "hc-orders-table-header-products-products",
                },
                // Status
                {
                    type: "other",
                    name: this.$t("order.table.column.others.status"),
                    icon: "fa fa-check icon-green",
                    columnId: "status",
                    headerId: "hc-orders-table-header-status-status",
                },
                // Steps
                {
                    type: "other",
                    name: this.$t("order.table.column.others.steps"),
                    icon: "fa fa-step-forward icon-orange",
                    columnId: "steps",
                    headerId: "hc-orders-table-header-steps-steps",
                },
                // Commissions
                {
                    type: "other",
                    name: this.$t("order.table.column.others.commissions"),
                    icon: "fa fa-money-bill icon-green",
                    columnId: "commissions",
                    headerId: "hc-orders-table-header-commissions-commissions",
                },
                // Default fields
                ...this.fields
                    .filter((field) => field.for == "order" && !field.meta)
                    .map((field) => ({
                        type: "default-field",
                        name: field.name,
                        icon: "fa fa-columns",
                        columnId: field.slug,
                        headerId:
                            "hc-orders-table-header-default-" + field.slug,
                    })),
                // Meta fields
                ...this.fields
                    .filter((field) => field.for == "order" && field.meta)
                    .map((field) => ({
                        type: "meta-field",
                        name: field.name,
                        icon: "fa fa-plus-square",
                        columnId: "meta->" + field.slug,
                        headerId: "hc-orders-table-header-meta-" + field.slug,
                    })),
                // Categories
                ...this.categories.map((category) => ({
                    type: "category",
                    icon: "fa fa-tags",
                    iconColor: category.bgcolor,
                    columnId: "category->" + category.id,
                    headerId: "hc-orders-table-header-category-" + category.id,
                    ...category,
                })),
            ]
                .map((column, i) => {
                    const index = this.projectUserSettingsOrdersTable.findIndex(
                        (c) => c.key == column.columnId
                    );
                    const c = this.projectUserSettingsOrdersTable.find(
                        (c) => c.key == column.columnId
                    );

                    return {
                        ...column,
                        index: index >= 0 ? index : 1000 + i,
                        fixed: c ? (c.fixed ? true : false) : null,
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
                    removeStringAccent(column.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredImports() {
            const keyword = removeStringAccent(this.importKeyword);

            return this.imports.filter(
                (orderImport) =>
                    removeStringAccent(orderImport.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
