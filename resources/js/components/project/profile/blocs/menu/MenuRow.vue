<template>
    <item
        tag="router-link"
        :to="to"
        class="hc-prospect-menu"
        :style="style"
        :title="menu.name"
    >
        <icon :size="30" class="fa fa-list" />
        <div class="hc-item-main-content" v-text="menu.name"></div>
        <div class="hc-prospect-menu-count" v-text="menu.count"></div>
        <icon
            tag="a"
            class="fa fa-globe-europe hc-show-on-hover"
            color="#007afd"
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
.hc-prospect-menu .hc-item-main-content {
    white-space: normal;
}

.hc-prospect-menu * {
    color: inherit;
}

.hc-prospect-menu-count {
    font-size: 12px;
    padding-right: 7px;
    font-weight: 600;
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
        ...mapGetters(["project", "prospectsParams"]),

        style() {
            return {
                color: this.menu.color,
                backgroundColor: this.menu.bgcolor,
            };
        },

        to() {
            return {
                name: "prospect",
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
