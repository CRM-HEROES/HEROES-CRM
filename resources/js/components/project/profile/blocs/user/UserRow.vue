<template>
    <item @click.prevent.stop="showUser">
        <icon class="fa fa-user" :size="30" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon
            tag="a"
            class="fa fa-users hc-show-on-hover"
            :icon-size="11"
            @click.prevent.stop="search"
        />
        <icon tag="a" @click.prevent.stop="edit" class="fa fa-cog" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_USER } from "@/actions/project/user";

export default {
    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_SLIDE, "manage-users");
            store.commit(SET_USER, this.user);
        },

        /**
         *
         */
        search() {
            this.$router.push({
                name: "prospect",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        withUsers: [this.user.id],
                    }),
                },
            });
        },

        /**
         * Go to the user profile
         */
        showUser() {
            store.commit(SET_USER, this.user);
            this.$router.push({
                name: "user.show",
                params: {
                    project: this.project.slug,
                    user: this.user.id,
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
