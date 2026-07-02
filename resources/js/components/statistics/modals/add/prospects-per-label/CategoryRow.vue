<template>
    <item>
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

export default {
    props: {
        category: {
            type: Object,
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
