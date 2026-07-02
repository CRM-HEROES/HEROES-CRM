<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-users" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="group.name"></div>
        <icon
            tag="a"
            class="fa fa-users hc-show-on-hover"
            :icon-size="11"
            @click.prevent.stop="search"
        />
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_GROUP } from "@/actions/project/group";

export default {
    props: {
        group: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "group-update");
            store.commit(SET_GROUP, this.group);
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
                        withGroups: [this.group.id],
                    }),
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        style() {
            return {
                color: this.group.bgcolor,
            };
        },
    },
};
</script>
