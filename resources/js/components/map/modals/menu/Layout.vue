<template>
    <form
        class="hc-flex-column"
        style="overflow: hidden"
        @submit.prevent="storeMenu"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <!-- Form -->

            <template #1>
                <item-list gap="5px">
                    <!-- Name -->

                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="menu.name"
                            :style="{
                                color: menu.color,
                                backgroundColor: menu.bgcolor,
                            }"
                            required
                    /></v-field>

                    <!-- Color -->

                    <v-field :label="$t('color')"
                        ><input type="color" v-model="menu.color"
                    /></v-field>

                    <!-- Background color -->

                    <color-palette v-model="menu.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="menu.bgcolor"
                    /></v-field>
                    <color-palette v-model="menu.bgcolor" />

                    <!-- Affected users -->

                    <!--item @click="tab = 3">
                            <icon class="fa fa-users" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('label.affected_users')"
                            ></div>
                            <div
                                v-if="menuUsers.length"
                                class="hc-item-count"
                                v-text="menuUsers.length"
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item-->
                </item-list>
            </template>

            <!--template #2>
                    <div class="hc-flex-column" style="height: 100%">
                        <item @click="tab = 0">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('label.affected_users')"
                            ></div>
                        </item>
                        <search v-model="userKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="5px"
                            style="max-height: 400px"
                        >
                            <user-row
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :user="user"
                                v-model="menuUsers"
                            />
                        </item-list>
                    </div>
                </template-->
        </tab-layout>
        <buttons>
            <button v-text="$t('add')"></button>
            <a
                v-if="tab == 1"
                @click.prevent="addCategory"
                v-text="$t('new')"
            ></a>
        </buttons>
        <loading :loading="addingMenu" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_MENU } from "@/actions/project/menu";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_MENU } from "@/actions/project/user/menu";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            tab: 0,
            menu: this.newMenu(),
            addingMenu: false,
            useCurrentFilters: true,
            userKeyword: "",
            menuUsers: [],
        };
    },

    methods: {
        /**
         *
         */
        newMenu() {
            return {
                name: "",
                for: "map",
                color: "#000000",
                bgcolor: "#FFFFFF",
                filters: {},
            };
        },

        /**
         *
         */
        async storeMenu() {
            this.addingMenu = true;

            if (this.useCurrentFilters) {
                this.menu.filters = this.prospectsParams;
            }

            try {
                const menu = await store.dispatch(ADD_MENU, this.menu);

                if (this.menuUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_MENU, {
                        users: this.menuUsers,
                        menus: [menu.id],
                    });
                }
            } finally {
                this.addingMenu = false;
                this.menu = newMenu();
                this.menuUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["prospectsParams", "prospectsParamExists", "users"]),

        /**
         *
         *
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },*/
    },
};
</script>
