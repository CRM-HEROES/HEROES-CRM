<template>
    <item>
        <icon :class="icon" :style="style" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FOLDER } from "@/actions/project/folder";

export default {
    props: {
        folder: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "folder-update");
            store.commit(SET_FOLDER, this.folder);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.folder.bgcolor,
            };
        },

        /**
         *
         */
        icon() {
            if (this.folder.private) {
                return "fa fa-folder";
            } else {
                return "fa fa-folder-open";
            }
        },
    },
};
</script>
