<template>
    <item tag="a">
        <icon class="fa fa-user-md" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <icon
            v-if="can('all.project.role.update')"
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
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ROLE } from "@/actions/project/role";

export default {
    props: {
        role: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "role-update");
            store.commit(SET_ROLE, this.role);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
