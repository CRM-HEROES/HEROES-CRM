<template>
    <div class="hc-flex-column" style="height: 100%">
        <tab-layout :count="3" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="projectKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <project-row
                            v-for="p in projects"
                            :key="p.id"
                            :project="p"
                            @click="(tab = 1), setProject(p)"
                        />
                    </item-list>
                    <loading :loading="bulking" />
                </div>
            </template>

            <template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 0" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="'Mapper des filtres entre les 2 projets'"
                        ></div>
                    </item>
                    <search v-model="categoryKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <category-row
                            v-for="c in filteredCategories"
                            :key="c.id"
                            :category="c"
                            :mapped-category="mappedCategory(c)"
                            @click="(category = c), (tab = 2)"
                        />
                    </item-list>
                    <buttons>
                        <a
                            @click.prevent="attach"
                            v-text="'Copier vers ' + project.name"
                        ></a>
                    </buttons>
                    <loading :loading="bulking" />
                </div>
            </template>

            <template #3>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 1" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('back')"
                        ></div>
                    </item>
                    <search v-model="categoryKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <mapped-category-row
                            v-for="c in filteredMappedCategories"
                            :key="c.id"
                            :category="c"
                            @click="(mapping[category.id] = c.id), (tab = 1)"
                        />
                    </item-list>
                    <loading :loading="bulking" />
                </div>
            </template>
        </tab-layout>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProjectService from "@/apis/project";
import CategoryService from "@/apis/project/category";

// Actions
import { BULK_PROJECT_PROSPECT } from "@/actions/project/prospect";
import { UPDATE_SELECTED_PROSPECTS } from "@/actions/project/prospect";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import ProjectRow from "./ProjectRow.vue";
import CategoryRow from "./CategoryRow.vue";
import MappedCategoryRow from "./MappedCategoryRow.vue";
import category from "../../../../apis/project/category";

export default {
    components: {
        ProjectRow,
        CategoryRow,
        MappedCategoryRow,
    },

    data() {
        return {
            tab: 0,
            bulking: false,
            projects: [],
            projectKeyword: "",
            projectCancelTokenSource: null,
            categoryKeyword: "",
            mappedCategoryKeyword: "",
            mapping: {},
            project: null,
            category: null,
            mappedCategories: [],
            name: "prospect-bulk-manage-project",
        };
    },

    created() {
        this.fetchProjects();
    },

    methods: {
        /**
         *
         * @param {*} project
         */
        async fetchProjects() {
            if (this.projectCancelTokenSource) {
                this.projectCancelTokenSource.cancel();
            }

            this.projectCancelTokenSource = axios.CancelToken.source();

            const { data } = await ProjectService.get({
                params: {
                    filters: JSON.stringify({
                        query: this.projectKeyword,
                        // type: ["professional"],
                    }),
                },
                cancelToken: this.projectCancelTokenSource.token,
            });

            this.projects = data.data;
        },

        /**
         *
         */
        setProject(project) {
            this.project = project;
            this.fetchMappedCategories();
        },

        /**
         *
         */
        mappedCategory(category) {
            if (!this.mapping[category.id]) {
                return null;
            }

            return this.mappedCategories.find(
                (c) => c.id == this.mapping[category.id]
            );
        },

        /**
         *
         */
        async fetchMappedCategories() {
            this.fetchingMappedCategories = true;

            try {
                const { data } = await CategoryService.get(this.project.slug);
                this.mappedCategories = data;
            } finally {
                this.fetchingMappedCategories = false;
            }
        },

        /**
         *
         */
        async attach() {
            if (!this.project) {
                return;
            }

            this.bulking = true;
            const prospectsSelected = this.prospectsSelected;
            store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                await store.dispatch(BULK_PROJECT_PROSPECT, {
                    prospects: prospectsSelected,
                    project: this.project.id,
                    mapping: this.mapping,
                });
                // store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulking = false;
                store.commit(CLOSE_SLIDE, this.name);
            }
        },
    },

    watch: {
        projectKeyword() {
            this.fetchProjects();
        },
    },

    computed: {
        ...mapGetters(["prospectsSelected", "categories", "can"]),

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    removeStringAccent(category.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredMappedCategories() {
            const keyword = removeStringAccent(this.mappedCategoryKeyword);
            const mappedCategories = Object.values(this.mapping);

            return this.mappedCategories.filter(
                (category) =>
                    removeStringAccent(category.name).indexOf(keyword) >= 0 &&
                    mappedCategories.findIndex((c) => c == category.id) < 0
            );
        },
    },
};
</script>
