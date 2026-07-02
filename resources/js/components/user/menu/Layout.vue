<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="menuKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <menu-row
                v-for="menu in filteredMenus"
                :key="menu.id"
                :menu="menu"
                :value="isMenuChecked(menu)"
            />
        </item-list>
        <buttons>
            <a @click.prevent="addMenu" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_MENU,
    BULK_REMOVE_USER_MENU,
    ADD_USER_MENU,
    REMOVE_USER_MENU,
} from "@/actions/project/user/menu";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import MenuRow from "./MenuRow.vue";

export default {
    components: {
        MenuRow,
    },

    data() {
        return {
            menuKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} menu
         */
        isMenuChecked(menu) {
            return (
                // this.can("all") ||
                this.userMenus.findIndex((l) => l.id == menu.id) >= 0
            );
        },

        /**
         *
         */
        addMenu() {
            store.commit(OPEN_MODAL, "menu-add");
        },
    },

    computed: {
        ...mapGetters([
            "menus",
            "user",
            "user.name",
            "userMenus",
            "slideOpen",
            "userFullName",
        ]),

        /**
         *
         */
        filteredMenus() {
            const keyword = removeStringAccent(this.menuKeyword);

            return this.menus.filter(
                (menu) => removeStringAccent(menu.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.userMenus.length == this.menus.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.menus.forEach((f) => {
                        store.commit(ADD_USER_MENU, f);
                    });
                    store.dispatch(BULK_ADD_USER_MENU, {
                        users: [this.user.id],
                        menus: this.menus.map((f) => f.id),
                    });
                } else {
                    this.menus.forEach((f) => {
                        store.commit(REMOVE_USER_MENU, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_MENU, {
                        users: [this.user.id],
                        menus: this.menus.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
