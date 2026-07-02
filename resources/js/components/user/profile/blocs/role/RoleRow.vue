<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-user-md" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <icon
            v-if="can('all.project.role.update')"
            tag="a"
            class="fa fa-cog"
            :size="28"
            :icon-size="9"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_ROLE } from "@/actions/project/role";

export default {
    props: {
        role: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_SLIDE, "manage-roles");
            store.commit(SET_ROLE, this.role);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.role.bgcolor,
            };
        },
    },
};
</script>
