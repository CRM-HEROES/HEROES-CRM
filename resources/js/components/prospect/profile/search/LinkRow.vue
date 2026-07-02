<template>
    <item tag="a" :href="link.link" target="_blank">
        <icon class="fa fa-link" :size="30" />
        <div class="hc-item-main-content" v-text="link.name"></div>
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_CATEGORY } from "@/actions/project/prospect";

export default {
    props: {
        link: {
            type: Object,
        },
    },

    methods: {
        action() {},
    },

    computed: {
        ...mapGetters(["categories"]),

        /**
         *
         */
        style() {
            return {
                color: this.link.bgcolor,
            };
        },
    },
};
</script>
