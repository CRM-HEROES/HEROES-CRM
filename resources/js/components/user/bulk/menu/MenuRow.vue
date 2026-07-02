<template>
    <item tag="label">
        <icon class="fa fa-list" :style="style" />
        <div class="hc-item-main-content" v-text="menu.name"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="menu.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_MENU } from "@/actions/project/menu";
import { UPDATE_USER_BULK_MENUS } from "@/actions/project/user";

export default {
    props: {
        menu: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "menu-update");
            store.commit(SET_MENU, this.menu);
        },
    },

    computed: {
        ...mapGetters(["userBulkMenus"]),

        /**
         *
         */
        style() {
            return {
                color: this.menu.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkMenus;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_MENUS, value);
            },
        },
    },
};
</script>
