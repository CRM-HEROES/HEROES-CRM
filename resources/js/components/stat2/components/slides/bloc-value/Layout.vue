<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <!-- Categories -->
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="blocValueKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <bloc-value-row
                        v-for="blocValue in filteredBlocValues"
                        :key="blocValue.key"
                        :bloc-value="blocValue"
                        @click="addBlocValue(blocValue)"
                    />
                    <category-row
                        v-for="category in filteredCategories"
                        :key="category.id"
                        :category="category"
                        @click="blocValueCategory = category"
                    />
                </item-list>
                <buttons>
                    <button @click.prevent="add" v-text="$t('add')"></button>
                </buttons>
                <loading :loading="false" />
            </div>
        </template>

        <!-- Labels -->
        <template #2>
            <div
                class="hc-flex-column"
                style="height: 100%"
                v-if="blocValueCategory"
            >
                <!-- Back -->
                <item @click="blocValueCategory = null" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <!-- Current category name -->
                    <div
                        class="hc-item-main-content"
                        v-text="blocValueCategory.name"
                    ></div>
                </item>

                <div
                    class="hc-flex-column"
                    style="height: auto; overflow: hidden; position: relative"
                >
                    <!-- Search -->
                    <search v-model="labelKeyword" />

                    <!-- Labels list -->
                    <item-list class="hc-flex-1" padding="5px">
                        <label-row
                            v-for="label in filteredLabels"
                            :key="label.id"
                            :label="label"
                            @click="
                                (blocValueCategory = null),
                                    addBlocValue({
                                        key: 'label.prospects.' + label.id,
                                        name: label.name,
                                        color: label.color,
                                        bgcolor: label.bgcolor,
                                    })
                            "
                        />
                    </item-list>

                    <loading :loading="bulkRemoving" />
                </div>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import BlocValueRow from "./BlocValueRow.vue";
import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";

import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

export default {
    components: {
        BlocValueRow,
        CategoryRow,
        LabelRow,
    },

    created() {
        this.blocValues = [
            {
                key: "prospects",
                name: "Prospects",
                color: "#FFFFFF",
                bgcolor: "#83c7d4",
            },
            {
                key: "orders-total",
                name: "Total des commandes",
                color: "#FFFFFF",
                bgcolor: "#ff8979",
            },
            {
                key: "orders",
                name: "Commandes",
                color: "#FFFFFF",
                bgcolor: "#ff8979",
            },
            {
                key: "interactions-duration",
                name: "Durées des appels",
                color: "#FFFFFF",
                bgcolor: "#8979ff",
            },
            {
                key: "interactions",
                name: "Appels",
                color: "#FFFFFF",
                bgcolor: "#8979ff",
            },
            {
                key: "sms",
                name: "SMS",
                color: "#FFFFFF",
                bgcolor: "#83d496",
            },
            {
                key: "files",
                name: "Fichiers",
                color: "#FFFFFF",
                bgcolor: "#ffae4c",
            },
            {
                key: "messages",
                name: "Messages",
                color: "#FFFFFF",
                bgcolor: "#ffae4c",
            },
            {
                key: "events",
                name: "RDV",
                color: "#FFFFFF",
                bgcolor: "#ffae4c",
            },
        ];
    },

    data() {
        return {
            tab: 0,
            blocValueKeyword: "",
            labelKeyword: "",
            blocValueCategory: null,
        };
    },

    methods: {
        addBlocValue(blocValue) {
            const newSettings = [
                ...this.projectUserSettingsStatBlocValue,
                blocValue,
            ];

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "stat-bloc-value",
                value: newSettings,
            });
            store.commit(CLOSE_SLIDE);
        },
    },

    watch: {
        blocValueCategory() {
            if (this.blocValueCategory) {
                this.tab = 1;
            } else {
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters(["categories", "projectUserSettingsStatBlocValue"]),

        /**
         *
         */
        filteredBlocValues() {
            const keyword = removeStringAccent(this.blocValueKeyword);

            return this.blocValues.filter(
                (blocValue) =>
                    removeStringAccent(blocValue.name).indexOf(keyword) >= 0 &&
                    !this.projectUserSettingsStatBlocValue.find(
                        (bloc) => bloc.key === blocValue.key
                    )
            );
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.blocValueKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    (removeStringAccent(category.name).indexOf(keyword) >= 0 ||
                        category.labels.find(
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
        filteredLabels() {
            const keyword = removeStringAccent(this.labelKeyword);

            const category = this.categories.find(
                (c) => c.id == this.blocValueCategory.id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
