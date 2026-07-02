<template>
    <modal
        name="field-add"
        :title="$t('field.add.title')"
        @open="fetchDefaultFields"
        @first-open="setFieldForTab"
        :width="400"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <tab
                        :count="6"
                        :tab="defaultFieldTab"
                        @tab:change="(tab) => (defaultFieldTab = tab)"
                    >
                        <template #1>{{ $t("prospects") }}</template>
                        <template #2>{{ $t("projects") }}</template>
                        <template #3>{{ $t("users") }}</template>
                        <template #4>{{ $t("products") }}</template>
                        <template #5>{{ $t("orders") }}</template>
                        <template #6>{{ $t("events") }}</template>
                    </tab>
                    <tab-layout
                        :count="6"
                        :tab="defaultFieldTab"
                        class="hc-flex-1"
                    >
                        <template #1>
                            <div class="hc-flex-column" style="height: 100%">
                                <search v-model="defaultFieldKeyword" />
                                <item-list class="hc-flex-1" padding="5px">
                                    <existing-field-row
                                        v-for="field in filteredProspectExistingFields"
                                        :key="field.id"
                                        :field="field"
                                        @click="removeField(field)"
                                    />
                                    <default-field-row
                                        v-for="defaultField in filteredProspectDefaultFields"
                                        :key="defaultField.id"
                                        :field="defaultField"
                                        @click="storeDefaultField(defaultField)"
                                    />
                                </item-list>
                                <buttons v-if="can('all.project.field.add')">
                                    <a
                                        @click.prevent="
                                            (tab = 1), (field.for = 'prospect')
                                        "
                                        v-text="$t('add')"
                                    ></a>
                                </buttons>
                            </div>
                        </template>
                        <template #2>
                            <div class="hc-flex-column" style="height: 100%">
                                <search v-model="defaultFieldKeyword" />
                                <item-list class="hc-flex-1" padding="5px">
                                    <default-field-row
                                        v-for="defaultField in filteredProjectDefaultFields"
                                        :key="defaultField.id"
                                        :field="defaultField"
                                        @click="storeDefaultField(defaultField)"
                                    />
                                </item-list>
                                <buttons v-if="can('all.project.field.add')">
                                    <a
                                        @click.prevent="
                                            (tab = 1), (field.for = 'project')
                                        "
                                        v-text="$t('add')"
                                    ></a>
                                </buttons>
                            </div>
                        </template>
                        <template #3>
                            <div class="hc-flex-column" style="height: 100%">
                                <search v-model="defaultFieldKeyword" />
                                <item-list class="hc-flex-1" padding="5px">
                                    <default-field-row
                                        v-for="defaultField in filteredUserDefaultFields"
                                        :key="defaultField.id"
                                        :field="defaultField"
                                        @click="storeDefaultField(defaultField)"
                                    />
                                </item-list>
                                <buttons v-if="can('all.project.field.add')">
                                    <a
                                        @click.prevent="
                                            (tab = 1), (field.for = 'user')
                                        "
                                        v-text="$t('add')"
                                    ></a>
                                </buttons>
                            </div>
                        </template>
                        <template #4>
                            <div class="hc-flex-column" style="height: 100%">
                                <search v-model="defaultFieldKeyword" />
                                <item-list class="hc-flex-1" padding="5px">
                                    <default-field-row
                                        v-for="defaultField in filteredProductDefaultFields"
                                        :key="defaultField.id"
                                        :field="defaultField"
                                        @click="storeDefaultField(defaultField)"
                                    />
                                </item-list>
                                <buttons v-if="can('all.project.field.add')">
                                    <a
                                        @click.prevent="
                                            (tab = 1), (field.for = 'product')
                                        "
                                        v-text="$t('add')"
                                    ></a>
                                </buttons>
                                <loading :loading="addingDefaultField" />
                            </div>
                        </template>
                        <template #5>
                            <div class="hc-flex-column" style="height: 100%">
                                <search v-model="defaultFieldKeyword" />
                                <item-list class="hc-flex-1" padding="5px">
                                    <default-field-row
                                        v-for="defaultField in filteredOrderDefaultFields"
                                        :key="defaultField.id"
                                        :field="defaultField"
                                        @click="storeDefaultField(defaultField)"
                                    />
                                </item-list>
                                <buttons v-if="can('all.project.field.add')">
                                    <a
                                        @click.prevent="
                                            (tab = 1), (field.for = 'order')
                                        "
                                        v-text="$t('add')"
                                    ></a>
                                </buttons>
                                <loading :loading="addingDefaultField" />
                            </div>
                        </template>
                        <template #6>
                            <div class="hc-flex-column" style="height: 100%">
                                <search v-model="defaultFieldKeyword" />
                                <item-list class="hc-flex-1" padding="5px">
                                    <default-field-row
                                        v-for="defaultField in filteredEventDefaultFields"
                                        :key="defaultField.id"
                                        :field="defaultField"
                                        @click="storeDefaultField(defaultField)"
                                    />
                                </item-list>
                                <buttons v-if="can('all.project.field.add')">
                                    <a
                                        @click.prevent="
                                            (tab = 1), (field.for = 'event')
                                        "
                                        v-text="$t('add')"
                                    ></a>
                                </buttons>
                                <loading :loading="addingDefaultField" />
                            </div>
                        </template>
                    </tab-layout>
                </div>
            </template>

            <template #2>
                <form
                    class="hc-flex-column"
                    style="height: 100%; overflow: auto"
                    @submit.prevent="storeField"
                >
                    <item class="bordered" @click="tab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('field.add.title')"
                        ></div>
                    </item>
                    <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                        <v-field :label="$t('name')" required v-slot="p"
                            ><input
                                ref="fieldName"
                                required
                                :placeholder="p.label + ' ...'"
                                v-model="field.name"
                        /></v-field>
                        <v-field :label="$t('description')" v-slot="p">
                            <textarea
                                v-model="field.description"
                                :placeholder="p.label + ' ...'"
                            ></textarea>
                        </v-field>
                        <v-field :label="$t('for')" required>
                            <select v-model="field.for">
                                <option
                                    value="prospect"
                                    v-text="$t('prospects')"
                                ></option>
                                <option
                                    value="project"
                                    v-text="$t('projects')"
                                ></option>
                                <option
                                    value="user"
                                    v-text="$t('users')"
                                ></option>
                                <option
                                    value="product"
                                    v-text="$t('products')"
                                ></option>
                                <option
                                    value="event"
                                    v-text="$t('events')"
                                ></option>
                                <option
                                    value="order"
                                    v-text="$t('orders')"
                                ></option>
                                <option
                                    value="order-product"
                                    v-text="
                                        $t('orders') +
                                        '(' +
                                        $t('products') +
                                        ')'
                                    "
                                ></option>
                            </select>
                        </v-field>
                        <v-field :label="$t('type')" required>
                            <select v-model="field.type">
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
                        <template v-if="field.for == 'prospect'">
                            <v-field
                                :label="$t('field.default_value')"
                                v-slot="p"
                                ><input
                                    :placeholder="p.label + ' ...'"
                                    v-model="field.default_value"
                            /></v-field>
                            <v-field :label="$t('field.format')" v-slot="p">
                                <select v-model="field.format">
                                    <option :value="null">Aucun</option>
                                    <option value="capitalize">
                                        Capitaliser
                                    </option>
                                    <option value="uppercase">Majuscule</option>
                                    <option value="lowercase">Miniscule</option>
                                </select>
                            </v-field>
                            <item tag="label" style="padding-right: 5px">
                                <icon class="fa fa-search" :style="style" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('field.required')"
                                ></div>
                                <checkbox v-model="field.required" />
                            </item>
                            <item tag="label" style="padding-right: 5px">
                                <icon class="fa fa-search" :style="style" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('field.unique')"
                                ></div>
                                <checkbox v-model="field.unique" />
                            </item>
                            <item
                                v-id="field.for == 'prospect'"
                                tag="label"
                                style="padding-right: 5px"
                            >
                                <icon class="fa fa-search" :style="style" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('field.add_to_global_search')"
                                ></div>
                                <checkbox v-model="field.searchable" />
                            </item>
                        </template>
                    </item-list>
                    <buttons>
                        <button v-text="$t('add')"></button>
                    </buttons>
                    <loading :loading="addingField" />
                </form>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_DEFAULT_FIELDS } from "@/actions/default-field";
import { CLOSE_MODAL } from "@/actions/modal";
import { ADD_FIELD, REMOVE_FIELD } from "@/actions/project/field";

// Components
import DefaultFieldRow from "./DefaultFieldRow.vue";
import ExistingFieldRow from "./ExistingFieldRow.vue";

export default {
    components: {
        DefaultFieldRow,
        ExistingFieldRow,
    },

    data() {
        return {
            tab: 0,
            defaultFieldTab: 0,
            field: this.newField(),
            fetchingDefaultFields: false,
            addingField: false,
            addingDefaultField: false,
            defaultFieldKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        newField() {
            return {
                name: "",
                description: "",
                for: "prospect",
                meta: true,
                type: "text",
                default_value: null,
                required: false,
                unique: false,
                searchable: false,
                format: null,
            };
        },

        /**
         *
         */
        async fetchDefaultFields() {
            this.fetchingDefaultFields = true;

            try {
                await store.dispatch(FETCH_DEFAULT_FIELDS);
            } finally {
                this.fetchingDefaultFields = false;
            }
        },

        /**
         * Store new field
         */
        async storeField() {
            this.addingField = true;

            try {
                await store.dispatch(ADD_FIELD, this.field);
            } finally {
                this.addingField = false;
                store.commit(CLOSE_MODAL);
                this.field = this.newField();
                this.$refs.fieldName.focus();
            }
        },

        /**
         * Store default field
         */
        async storeDefaultField(field) {
            this.addingDefaultField = true;

            try {
                await store.dispatch(ADD_FIELD, {
                    ...field,
                    meta: false,
                });
            } finally {
                this.addingDefaultField = false;
            }
        },

        /***
         *
         */
        async removeField(field) {
            this.removingField = true;

            try {
                await store.dispatch(REMOVE_FIELD, field.id);
            } finally {
                this.removingField = false;
            }
        },

        /**
         *
         */
        filteredDefaultFields(fieldFor) {
            const keyword = removeStringAccent(this.defaultFieldKeyword);

            return this.defaultFields.filter(
                (defaultField) =>
                    defaultField.for == fieldFor &&
                    !this.fields.find(
                        (f) =>
                            f.slug == defaultField.slug &&
                            !f.meta &&
                            f.for == defaultField.for
                    ) &&
                    removeStringAccent(defaultField.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredExistingFields(fieldFor) {
            const keyword = removeStringAccent(this.defaultFieldKeyword);

            return this.fields.filter(
                (f) =>
                    f.for == fieldFor &&
                    removeStringAccent(f.name).indexOf(keyword) >= 0
            );
        },

        setFieldForTab() {
            if (this.newFieldFor == "prospect") {
                this.defaultFieldTab = 0;
            } else if (this.newFieldFor == "project") {
                this.defaultFieldTab = 1;
            } else if (this.newFieldFor == "user") {
                this.defaultFieldTab = 2;
            } else if (this.newFieldFor == "product") {
                this.defaultFieldTab = 3;
            } else if (this.newFieldFor == "order") {
                this.defaultFieldTab = 4;
            }

            if (this.newFieldFor != "prospect") {
                this.tab = 1;
            }

            this.field.for = this.newFieldFor;
        },
    },

    watch: {
        newFieldFor(newValue) {
            this.setFieldForTab();
        },
    },

    computed: {
        ...mapGetters(["defaultFields", "fields", "newFieldFor", "can"]),

        /**
         *
         */
        filteredProspectDefaultFields() {
            return this.filteredDefaultFields("prospect");
        },

        /**
         *
         */
        filteredProspectExistingFields() {
            return this.filteredExistingFields("prospect");
        },

        /**
         *
         */
        filteredProjectDefaultFields() {
            return this.filteredDefaultFields("project");
        },

        /**
         *
         */
        filteredUserDefaultFields() {
            return this.filteredDefaultFields("user");
        },

        /**
         *
         */
        filteredProductDefaultFields() {
            return this.filteredDefaultFields("product");
        },

        /**
         *
         */
        filteredOrderDefaultFields() {
            return this.filteredDefaultFields("order");
        },

        /**
         *
         */
        filteredEventDefaultFields() {
            return this.filteredDefaultFields("event");
        },
    },
};
</script>
