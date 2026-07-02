<template>
    <slide
        :name="name"
        :title="$t('campaign.action.order.sign_invoice.title')"
        icon="fa fa-users"
        style="width: 260px"
    >
        <div
            class="hc-flex-1 hc-flex-column"
            style="height: 100%; overflow: hidden; position: relative"
        >
            <search v-model="documentKeyword" />
            <item-list class="hc-flex-1" padding="12px">
                <document-row
                    v-for="document in filteredDocuments"
                    :key="document.id"
                    :document="document"
                    @dragging="dragging"
                    @dragged="dragged"
                />
            </item-list>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";

import DocumentRow from "./DocumentRow.vue";

export default {
    components: {
        DocumentRow,
    },

    data() {
        return {
            name: "campaign-action-order-sign-invoice",
            tab: 0,
            documentKeyword: "",
        };
    },

    methods: {
        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },

    computed: {
        ...mapGetters(["documents", "slideOpen"]),

        /**
         *
         */
        filteredDocuments() {
            const keyword = removeStringAccent(this.documentKeyword);

            return this.documents.filter(
                (document) =>
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
