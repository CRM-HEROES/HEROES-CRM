<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="documentKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <document-row
                v-for="document in filteredDocuments"
                :key="document.id"
                :document="document"
            />
        </item-list>
        <buttons>
            <button @click.prevent="attach" :disabled="disabled">
                Assigner
            </button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
            >
                Retirer
            </button>
            <a
                v-if="can('all.project.document.add')"
                @click.prevent="addDocument"
                v-text="$t('add')"
            ></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_DOCUMENT,
    BULK_REMOVE_USER_DOCUMENT,
} from "@/actions/project/user/document";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_DOCUMENTS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import DocumentRow from "./DocumentRow.vue";

export default {
    components: {
        DocumentRow,
    },

    data() {
        return {
            bulking: false,
            documentKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_DOCUMENT, {
                    users: usersSelected,
                    documents: this.userBulkDocuments,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_DOCUMENTS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-documents");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_DOCUMENT, {
                    users: usersSelected,
                    documents: this.userBulkDocuments,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_DOCUMENTS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-documents");
            }
        },
    },

    computed: {
        ...mapGetters([
            "documents",
            "usersSelected",
            "userBulkDocuments",
            "can",
        ]),

        /**
         *
         */
        disabled() {
            return this.userBulkDocuments.length == 0;
        },

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

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredDocuments) {
                    if (
                        !this.userBulkDocuments.find(
                            (document) =>
                                document == this.filteredDocuments[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_DOCUMENTS,
                        this.filteredDocuments.map((document) => document.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_DOCUMENTS, []);
                }
            },
        },
    },
};
</script>
