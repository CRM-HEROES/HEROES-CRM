<template>
    <item class="hc-prospects-table-filter-category">
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon
            v-if="can('all.project.category.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <span
            v-if="filtersCount"
            class="hc-prospects-table-filter-category-count"
            v-text="filtersCount"
            @click.stop="removeFilter"
        ></span>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<style>
.hc-prospects-table-filter-category-count {
    height: 16px;
    line-height: 16px;
    padding: 0 5px;
    background-color: #1e88e5;
    border-radius: 8px;
    color: white;
    font-size: 11px;
    font-family: monospace;
    cursor: pointer;
}
.hc-prospects-table-filter-category-count:hover {
    background-color: #d9402c;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_PROSPECTS,
    REMOVE_PROSPECT_PARAMS,
} from "@/actions/project/prospect";
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

        removeFilter() {
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.filterWithKey,
            });
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.filterWithoutKey,
            });
            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters(["prospectsParamCount", "can"]),

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
        filterWithKey() {
            return "withCategory_" + this.category.id;
        },

        /**
         *
         */
        filterWithoutKey() {
            return "withoutCategory_" + this.category.id;
        },

        /**
         *
         */
        filtersCount() {
            return (
                this.prospectsParamCount(this.filterWithKey) +
                this.prospectsParamCount(this.filterWithoutKey)
            );
        },
    },
};
</script>
