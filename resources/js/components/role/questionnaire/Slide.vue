<template>
    <slide
        name="role-manage-questionnaires"
        :title="$t('user.questionnaires.title') + ' (' + roleFullName + ')'"
        icon="fa fa-clipboard"
        style="width: 260px"
        @open="fetchRoleQuestionnaires()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_QUESTIONNAIRES } from "@/actions/project/role/questionnaire";

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
        fetchRoleQuestionnaires() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_QUESTIONNAIRES, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["role", "roleFullName"]),
    },
};
</script>
