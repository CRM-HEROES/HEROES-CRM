<template>
    <slide
        name="user-manage-questionnaires"
        :title="$t('user.questionnaires.title') + ' (' + userFullName + ')'"
        icon="fa fa-clipboard"
        style="width: 260px"
        @open="fetchUserQuestionnaires()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_QUESTIONNAIRES } from "@/actions/project/user/questionnaire";

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
        fetchUserQuestionnaires() {
            if (this.user) {
                store.dispatch(FETCH_USER_QUESTIONNAIRES, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
