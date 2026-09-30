<template>
    <label
        :class="[
            'hc-default-header-cell-label',
            requiredShown ? 'required-shown' : '',
            showOptions ? 'show-options' : '',
        ]"
    >
        <div
            class="hc-default-header-cell-options"
            @click.stop="showOptions = !showOptions"
        >
            <span
                :class="[
                    'fa',
                    sortedBy && sortedAsc ? 'fa-caret-down' : 'fa-caret-up',
                ]"
                @click.prevent="sortBy"
            >
                <span>Trier</span>
                <loading :loading="sorting" />
            </span>
            <span
                v-if="can('all.project.field.update')"
                class="fa fa-cog"
                @click.prevent="edit"
            >
                <span>Modifier le champ</span>
            </span>
        </div>

        <input
            :placeholder="column.name + ' ...'"
            @focus="$emit('focus'), showRequired()"
            @blur="$emit('blur'), hideRequired(), hideOptions()"
            @change="search"
            ref="search"
        />

        <a
            :class="[
                'fa',
                'fa-asterisk',
                'hc-default-header-cell-label-required',
                isRequiredFilter
                    ? prospectsParamValue(isRequiredFilterKey) == 1
                        ? 'icon-green'
                        : 'icon-red'
                    : 'icon-grey',
            ]"
            v-tooltip="
                isRequiredFilter
                    ? prospectsParamValue(isRequiredFilterKey) == 1
                        ? 'Sans ' + column.name
                        : 'Tous'
                    : 'Avec ' + column.name
            "
            @click.prevent.stop="toggleWithField"
        ></a>

        <a
            v-if="!column.meta && column.id == 'street'"
            :class="[
                'fa',
                'fa-check',
                'hc-default-header-cell-label-valid',
                isValidFilter
                    ? prospectsParamValue(isValidFilterKey) == 1
                        ? 'icon-red'
                        : 'icon-grey'
                    : 'icon-green',
            ]"
            v-tooltip="
                isValidFilter
                    ? prospectsParamValue(isValidFilterKey) == 1
                        ? column.name + ' non valide'
                        : 'Tous'
                    : column.name + ' valide'
            "
            @click.prevent.stop="toggleValidField"
        ></a>

        <span v-text="column.name"></span>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    SET_PROSPECTS_SORT_ORDER,
    SET_PROSPECTS_SORT_BY,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_FIELD } from "@/actions/project/field";

export default {
    props: {
        /**
         * Column
         */
        column: {
            type: Object,
        },
    },

    data() {
        return {
            sorting: false,
            requiredShown: false,
            showOptions: false,
        };
    },

    methods: {
        /**
         * Sort prospects list by
         * the current field
         */
        async sortBy() {
            this.sorting = true;

            try {
                // Sort by the current field (column.key)
                await store.commit(SET_PROSPECTS_SORT_BY, this.column.key);

                // Toggle sort order
                await store.commit(
                    SET_PROSPECTS_SORT_ORDER,
                    this.sortedAsc ? "desc" : "asc"
                );

                // Update prospects list
                await store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.sorting = false;
            }
        },

        /**
         * Perform a prospects search
         * from the current field
         * using the entered keyword
         * @param {*} e
         */
        async search(e) {
            const value = e.target.value;
            e.target.value = "";

            try {
                // Perform search when keyword is given
                if (value) {
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.searchKey,
                        value: value,
                        multiple: false,
                    });
                    // else cancel search
                } else {
                    await store.commit(REMOVE_PROSPECT_PARAMS, {
                        key: this.searchKey,
                    });
                }

                // Update prospects list
                store.dispatch(FETCH_PROSPECTS);
            } finally {
            }
        },

        /**
         * Edit current field
         */
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },

        /**
         *
         */
        async toggleWithField() {
            this.$refs.search.focus();

            try {
                // Perform search when keyword is given
                if (!this.isRequiredFilter) {
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.isRequiredFilterKey,
                        value: 1,
                        multiple: false,
                    });
                    // else cancel search
                } else if (
                    this.prospectsParamValue(this.isRequiredFilterKey) == 1
                ) {
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.isRequiredFilterKey,
                        value: 0,
                        multiple: false,
                    });
                } else {
                    await store.commit(REMOVE_PROSPECT_PARAMS, {
                        key: this.isRequiredFilterKey,
                    });
                }

                // Update prospects list
                store.dispatch(FETCH_PROSPECTS);
            } finally {
            }
        },

        /**
         *
         */
        async toggleValidField() {
            this.$refs.search.focus();

            try {
                // Perform search when keyword is given
                if (!this.isValidFilter) {
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.isValidFilterKey,
                        value: 1,
                        multiple: false,
                    });
                    // else cancel search
                } else if (
                    this.prospectsParamValue(this.isValidFilterKey) == 1
                ) {
                    await store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.isValidFilterKey,
                        value: 0,
                        multiple: false,
                    });
                } else {
                    await store.commit(REMOVE_PROSPECT_PARAMS, {
                        key: this.isValidFilterKey,
                    });
                }

                // Update prospects list
                store.dispatch(FETCH_PROSPECTS);
            } finally {
            }
        },

        /**
         * Show asterisk sign for required search
         */
        showRequired() {
            this.requiredShown = true;
        },

        /**
         * Hide asterisk sign for required search
         */
        hideRequired() {
            setTimeout(() => {
                this.requiredShown = false;
            }, 200);
        },

        /**
         */
        hideOptions() {
            setTimeout(() => {
                this.showOptions = false;
            }, 200);
        },
    },

    computed: {
        ...mapGetters([
            "prospectsParams",
            "fields",
            "prospectsSortOrder",
            "prospectsSortBy",
            "prospectsParamValue",
            "prospectsParamExists",
            "can",
        ]),

        /**
         * Check if current prospects list
         * is sorted by the current field
         */
        sortedBy() {
            return this.prospectsSortBy == this.column.key;
        },

        /**
         * Get sort order
         */
        sortedAsc() {
            return this.prospectsSortOrder == "asc";
        },

        /**
         * Gest the prospect param
         * for the current field search
         */
        searchKey() {
            return this.column.category == "meta"
                ? "meta_" + this.column.id
                : "field_" + this.column.id;
        },

        /**
         * Current field
         */
        field() {
            return this.fields.find(
                (f) =>
                    f.for == "prospect" &&
                    (this.column.category == "meta"
                        ? f.meta && f.slug == this.column.id
                        : !f.meta && f.slug == this.column.id)
            );
        },

        isRequiredFilterKey() {
            return (
                "with" +
                (this.column.category == "meta" ? "Meta_" : "Field_") +
                this.column.id
            );
        },

        isRequiredFilter() {
            return this.prospectsParamExists(this.isRequiredFilterKey);
        },

        isValidFilterKey() {
            return "validAddress";
        },

        isValidFilter() {
            return this.prospectsParamExists(this.isValidFilterKey);
        },
    },
};
</script>
