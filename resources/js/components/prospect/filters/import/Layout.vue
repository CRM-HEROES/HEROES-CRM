<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="importKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-prospects-table-filter-import">
                <icon class="fa fa-filter" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.filters.imports.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeImportStyle"
                    :title="$t('prospect.table.filters.imports.without')"
                    @click.prevent="toggleExcludeImport"
                />
                <checkbox
                    :model-value="isCheckedImport"
                    @change="changeImport"
                />
            </item>
            <import-row
                v-for="prospectImport in filteredImports"
                :key="prospectImport.id"
                :prospect-import="prospectImport"
            />
        </item-list>
        <buttons v-if="can('all.prospect.import')">
            <a @click.prevent="addImport" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ImportRow from "./ImportRow.vue";

export default {
    components: {
        ImportRow,
    },

    data() {
        return {
            tab: 0,
            importKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addImport() {
            store.commit(OPEN_MODAL, "import-add");
        },

        changeImport(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyImport,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyImport,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        toggleExcludeImport() {
            // Add or remove with param
            store.commit(
                this.isExcludedImport
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyImport,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedImport
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyImport,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters([
            "imports",
            "prospect",
            "prospectFullName",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyImport() {
            return "withImports";
        },

        /**
         *
         */
        withoutKeyImport() {
            return "withoutImports";
        },

        /**
         *
         */
        isCheckedImport() {
            return (
                this.prospectsParamValue(this.withKeyImport) === "" ||
                this.prospectsParamValue(this.withoutKeyImport) === ""
            );
        },

        /**
         *
         */
        isExcludedImport() {
            return this.prospectsParamValue(this.withoutKeyImport) === "";
        },

        /**
         *
         */
        excludeImportStyle() {
            return {
                color: this.isExcludedImport ? "#CC0000" : "#CCCCCC",
            };
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
    },
};
</script>
