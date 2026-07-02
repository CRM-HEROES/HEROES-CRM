<template>
    <item tag="label">
        <icon class="fa fa-folder" :style="style" />
        <div class="hc-item-main-content" v-text="folder.name"></div>
        <icon
            v-if="can('all.project.folder.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_USER_FOLDER,
    REMOVE_USER_FOLDER,
} from "@/actions/project/user/folder";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FOLDER } from "@/actions/project/folder";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        folder: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked ? ADD_USER_FOLDER : REMOVE_USER_FOLDER,
                this.folder
            );
            this.$emit("change", event);
        },

        edit() {
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
    },
};
</script>
