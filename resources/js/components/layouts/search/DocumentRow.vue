<template>
    <item @click="edit">
        <icon class="fa fa-file-pdf" :size="28" :style="style" />
        <div class="hc-item-main-content" v-text="document.name"></div>
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_DOCUMENT } from "@/actions/project/document";

export default {
    props: {
        document: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "document-update");
            store.commit(SET_DOCUMENT, this.document);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.document.bgcolor,
            };
        },
    },
};
</script>
