<template>
    <a class="hc-order-document hc-flex-column" :href="url" target="_blank">
        <div class="hc-order-document-thumbnail hc-flex-column">
            <div
                @click.prevent.stop="editDocument"
                class="hc-order-document-edit fa fa-cog"
                v-tooltip="'Modifier le template document'"
            ></div>
            <div class="hc-order-document-thumbnail-content">
                <img v-if="document.thumbnail" :src="document.thumbnail" />
            </div>
            <div class="hc-order-document-plus">
                <icon class="fa fa-plus icon-black" />
            </div>
            <div
                @click.prevent.stop="sendToThread"
                class="hc-order-document-message fa fa-paper-plane"
                v-tooltip="'Envoyer dans un canal de discussion'"
            >
                <loading :loading="generatingInvoice" />
            </div>
        </div>
        <div class="hc-order-document-name" v-text="document.name"></div>
    </a>
</template>

<style>
.hc-order-document {
    padding: 8px;
    float: left;
    width: 33.333%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-order-document:hover {
    transform: scale(1.025);
    background-color: #ffffff;
}

.hc-order-document-thumbnail {
    position: relative;
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #ffffff;
    outline: 1px solid #cccccc;
}

.hc-order-document-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 141.285%;
}

.hc-order-document-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-order-document-name {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-top: 7px;
    color: #000000;
    text-align: center;
}

.hc-order-document-edit {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #aaaaaa;
    font-size: 17px;
    text-align: center;
    line-height: 40px;
    cursor: pointer;
}

.hc-order-document-edit:hover {
    color: #888888;
}

.hc-order-document-message {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #1e6ee5;
    background-color: #ffffff;
    border: 1px solid #eeeeee;
    font-size: 17px;
    text-align: center;
    line-height: 40px;
    cursor: pointer;
    z-index: 2;
}

.hc-order-document-plus {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #fff4;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
}

.hc-order-document-message:hover {
    color: #ffffff;
    background-color: #1e6ee5;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";
import { API_URL } from "@/apis/common";

import { OPEN_SUB_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_MESSAGE_INVOICE } from "@/actions/project/prospect/message";

export default {
    props: {
        /**
         *
         */
        document: {
            type: Object,
        },
    },

    data() {
        return {
            generatingInvoice: false,
        };
    },

    methods: {
        async sendToThread() {
            this.generatingInvoice = true;

            try {
                let { data } = await ApiService.get(
                    this.invoiceUri + "/generate"
                );

                store.commit(OPEN_SUB_SLIDE, "prospect-manage-messages");
                store.commit(SET_PROSPECT_MESSAGE_INVOICE, data);
            } finally {
                this.generatingInvoice = false;
            }
        },

        editDocument() {
            const routeData = this.$router.resolve({
                name: "document.show",
                params: {
                    project: this.project.slug,
                    document: this.document.id,
                },
            });
            window.open(routeData.href, "_blank");
        },
    },

    computed: {
        ...mapGetters(["project", "prospectOrder"]),

        invoiceUri() {
            return `project/${this.project.slug}/prospect/${this.prospectOrder.prospect_id}/order/${this.prospectOrder.id}/invoice/${this.document.id}`;
        },

        documentUri() {
            return `project/${this.project.slug}/prospect/${this.prospectOrder.prospect_id}/order/${this.prospectOrder.id}/document/${this.document.id}`;
        },

        uri() {
            if (this.document.for == "invoice") {
                return this.invoiceUri;
            } else {
                return this.documentUri;
            }
        },

        url() {
            return API_URL + "/" + this.uri;
        },
    },
};
</script>
