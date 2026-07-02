<template>
    <item>
        <icon :class="icon" :style="style" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <icon
            v-if="can('all.project.folder.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <div v-if="count" class="hc-item-count" v-text="count"></div>
        <icon class="fa fa-caret-right" />
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
        count: {
            type: Number,
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
