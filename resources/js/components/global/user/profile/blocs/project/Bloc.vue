<template>
    <bloc icon="fa fa-file" :name="$t('user.profile.affected_projects')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click="addProject" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="globalUser.projects"
            >
                <project-row
                    v-for="(project, i) in globalUser.projects"
                    :key="project.id"
                    :project="project"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/global/user/profile/blocs/Bloc.vue";
import ProjectRow from "./ProjectRow.vue";

export default {
    components: {
        Bloc,
        ProjectRow,
    },

    methods: {
        /**
         * Add project
         * See: @/components/project/add/Modal.vue
         */
        addProject() {
            store.commit(OPEN_SLIDE, "user-manage-projects");
        },
    },

    computed: {
        ...mapGetters(["project", "globalUser"]),
    },
};
</script>
