<template>
    <slide
        name="role-manage-categories"
        :title="$t('user.categories.title') + ' (' + roleFullName + ')'"
        icon="fa fa-tags"
        style="width: 260px"
        @open="fetchRoleCategories()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_CATEGORIES } from "@/actions/project/role/category";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    methods: {
        /**
         *
         */
        fetchRoleCategories() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_CATEGORIES, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["roleFullName", "role"]),
    },
};
</script>
