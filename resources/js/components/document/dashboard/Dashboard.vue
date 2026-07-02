<template>
    <div id="document-dashboard">
        <div id="document-dashboard-list">
            <div>
                <div class="document-dashboard-title">
                    <span
                        style="
                            border-radius: 5px;
                            padding: 4px 10px;
                            display: inline-block;
                            background-color: #7939b8;
                            color: white;
                            font-size: 14px;
                            font-weight: 400;
                        "
                        v-text="$t('document.dashboard.your_documents')"
                    ></span>
                </div>
                <div class="document-dashboard-body">
                    <document-row
                        v-for="document in documents"
                        :key="document.key"
                        :document="document"
                        @click.prevent="showDocument(document)"
                    />
                </div>
            </div>
        </div>
        <div
            id="document-dashboard-templates"
            style="background-color: #eee; position: relative"
        >
            <div>
                <div class="document-dashboard-title">
                    <span
                        v-text="$t('document.dashboard.new_from_template')"
                    ></span>
                </div>
                <div class="document-dashboard-body">
                    <template-row
                        v-for="template in documentTemplates"
                        :key="template.key"
                        :document-template="template"
                        @click.prevent="createDocumentFromTemplate(template)"
                    />

                    <label
                        class="hc-dashboard-document-template hc-flex-column"
                        target="_blank"
                    >
                        <div
                            class="hc-dashboard-document-template-thumbnail hc-flex-column"
                        >
                            <div
                                class="hc-dashboard-document-template-thumbnail-content"
                            >
                                <img
                                    :src="
                                        '/images/document/templates/' +
                                        this.pdfTemplate.key +
                                        '.jpg'
                                    "
                                />
                            </div>
                        </div>
                        <div
                            class="hc-dashboard-document-template-name"
                            v-text="pdfTemplate.name"
                        ></div>
                        <div
                            class="hc-dashboard-document-template-description"
                            v-text="pdfTemplate.description"
                        ></div>
                        <input
                            type="file"
                            accept="application/pdf"
                            @change="createFromPDFTemplate"
                            multiple
                            style="display: none"
                        />
                    </label>
                </div>
            </div>
        </div>
    </div>
    <loading :loading="creatingFromTemplate" />
</template>

<style>
#document-dashboard {
    width: 100%;
    height: 100%;
    overflow: auto;
    display: flex;
    flex-direction: column;
    position: relative;
}

#document-dashboard > div {
    width: 100%;
    padding: 25px 15px;
}

#document-dashboard > div > div {
    width: 1000px;
    max-width: 100%;
    padding: 15px;
    margin: auto;
}

.document-dashboard-title {
    font-weight: bold;
    width: 100%;
    margin-bottom: 10px;
    font-size: 16px;
}

.document-dashboard-body {
    width: 100%;
    float: left;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import DocumentFileService from "@/apis/project/document/file";

// Actions
import { FETCH_DOCUMENTS, ADD_DOCUMENT } from "@/actions/project/document";
import {
    FETCH_DOCUMENT_TEMPLATES,
    ADD_DOCUMENT_TEMPLATE,
} from "@/actions/project/document-template";

import TemplateRow from "./TemplateRow";
import DocumentRow from "./DocumentRow";

export default {
    components: {
        TemplateRow,
        DocumentRow,
    },

    data() {
        return {
            creatingFromTemplate: false,
            pdfTemplate: {
                key: "pdf",
                name: "Template PDF",
                description: "Choisir un fichier PDF de fond",
            },
        };
    },

    created() {
        this.fetchTemplates();
        this.fetchDocuments();
    },

    methods: {
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
         */
        async createFromPDFTemplate(event) {
            const files = event.target.files;

            this.creatingFromTemplate = true;

            try {
                const document = await store.dispatch(ADD_DOCUMENT, {
                    name: files[0].name.substring(0, 20),
                });

                for (var i = 0; i < files.length; i++) {
                    var formData = new FormData();
                    formData.append("file", files[i]);

                    // dispatch document pdf
                    await DocumentFileService.create(
                        this.project.slug,
                        document.id,
                        formData
                    );
                }
                this.showDocument(document);
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

        fetchDocuments() {
            store.dispatch(FETCH_DOCUMENTS);
        },

        fetchTemplates() {
            store.dispatch(FETCH_DOCUMENT_TEMPLATES);
        },
    },

    computed: {
        // Store
        ...mapGetters(["project", "documents", "documentTemplates"]),
    },
};
</script>
