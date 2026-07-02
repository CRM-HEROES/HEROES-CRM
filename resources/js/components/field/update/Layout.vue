<template>
    <form
        class="hc-flex-column"
        style="height: 100%; overflow: auto"
        v-if="fieldToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('name')" required v-slot="p"
                ><input
                    ref="fieldName"
                    required
                    :placeholder="p.field + ' ...'"
                    v-model="fieldToUpdate.name"
            /></v-field>
            <template v-if="field.meta">
                <v-field :label="$t('description')" v-slot="p">
                    <textarea
                        v-model="fieldToUpdate.description"
                        :placeholder="p.label + ' ...'"
                    ></textarea>
                </v-field>
                <v-field :label="$t('type')" required>
                    <select v-model="fieldToUpdate.type">
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
            </template>
            <v-field :label="$t('field.default_value')" v-slot="p"
                ><input
                    :placeholder="p.label + ' ...'"
                    v-model="fieldToUpdate.default_value"
            /></v-field>
            <v-field :label="$t('field.format')" v-slot="p">
                <select v-model="fieldToUpdate.format">
                    <option :value="null">Aucun</option>
                    <option value="capitalize">Capitaliser</option>
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
                <checkbox v-model="fieldToUpdate.required" />
            </item>
            <item tag="label" style="padding-right: 5px">
                <icon class="fa fa-check" :style="style" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('field.unique')"
                ></div>
                <checkbox v-model="fieldToUpdate.unique" />
            </item>
            <item
                tag="label"
                style="padding-right: 5px"
                v-if="field.for == 'prospect'"
            >
                <icon class="fa fa-search" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('field.add_to_global_search')"
                ></div>
                <checkbox
                    v-model="fieldToUpdate.searchable"
                    @change="toggleSearchable"
                />
            </item>
        </item-list>
        <buttons>
            <button
                v-if="can('all.project.field.delete')"
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button
                v-if="field.meta"
                @click.prevent="toCategory"
                class="hc-button-warning"
                v-text="$t('to_category')"
                v-tuto="{
                    key: 'project.field.to-category',
                    name: 'Champ - Transformer en filtre',
                    body: 'Transformez un champ en une catégorie de filtres.',
                    timeout: 500,
                }"
            ></button>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading
            :loading="
                updatingField ||
                removingField ||
                turningIntoCategory ||
                fetchingProspectsCount
            "
        />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectService from "@/apis/project/prospect";

// Actions
import {
    FIELD_TO_CATEGORY,
    SHOW_FIELD,
    UPDATE_FIELD,
    REMOVE_FIELD,
} from "@/actions/project/field";
import { GET_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            updatingField: false,
            removingField: false,
            fetchingField: false,
            turningIntoCategory: false,
            fetchingProspectsCount: false,
            fieldToUpdate: this.field,
        };
    },

    created() {
        this.fieldToUpdate = this.field;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingField = true;

            try {
                await store.dispatch(UPDATE_FIELD, this.fieldToUpdate);
            } finally {
                this.updatingField = false;
                store.commit(CLOSE_MODAL);
            }
        },

        toggleSearchable() {
            const { id, searchable } = this.fieldToUpdate;
            store.dispatch(UPDATE_FIELD, { id, searchable });
        },

        /**
         *
         */
        async remove() {
            this.fetchingProspectsCount = true;

            const action = async () => {
                this.removingField = true;

                try {
                    await store.dispatch(REMOVE_FIELD, this.fieldToUpdate.id);
                } finally {
                    this.removingField = false;
                    store.commit(CLOSE_MODAL);
                }
            };

            try {
                const { data } = await ProspectService.count(
                    this.project.slug,
                    {
                        params: {
                            filters: JSON.stringify({
                                [(this.fieldToUpdate.meta
                                    ? "meta_"
                                    : "field_") + this.fieldToUpdate.slug]: "",
                            }),
                        },
                    }
                );

                if (data) {
                    hcConfirm(
                        this.$t("field.update.delete_confirm", { count: data }),
                        action
                    );
                } else {
                    action();
                }
            } finally {
                this.fetchingProspectsCount = false;
            }
        },

        /**
         *
         */
        toCategory() {
            hcConfirm(this.$t("to_category_confirm"), async () => {
                this.turningIntoCategory = true;

                try {
                    await store.dispatch(
                        FIELD_TO_CATEGORY,
                        this.fieldToUpdate.id
                    );

                    store.dispatch(GET_PROJECT_USER_SETTING, {
                        key: "prospects-table",
                    });
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.turningIntoCategory = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async field(newValue) {
            this.fieldToUpdate = newValue;
            this.fetchingField = true;

            try {
                this.fieldToUpdate = await store.dispatch(
                    SHOW_FIELD,
                    newValue.id
                );
            } finally {
                this.fetchingField = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "field", "can"]),
    },
};
</script>
