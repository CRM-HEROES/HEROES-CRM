<template>
    <item>
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-tags hc-show-on-hover"
            @click.stop.prevent="labels"
        />
        <icon
            v-if="category.id == mapColorByLabel"
            tag="a"
            class="fa fa-check icon-blue"
        />
        <icon
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <icon
            v-if="can('all')"
            tag="a"
            :class="
                color == category.id
                    ? 'fa fa-star icon-orange'
                    : 'fa fa-star hc-show-on-hover'
            "
            @click.prevent="setDefault"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

import { UPDATE_SETTING } from "@/actions/project/setting";

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
                key: "map.color",
                value: this.category.id,
            });
        },
    },

    computed: {
        ...mapGetters(["can", "mapColorByLabel", "settingsGet"]),

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
