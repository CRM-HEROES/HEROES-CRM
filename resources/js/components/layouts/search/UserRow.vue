<template>
    <item @click="search">
        <icon class="fa fa-user-tie" :size="28" />
        <div class="hc-item-main-content" v-text="fullName"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="28"
            @click.prevent.stop="edit"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_USER, SHOW_USER } from "@/actions/project/user";

export default {
    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        /**
         *
         */
        search() {
            if (this.project) {
                this.$router.push({
                    name: "user.show",
                    params: {
                        project: this.project.slug,
                        user: this.user.id,
                    },
                });
            } else {
                this.$router.push({
                    name: "global.user.show",
                    params: {
                        user: this.user.id,
                    },
                });
            }
        },

        async edit(e) {
            store.commit(OPEN_SLIDE, "manage-users");
            store.commit(SET_USER, this.user);

            try {
                const u = await store.dispatch(SHOW_USER, this.user.id);
                store.commit(SET_USER, u);
            } finally {
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        filter() {
            return {
                withUsers: {
                    ids: [this.user.id],
                },
            };
        },

        /**
         *
         */
        query() {
            return {
                filters: JSON.stringify(this.filter),
            };
        },

        fullName() {
            return (
                [this.user.name, this.user.last_name]
                    .filter((name) => name)
                    .join(" ") +
                " (" +
                this.user.email +
                ")"
            );
        },
    },
};
</script>
