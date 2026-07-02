<template>
    <item tag="a" v-tooltip="label.name" @click.prevent="action">
        <icon class="fa fa-tag" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="label.name"></div>
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
        label: {
            type: Object,
        },
    },

    methods: {
        action() {
            store.commit(
                SET_PROSPECT_CATEGORY,
                this.categories.find((c) => c.id == this.label.category_id)
            );
            store.commit(OPEN_SLIDE, "prospect-manage-labels");
        },
    },

    computed: {
        ...mapGetters(["categories"]),

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },
    },
};
</script>
