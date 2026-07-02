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
            />
        </item-list>
        <buttons>
            <button @click.prevent="attach" :disabled="disabled">
                Assigner
            </button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
            >
                Retirer
            </button>
            <a @click.prevent="addMenu" v-text="$t('add')"></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_MENU,
    BULK_REMOVE_USER_MENU,
} from "@/actions/project/user/menu";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_MENUS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import MenuRow from "./MenuRow.vue";

export default {
    components: {
        MenuRow,
    },

    data() {
        return {
            bulking: false,
            menuKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addMenu() {
            store.commit(OPEN_MODAL, "menu-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_MENU, {
                    users: usersSelected,
                    menus: this.userBulkMenus,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_MENUS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-menus");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_MENU, {
                    users: usersSelected,
                    menus: this.userBulkMenus,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_MENUS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-menus");
            }
        },
    },

    computed: {
        ...mapGetters(["menus", "usersSelected", "userBulkMenus"]),

        /**
         *
         */
        disabled() {
            return this.userBulkMenus.length == 0;
        },

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
                for (let i in this.filteredMenus) {
                    if (
                        !this.userBulkMenus.find(
                            (menu) => menu == this.filteredMenus[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_MENUS,
                        this.filteredMenus.map((menu) => menu.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_MENUS, []);
                }
            },
        },
    },
};
</script>
