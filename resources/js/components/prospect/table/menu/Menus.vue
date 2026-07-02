<template>
    <div :class="['hc-prospect-menus', prospectsMenus ? '' : 'hide']">
        <div id="hc-prospect-menus-header" class="hc-flex-row">
            <div
                id="hc-prospect-menus-header-title"
                class="hc-flex-1"
                v-text="project ? project.name : ''"
            ></div>
            <a
                @click="toggleProspectsMenus"
                id="hc-prospect-menus-header-close"
                class="hc-flex-column hc-flex-center"
            >
                &times;
            </a>
        </div>
        <div id="hc-prospect-menus-search">
            <search />
        </div>
        <nav class="hc-prospect-menus-list">
            <ul>
                <li v-for="menu in filteredDefaultMenus" :key="menu.id">
                    <prospect-menu :default-menu="true" :menu="menu" />
                </li>
            </ul>
            <draggable
                tag="ul"
                :list="menuKeyword ? filteredMenus : menus"
                :disabled="menuKeyword || !movable"
                style="overflow: auto"
                item-key="id"
                @end="updateMenuOrder"
            >
                <template #item="{ element }">
                    <li>
                        <prospect-menu :default-menu="false" :menu="element" />
                    </li>
                </template>
            </draggable>

            <item tag="a" @click.prevent="createMenu">
                <icon class="fa fa-plus icon-blue" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('menu.add.title')"
                ></div>
            </item>

            <item
                v-if="can('all.prospect.add')"
                tag="a"
                @click.prevent="addProspect"
            >
                <icon class="fa fa-plus icon-blue" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.footer.add_prospect')"
                ></div>
            </item>

            <item tag="a" @click.prevent="addColumn">
                <icon class="fa fa-table icon-blue" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.footer.setting')"
                ></div>
            </item>

            <item
                v-if="can('all.prospect.import')"
                tag="a"
                @click.prevent="importProspects"
            >
                <icon class="fa fa-file-download icon-green" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.footer.import')"
                ></div>
            </item>

            <item
                v-if="can('all.prospect.import')"
                tag="a"
                @click.prevent="exportProspects"
            >
                <icon class="fa fa-file-upload icon-green" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.footer.export')"
                ></div>
            </item>

            <item
                v-if="can('all.prospect.import')"
                tag="a"
                @click.prevent="findDuplicates"
            >
                <icon class="fa fa-copy icon-blue" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.footer.find_duplicates')"
                ></div>
            </item>
        </nav>
    </div>
</template>

<style>
#hc-prospect-menus-header {
    border-bottom: 1px solid #eeeeee;
    display: flex;
    flex-direction: row;
    font-size: 13px;
    align-items: center;
    position: relative;
}

#hc-prospect-menus-header-title {
    font-weight: bold;
    font-size: 17px;
    padding-left: 20px;
    height: 40px;
    line-height: 40px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#hc-prospect-menus-header-close {
    height: 30px;
    text-align: center;
    width: 30px;
    font-weight: bold;
    font-size: 15px;
    color: #888888;
    cursor: pointer;
    margin: 5px;
    border-radius: 15px;
    border-radius: 15px;
    font-weight: 200;
    text-decoration: none;
}

#hc-prospect-menus-header-close:hover {
    background-color: #eeeeee;
    color: #555555;
    text-decoration: none;
}

.hc-prospect-menus {
    width: 240px;
    height: 100%;
    overflow: auto;
    background-color: white;
    border-right: 1px solid #dddddd;
    display: flex;
    flex-direction: column;
    transition: all 100ms ease-out;
}

.hc-prospect-menus.hide {
    width: 0;
    border: none;
}

.hc-prospect-menus-list {
    padding: 5px 10px;
    width: 100%;
    flex: 1;
    overflow: auto;
}

.hc-prospect-menus-list > ul {
    margin: 0;
    padding: 5px 0;
    list-style: none;
    width: 100%;
    border-bottom: 1px solid #eee;
}

.hc-prospect-menus-list > ul > li {
    width: 100%;
    margin: 2px 0;
}

#hc-prospect-menus-search {
    width: 100% !important;
    height: 60px !important;
    padding: 10px !important;
    border-bottom: 1px solid #eee;
}

#hc-prospect-menus-search > #hc-global-search {
    position: relative !important;
    width: 100% !important;
}

#hc-prospect-menus-search
    > #hc-global-search
    > #hc-global-search-input-container {
    position: unset !important;
    visibility: visible !important;
    opacity: 1 !important;
}

#hc-prospect-menus-search > #hc-global-search > #hc-global-search-input-icon {
    position: absolute !important;
}

#hc-prospect-menus-search
    > #hc-global-search
    > #hc-global-search-input-container
    > input {
    border-radius: 20px !important;
}

#hc-prospect-menus-search > #hc-global-search > #hc-global-search-result {
    top: 36px !important;
}

@media (max-width: 767px) {
    .hc-prospect-menus {
        width: 1600px;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { API_URL } from "@/apis/common";
import DefaultMenuService from "@/apis/project/default-menu";

import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";
import {
    ADD_PROSPECT,
    REMOVE_PROSPECT,
    UPDATE_PROSPECT,
    TOGGLE_PROSPECTS_MENUS,
} from "@/actions/project/prospect";
import { FETCH_MENUS, UPDATE_MENU } from "@/actions/project/menu";

// Components
import ProspectMenu from "./Menu.vue";
import Search from "@/components/layouts/Search.vue";

export default {
    components: {
        ProspectMenu,
        Search,
    },

    data() {
        return {
            createdProspects: 0,
            defaultMenus: [],
            fetchingDefaultMenus: false,
            menuKeyword: "",
            movable: deviceType() == "desktop",
        };
    },

    created() {
        if (this.prospectsMenus) {
            this.fetchDefaultMenus();
            this.fetchMenus();

            setInterval(() => {
                this.fetchDefaultMenus();
                this.fetchMenus();
            }, 300000);
        }
    },

    methods: {
        async fetchDefaultMenus() {
            this.fetchingDefaultMenus = true;
            const { data } = await DefaultMenuService.get(this.project.slug);
            this.fetchingDefaultMenus = false;
            this.defaultMenus = data;
        },

        fetchMenus() {
            store.dispatch(FETCH_MENUS);
        },

        /**
         * Create menu
         * for selected filters
         */
        createMenu() {
            store.commit(OPEN_MODAL, "menu-add");
        },

        /**
         *
         * @param {*} e
         */
        async updateMenuOrder(e) {
            const menu = this.menus[e.newDraggableIndex];

            try {
                await store.dispatch(UPDATE_MENU, {
                    id: menu.id,
                    order: e.newDraggableIndex + 1,
                });
                this.fetchMenus();
            } finally {
            }
        },

        /**
         * Create new prospect line
         */
        newProspect() {
            this.createdProspects++;

            // Create new prospect
            // with empty fields values
            return this.fields
                .filter((field) => field.for == "prospect")
                .reduce(
                    (prospect, field) => {
                        // Empty meta field
                        if (field.meta) {
                            prospect.meta[field.slug] = "";
                            // Empty default field
                        } else {
                            prospect[field.slug] = "";
                        }

                        return prospect;
                    },
                    // Initial new prospect state
                    {
                        // use created prospects count
                        // as default ID
                        id: -this.createdProspects,
                        meta: {},
                    }
                );
        },

        /**
         * Get updated values in new prospect
         * @param {*} prospect
         */
        newProspectUpdatedValues(prospect) {
            // Updated meta fields
            const meta = Object.fromEntries(
                Object.entries(prospect.meta).filter(([k, v]) => v != "")
            );
            // Updated default fields
            const updated = Object.fromEntries(
                Object.entries(prospect).filter(
                    ([k, v]) => k != "id" && k != "meta" && v != ""
                )
            );
            // If meta fields are updated
            if (Object.keys(meta).length > 0) {
                updated.meta = meta;
            }

            return updated;
        },

        /**
         * Store new prospect
         */
        async addProspect() {
            // Create empty prospect
            const prospect = this.newProspect();
            store.commit(ADD_PROSPECT, prospect);

            try {
                // Dispatch new prospect
                const createdProspect = await store.dispatch(
                    ADD_PROSPECT,
                    prospect
                );

                // Get updated values from empty created prospect
                const updatedProspect = this.newProspectUpdatedValues(
                    this.prospects.find((p) => p.id == prospect.id)
                );

                // Dispatch updated values
                if (Object.keys(updatedProspect).length > 0) {
                    updatedProspect.id = createdProspect.id;
                    store.dispatch(UPDATE_PROSPECT, updatedProspect);
                }
            } finally {
                // Remove empty created prospect from the table
                // it will be replaced by the prospect
                // created from the database
                store.commit(REMOVE_PROSPECT, prospect.id);
            }
        },

        /**
         * Add other columns
         * in the table
         */
        addColumn() {
            store.commit(OPEN_SLIDE, "prospects-table-columns-setting");
        },

        /**
         * Import prospects (XLSX, CSV)
         */
        importProspects() {
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         * Download prospects list
         * in a XLSX file
         */
        async exportProspects() {
            let params = this.prospectsParams;
            // We also add selected prospects
            // as filter for the list of prospects
            // do download
            if (this.prospectsSelected.length > 0) {
                params.ids = this.prospectsSelected;
            }

            // Build query URL
            params = JSON.stringify(params);

            // Download prospects list
            location.href =
                `${API_URL}/project/${this.project.slug}/export/create` +
                (params ? "?filters=" + params : "");
        },

        /**
         * Find duplicates
         */
        findDuplicates() {
            store.commit(OPEN_SLIDE, "duplicate");
        },

        /**
         * Toggle prospects menus
         */
        toggleProspectsMenus() {
            store.commit(TOGGLE_PROSPECTS_MENUS);
        },
    },

    watch: {
        prospectsMenus(newValue) {
            if (newValue && this.defaultMenus.length == 0) {
                this.fetchDefaultMenus();
                this.fetchMenus();
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospects",
            "menus",
            "prospectsParamExists",
            "prospectsParams",
            "prospectsSelected",
            "prospectsMenus",
            "fields",
            "can",
        ]),

        /**
         * Check if
         * there are activated filters
         */
        filtersActivated() {
            return this.prospectsParamExists();
        },

        /**
         *
         */
        filteredDefaultMenus() {
            const keyword = removeStringAccent(this.menuKeyword);

            return this.defaultMenus.filter(
                (menu) => removeStringAccent(menu.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredMenus() {
            const keyword = removeStringAccent(this.menuKeyword);

            return this.menus.filter(
                (menu) =>
                    menu.for == "prospect" &&
                    removeStringAccent(menu.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
