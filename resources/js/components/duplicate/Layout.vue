<template>
    <tab-layout :count="2" :tab="duplicateTab" class="hc-flex-1">
        <template #1>
            <div
                class="hc-flex-column hc-flex-1"
                style="overflow: hidden; height: 100%"
            >
                <item tag="a">
                    <icon class="fa fa-copy" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('duplicate.title')"
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
                        v-text="$t('duplicate.fields')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item-list class="hc-flex-1" style="position: relative">
                    <item tag="label">
                        <icon class="fa fa-check-square" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('all')"
                        ></div>
                        <checkbox v-model="all" />
                    </item>
                    <duplicate-row
                        v-for="(duplicate, i) in duplicates"
                        :key="duplicate.id"
                        :index="i - 1"
                        :duplicate="duplicate"
                        :prospect="duplicate.duplicate"
                        v-model="selectedProspects"
                        @duplicate-checked="duplicateChecked"
                    />

                    <buttons>
                        <button
                            @click.prevent="
                                (duplicateTab = 1), (duplicateActionTab = 1)
                            "
                            class="hc-button-danger"
                            v-text="$t('duplicate.choose_action')"
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
                <template #1>
                    <div class="hc-flex-column" style="height: 100%">
                        <item tag="a" @click.prevent="duplicateTab = 0">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('duplicate.fields')"
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
                        <buttons>
                            <button
                                @click.prevent="findDuplicates"
                                v-text="$t('search')"
                            ></button>
                        </buttons>
                    </div>
                </template>
                <template #2>
                    <item
                        tag="a"
                        @click.prevent="
                            (duplicateActionTab = 0), (duplicateTab = 0)
                        "
                    >
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('duplicate.choose_action')"
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
                                    'Supprimer les prospects créés antérieurement'
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
                                    'Supprimer les prospects créés ultérieurement'
                                "
                            ></div>
                        </item>
                        <!--item
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
                                            'duplicate.replace_duplicating'
                                        )
                                    "
                                ></div>
                            </item-->
                    </item-list>
                </template>
            </frame-layout>
        </template>
    </tab-layout>
</template>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

import { FETCH_PROSPECTS } from "@/actions/project/prospect";

// Components
import DuplicateRow from "./DuplicateRow.vue";
import DuplicateFieldRow from "./FieldRow.vue";

export default {
    components: {
        DuplicateRow,
        DuplicateFieldRow,
    },

    data() {
        return {
            duplicateTab: 0,
            duplicateActionTab: 0,
            duplicateFields: [],
            duplicates: [],
            selectedProspects: [],
            selectedIndex: null,
            fieldKeyword: "",
            fetchingDuplicates: false,
            handlingDuplicates: false,
        };
    },

    methods: {
        async findDuplicates() {
            this.duplicateTab = 0;

            this.fetchingDuplicates = true;

            try {
                await ApiService.get(
                    `project/${this.project.slug}/duplicate/find`,
                    {
                        params: {
                            fields: this.duplicateFields,
                        },
                    }
                );

                let { data } = await ApiService.get(
                    `project/${this.project.slug}/duplicate/show`
                );
                this.duplicates = data;
            } finally {
                this.fetchingDuplicates = false;
            }
        },

        duplicateChecked(index, shift, checked) {
            const selectedIndex = this.selectedIndex;
            this.selectedIndex = index;

            if (selectedIndex === null || !shift || index === selectedIndex) {
                return;
            }

            const subset = this.duplicates
                .slice(
                    Math.min(index, selectedIndex + 1),
                    Math.max(index, selectedIndex - 1) + 1
                )
                .map((duplicate) => duplicate.id);

            this.selectedProspects = checked
                ? [
                      ...this.selectedProspects,
                      ...subset.filter(
                          (id) => this.selectedProspects.indexOf(id) == -1
                      ),
                  ]
                : this.selectedProspects.filter(
                      (id) => subset.indexOf(id) == -1
                  );
        },

        handleDuplicates(action) {
            const callback = async () => {
                this.duplicateTab = 0;

                this.handlingDuplicates = true;

                try {
                    await ApiService.delete(
                        `project/${this.project.slug}/duplicate`,
                        {
                            params: {
                                action: action,
                                prospects: this.selectedProspects,
                            },
                        }
                    );
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.duplicates =
                        this.selectedProspects.length == 0
                            ? []
                            : this.duplicates.filter(
                                  (d) =>
                                      !this.selectedProspects.find(
                                          (s) => s == d.id
                                      )
                              );
                    this.handlingDuplicates = false;
                }
            };

            if (this.selectedProspects.length == 0) {
                hcConfirm(
                    "Aucun prospect n'est sélectionné,<br>cette action sera appliquée à tous les prospects de cette liste.<br><br>Voulez-vous vraiment effectuer cette action?",
                    callback
                );
            } else {
                callback();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "fields"]),

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

        /**
         *
         */
        all: {
            get: function () {
                return this.selectedProspects.length == this.duplicates.length;
            },
            set: function (value) {
                this.selectedProspects = value
                    ? this.duplicates.map((d) => d.id)
                    : [];
            },
        },
    },
};
</script>
