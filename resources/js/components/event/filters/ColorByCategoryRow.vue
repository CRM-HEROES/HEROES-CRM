<template>
    <item>
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

export default {
    props: {
        category: {
            type: Object,
        },
    },

    methods: {
        /**
         *
         */
        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },

        /**
         *
         */
        labels() {
            this.$emit("labels");
        },

        /**
         *
         */
        setDefault() {
            store.dispatch(UPDATE_SETTING, {
                key: "event.color",
                value: this.category.id,
            });
        },
    },

    computed: {
        ...mapGetters(["can", "settingsGet"]),

        /**
         *
         */
        color() {
            const setting = this.settingsGet("map.color");

            return setting;
        },

        /**
         *
         */
        style() {
            return {
                color: this.category.bgcolor,
            };
        },
    },
};
</script>
