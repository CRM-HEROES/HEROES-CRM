<template>
    <item
        tag="a"
        :to="to"
        class="hc-prospect-menu"
        :title="menu.name"
        @click.prevent="showProspects"
        v-tooltip="menu.name"
    >
        <icon :size="30" class="fa fa-star" :style="style" />
        <div class="hc-item-main-content" v-text="menu.name"></div>
        <div class="hc-prospect-menu-count" v-text="menu.count"></div>
        <!--icon
            tag="a"
            :class="[
                'fa',
                'fa-star',
                'hc-show-on-hover',
                isDefault ? 'icon-orange' : 'icon-grey',
            ]"
            :size="30"
            @click.prevent.stop="toggleDefault"
        /-->
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
import { SET_USER } from "@/actions/project/user";
import { SET_MENU } from "@/actions/project/menu";
import { UPDATE_USER_MENU } from "@/actions/project/user/menu";
import {
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
    SET_PROSPECTS_COUNT,
} from "@/actions/project/prospect";
import {
    UPDATE_PROJECT_USER_SETTING,
    REMOVE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";

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
         * Show prospects
         * Associated to the menus
         */
        showProspects(e) {
            let params = {
                ...(e.ctrlKey ? this.prospectsParams : {}),
                ...this.menu.filters,
            };

            store.commit(INIT_PROSPECT_PARAMS);

            store.commit(SET_PROSPECT_PARAMS, params);
            store.commit(SET_PROSPECTS_COUNT, 1000);
            store.dispatch(FETCH_PROSPECTS);
            // store.commit(SET_PROSPECTS_MENU, this.menu);
        },

        toggleDefault() {
            /*store.commit(SET_USER, this.user);
            store.dispatch(UPDATE_USER_MENU, {
                menu: this.menu,
                params: {
                    default: !this.isDefault,
                },
            });*/
            if (this.isDefault) {
                store.dispatch(REMOVE_PROJECT_USER_SETTING, {
                    key: "prospects.default-menu",
                });
            } else {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "prospects.default-menu",
                    value: this.menu.id,
                });
            }
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "prospectsParams",
            "projectUserSettingsProspectsDefaultMenu",
        ]),

        style() {
            return {
                color: this.menu.bgcolor,
            };
        },

        to() {
            return {
                name: "map",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify(this.menu.filters),
                },
            };
        },

        isDefault() {
            return (
                this.projectUserSettingsProspectsDefaultMenu &&
                this.projectUserSettingsProspectsDefaultMenu == this.menu.id
            );
        },
    },
};
</script>
