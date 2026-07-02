<template>
    <item @click.prevent.stop="labels">
        <icon class="fa fa-tags" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="28"
            :icon-size="9"
            @click.prevent.stop="edit"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
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
