<template>
    <item>
        <icon :class="icon" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            v-if="can('all.project.category.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent=""
        />
        <icon class="fa fa-caret-right" />
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
        edit(e) {
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

        /**
         *
         */
        icon() {
            switch (this.category.for) {
                case "prospect":
                    return "fa fa-users";
                case "user":
                    return "fa fa-user";
                case "order":
                    return "fa fa-shopping-cart";
                case "product":
                    return "fa fa-gift";
                default:
                    return "fa fa-tags";
            }
        },
    },
};
</script>
