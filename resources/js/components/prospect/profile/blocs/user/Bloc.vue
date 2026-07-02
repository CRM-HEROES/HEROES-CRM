<template>
    <bloc
        icon="fa fa-user icon-brown"
        :name="$t('prospect.profile.bloc.affected_users')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="manageUsers"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <user-row
                    v-for="(user, i) in prospectUsers"
                    :key="user.id"
                    :user="user"
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
import { SET_PROSPECT } from "@/actions/project/prospect";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        Bloc,
        UserRow,
    },

    methods: {
        /**
         * Manage prospect users
         * See: @/components/prospect/user/Slide.vue
         */
        manageUsers() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-users");
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectUsers"]),
    },
};
</script>
