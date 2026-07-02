<template>
    <label class="hc-relation-header-cell-label">
        <div class="hc-relation-header-cell-options">
            <span class="fa fa-filter"></span>
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
};
</script>
