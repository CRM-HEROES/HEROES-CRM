<template>
    <bloc icon="fa fa-list" :name="$t('project.profile.blocs.menus')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addMenu" />
            <icon v-if="menus.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="menus.length > 0"
            >
                <menu-row
                    v-for="(menu, i) in menus"
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
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_MODAL, "menu-add");
        },
    },

    computed: {
        ...mapGetters(["project", "menus"]),
    },
};
</script>
