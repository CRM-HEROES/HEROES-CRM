<template>
    <!-- body -->
    <tab-layout :tab="tab" :count="2">
        <template #1>
            <form style="width: 100%; height: auto; position: relative">
                <div
                    style="
                        width: 100%;
                        height: auto;
                        text-align: center;
                        padding-top: 16px;
                    "
                >
                    <img style="width: 25px" src="/images/email/rocket.png" />
                    <br />
                    <span
                        style="
                            font-size: 18px;
                            font-weight: bold;
                            display: inline-block;
                            margin-top: 16px;
                        "
                        >Projets</span
                    >
                    <br />
                    <span
                        style="
                            font-size: 14px;
                            text-align: center;
                            display: inline-block;
                            margin-top: 8px;
                            margin-bottom: 16px;
                            line-height: 24px;
                            width: 100%;
                            padding: 0 30px;
                            box-sizing: border-box;
                        "
                        >Les projets suivants vous a été attribués, avec ces
                        rôles respectifs:</span
                    >
                    <br />
                    <div
                        style="
                            display: flex;
                            flex-direction: column;
                            gap: -1px;
                            text-align: left;
                        "
                    >
                        <project-row
                            v-for="project in projects"
                            :project="project"
                            @click="viewProjectSetting(project)"
                        />
                    </div>
                </div>

                <!-- Footer -->
                <!--div style="width: 100%; height: auto">
                    <div
                        style="
                            width: 100%;
                            height: auto;
                            box-sizing: border-box;
                            padding: 30px 10px;
                            text-align: center;
                        "
                    >
                        <a
                            style="
                                padding: 8px 16px;
                                background-color: rgb(73, 189, 225);
                                color: white;
                                font-size: 15px;
                                border-radius: 4px;
                                border: none;
                            "
                            >Suivant</a
                        >
                    </div>
                </div-->

                <loading :loading="settingProject" />
            </form>
        </template>

        <template #2>
            <setting-prospects-table-column v-if="project" />
        </template>
    </tab-layout>
</template>

<style></style>

<script>
import ApiService from "@/apis/api.service";
import store from "@/store";
import { mapGetters } from "vuex";
import ProjectRow from "./ProjectRow";
import SettingProspectsTableColumn from "../setting/prospects-table-column/Setting.vue";

import { SET_PROJECT, SHOW_PROJECT } from "@/actions/project";

export default {
    components: {
        ProjectRow,
        SettingProspectsTableColumn,
    },

    data() {
        return {
            tab: 0,
            projects: [],
            fetchingProjects: false,
            settingProject: false,
        };
    },

    created() {
        this.fetchProjects();
    },

    methods: {
        async fetchProjects() {
            this.fetchingProjects = true;

            try {
                const { data } = await ApiService.get(
                    `user/${this.user.id}/project`
                );
                this.projects = data;
            } finally {
                this.fetchingProjects = false;
            }
        },

        async setProject(slug) {
            this.settingProject = true;

            try {
                await store.dispatch(SHOW_PROJECT, slug);
                this.tab = 1;
            } finally {
                this.settingProject = false;
            }
        },

        async viewProjectSetting(project) {
            this.$router.push({
                name: "registration.step.project",
                params: {
                    project: project.slug,
                },
            });
        },
    },

    watch: {
        projectParam: {
            handler(newValue) {
                if (newValue) {
                    this.setProject(newValue);
                } else {
                    this.tab = 0;
                }
            },
            immediate: true,
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters(["project", "projectUserSettingsProspectsTable"]),

        projectParam() {
            return this.$route.params.project;
        },
    },
};
</script>
