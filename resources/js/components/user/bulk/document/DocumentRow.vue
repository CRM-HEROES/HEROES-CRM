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
        <checkbox v-model="selected" :value="document.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_DOCUMENT } from "@/actions/project/document";
import { UPDATE_USER_BULK_DOCUMENTS } from "@/actions/project/user";

export default {
    props: {
        document: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "document-update");
            store.commit(SET_DOCUMENT, this.document);
        },
    },

    computed: {
        ...mapGetters(["userBulkDocuments", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.document.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkDocuments;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_DOCUMENTS, value);
            },
        },
    },
};
</script>
