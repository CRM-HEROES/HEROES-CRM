<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-user" :size="30" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon
            v-if="can('all')"
            tag="a"
            class="fa fa-users hc-show-on-hover"
            :size="28"
            :icon-size="11"
            @click.prevent.stop="search"
        />
        <icon tag="a" class="fa fa-cog" :size="28" :icon-size="9" />
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
                name: "user",
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
    },

    computed: {
        ...mapGetters(["project", "can"]),
    },
};
</script>
