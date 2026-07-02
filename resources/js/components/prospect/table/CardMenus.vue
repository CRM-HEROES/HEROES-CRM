<template>
    <card-menu-list>
        <template v-if="!bulkDisabled">
            <card-menu
                icon="fa fa-check-square"
                :label="$t('prospect.table.footer.deselect')"
                :text="prospectsSelected.length"
                @click="bulkDeselect"
            />
        </template>

        <template v-if="bulkDisabled">
            <card-menu
                v-if="filtersActivated"
                icon="fa fa-filter"
                :label="$t('prospect.table.footer.init_filters')"
                title="Réinitialiser les filtres"
                style="background-color: #e6effd"
                color="#1a73e8"
                @click="initParams"
            />
            <card-menu
                icon="fa fa-list"
                :label="$t('prospect.table.footer.toggle_menus')"
                v-tuto="{
                    key: 'project.prospect.table.footer.toggle_menus',
                    name: 'Tableau de prospects - Menus',
                    body: 'En appliquant des filtres à votre tableau de prospects, vous pouvez créer/gérer ici des menus raccourcis.<br>(Ex: Prospects avec devis, Nouveau lead, ...)',
                }"
                @click="toggleProspectsMenus"
            />
            <card-menu
                icon="fa fa-ellipsis-h"
                :label="$t('prospect.table.footer.toggle_prospect_menus')"
                v-tuto="{
                    key: 'project.prospect.table.footer.toggle-prospect-menus',
                    name: 'Tableau de prospects - Menus par ligne',
                    body: 'Vous pouvez afficher des menus raccourcis par prospect',
                }"
                @click="toggleProspectsOptions"
            />
            <card-menu
                v-if="can('all.prospect.add')"
                icon="fa fa-plus icon-green"
                :label="$t('prospect.table.footer.add_prospect')"
                v-tuto="{
                    key: 'project.prospect.table.footer.add-prospect',
                    name: 'Tableau de prospects - Nouveau prospect',
                    body: 'Créer une nouvelle ligne de prospect',
                }"
                @click="addProspect"
            />
            <div
                class="hc-prospects-table-footer-select"
                id="hc-prospects-table-rows-count"
                v-tooltip="$t('prospect.table.footer.page_prospects_count')"
            >
                <select v-model="count">
                    <option
                        v-for="chunk in chunks"
                        :key="chunk"
                        :value="chunk"
                        v-text="chunk"
                    ></option>
                </select>
                <loading :loading="settingProspectsCount" />
            </div>
            <div
                v-tooltip="$t('prospect.table.footer.prospects_count')"
                id="hc-prospects-table-total"
                v-text="prospectsTotal"
            ></div>
        </template>

        <card-menu
            v-if="can('all.prospect.setting-prospects-table')"
            icon="fa fa-table"
            :label="$t('prospect.table.footer.setting')"
            v-tuto="{
                key: 'project.prospect.table.footer.add-column',
                name: 'Tableau de prospects - Colonnes',
                body: 'Ajouter une nouvelle colonne dans le tableau de prospects.<br>(SMS, RDV, devis, appels, filtres, ...)',
            }"
            @click="addColumn"
        />

        <template v-if="bulkDisabled">
            <div
                id="hc-prospects-table-row-max-lines"
                class="hc-prospects-table-footer-select"
                v-tooltip="$t('prospect.table.footer.row_max_lines')"
                v-tuto="{
                    key: 'project.prospect.table.footer.row-max-lines',
                    name: 'Tableau de prospects - Nb lignes max par prospect',
                    body: '<div style=&quot;display: flex; flex-direction: row;align-items: start;gap: 5px;&quot;><div style=&quot;flex: 1&quot;>Afficher les informations des prospects dans les cellules sur plusieurs lignes.</div><img style=&quot;border-radius: 5px;width: 100px;&quot; src=&quot;/images/tutorial/prospects.table.row-max-lines.gif&quot; /></div>',
                }"
            >
                <select v-model="maxLinesPerRow">
                    <option v-for="i in 4" :value="i" v-text="i"></option>
                </select>
            </div>
        </template>

        <card-menu
            v-if="can('all.map')"
            :label="$t('prospect.table.footer.map')"
            @click="showMap"
        >
            <svg
                style="width: 15px"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 92.3 132.3"
            >
                <path
                    fill="#1a73e8"
                    d="M60.2 2.2C55.8.8 51 0 46.1 0 32 0 19.3 6.4 10.8 16.5l21.8 18.3L60.2 2.2z"
                ></path>
                <path
                    fill="#ea4335"
                    d="M10.8 16.5C4.1 24.5 0 34.9 0 46.1c0 8.7 1.7 15.7 4.6 22l28-33.3-21.8-18.3z"
                ></path>
                <path
                    fill="#4285f4"
                    d="M46.2 28.5c9.8 0 17.7 7.9 17.7 17.7 0 4.3-1.6 8.3-4.2 11.4 0 0 13.9-16.6 27.5-32.7-5.6-10.8-15.3-19-27-22.7L32.6 34.8c3.3-3.8 8.1-6.3 13.6-6.3"
                ></path>
                <path
                    fill="#fbbc04"
                    d="M46.2 63.8c-9.8 0-17.7-7.9-17.7-17.7 0-4.3 1.5-8.3 4.1-11.3l-28 33.3c4.8 10.6 12.8 19.2 21 29.9l34.1-40.5c-3.3 3.9-8.1 6.3-13.5 6.3"
                ></path>
                <path
                    fill="#34a853"
                    d="M59.1 109.2c15.4-24.1 33.3-35 33.3-63 0-7.7-1.9-14.9-5.2-21.3L25.6 98c2.6 3.4 5.3 7.3 7.9 11.3 9.4 14.5 6.8 23.1 12.8 23.1s3.4-8.7 12.8-23.2"
                ></path>
            </svg>
        </card-menu>

        <card-menu
            v-if="bulkDisabled || can('all.prospect.import')"
            icon="fa fa-file-download icon-green"
            :label="$t('prospect.table.footer.import')"
            v-tuto="{
                key: 'project.prospect.table.footer.import',
                name: 'Tableau de prospects - Import',
                body: '<div style=&quot;display: flex; flex-direction: row;align-items: start;gap: 5px;&quot;><img style=&quot;border-radius: 5px;width: 60px;&quot; src=&quot;/images/tutorial/prospects.table.imports.png&quot; /><div style=&quot;flex: 1&quot;>Importer des fichiers XLSX de liste de prospects dans le tableau de prospects.</div></div>',
            }"
            @click="importProspects"
        />
        <card-menu
            v-if="can('all.prospect.export')"
            icon="fa fa-file-upload icon-green"
            :label="$t('prospect.table.footer.export')"
            v-tuto="{
                key: 'project.prospect.table.footer.export',
                name: 'Tableau de prospects - Export',
                body: '<div style=&quot;display: flex; flex-direction: row;align-items: start;gap: 5px;&quot;><img style=&quot;border-radius: 5px;width: 60px;&quot; src=&quot;/images/tutorial/prospects.table.imports.png&quot; /><div style=&quot;flex: 1&quot;>Exporter les prospects sélectionnés dans votre tableau dans un fichier XLSX.</div></div>',
            }"
            @click="exportProspects"
        />

        <template v-if="bulkDisabled">
            <card-menu
                :disabled="fetchingPreviousPage || prospectsPage <= 1"
                icon="fa fa-caret-left"
                :label="$t('prospect.table.footer.previous')"
                @click="previousPage"
            >
                <loading :loading="fetchingPreviousPage" />
            </card-menu>
            <div
                class="hc-prospects-table-footer-select"
                id="hc-prospects-table-pages"
                v-tooltip="$t('prospect.table.footer.current_page')"
            >
                <select v-model="page">
                    <option
                        v-for="i in pagesCount"
                        :value="i"
                        v-text="i"
                    ></option>
                </select>
                <loading :loading="settingProspectsPage" />
            </div>
            <card-menu
                :disabled="fetchingNextPage || page >= pagesCount"
                icon="fa fa-caret-right"
                :label="$t('prospect.table.footer.next')"
                @click="nextPage"
            >
                <loading :loading="fetchingNextPage" />
            </card-menu>
            <card-menu
                icon="fa fa-forward"
                :label="'Pipeline'"
                @click="pipeline"
            ></card-menu>
            <card-menu
                v-if="can('all.prospect.ocr')"
                icon="fa fa-qrcode"
                :label="$t('prospect.table.footer.scan')"
                @click="ocr"
            />
        </template>

        <template v-if="!bulkDisabled">
            <card-menu
                v-if="can('all.prospect.delete')"
                icon="fa fa-trash"
                :label="$t('prospect.table.footer.bulk_delete')"
                color="#d9402c"
                @click="bulkRemove"
            >
                <loading :loading="bulkingRemove" />
            </card-menu>
        </template>

        <template v-if="!bulkDisabled || filtersActivated">
            <card-menu
                v-if="!selectedProspectsProcessed || filtersActivated"
                icon="fa fa-user-plus"
                :label="$t('prospect.table.footer.bulk_users')"
                @click="bulkUser"
            />
        </template>

        <template v-if="!bulkDisabled">
            <card-menu
                v-if="!selectedProspectsProcessed"
                icon="fa fa-users"
                :label="$t('prospect.table.footer.bulk_groups')"
                @click="bulkGroup"
            />
            <card-menu
                v-if="!selectedProspectsProcessed"
                icon="fa fa-tags"
                :label="$t('prospect.table.footer.bulk_labels')"
                @click="bulkLabel"
            />
            <card-menu
                v-if="!selectedProspectsProcessed"
                icon="fa fa-copy"
                :label="$t('prospect.table.footer.bulk_project')"
                @click="bulkProject"
            />
            <card-menu
                v-if="!selectedProspectsProcessed && can('all.prospect.sms')"
                icon="fa fa-comments"
                :label="$t('prospect.table.footer.bulk_sms')"
                color="#7939b8"
                @click="bulkSms"
            />
            <card-menu
                v-if="!selectedProspectsProcessed"
                icon="fa fa-envelope"
                :label="$t('prospect.table.footer.bulk_messages')"
                color="#dd214a"
                @click="bulkMessage"
            />
            <card-menu
                v-if="!selectedProspectsProcessed"
                icon="fa fa-pen"
                :label="$t('prospect.table.footer.bulk_update')"
                color="#1e6ee5"
                @click="bulkUpdate"
            />
            <card-menu
                v-if="
                    !selectedProspectsProcessed && can('all.prospect.archive')
                "
                icon="fa fa-lock"
                :label="$t('prospect.table.footer.bulk_archive')"
                color="#e38a00"
                @click="bulkProcessed"
            >
                <loading :loading="bulkingProcessed"
            /></card-menu>
            <card-menu
                v-if="
                    !selectedProspectsNotProcessed &&
                    can('all.prospect.archive')
                "
                icon="fa fa-unlock"
                :label="$t('prospect.table.footer.bulk_do_not_archive')"
                color="#22bcd7"
                @click="bulkNotProcessed"
            >
                <loading :loading="bulkingNotProcessed"
            /></card-menu>
            <card-menu
                v-if="!selectedProspectsProcessed"
                :label="$t('prospect.table.footer.bulk_aircall_interactions')"
                @click="bulkAircallInteraction"
            >
                <icon>
                    <svg viewBox="0 0 40 40">
                        <path
                            fill="#00B388"
                            d="M39.1,9.8c-0.9-4.5-4.5-8-9-9C27.9,0.3,24.2,0,20,0S12.1,0.3,9.9,0.8c-4.5,0.9-8,4.5-9,9 c-0.5,2.3-0.9,6-0.9,10.2c0,4.2,0.3,7.9,0.9,10.2c0.9,4.5,4.5,8,9,9C12.1,39.6,15.8,40,20,40s7.9-0.3,10.1-0.9c4.5-0.9,8-4.5,9-9 c0.5-2.3,0.9-6,0.9-10.2C39.9,15.8,39.6,12.1,39.1,9.8z M29.3,30.5C29.3,30.5,29.3,30.5,29.3,30.5c-0.7,0.3-1.9,0.5-3.5,0.6 c-0.1,0-0.1,0-0.2,0c-0.3,0-0.6-0.2-0.8-0.5c-0.4-0.9-1.2-1.6-2.2-1.8c-0.6-0.1-1.5-0.2-2.6-0.2s-2,0.1-2.6,0.2 c-1,0.2-1.8,0.9-2.2,1.8c-0.1,0.3-0.4,0.5-0.8,0.5c-0.1,0-0.2,0-0.2,0c-1.6-0.2-2.8-0.4-3.5-0.6c0,0,0,0,0,0 c-0.5-0.2-0.8-0.6-0.8-1.2c0,0,0,0,0,0c0,0,0,0,0-0.1c0,0,0,0,0,0c0,0,0,0,0,0c0.1-1.6,1.1-5.5,2.6-9.8c1.7-5,3.5-9,4.2-9.8 c0.1-0.1,0.3-0.2,0.4-0.3c0.1,0,0.1-0.1,0.2-0.1c0,0,0,0,0,0c0.5-0.2,1.5-0.3,2.6-0.3c1.1,0,2.1,0.1,2.6,0.3c0,0,0,0,0,0 c0.1,0,0.2,0.1,0.2,0.1c0.2,0.1,0.3,0.2,0.4,0.3c0,0,0,0,0,0c0.8,0.8,2.6,4.8,4.2,9.8c1.5,4.4,2.5,8.2,2.6,9.8c0,0,0,0,0,0 c0,0,0,0,0,0c0,0,0,0,0,0.1c0,0,0,0,0,0C30.1,29.8,29.8,30.3,29.3,30.5z"
                        ></path>
                    </svg>
                </icon>
            </card-menu>
            <card-menu
                v-if="!selectedProspectsProcessed"
                icon="fa fa-phone"
                :label="$t('prospect.table.footer.bulk_ringover_interactions')"
                @click="bulkRingoverInteraction"
            >
                <icon>
                    <svg viewBox="0 0 40 40">
                        <path
                            d="M9.9,16.9c1.3-4.3,5.3-7.4,10.1-7.4s8.7,3.1,10.1,7.4h9.7C38.2,7.3,30,0,20,0S1.8,7.3,0.3,16.9H9.9z"
                            style="fill: rgb(85, 195, 192)"
                        ></path>
                        <path
                            d="M30.1,23.1c-1.3,4.3-5.3,7.4-10.1,7.4s-8.7-3.1-10.1-7.4H0.3C1.8,32.7,10,40,20,40s18.2-7.3,19.7-16.9H30.1z"
                            style="fill: rgb(85, 195, 192)"
                        ></path>
                    </svg>
                </icon>
            </card-menu>
        </template>

        <!--card-menu icon="fa fa-search" :label="$t('prospect.table.footer.search_and_replace')" /-->
    </card-menu-list>
</template>

<style scoped>
.hc-prospects-table-footer-select {
    position: relative;
    width: unset !important;
    min-width: unset !important;
}

.hc-prospects-table-footer-select > select {
    border: none;
    background: none;
    padding: 3px 20px 3px 8px;
    border-radius: 7px;
    font-size: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    height: 40px;
    width: auto;
    background-color: #f0f0f0;
}

.hc-prospects-table-footer-select > select:hover {
    background-color: #eeeeee;
    cursor: pointer;
}

.hc-prospects-table-footer-select:before {
    pointer-events: none;
}

.hc-prospects-table-footer-select:after {
    content: "";
    position: absolute;
    right: 7px;
    top: 17px;
    width: 0;
    height: 0;
    border-radius: 2px;
    border-top: 5px solid #555555;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    pointer-events: none;
}

#hc-prospects-table-total {
    background-color: #7939b8;
    color: white;
    padding: 0 10px;
    margin: 5px;
    border-radius: 15px;
    height: 30px;
    line-height: 30px;
    width: auto;
    font-size: 13px;
    min-width: unset;
}

/*#hc-prospects-table-rows-count:before {
    content: "";
    position: absolute;
    right: 7px;
    top: 12px;
    width: 0;
    height: 0;
    border: 1px solid;
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

#hc-prospects-table-rows-count:after {
    right: 3px;
    top: 21px;
    border-bottom: none;
    border-top: 1px solid;
    border-left: 1px solid;
    border-right: 1px solid;
    width: 16px;
    height: 5px;
    border-radius: 50% 50% 1px 1px;
}*/

#hc-prospects-table-row-max-lines > select {
    border: none;
}

#hc-prospects-table-row-max-lines:before {
    content: "";
    position: absolute;
    right: 10px;
    top: 19px;
    width: 7px;
    height: 0;
    border-top: 1px solid;
}

#hc-prospects-table-row-max-lines:after {
    top: 15px;
    width: 10px;
    height: 9px;
    border-radius: 0;
    border-left: none;
    border-right: none;
    border-top: 1px solid;
    border-bottom: 1px solid;
}

#hc-prospects-table-pages:before {
    content: "";
    position: absolute;
    border-left: 1px solid;
    top: 13px;
    right: 9px;
    width: 0;
    height: 12px;
    z-index: 1;
}

#hc-prospects-table-pages:after {
    top: 13px;
    right: 2px;
    width: 15px;
    height: 12px;
    border: 1px solid;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE, OPEN_LEFT_SLIDE } from "@/actions/slide";
import {
    BULK_REMOVE_PROSPECT,
    BULK_RESTORE_PROSPECT,
    BULK_PROCESSED_PROSPECT,
    BULK_NOT_PROCESSED_PROSPECT,
    FETCH_PROSPECTS,
    UPDATE_SELECTED_PROSPECTS,
    INIT_PROSPECT_PARAMS,
    ADD_PROSPECT,
    REMOVE_PROSPECT,
    UPDATE_PROSPECT,
    SET_PROSPECT,
    SET_PROSPECTS_PAGE,
    TOGGLE_PROSPECTS_OPTIONS,
    TOGGLE_PROSPECTS_MENUS,
    SET_PROSPECTS_COUNT,
    // SET_PROSPECTS_MAX_LINES_PER_ROW,
} from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import {
    SET_PROSPECT_INTERACTION_TAB,
    SET_PROSPECT_INTERACTION_FRAME_TAB,
} from "@/actions/project/prospect/interaction";

import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { SET_USER } from "@/actions/project/user";
import { API_URL } from "@/apis/common";

export default {
    data() {
        return {
            createdProspects: 0,
            bulkingRemove: false,
            bulkingRestore: false,
            bulkingProcessed: false,
            bulkingNotProcessed: false,
            fetchingPreviousPage: false,
            fetchingNextPage: false,
            settingProspectsCount: false,
            settingProspectsPage: false,
            chunks: [10, 20, 50, 100, 200, 300, 400, 500],
        };
    },

    created() {
        if (deviceType() != "desktop") {
            if (this.prospectsMenus) {
                this.toggleProspectsMenus();
            }

            if (this.prospectsOptions) {
                this.toggleProspectsOptions();
            }
        }
    },

    methods: {
        /**
         * Deselect selected prospect
         */
        bulkDeselect() {
            store.commit(UPDATE_SELECTED_PROSPECTS, []);
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
            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "prospects-table-columns-setting");
        },

        /**
         * Scan CIN
         */
        ocr() {
            store.commit(OPEN_MODAL, "prospects-table-ocr");
        },

        /**
         * Previous page
         */
        async previousPage() {
            store.commit(SET_PROSPECTS_PAGE, parseInt(this.prospectsPage) - 1);

            // Refresh prospects list
            this.fetchingPreviousPage = true;

            try {
                await store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.fetchingPreviousPage = false;
            }
        },

        /**
         * Next page
         */
        async nextPage() {
            store.commit(SET_PROSPECTS_PAGE, parseInt(this.prospectsPage) + 1);

            // Refresh prospects list
            this.fetchingNextPage = true;

            try {
                await store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.fetchingNextPage = false;
            }
        },

        pipeline() {
            this.$router.push({
                name: "pipeline",
                params: {
                    project: this.project.slug,
                },
            });
        },

        /**
         * Toggle prospects menus
         */
        toggleProspectsMenus() {
            store.commit(TOGGLE_PROSPECTS_MENUS);
        },

        /**
         * Init filters
         */
        initParams() {
            store.commit(INIT_PROSPECT_PARAMS);
            store.dispatch(FETCH_PROSPECTS);
        },

        /**
         * Toggle prospects options
         * Show or hide each prospect menus (sms, phone, order, ..)
         * from the prospects table
         */
        toggleProspectsOptions() {
            store.commit(TOGGLE_PROSPECTS_OPTIONS);
        },

        /**
         * Import prospects (XLSX, CSV)
         */
        importProspects() {
            store.commit(OPEN_SLIDE, "import");
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         * Bulk groups for selected prospects
         * See: @/components/prospect/bulk/group/Slide.vue
         */
        bulkGroup() {
            store.commit(OPEN_SLIDE, "prospect-bulk-manage-groups");
        },

        /**
         * Bulk labels for selected prospects
         * See: @/components/prospect/bulk/label/Slide.vue
         */
        bulkLabel() {
            store.commit(OPEN_SLIDE, "prospect-bulk-manage-labels");
        },

        /**
         * Bulk project for selected prospects
         * See: @/components/prospect/bulk/project/Slide.vue
         */
        bulkProject() {
            store.commit(OPEN_SLIDE, "prospect-bulk-manage-project");
        },

        /**
         * Bulk sms for selected prospects
         * See: @/components/prospect/sms/Slide.vue
         */
        bulkSms() {
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },

        /**
         * Bulk users for selected prospects
         * See: @/components/prospect/bulk/user/Slide.vue
         */
        bulkUser() {
            store.commit(OPEN_SLIDE, "prospect-bulk-manage-users");
        },

        /**
         * Remove multiple prospects
         */
        async bulkRemove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.bulkingRemove = true;

                // Selected prospects
                const prospectsSelected = this.prospectsSelected;
                store.commit(UPDATE_SELECTED_PROSPECTS, []);

                try {
                    // Dispatch bulk remove
                    await store.dispatch(
                        BULK_REMOVE_PROSPECT,
                        prospectsSelected
                    );
                    // Refresh prospects list
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.bulkingRemove = false;
                }
            });
        },

        /**
         * Restore multiple deleted prospects
         */
        async bulkRestore() {
            this.bulkingRestore = true;

            // Selected prospects
            const prospectsSelected = this.prospectsSelected;
            store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                // Dispatch bulk restore
                await store.dispatch(BULK_RESTORE_PROSPECT, prospectsSelected);

                // Refresh prospects list
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulkingRestore = false;
            }
        },

        /**
         * Send message to multiple prospects
         */
        async bulkMessage() {
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         * Update multiple prospects field
         */
        async bulkUpdate() {
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-bulk-update");
        },

        /**
         * Mark multiple prospects as processed
         */
        async bulkProcessed() {
            this.bulkingProcessed = true;

            // Selected prospects
            const prospectsSelected = this.prospectsSelected;
            store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                // Dispatch bulk processed
                await store.dispatch(
                    BULK_PROCESSED_PROSPECT,
                    prospectsSelected
                );

                // Refresh prospects list
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulkingProcessed = false;
            }
        },

        /**
         * Mark multiple prospects as not processed
         */
        async bulkNotProcessed() {
            this.bulkingNotProcessed = true;

            // Selected prospects
            const prospectsSelected = this.prospectsSelected;
            store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                // Dispatch bulk not processed
                await store.dispatch(
                    BULK_NOT_PROCESSED_PROSPECT,
                    prospectsSelected
                );

                // Refresh prospects list
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulkingNotProcessed = false;
            }
        },

        /**
         * Call prospects one after the other
         */
        bulkAircallInteraction() {
            store.commit(SET_INTERACTION_PROSPECT, null);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
            store.commit(SET_PROSPECT_INTERACTION_TAB, 1);
            store.commit(SET_PROSPECT_INTERACTION_FRAME_TAB, 0);
        },

        /**
         * Call prospects one after the other
         */
        bulkRingoverInteraction() {
            store.commit(SET_INTERACTION_PROSPECT, null);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
            store.commit(SET_PROSPECT_INTERACTION_TAB, 1);
            store.commit(SET_PROSPECT_INTERACTION_FRAME_TAB, 1);
        },

        /**
         * Show Map
         */
        async showMap() {
            // Get current prospects query
            // and use it as default filter
            // in the MAP
            let query = {};
            let filters = {};

            if (this.prospectsParams) {
                filters = { ...this.prospectsParams };
            }

            // We also add selected prospects
            // as filter in the MAP
            if (this.prospectsSelected.length > 0) {
                filters.ids = this.prospectsSelected;
            }

            if (Object.keys(filters).length > 0) {
                query.filters = JSON.stringify(filters);
            }

            // show the MAP
            this.$router.push({
                name: "map",
                params: {
                    project: this.project.slug,
                },
                query,
            });
        },

        /**
         * Download prospects list
         * in a XLSX file
         */
        async exportProspects() {
            let params = this.prospectsParams;

            const action = () => {
                // Build query URL
                params = JSON.stringify(params);

                // Download prospects list
                location.href =
                    `${API_URL}/project/${this.project.slug}/export/create` +
                    (params ? "?filters=" + params : "");
            };

            // We also add selected prospects
            // as filter for the list of prospects
            // do download
            if (this.prospectsSelected.length > 0) {
                params.ids = this.prospectsSelected;
                action();
            } else {
                /*hcConfirm(
                    "Vous êtes en train d'exporter tous les prospects filtrés, sans avoir sélectionné aucun prospect.<br>Les fichiers d'export vous seront envoyés par email.<br>Etes-vous sûr d'effectuer cette action ?",
                    action
                );*/
                store.commit(OPEN_MODAL, "prospect-export");
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),

        ...mapGetters([
            "project",
            "prospectsSelected",
            "prospects",
            "prospectsParamExists",
            "fields",
            "prospectsPage",
            "prospectsCount",
            "prospectsTotal",
            // "prospectsMaxLinesPerRow",
            "projectUserSettingsProspectsMaxRowLines",
            "prospectsParams",
            "prospectsParamsUrl",
            "prospectsSortOrder",
            "prospectsSortBy",
            "prospectsMenus",
            "prospectsOptions",
            "can",
        ]),

        /**
         * Check if there are
         * selected prospects
         */
        bulkDisabled() {
            return this.prospectsSelected.length == 0;
        },

        /**
         * Check if
         * all selected prospects
         * is processed
         */
        selectedProspectsProcessed() {
            return (
                this.prospects.filter(
                    (p) =>
                        this.prospectsSelected.indexOf(p.id) >= 0 &&
                        p.processed_at
                ).length == this.prospectsSelected.length
            );
        },

        /**
         * Check if
         * all selected prospects
         * is not processed
         */
        selectedProspectsNotProcessed() {
            return (
                this.prospects.filter(
                    (p) =>
                        this.prospectsSelected.indexOf(p.id) >= 0 &&
                        !p.processed_at
                ).length == this.prospectsSelected.length
            );
        },

        /**
         * Check if
         * there are activated filters
         */
        filtersActivated() {
            return (
                this.prospectsParamExists() ||
                this.prospectsSortOrder ||
                this.prospectsSortBy
            );
        },

        count: {
            get: function () {
                return this.prospectsCount;
            },
            set: async function (value) {
                store.commit(SET_PROSPECTS_COUNT, value);
                this.settingProspectsCount = true;

                try {
                    await store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.settingProspectsCount = false;
                }
            },
        },

        page: {
            get: function () {
                return this.prospectsPage;
            },
            set: async function (value) {
                store.commit(SET_PROSPECTS_PAGE, value);
                this.settingProspectsPage = true;

                try {
                    await store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.settingProspectsPage = false;
                }
            },
        },

        maxLinesPerRow: {
            get: function () {
                return this.projectUserSettingsProspectsMaxRowLines;
            },
            set: async function (value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "prospects.max-row-lines",
                    value: value,
                });
                // store.commit(SET_PROSPECTS_MAX_LINES_PER_ROW, value);
            },
        },

        pagesCount() {
            return Math.ceil(this.prospectsTotal / this.prospectsCount);
        },
    },
};
</script>
