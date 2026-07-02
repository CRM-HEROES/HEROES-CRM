<template>
    <item tag="label">
        <icon class="fa fa-list" :style="style" />
        <div class="hc-item-main-content" v-text="menu.name"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { ADD_USER_MENU, REMOVE_USER_MENU } from "@/actions/project/user/menu";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_MENU } from "@/actions/project/menu";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        menu: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked ? ADD_USER_MENU : REMOVE_USER_MENU,
                this.menu
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "menu-update");
            store.commit(SET_MENU, this.menu);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.menu.bgcolor,
            };
        },
    },
};
</script>
