<template>
    <slide
        name="prospect-bulk-manage-project"
        :title="$t('prospect.table.bulk.project.title')"
        icon="fa fa-copy"
        style="width: 260px"
        @open="fetchProjects"
    >
        <layout />
    </slide>
</template>

<script>
import store from "@/store";

// Actions
import { FETCH_PROJECTS } from "@/actions/project";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    methods: {
        /**
         *
         * @param {*} project
         */
        fetchProjects() {
            store.dispatch(FETCH_PROJECTS);
        },
    },
};
</script>
