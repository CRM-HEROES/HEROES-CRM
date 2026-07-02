<template>
    <item>
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <div v-if="count > 0" class="hc-item-count" v-text="count"></div>
        <icon
            v-if="can('all.project.group.update')"
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
        ...mapGetters(["prospectBulkLabels", "can"]),

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
        count() {
            return this.category.labels.filter((label) =>
                this.prospectBulkLabels.find((l) => l == label.id)
            ).length;
        },
    },
};
</script>
