<template>
    <item
        tag="router-link"
        :to="{
            name: 'document.show',
            params: {
                project: this.project.slug,
                document: this.document.id,
            },
        }"
    >
        <icon :class="['fa', icon]" />
        <div class="hc-item-main-content" v-text="document.name"></div>
        <icon
            v-if="can('all.project.document.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
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
        edit() {
            store.commit(OPEN_MODAL, "document-update");
            store.commit(SET_DOCUMENT, this.document);
        },
    },

    computed: {
        ...mapGetters(["project", "can"]),

        /**
         *
         */
        icon() {
            switch (this.document.for) {
                case "prospect":
                    return "fa-user";
                case "order":
                    return "fa-shopping-cart";
                default:
                    return "fa-file-pdf";
            }
        },
    },
};
</script>
