<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeDocument"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model="document.name"
                    /></v-field>
                    <v-field :label="$t('for')" required>
                        <select v-model="document.for">
                            <option
                                value="prospect"
                                v-text="$t('prospects')"
                            ></option>
                            <option
                                value="order"
                                v-text="$t('orders')"
                            ></option>
                            <option
                                value="invoice"
                                v-text="$t('invoices')"
                            ></option>
                        </select>
                    </v-field>
                    <item @click="tab = 1">
                        <icon class="fa fa-file-pdf icon-garnet" />
                        <div
                            class="hc-item-main-content"
                            v-text="'Créer à partir d\'un template prédéfini'"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingDocument" />
            </form>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div class="hc-item-main-content" v-text="$t('back')"></div>
                </item>
                <search v-model="documentTemplateKeyword" />
                <div style="max-height: 400px; padding: 5px; overflow: auto">
                    <template-row
                        v-for="template in documentTemplates"
                        style="width: 33.333%"
                        :key="template.key"
                        :document-template="template"
                        @click.prevent="createDocumentFromTemplate(template)"
                    />
                </div>
                <loading :loading="creatingFromTemplate" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_DOCUMENT } from "@/actions/project/document";
import { CLOSE_MODAL } from "@/actions/modal";
import {
    FETCH_DOCUMENT_TEMPLATES,
    ADD_DOCUMENT_TEMPLATE,
} from "@/actions/project/document-template";

import TemplateRow from "../dashboard/TemplateRow";

export default {
    components: {
        TemplateRow,
    },

    data() {
        return {
            tab: 0,
            document: this.newDocument(),
            addingDocument: false,
            creatingFromTemplate: false,
            documentTemplateKeyword: "",
        };
    },

    created() {
        this.fetchTemplates();
    },

    methods: {
        /**
         *
         */
        newDocument() {
            return {
                name: "",
                description: "",
                for: "prospect",
            };
        },

        /**
         *
         */
        async storeDocument() {
            this.addingDocument = true;

            try {
                const document = await store.dispatch(
                    ADD_DOCUMENT,
                    this.document
                );
                store.commit(CLOSE_MODAL);

                this.$router.push({
                    name: "document.show",
                    params: {
                        document: document.id,
                    },
                });
            } finally {
                this.document = this.newDocument();
                this.addingDocument = false;
            }
        },

        /**
         *
         * @param {*} template
         */
        async createDocumentFromTemplate(template) {
            this.creatingFromTemplate = true;

            try {
                const data = await store.dispatch(
                    ADD_DOCUMENT_TEMPLATE,
                    template.key
                );
                this.showDocument(data);
            } finally {
                this.creatingFromTemplate = false;
            }
        },

        /**
         *
         * @param {*} document
         */
        showDocument(document) {
            this.$router.push({
                name: "document.show",
                params: {
                    project: this.project.slug,
                    document: document.id,
                },
            });
        },

        fetchTemplates() {
            store.dispatch(FETCH_DOCUMENT_TEMPLATES);
        },
    },

    computed: {
        // Store
        ...mapGetters(["project", "documentTemplates"]),
    },
};
</script>
