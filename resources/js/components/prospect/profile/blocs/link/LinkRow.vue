<template>
    <item tag="a" :href="link.link" target="_blank">
        <icon class="fa fa-external-link" :size="28" :icon-size="9" />
        <div class="hc-item-main-content" v-text="link.name"></div>
        <icon
            tag="a"
            class="fa fa-cog"
            :size="28"
            :icon-size="9"
            @click.prevent.stop="edit"
        />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_PROSPECT_LINK } from "@/actions/project/prospect/link";

export default {
    props: {
        link: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(SET_PROSPECT_LINK, this.link);
            store.commit(OPEN_MODAL, "prospect-link-update");
        },
    },
};
</script>
