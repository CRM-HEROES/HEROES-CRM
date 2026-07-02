<template>
    <slide
        :name="name"
        :title="'Favoris'"
        icon="fa fa-star"
        style="width: 260px"
        @open="fetchMenus"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="menuKeyword" />
            <item-list class="hc-flex-1" padding="5px">
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
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { FETCH_MENUS } from "@/actions/project/menu";

// Components
import MenuRow from "./MenuRow.vue";

export default {
    components: {
        MenuRow,
    },

    data() {
        return {
            name: "map-menus",
            tab: 0,
            menuKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} menu
         */
        fetchMenus() {
            store.dispatch(FETCH_MENUS);
        },

        /**
         *
         */
        addMenu() {
            store.commit(OPEN_MODAL, "map-menu-add");
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "menus",
            "prospectMenus",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        filteredMenus() {
            const keyword = removeStringAccent(this.menuKeyword);

            return this.menus.filter(
                (menu) =>
                    (menu.for == "map") ==
                    removeStringAccent(menu.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
