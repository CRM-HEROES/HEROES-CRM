<template>
    <item
        tag="router-link"
        :to="to"
        class="hc-user-menu"
        :style="style"
        :title="menu.name"
    >
        <icon :size="30" class="fa fa-list" />
        <div class="hc-item-main-content" v-text="menu.name"></div>
        <div class="hc-user-menu-count" v-text="menu.count"></div>
        <icon
            tag="a"
            class="fa fa-globe-europe hc-show-on-hover"
            color="#007afd"
            :size="28"
            @click.prevent.stop="map"
        />
        <icon
            v-if="!isNaN(menu.id)"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="30"
            @click.prevent.stop="edit"
        />
    </item>
</template>

<style>
.hc-user-menu * {
    color: inherit;
}

.hc-user-menu-count {
    opacity: 0.8;
    font-size: 11px;
    padding-right: 7px;
}
</style>

<script>
import { mapGetters } from "vuex";

import store from "@/store";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_MENU } from "@/actions/project/menu";

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

        /**
         *
         */
        map() {
            this.$router.push({
                name: "map",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify(this.menu.filters),
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project", "usersParams"]),

        style() {
            return {
                color: this.menu.color,
                backgroundColor: this.menu.bgcolor,
            };
        },

        to() {
            return {
                name: "user",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify(this.menu.filters),
                },
            };
        },
    },
};
</script>
