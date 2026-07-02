<template>
    <item @click="edit">
        <icon class="fa fa-user-md" :size="28" :style="style" />
        <div class="hc-item-main-content" v-text="role.name"></div>
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_ROLE, SHOW_ROLE } from "@/actions/project/role";

export default {
    props: {
        role: {
            type: Object,
        },
    },

    methods: {
        async edit(e) {
            store.commit(OPEN_SLIDE, "manage-roles");
            store.commit(SET_ROLE, this.role);

            try {
                const u = await store.dispatch(SHOW_ROLE, this.role.id);
                store.commit(SET_ROLE, u);
            } finally {
            }
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
