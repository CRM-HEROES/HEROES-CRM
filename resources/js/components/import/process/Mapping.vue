<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1">
        <!-- Column Tab -->
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="columnKeyword" />
                <item-list padding="5px" id="hc-import-process-mapping-columns">
                    <column-row
                        v-for="header in filteredHeaders"
                        :key="header.index"
                        :column="header"
                        :mapping="getMapping(header.index)"
                        :prospect-import="prospectImport"
                        @click="(column = header), (tab = 1)"
                        @edit="editMappingParam"
                        @add-field="addField"
                        @add-category="addCategory"
                    />
                </item-list>
                <buttons
                    v-if="
                        prospectImport && prospectImport.source == 'webservice'
                    "
                >
                    <a @click.prevent="tab = 1" v-text="$t('new')"></a>
                </buttons>
            </div>
        </template>

        <!-- Mapping Tab -->
        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item class="bordered" @click="(column = null), (tab = 0)">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="column ? column.header : ''"
                    ></div>
                </item>
                <search v-model="mappingKeyword" />
                <item-list padding="5px">
                    <item
                        v-if="column && getMapping(column.index)"
                        @click="removeMapping"
                    >
                        <icon class="fa fa-times" color="#d9402c" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('import.process.tab.mapping.cancel')"
                        ></div>
                    </item>
                    <template
                        v-for="mapping in filteredMapping"
                        :key="mapping.type + '-' + mapping.item.id"
                    >
                        <mapping-default-field-row
                            v-if="mapping.type == 'default-field'"
                            :field="mapping.item"
                            @click="
                                setMappingDefaultField(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index,
                                    mapping.item
                                )
                            "
                        />
                        <mapping-meta-field-row
                            v-else-if="mapping.type == 'meta-field'"
                            :meta="mapping.item"
                            @click="
                                setMappingMetaField(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index,
                                    mapping.item
                                )
                            "
                        />
                        <mapping-category-row
                            v-else-if="mapping.type == 'category'"
                            :category="mapping.item"
                            @click="
                                setMappingCategory(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index,
                                    mapping.item
                                )
                            "
                        />
                        <mapping-thread-row
                            v-else-if="mapping.type == 'thread'"
                            :thread="mapping.item"
                            @click="
                                setMappingThread(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index,
                                    mapping.item
                                )
                            "
                        />
                        <item
                            v-else-if="mapping.type == 'events'"
                            @click="
                                setMappingEvent(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index
                                )
                            "
                        >
                            <icon class="fa fa-calendar" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('import.process.tab.mapping.event')"
                            ></div>
                        </item>
                        <item
                            v-else-if="mapping.type == 'orders'"
                            @click="
                                setMappingOrder(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index
                                )
                            "
                        >
                            <icon class="fa fa-shopping-cart" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('import.process.tab.mapping.order')"
                            ></div>
                        </item>
                        <item
                            v-else-if="mapping.type == 'sms'"
                            @click="
                                setMappingSms(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index
                                )
                            "
                        >
                            <icon class="fa fa-comment" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('import.process.tab.mapping.sms')"
                            ></div>
                        </item>
                        <item
                            v-else-if="mapping.type == 'interactions'"
                            @click="
                                setMappingInteraction(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index
                                )
                            "
                        >
                            <icon class="fa fa-phone" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t('import.process.tab.mapping.interaction')
                                "
                            ></div>
                        </item>
                        <item
                            v-else-if="mapping.type == 'links'"
                            @click="
                                setMappingLink(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index
                                )
                            "
                        >
                            <icon class="fa fa-external-link" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('import.process.tab.mapping.link')"
                            ></div>
                        </item>
                        <item
                            v-else-if="mapping.type == 'users'"
                            @click="
                                setMappingUser(
                                    prospectImport.source == 'webservice'
                                        ? prospectImport.headers
                                            ? prospectImport.headers.length
                                            : 0
                                        : column.index
                                )
                            "
                        >
                            <icon class="fa fa-user" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('import.process.tab.mapping.user')"
                            ></div>
                        </item>
                    </template>
                </item-list>
                <buttons id="hc-import-process-mapping-fields-add">
                    <a
                        @click.prevent="addField(null)"
                        v-text="$t('import.process.tab.mapping.plus_field')"
                    ></a>
                    <a
                        @click.prevent="addCategory(null)"
                        v-text="$t('import.process.tab.mapping.plus_category')"
                    ></a>
                </buttons>
                <loading :loading="addingColumn" />
            </div>
        </template>

        <!-- New field/category -->
        <template #3>
            <frame-layout :count="3" :tab="newFieldCategoryTab">
                <!-- New field -->
                <template #1>
                    <form
                        class="hc-flex-column"
                        style="height: 100%"
                        @submit.prevent="storeField"
                    >
                        <item @click="tab = 1" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t('import.process.tab.mapping.new_field')
                                "
                            ></div>
                        </item>
                        <item-list gap="5px" class="hc-flex-1" padding="12px">
                            <v-field :label="$t('name')" required v-slot="p"
                                ><input
                                    ref="fieldName"
                                    required
                                    :placeholder="p.label + ' ...'"
                                    v-model="newField.name"
                            /></v-field>
                            <v-field :label="$t('type')" required>
                                <select v-model="newField.type">
                                    <option
                                        value="text"
                                        v-text="$t('field.types.text')"
                                    ></option>
                                    <option
                                        value="long_text"
                                        v-text="$t('field.types.long_text')"
                                    ></option>
                                    <option
                                        value="number"
                                        v-text="$t('field.types.number')"
                                    ></option>
                                    <option
                                        value="email"
                                        v-text="$t('field.types.email')"
                                    ></option>
                                    <option
                                        value="date"
                                        v-text="$t('field.types.date')"
                                    ></option>
                                    <option
                                        value="datetime"
                                        v-text="$t('field.types.datetime')"
                                    ></option>
                                    <option
                                        value="address"
                                        v-text="$t('field.types.address')"
                                    ></option>
                                    <option
                                        value="url"
                                        v-text="$t('field.types.url')"
                                    ></option>
                                    <option
                                        value="tel"
                                        v-text="$t('field.types.tel')"
                                    ></option>
                                </select>
                            </v-field>
                        </item-list>
                        <buttons>
                            <button v-text="$t('add')"></button>
                        </buttons>
                        <loading :loading="addingField" />
                    </form>
                </template>

                <!-- New Category -->
                <template #2>
                    <form
                        class="hc-flex-column"
                        style="height: 100%"
                        @submit.prevent="storeCategory"
                    >
                        <item @click="tab = 1" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t(
                                        'import.process.tab.mapping.new_category'
                                    )
                                "
                            ></div>
                        </item>
                        <item-list gap="5px" class="hc-flex-1" padding="12px">
                            <v-field
                                :label="$t('name')"
                                required
                                v-slot="{ label }"
                                ><input
                                    required
                                    :placeholder="label + ' ...'"
                                    v-model="newCategory.name"
                            /></v-field>
                            <v-field :label="$t('description')" v-slot="p">
                                <textarea
                                    v-model="newCategory.description"
                                    :placeholder="p.label + ' ...'"
                                ></textarea>
                            </v-field>
                            <v-field :label="$t('color')"
                                ><input
                                    type="color"
                                    v-model="newCategory.color"
                            /></v-field>
                            <v-field :label="$t('bgcolor')"
                                ><input
                                    type="color"
                                    v-model="newCategory.bgcolor"
                            /></v-field>
                        </item-list>
                        <buttons>
                            <button v-text="$t('add')"></button>
                        </buttons>
                        <loading :loading="addingCategory" />
                    </form>
                </template>

                <!-- Mapping to edit -->
                <template #3>
                    <form
                        class="hc-flex-column"
                        style="height: 100%"
                        v-if="mappingParamToEdit"
                        @submit.prevent="updateMappingParam"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t('update') +
                                    ': ' +
                                    mappingParamToEdit.name
                                "
                            ></div>
                        </item>
                        <item-list gap="5px" class="hc-flex-1" padding="12px">
                            <v-field
                                :label="$t('name')"
                                required
                                v-slot="{ label }"
                                ><input
                                    required
                                    :placeholder="label + ' ...'"
                                    v-model="mappingParamToEdit.name"
                            /></v-field>
                        </item-list>
                        <buttons>
                            <button v-text="$t('update')"></button>
                        </buttons>
                        <loading :loading="updatingMappingParam" />
                    </form>
                </template>
            </frame-layout>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_IMPORT_MAPPING_FIELD,
    ADD_IMPORT_MAPPING_META,
    ADD_IMPORT_MAPPING_CATEGORY,
    ADD_IMPORT_MAPPING_THREAD,
    ADD_IMPORT_MAPPING_EVENT,
    ADD_IMPORT_MAPPING_ORDER,
    ADD_IMPORT_MAPPING_SMS,
    ADD_IMPORT_MAPPING_INTERACTION,
    ADD_IMPORT_MAPPING_LINK,
    ADD_IMPORT_MAPPING_USER,
    REMOVE_IMPORT_MAPPING,
    UPDATE_IMPORT,
} from "@/actions/project/import";
import { ADD_FIELD } from "@/actions/project/field";
import { ADD_CATEGORY } from "@/actions/project/category";

// Components
import ColumnRow from "./ColumnRow.vue";
import MappingDefaultFieldRow from "./mapping/MappingDefaultFieldRow.vue";
import MappingMetaFieldRow from "./mapping/MappingMetaFieldRow.vue";
import MappingCategoryRow from "./mapping/MappingCategoryRow.vue";
import MappingThreadRow from "./mapping/MappingThreadRow.vue";

export default {
    components: {
        ColumnRow,
        MappingDefaultFieldRow,
        MappingMetaFieldRow,
        MappingCategoryRow,
        MappingThreadRow,
    },

    data() {
        return {
            columnKeyword: "",
            mappingKeyword: "",
            tab: 0,
            newFieldCategoryTab: 0,
            column: null,
            newField: {
                name: "",
                type: "text",
                meta: true,
                for: "prospect",
            },
            newCategory: {
                name: "",
                for: "prospect",
                color: "#FFFFFF",
                bgcolor: "#000000",
            },
            mappingParamToEdit: null,
            addingField: false,
            addingCategory: false,
            addingColumn: false,
            updatingMappingParam: false,
        };
    },

    mounted() {
        /*if (
            this.prospectImport &&
            this.prospectImport.headers &&
            (!this.prospectImport.mapping ||
                !Array.isArray(this.prospectImport.mapping) ||
                this.prospectImport.mapping.length == 0)
        ) {
            this.autoMapping();
        }*/
    },

    methods: {
        /**
         * Get mapping column name
         * @param {*} category
         */
        getMapping(index) {
            if (!this.prospectImport.mapping) {
                return null;
            }

            const key = this.prospectImport.mapping[index];
            if (!key) {
                return null;
            }

            // Event
            if (key == "events") {
                return this.$t("import.process.tab.mapping.event");
                // Order
            } else if (key == "orders") {
                return this.$t("import.process.tab.mapping.order");
                // SMS
            } else if (key == "sms") {
                return this.$t("import.process.tab.mapping.sms");
                // Interaction
            } else if (key == "interactions") {
                return this.$t("import.process.tab.mapping.interaction");
                // Link
            } else if (key == "links") {
                return this.$t("import.process.tab.mapping.link");
                // Affected user
            } else if (key == "users") {
                return this.$t("import.process.tab.mapping.user");
                // Auto meta field
            } else if (key == "meta->") {
                return this.$t("import.process.tab.mapping.auto_field");
                // Auto category
            } else if (key == "category->") {
                return this.$t("import.process.tab.mapping.auto_category");
                // Auto thread
            } else if (key == "thread->") {
                return this.$t("import.process.tab.mapping.auto_thread");
                // Category
            } else if (key.indexOf("category->") == 0) {
                const id = key.substring(10);
                const category = this.categories.find((c) => c.id == id);

                if (category) {
                    return category.name;
                }
                // Thread
            } else if (key.indexOf("thread->") == 0) {
                const id = key.substring(8);
                const thread = this.threads.find((t) => t.id == id);

                if (thread) {
                    return thread.name;
                }
                // Meta field
            } else if (key.indexOf("meta->") == 0) {
                const slug = key.substring(6);
                const field = this.fields.find(
                    (f) => f.meta && f.for == "prospect" && f.slug == slug
                );

                if (field) {
                    return field.name;
                }
                // Default field
            } else {
                const slug = key;
                const field = this.fields.find(
                    (f) => !f.meta && f.for == "prospect" && f.slug == slug
                );

                if (field) {
                    return field.name;
                }
            }

            return null;
        },

        /**
         * String slug
         * @param {*} str
         */
        slugify(str) {
            return String(str)
                .normalize("NFKD") // split accented characters into their base characters and diacritical marks
                .replace(/[\u0300-\u036f]/g, "") // remove all the accents, which happen to be all in the \u03xx UNICODE block.
                .trim() // trim leading or trailing whitespace
                .toLowerCase() // convert to lowercase
                .replace(/[^a-z0-9 -]/g, "") // remove non-alphanumeric characters
                .replace(/\s+/g, "-") // replace spaces with hyphens
                .replace(/-+/g, "-"); // remove consecutive hyphens
        },

        /**
         *
         * @param {*} slug
         */
        availableHeaderSlug(slug) {
            if (!this.prospectImport.headers) {
                return slug;
            }

            let index = 0;
            let newSlug = slug;

            do {
                if (index > 0) {
                    newSlug = slug + "_" + index;
                }
            } while (
                this.prospectImport.headers.find((header) => header == newSlug)
            );

            return newSlug;
        },

        /**
         *
         * @param {*} name
         */
        async addHeaderColumn(name) {
            if (this.prospectImport.source != "webservice") {
                return;
            }

            this.addingColumn = true;

            try {
                await store.dispatch(UPDATE_IMPORT, {
                    id: this.prospectImport.id,
                    headers: [
                        ...(this.prospectImport.headers
                            ? this.prospectImport.headers
                            : []),
                        this.availableHeaderSlug(this.slugify(name)),
                    ],
                    mapping: [
                        ...(this.prospectImport.mapping
                            ? this.prospectImport.mapping
                            : []),
                        null,
                    ],
                });
            } finally {
                this.addingColumn = false;
            }
        },

        /**
         *
         * @param {*} index
         * @param {*} field
         */
        async setMappingDefaultField(index, field) {
            this.addHeaderColumn(field.name).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_FIELD, {
                    column: index,
                    field: field.slug,
                });
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         * @param {*} index
         * @param {*} meta
         */
        async setMappingMetaField(index, meta) {
            this.addHeaderColumn(meta.name).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_META, {
                    column: index,
                    meta: meta.slug,
                });
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         * @param {*} index
         * @param {*} category
         */
        async setMappingCategory(index, category) {
            this.addHeaderColumn(category.name).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_CATEGORY, {
                    column: index,
                    category: category.id,
                });
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         * @param {*} index
         * @param {*} thread
         */
        async setMappingThread(index, thread) {
            this.addHeaderColumn(thread.name).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_THREAD, {
                    column: index,
                    thread: thread.id,
                });
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         * @param {*} index
         */
        async setMappingEvent(index) {
            this.addHeaderColumn(
                this.$t("import.process.tab.mapping.event")
            ).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_EVENT, index);
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         * @param {*} index
         */
        async setMappingOrder(index) {
            this.addHeaderColumn(
                this.$t("import.process.tab.mapping.order")
            ).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_ORDER, index);
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         */
        async setMappingSms(index) {
            this.addHeaderColumn(
                this.$t("import.process.tab.mapping.sms")
            ).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_SMS, index);
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         * @param {*} index
         */
        async setMappingInteraction(index) {
            this.addHeaderColumn(
                this.$t("import.process.tab.mapping.interaction")
            ).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_INTERACTION, index);
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         * @param {*} index
         */
        async setMappingLink(index) {
            this.addHeaderColumn(
                this.$t("import.process.tab.mapping.link")
            ).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_LINK, index);
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         */
        async setMappingUser(index) {
            this.addHeaderColumn(
                this.$t("import.process.tab.mapping.user")
            ).then(() => {
                store.dispatch(ADD_IMPORT_MAPPING_USER, index);
                this.column = null;
                this.tab = 0;
            });
        },

        /**
         *
         */
        removeMapping() {
            store.dispatch(REMOVE_IMPORT_MAPPING, this.column.index);
            this.column = null;
            this.tab = 0;
        },

        /**
         *
         */
        addField(column) {
            if (column) {
                this.column = column;
            }

            // store.commit(OPEN_MODAL, "field-add");
            this.newField.name = this.column
                ? this.column.header
                : this.mappingKeyword;
            this.tab = 2;
            this.newFieldCategoryTab = 0;
        },

        /**
         * Store new field
         */
        async storeField() {
            this.addingField = true;

            try {
                const field = await store.dispatch(ADD_FIELD, this.newField);
                this.setMappingMetaField(
                    this.prospectImport.source == "webservice"
                        ? this.prospectImport.headers
                            ? this.prospectImport.headers.length
                            : 0
                        : this.column.index,
                    field
                );
            } finally {
                this.addingField = false;
                this.tab = 0;
            }
        },

        /**
         *
         */
        addCategory(column) {
            if (column) {
                this.column = column;
            }

            // store.commit(OPEN_MODAL, "category-add");
            this.newCategory.name = this.column
                ? this.column.header
                : this.mappingKeyword;
            this.tab = 2;
            this.newFieldCategoryTab = 1;
        },

        /**
         *
         */
        async storeCategory() {
            this.addingCategory = true;

            try {
                const category = await store.dispatch(
                    ADD_CATEGORY,
                    this.newCategory
                );

                this.setMappingCategory(
                    this.prospectImport.source == "webservice"
                        ? this.prospectImport.headers
                            ? this.prospectImport.headers.length
                            : 0
                        : this.column.index,
                    category
                );
            } finally {
                this.addingCategory = false;
                this.tab = 0;
            }
        },

        /**
         *
         * @param {*} index
         */
        editMappingParam(index) {
            this.mappingParamToEdit = {
                index: index,
                name: this.prospectImport.headers[index],
            };

            this.tab = 2;
            this.newFieldCategoryTab = 2;
        },

        /**
         *
         */
        async updateMappingParam() {
            if (this.prospectImport.source != "webservice") {
                return;
            }

            if (!this.mappingParamToEdit) {
                return;
            }

            this.updatingMappingParam = true;

            try {
                await store.dispatch(UPDATE_IMPORT, {
                    id: this.prospectImport.id,
                    headers: this.prospectImport.headers.map((header, index) =>
                        index == this.mappingParamToEdit.index
                            ? this.mappingParamToEdit.name
                            : header
                    ),
                });
            } finally {
                this.updatingMappingParam = false;
                this.tab = 0;
            }
        },

        /**
         *
         */
        autoMapping() {
            const mapping = this.prospectImport.headers.map((header) => {
                if (!header) {
                    return null;
                }

                header = removeStringAccent(header);

                const field = this.fields.find(
                    (field) =>
                        field.slug == header ||
                        removeStringAccent(field.name) == header
                );

                if (field) {
                    if (field.meta) {
                        return "meta->" + field.slug;
                    } else {
                        return field.slug;
                    }
                } else {
                    const category = this.categories.find(
                        (category) =>
                            removeStringAccent(category.name) == header
                    );

                    if (category) {
                        return "category->" + category.id;
                    } else {
                        const thread = this.threads.find(
                            (thread) =>
                                removeStringAccent(thread.name) == header
                        );

                        if (thread) {
                            return "thread->" + thread.id;
                        } else {
                            return null;
                        }
                    }
                }
            });

            store.dispatch(UPDATE_IMPORT, {
                id: this.prospectImport.id,
                mapping: mapping,
            });
        },

        /**
         *
         * @param {*} word1
         * @param {*} word2
         */
        calculateWordsDistance(word1, word2) {
            const m = word1.length;
            const n = word2.length;
            const dp = Array.from({ length: m + 1 }, (_, i) =>
                Array.from({ length: n + 1 }, (_, j) => 0)
            );

            for (let i = 0; i <= m; i++) {
                for (let j = 0; j <= n; j++) {
                    if (i === 0) {
                        dp[i][j] = j;
                    } else if (j === 0) {
                        dp[i][j] = i;
                    } else if (word1[i - 1] === word2[j - 1]) {
                        dp[i][j] = dp[i - 1][j - 1];
                    } else {
                        dp[i][j] =
                            1 +
                            Math.min(
                                dp[i - 1][j],
                                dp[i][j - 1],
                                dp[i - 1][j - 1]
                            );
                    }
                }
            }

            return dp[m][n];
        },
    },

    watch: {
        prospectImport() {
            if (
                this.prospectImport &&
                this.prospectImport.headers &&
                (!this.prospectImport.mapping ||
                    !Array.isArray(this.prospectImport.mapping) ||
                    this.prospectImport.mapping.length == 0)
            ) {
                this.autoMapping();
            }
        },

        async tab() {
            if (this.tab == 1) {
                await this.$nextTick();

                tuto(
                    document.getElementById(
                        "hc-import-process-mapping-fields-add"
                    ),
                    "Si vous ne trouvez pas un champ à associer à votre colonne dans la liste ci-dessus,<br>vous pouvez créer un nouveau champ personnalisé<br>ou une nouvelle catégorie de filtres.",
                    "import.mapping.fields.add",
                    "Import - Créer un nouveau champ/catégorie de filtre",
                    500
                );
            }
        },
    },

    computed: {
        ...mapGetters(["fields", "categories", "prospectImport", "threads"]),

        mapping() {
            if (
                !this.prospectImport ||
                !this.prospectImport.mapping ||
                !Array.isArray(this.prospectImport.mapping)
            ) {
                return [];
            }

            return this.prospectImport.mapping;
        },

        /**
         *
         */
        filteredHeaders() {
            this.mapping;

            if (!this.prospectImport) {
                return [];
            }

            const keyword = removeStringAccent(this.columnKeyword);

            if (!this.prospectImport.headers) {
                return [];
            }

            return this.prospectImport.headers
                .map((header, index) => ({ header, index }))
                .filter(
                    ({ header }) =>
                        removeStringAccent(header).indexOf(keyword) >= 0
                );
        },

        /**
         *
         */
        filteredMappingDefaultField() {
            const keyword = removeStringAccent(this.mappingKeyword);

            return this.fields.filter(
                (field) =>
                    field.for == "prospect" &&
                    !field.meta &&
                    removeStringAccent(field.name).indexOf(keyword) >= 0 &&
                    (!this.prospectImport.mapping ||
                        this.prospectImport.mapping.indexOf(field.slug) < 0)
            );
        },

        /**
         *
         */
        filteredMappingMetaField() {
            const keyword = removeStringAccent(this.mappingKeyword);

            return this.fields.filter(
                (field) =>
                    field.for == "prospect" &&
                    field.meta &&
                    removeStringAccent(field.name).indexOf(keyword) >= 0 &&
                    (!this.prospectImport.mapping ||
                        this.prospectImport.mapping.indexOf(
                            "meta->" + field.slug
                        ) < 0)
            );
        },

        /**
         *
         */
        filteredMappingCategory() {
            const keyword = removeStringAccent(this.mappingKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    removeStringAccent(category.name).indexOf(keyword) >= 0 &&
                    (!this.prospectImport.mapping ||
                        this.prospectImport.mapping.indexOf(
                            "category->" + category.id
                        ) < 0)
            );
        },

        /**
         *
         */
        filteredMappingThread() {
            const keyword = removeStringAccent(this.mappingKeyword);

            return this.threads.filter(
                (thread) =>
                    removeStringAccent(thread.name).indexOf(keyword) >= 0 &&
                    (!this.prospectImport.mapping ||
                        this.prospectImport.mapping.indexOf(
                            "thread->" + thread.id
                        ) < 0)
            );
        },

        /**
         *
         */
        filteredMapping() {
            const items = [
                ...this.filteredMappingDefaultField.map((f) => ({
                    type: "default-field",
                    item: f,
                })),
                ...this.filteredMappingMetaField.map((f) => ({
                    type: "meta-field",
                    item: f,
                })),
                ...this.filteredMappingCategory.map((f) => ({
                    type: "category",
                    item: f,
                })),
                ...this.filteredMappingThread.map((f) => ({
                    type: "thread",
                    item: f,
                })),
                {
                    type: "events",
                    item: {
                        name: this.$t("import.process.tab.mapping.event"),
                    },
                },
                {
                    type: "orders",
                    item: {
                        name: this.$t("import.process.tab.mapping.order"),
                    },
                },
                {
                    type: "sms",
                    item: {
                        name: this.$t("import.process.tab.mapping.sms"),
                    },
                },
                {
                    type: "interactions",
                    item: {
                        name: this.$t("import.process.tab.mapping.interaction"),
                    },
                },
                {
                    type: "links",
                    item: {
                        name: this.$t("import.process.tab.mapping.link"),
                    },
                },
                {
                    type: "users",
                    item: {
                        name: this.$t("import.process.tab.mapping.user"),
                    },
                },
            ];

            if (!this.column) {
                return items;
            }

            const distances = items.map((item) => ({
                item,
                distance: this.calculateWordsDistance(
                    this.column.header,
                    item.item.name
                ),
            }));

            distances.sort((a, b) => a.distance - b.distance);

            return distances.map((distance) => distance.item);
        },
    },
};
</script>
