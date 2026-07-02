<template>
    <label class="hc-relation-header-cell-label">
        <div class="hc-relation-header-cell-options">
            <span class="fa fa-filter"></span>
            <span
                v-if="
                    column.category == 'category' &&
                    can('all.project.category.update')
                "
                class="fa fa-cog"
                @click.prevent.stop="edit"
            ></span>
        </div>

        <span v-text="column.name"></span>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";

export default {
    props: {
        /**
         * Column
         */
        column: {
            type: Object,
        },
    },

    methods: {
        /**
         * ONly for category column
         * Allow user to update the category informations
         */
        edit() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(
                SET_CATEGORY,
                this.categories.find((c) => c.id == this.column.id)
            );
        },
    },

    computed: {
        ...mapGetters(["categories", "ordersParamExists", "can"]),
    },
};
</script>
