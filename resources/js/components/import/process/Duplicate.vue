<template>
    <!--tab-layout :count="2" :tab="duplicateTab" class="hc-flex-1">
        <template #1>
            <div
                class="hc-flex-column hc-flex-1"
                style="overflow: hidden; height: 100%"
            >
                <item tag="a">
                    <icon class="fa fa-copy" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('import.process.tab.import.duplicate.title')"
                    ></div>
                </item>
                <item
                    tag="a"
                    @click.prevent="
                        (duplicateTab = 1), (duplicateActionTab = 0)
                    "
                >
                    <icon class="fa fa-search" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t('import.process.tab.import.duplicate.fields')
                        "
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item-list class="hc-flex-1" style="position: relative">
                    <duplicate-row
                        v-for="duplicate in duplicates"
                        :duplicate="duplicate"
                        :prospect="duplicate.duplicate"
                    />

                    <buttons>
                        <button
                            @click.prevent="
                                (duplicateTab = 1), (duplicateActionTab = 1)
                            "
                            class="hc-button-danger"
                            v-text="
                                $t(
                                    'import.process.tab.import.duplicate.choose_action'
                                )
                            "
                        ></button>
                    </buttons>
                    <loading
                        :loading="fetchingDuplicates || handlingDuplicates"
                    />
                </item-list>
            </div>
        </template>

        <template #2>
            <frame-layout
                :count="2"
                :tab="duplicateActionTab"
                class="hc-flex-1"
            >
                <template #1-->
    <div class="hc-flex-column" style="height: 100%">
        <item tag="a" @click.prevent="duplicateTab = 0">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content"
                v-text="$t('import.process.tab.import.duplicate.fields')"
            ></div>
        </item>
        <search v-model="fieldKeyword" />
        <item-list class="hc-flex-1">
            <duplicate-field-row
                v-for="field in filteredFields"
                :key="field.id"
                :field="field"
                v-model="duplicateFields"
            />
        </item-list>
        <!--buttons>
            <button
                @click.prevent="findDuplicates"
                v-text="$t('search')"
            ></button>
        </buttons-->
    </div>
    <!--/template>
                <template #2>
                    <item tag="a" @click.prevent="duplicateActionTab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t(
                                    'import.process.tab.import.duplicate.choose_action'
                                )
                            "
                        ></div>
                    </item>
                    <item-list class="hc-flex-1">
                        <item
                            tag="a"
                            @click.prevent="
                                handleDuplicates('remove_duplicating')
                            "
                        >
                            <icon class="fa fa-trash" color="#c33434" />
                            <div
                                class="hc-item-main-content"
                                style="white-space: normal"
                                v-text="
                                    $t(
                                        'import.process.tab.import.duplicate.remove_duplicating'
                                    )
                                "
                            ></div>
                        </item>
                        <item
                            tag="a"
                            @click.prevent="
                                handleDuplicates('remove_duplicates')
                            "
                        >
                            <icon class="fa fa-trash" color="#c33434" />
                            <div
                                class="hc-item-main-content"
                                style="white-space: normal"
                                v-text="
                                    $t(
                                        'import.process.tab.import.duplicate.remove_duplicates'
                                    )
                                "
                            ></div>
                        </item>
                        <item
                            tag="a"
                            @click.prevent="
                                handleDuplicates('replace_duplicating')
                            "
                        >
                            <icon class="fa fa-trash" color="#c33434" />
                            <div
                                class="hc-item-main-content"
                                style="white-space: normal"
                                v-text="
                                    $t(
                                        'import.process.tab.import.duplicate.replace_duplicating'
                                    )
                                "
                            ></div>
                        </item>
                    </item-list>
                </template>
            </frame-layout>
        </template>
    </tab-layout-->
</template>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

import { UPDATE_IMPORT } from "@/actions/project/import";

// Components
import DuplicateRow from "./duplicate/DuplicateRow.vue";
import DuplicateFieldRow from "./duplicate/FieldRow.vue";
import MappingColumnFieldCopyRow from "./process/MappingColumnFieldCopyRow.vue";

export default {
    components: {
        DuplicateRow,
        DuplicateFieldRow,
        MappingColumnFieldCopyRow,
    },

    data() {
        return {
            duplicateTab: 0,
            duplicateActionTab: 0,
            duplicates: [],
            fieldKeyword: "",
            fetchingDuplicates: false,
            handlingDuplicates: false,
        };
    },

    methods: {
        /*async findDuplicates() {
            this.duplicateTab = 0;

            this.fetchingDuplicates = true;

            try {
                await ApiService.get(
                    `project/${this.project.slug}/import/${this.prospectImport.id}/duplicate/find`,
                    {
                        params: {
                            fields: this.duplicateFields,
                        },
                    }
                );

                let { data } = await ApiService.get(
                    `project/${this.project.slug}/import/${this.prospectImport.id}/duplicate/show`
                );
                this.duplicates = data;
            } finally {
                this.fetchingDuplicates = false;
            }
        },

        async handleDuplicates(action) {
            this.duplicateTab = 0;

            this.handlingDuplicates = true;

            try {
                await ApiService.delete(
                    `project/${this.project.slug}/import/${this.prospectImport.id}/duplicate`,
                    {
                        params: {
                            action: action,
                        },
                    }
                );

                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.duplicates = [];
                this.handlingDuplicates = false;
            }
        },*/
    },

    watch: {
        async duplicateFields(newValue) {
            try {
                await store.dispatch(UPDATE_IMPORT, {
                    id: this.prospectImport.id,
                    duplicates_fields: newValue,
                });
            } finally {
            }
        },
    },

    computed: {
        ...mapGetters(["project", "prospectImport", "fields"]),

        duplicateFields: {
            get() {
                return this.prospectImport &&
                    this.prospectImport.duplicates_fields
                    ? this.prospectImport.duplicates_fields
                    : [];
            },
            set(value) {
                this.prospectImport.duplicates_fields = value;
            },
        },

        /**
         */
        filteredFields() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.fields.filter(
                (field) =>
                    field.for == "prospect" &&
                    removeStringAccent(field.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
