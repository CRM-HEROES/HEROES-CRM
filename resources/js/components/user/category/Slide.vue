<template>
    <slide
        name="user-manage-categories"
        :title="$t('user.categories.title') + ' (' + userFullName + ')'"
        icon="fa fa-tags"
        style="width: 260px"
        @open="fetchUserCategories()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_CATEGORIES } from "@/actions/project/user/category";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    data() {
        return {
            name: "user-manage-categories",
            categoryKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        fetchUserCategories() {
            if (this.user) {
                store.dispatch(FETCH_USER_CATEGORIES, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
