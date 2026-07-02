<template>
    <modal
        :title="$t('user.table.column.manage', { project: project.slug })"
        name="users-table-add-column"
        :width="380"
    >
        <layout />
    </modal>
</template>

<script>
import { mapGetters } from "vuex";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
