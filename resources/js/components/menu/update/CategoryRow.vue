<template>
    <item>
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <div
            v-if="count"
            class="hc-menu-category-row-labels-count"
            v-text="count"
        ></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<style>
.hc-menu-category-row-labels-count {
    height: 18px;
    line-height: 18px;
    padding: 0 5px;
    background-color: #7939b8;
    color: white;
    margin: 0 5px;
    font-family: monospace;
    border-radius: 9px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

export default {
    props: {
        category: {
            type: Object,
        },
        count: {
            type: Number,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },
    },

    computed: {
        ...mapGetters(["can"]),

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
