<template>
    <item tag="a">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon
            v-if="can('all')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SET_USER } from "@/actions/project/user";

export default {
    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        edit() {
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
        ...mapGetters(["project", "can"]),
    },
};
</script>
