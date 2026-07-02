<template>
    <bloc icon="fa fa-list icon-garnet" :name="$t('user.profile.blocs.menus')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addMenu"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <menu-row
                    v-for="(menu, i) in userMenus"
                    :key="menu.id"
                    :menu="menu"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import MenuRow from "./MenuRow.vue";

export default {
    components: {
        Bloc,
        MenuRow,
    },

    methods: {
        /**
         * Add menu
         * See: @/components/menu/add/Modal.vue
         */
        addMenu() {
            store.commit(OPEN_SLIDE, "user-manage-menus");
        },
    },

    computed: {
        ...mapGetters(["project", "userMenus"]),
    },
};
</script>
