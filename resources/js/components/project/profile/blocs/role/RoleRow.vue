<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-user-md" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<script>
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
