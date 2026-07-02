<template>
    <label
        :class="[
            'hc-default-header-cell-label',
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
            </span>
            <!--span
                v-if="can('all.project.field.update')"
                class="fa fa-cog"
                @click.prevent.stop="edit"
            >
                <span>Modifier le champ</span>
            </span-->
        </div>

        <input
            :placeholder="column.name + ' ...'"
            @change="search"
            @blur="hideOptions()"
            :value="projectsParams[searchKey]"
        />
        <span v-text="column.name"></span>
    </label>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    ADD_PROJECT_PARAMS,
    REMOVE_PROJECT_PARAMS,
    SET_PROJECTS_SORT_ORDER,
    SET_PROJECTS_SORT_BY,
    FETCH_PROJECTS,
} from "@/actions/project";

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
            showOptions: false,
        };
    },

    methods: {
        /**
         * Sort projects list by
         * the current field
         */
        async sortBy() {
            // Toggle sort order
            await store.commit(
                SET_PROJECTS_SORT_ORDER,
                this.sortedAsc ? "desc" : "asc"
            );

            // Sort by the current field (column.key)
            await store.commit(SET_PROJECTS_SORT_BY, this.column.key);

            // Update projects list
            store.dispatch(FETCH_PROJECTS);
        },

        /**
         * Perform a projects search
         * from the current field
         * using the entered keyword
         * @param {*} e
         */
        async search(e) {
            const value = e.target.value;

            // Perform search when keyword is given
            if (value) {
                await store.commit(ADD_PROJECT_PARAMS, {
                    key: this.searchKey,
                    value: value,
                    multiple: false,
                });
                // else cancel search
            } else {
                await store.commit(REMOVE_PROJECT_PARAMS, {
                    key: this.searchKey,
                });
            }

            // Update projects list
            store.dispatch(FETCH_PROJECTS);
        },

        /**
         * Edit current field
         */
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
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
            "projectsParams",
            "fields",
            "projectsSortOrder",
            "projectsSortBy",
            "can",
        ]),

        /**
         * Check if current projects list
         * is sorted by the current field
         */
        sortedBy() {
            return this.projectsSortBy == this.column.key;
        },

        /**
         * Get sort order
         */
        sortedAsc() {
            return this.projectsSortOrder == "asc";
        },

        /**
         * Gest the project param
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
                    f.for == "project" &&
                    (this.column.category == "meta"
                        ? f.meta && f.slug == this.column.id
                        : !f.meta && f.slug == this.column.id)
            );
        },
    },
};
</script>
