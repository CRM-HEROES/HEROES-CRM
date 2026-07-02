<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="menuKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-menu">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.menus.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeMenuStyle"
                    :title="$t('user.table.filters.menus.without')"
                    @click.prevent="toggleExcludeMenu"
                />
                <checkbox :model-value="isCheckedMenu" @change="changeMenu" />
            </item>
            <menu-row
                v-for="menu in filteredMenus"
                :key="menu.id"
                :menu="menu"
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
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import MenuRow from "./MenuRow.vue";

export default {
    components: {
        MenuRow,
    },

    data() {
        return {
            tab: 0,
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

        changeMenu(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyMenu,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyMenu,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeMenu() {
            // Add or remove with param
            store.commit(
                this.isExcludedMenu ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyMenu,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedMenu ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyMenu,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["menus", "user", "usersParamValue"]),

        /**
         *
         */
        withKeyMenu() {
            return "withMenus";
        },

        /**
         *
         */
        withoutKeyMenu() {
            return "withoutMenus";
        },

        /**
         *
         */
        isCheckedMenu() {
            return (
                this.usersParamValue(this.withKeyMenu) === "" ||
                this.usersParamValue(this.withoutKeyMenu) === ""
            );
        },

        /**
         *
         */
        isExcludedMenu() {
            return this.usersParamValue(this.withoutKeyMenu) === "";
        },

        /**
         *
         */
        excludeMenuStyle() {
            return {
                color: this.isExcludedMenu ? "#CC0000" : "#CCCCCC",
            };
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
    },
};
</script>
