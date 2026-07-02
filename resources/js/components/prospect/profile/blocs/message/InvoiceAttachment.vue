<template>
    <div class="hc-prospect-uploading-attachment hc-flex-column">
        <div class="hc-prospect-uploading-attachment-thumbnail hc-flex-column">
            <div class="hc-prospect-uploading-attachment-thumbnail-content">
                <img :src="invoice.thumbnail" />
                <div
                    class="hc-prospect-uploading-attachment-remove"
                    @click.prevent.stop="remove"
                >
                    ×
                </div>
            </div>
        </div>
        <div
            class="hc-prospect-uploading-attachment-name"
            v-text="document ? document.name : ''"
        ></div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        /**
         *
         */
        invoice: {
            type: Object,
        },
    },

    data() {
        return {
            thumbnail: null,
            uploadProgress: 0,
        };
    },

    computed: {
        ...mapGetters(["documents"]),

        document() {
            return this.documents.find((d) => d.id == this.invoice.document_id);
        },
    },

    methods: {
        remove() {
            this.$emit("remove", this.attachment);
        },
    },
};
</script>
