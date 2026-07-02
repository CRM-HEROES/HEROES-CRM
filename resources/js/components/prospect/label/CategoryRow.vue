<template>
    <item>
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <tag
            v-if="count == 1"
            :text="labels[0].name"
            :color="labels[0].color"
            :bgcolor="labels[0].bgcolor"
        />
        <div v-else-if="count > 1" class="hc-item-count" v-text="count"></div>
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <icon class="fa fa-caret-right" />
    </item>
</template>

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
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
        },
    },

    computed: {
        ...mapGetters(["prospectLabels", "can"]),

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
        labels() {
            return this.prospectLabels.filter(
                (l) => l.category_id == this.category.id
            );
        },

        /**
         *
         */
        count() {
            return this.labels.length;
        },
    },
};
</script>
