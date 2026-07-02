<template>
    <modal
        name="document-page-add"
        :title="$t('document.page.add.title')"
        :width="file ? 432 : 300"
        @open="initPage()"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="storePage"
        >
            <div class="hc-flex-row">
                <v-field :label="$t('document.page.add.choose_file')" required>
                    <select v-model="page.file_id" @change="page.file_page = 0">
                        <option :value="null">
                            {{ $t("document.page.add.empty") }}
                        </option>
                        <option
                            v-for="file in documentFiles"
                            :value="file.id"
                            v-text="file.name"
                        ></option>
                    </select>
                </v-field>
                <v-field
                    :label="$t('document.page.add.page')"
                    v-slot="{ label }"
                    required
                >
                    <select v-model="page.page">
                        <option
                            v-for="i in documentPages
                                ? documentPages.length + 1
                                : 1"
                            :value="i"
                            v-text="i"
                        ></option>
                    </select>
                </v-field>
            </div>
            <div
                class="hc-flex-row"
                style="flex-wrap: wrap; max-height: 420px; overflow: auto"
                v-if="file"
            >
                <page-row
                    v-for="p in file.pages_count"
                    :key="p"
                    :file="file"
                    :page="p"
                    :selected="p == page.file_page"
                    @click="page.file_page = p"
                />
            </div>
            <template v-else>
                <v-field
                    :label="$t('document.page.add.width')"
                    v-slot="{ label }"
                    required
                >
                    <input v-model="page.width" :placeholder="label" />
                </v-field>
                <v-field
                    :label="$t('document.page.add.height')"
                    v-slot="{ label }"
                    required
                >
                    <input v-model="page.height" :placeholder="label" />
                </v-field>
            </template>
            <buttons>
                <button v-text="$t('add')"></button>
            </buttons>
            <loading :loading="addingPage" />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_DOCUMENT_PAGE,
    SHOW_DOCUMENT_PAGE,
    FETCH_DOCUMENT_PAGES,
} from "@/actions/project/document";
import { CLOSE_MODAL } from "@/actions/modal";

import PageRow from "./PageRow.vue";

export default {
    components: {
        PageRow,
    },

    data() {
        return {
            page: this.newPage(),
            addingPage: false,
        };
    },

    methods: {
        /**
         *
         */
        initPage() {
            this.page = {
                ...this.newPage(),
                ...this.documentNewPage,
            };
        },

        /**
         *
         */
        newPage() {
            return {
                file_id: null,
                file_page: 0,
                page: this.documentPage
                    ? this.documentPage.page + 1
                    : this.documentPages
                    ? this.documentPages.length + 1
                    : 1,
                width: "209.90277777778mm",
                height: "297.03888888889mm",
            };
        },

        /**
         *
         */
        async storePage() {
            this.addingPage = true;

            try {
                const page = await store.dispatch(ADD_DOCUMENT_PAGE, this.page);
                store.commit(SHOW_DOCUMENT_PAGE, page);
                store.dispatch(FETCH_DOCUMENT_PAGES);
            } finally {
                this.addingPage = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        // Store
        ...mapGetters([
            "documentFiles",
            "documentPages",
            "documentPage",
            "documentNewPage",
        ]),

        file() {
            return this.documentFiles.find(
                (file) => file.id == this.page.file_id
            );
        },
    },
};
</script>
