<template>
    <div id="hc-dashboard">
        <h1><img src="/images/logo.png" /></h1>

        <div id="hc-dashboard-content">
            <div
                id="hc-dashboard-projects"
                class="hc-dashboard-row"
                v-tuto="{
                    key: 'dashboard.project',
                    name: $t('tutorial.dashboard_project.name'),
                    body: $t('tutorial.dashboard_project.body'),
                }"
            >
                <h2 v-text="$t('dashboard.your_projects')"></h2>
                <draggable
                    class="hc-dashboard-projects"
                    :list="projects"
                    item-key="id"
                    :disabled="!projectsDraggable"
                    @end="projectMoved"
                >
                    <template #header>
                        <a
                            class="hc-dashboard-project"
                            @click.prevent="addProject"
                        >
                            <div
                                class="hc-dashboard-project-card"
                                style="border: 3px solid #0068e555"
                                v-tuto="{
                                    key: 'dashboard.project.new',
                                    name: $t(
                                        'tutorial.dashboard_project_new.name'
                                    ),
                                    body: $t(
                                        'tutorial.dashboard_project_new.body'
                                    ),
                                }"
                            >
                                <div>
                                    <img
                                        src="/images/dashboard/add.jpg"
                                        :alt="$t('dashboard.new_project')"
                                    />
                                </div>
                            </div>
                            <span v-text="$t('dashboard.new_project')"></span>
                        </a>
                    </template>
                    <template #item="{ element }">
                        <project :project="element" />
                    </template>
                </draggable>
            </div>

            <stat />
        </div>
        <div id="hc-dashboard-copyright">
            <div v-text="$t('setting.copyright.title')"></div>
        </div>

        <add-project-modal />
        <add-stat-chart-modal />
    </div>
</template>

<style>
#hc-dashboard {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    background-image: url(/images/bg.jpg);
    background-size: cover;
    background-position: center;
}

#hc-dashboard > h1 {
    width: 100%;
    text-align: left;
    font-weight: bold;
    color: #00a1e1;
    font-size: 34px;
    margin: 0 0 0px 0;
}

#hc-dashboard > h1::first-letter {
    font-size: 40px;
}

#hc-dashboard > h1 > img {
    height: 20px;
}

#hc-dashboard-content {
    display: flex;
    flex-direction: row;
    justify-content: center;
    width: 1600px;
}

.hc-dashboard-row {
    width: 100%;
    max-width: 1200px;
    display: flex;
    flex-direction: column;
}

.hc-dashboard-row > h2 {
    font-size: 20px;
    margin-top: 16px;
    margin-bottom: 8px;
    padding: 0 5px;
    font-weight: 700;
}

#hc-dashboard-projects {
    max-width: 360px;
}

.hc-dashboard-projects {
    display: flex;
    flex-direction: row;
    width: 100%;
    flex-wrap: wrap;
    justify-content: center;
}

.hc-dashboard-project {
    width: 114px;
    padding: 8px 8px;
    display: flex;
    flex-direction: column;
    border-radius: 30px;
    gap: 5px;
    transition: all 200ms ease-out;
    opacity: 0.9;
    text-decoration: none;
}

.hc-dashboard-project:hover {
    background-color: #0001;
    cursor: pointer;
    text-decoration: none;
    transform: scale(1.05);
    opacity: 1;
}

.hc-dashboard-project-card {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.15);
    background-color: white;
    overflow: hidden;
    position: relative;
}

.hc-dashboard-project-card > div {
    width: 100%;
    height: 100%;
    transform: translateX(50%);
    position: relative;
}

.hc-dashboard-project-card > div > img {
    height: 100%;
    max-height: 100%;
    transform: translateX(-50%);
    width: auto;
    user-select: none;
}

.hc-dashboard-project > span {
    display: inline-block;
    font-size: 13px;
    text-align: center;
    color: #555555;
    font-weight: 600;
}

.hc-dashboard-project:hover > span {
    color: #000000;
}

.hc-dashboard-count {
    position: absolute;
    bottom: 10px;
    left: 0;
    width: 100%;
    display: inline-block;
    font-size: 13px;
    text-align: center;
    color: #ffffff;
    font-weight: bold;
}

#hc-dashboard-copyright {
    margin-top: 20px;
    width: 100%;
    padding: 10px 20px;
    color: #888;
    text-align: center;
    font-size: 12px;
}

@media (max-width: 1700px) {
    #hc-dashboard-content {
        width: 100%;
    }
}

@media (max-width: 767px) {
    #hc-dashboard {
        padding: 5px;
    }

    #hc-dashboard > h1 {
        margin: 0;
        font-size: 25px;
        padding: 14px;
        border-radius: 10px;
    }

    #hc-dashboard-content {
        flex-direction: column;
    }

    .hc-dashboard-row > h2 {
        margin-bottom: 20px;
        margin-top: 0;
        font-size: 20px;
    }

    #hc-dashboard-projects {
        max-width: 100%;
    }

    .hc-dashboard-project {
        width: 110px;
        padding: 5px;
        display: flex;
        flex-direction: column;
        border-radius: 5px;
    }

    .hc-dashboard-project-card {
        width: 100px;
        height: 100px;
        border-radius: 20px;
    }

    .stat-chart-bloc-content {
        width: 100% !important;
    }
}
</style>

<script>
import ApiService from "@/apis/api.service";
import ProjectService from "@/apis/project";
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";

import AddProjectModal from "@/components/project/add/Modal.vue";
import AddStatChartModal from "@/components/statistics/modals/add/Modal.vue";
import Stat from "./Stat.vue";
import Project from "./Project.vue";
import { SET_GLOBAL_USERS } from "@/actions/user";

export default {
    components: {
        AddProjectModal,
        AddStatChartModal,
        Project,
        Stat,
    },

    name: "dashboard",
    data() {
        return {
            user: this.$store.state.auth.user,
            projects: [],
        };
    },

    created() {
        this.fetchProjects();
        store.commit(SET_GLOBAL_USERS, []);
    },

    methods: {
        /**
         * Fetch all projects
         */
        async fetchProjects() {
            try {
                const { data } = await ApiService.get(`dashboard/projects`);
                this.projects = data;
            } finally {
            }
        },

        /**
         * Add new project
         */
        addProject() {
            store.commit(OPEN_MODAL, "project-add");
        },

        /**
         * Add new user
         */
        addUser() {
            store.commit(OPEN_MODAL, "global-user-add");
        },

        projectMoved() {
            const orders = this.projects.map((project, i) => ({
                project: project.id,
                order: i,
            }));

            ProjectService.orders({
                orders,
            });
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters("route", ["changing"]),

        projectsDraggable() {
            return deviceType() == "desktop";
        },
    },
};
</script>
