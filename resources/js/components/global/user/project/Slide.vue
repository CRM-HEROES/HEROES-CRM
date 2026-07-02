<template>
    <slide
        :name="name"
        :title="$t('user.project.title')"
        icon="fa fa-file"
        style="width: 260px"
        @open="fetchProjects()"
        @close="projectCancelTokenSource && projectCancelTokenSource.cancel()"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="projectKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <project-row
                    v-for="project in projects"
                    :key="project.id"
                    :project="project"
                    :value="isProjectChecked(project)"
                />
            </item-list>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProjectService from "@/apis/project";

// Actions
import {
    FETCH_USER_PROJECTS,
    ADD_USER_PROJECT,
    REMOVE_USER_PROJECT,
} from "@/actions/user/project";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ProjectRow from "./ProjectRow.vue";

export default {
    components: {
        ProjectRow,
    },

    data() {
        return {
            name: "user-manage-projects",
            tab: 0,
            projects: [],
            projectKeyword: "",
            projectCancelTokenSource: null,
        };
    },

    methods: {
        /**
         *
         * @param {*} project
         */
        isProjectChecked(project) {
            return this.userProjects.findIndex((l) => l.id == project.id) >= 0;
        },

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
        addProject() {
            store.commit(OPEN_MODAL, "project-add");
        },
    },

    watch: {
        globalUser(newValue) {
            if (this.slideOpen(this.name)) {
                store.dispatch(FETCH_USER_PROJECTS, newValue);
            }
        },

        projectKeyword() {
            this.fetchProjects();
        },
    },

    computed: {
        ...mapGetters(["globalUser", "userProjects", "slideOpen", "can"]),

        /**
         *
         */
        filteredProjects() {
            const keyword = removeStringAccent(this.projectKeyword);

            return this.projects.filter(
                (project) =>
                    removeStringAccent(project.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredProjects) {
                    if (
                        !this.userProjects.find(
                            (project) =>
                                project.id == this.filteredProjects[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    this.filteredProjects.forEach((f) => {
                        store.commit(ADD_USER_PROJECT, f);
                    });
                    store.dispatch(BULK_ADD_USER_PROJECT, {
                        users: [this.user.id],
                        projects: this.filteredProjects.map((f) => f.id),
                    });
                } else {
                    this.filteredProjects.forEach((f) => {
                        store.commit(REMOVE_USER_PROJECT, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_PROJECT, {
                        users: [this.user.id],
                        projects: this.filteredProjects.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
