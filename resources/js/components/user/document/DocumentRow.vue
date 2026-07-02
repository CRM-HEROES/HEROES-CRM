<template>
    <item tag="label">
        <icon class="fa fa-file-pdf" :style="style" />
        <div class="hc-item-main-content" v-text="document.name"></div>
        <icon
            v-if="can('all.project.document.update')"
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
    ADD_USER_DOCUMENT,
    REMOVE_USER_DOCUMENT,
} from "@/actions/project/user/document";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_DOCUMENT } from "@/actions/project/document";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        document: {
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
                event.target.checked ? ADD_USER_DOCUMENT : REMOVE_USER_DOCUMENT,
                this.document
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "document-update");
            store.commit(SET_DOCUMENT, this.document);
        },
    },

    computed: {
        ...mapGetters(["can"]),

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
