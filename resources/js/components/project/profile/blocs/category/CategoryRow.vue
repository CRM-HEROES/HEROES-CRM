<template>
    <item @click.prevent.stop="labels">
        <icon class="fa fa-tags" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon tag="a" class="fa fa-cog" @click.prevent.stop="edit" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

export default {
    props: {
        category: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },
        labels() {
            store.commit(OPEN_SLIDE, "categories");
            store.commit(SET_CATEGORY, this.category);
        },
    },

    computed: {
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
