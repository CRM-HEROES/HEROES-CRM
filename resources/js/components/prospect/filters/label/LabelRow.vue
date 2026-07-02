<template>
    <item
        tag="label"
        v-tooltip="label.name"
        class="hc-prospects-table-filter-label"
    >
        <icon class="fa fa-tag" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <icon
            v-if="can('all.project.category.label.update') && showLabelSetting"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
            :size="30"
        />
        <div
            v-if="label.prospects_count"
            class="hc-item-count"
            v-text="label.prospects_count"
            @click.prevent.stop="search"
        ></div>
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :size="30"
            :title="
                $t('prospect.table.filters.label.without_label', {
                    label: label.name,
                })
            "
            @click.prevent="toggleExclude"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-prospects-table-filter-label {
    height: 34px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_PROSPECTS,
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
} from "@/actions/project/prospect";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    props: {
        label: {
            type: Object,
        },
        showLabelSetting: {
            type: Boolean,
        },
    },

    methods: {
        change(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded ? ADD_PROSPECT_PARAMS : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded ? REMOVE_PROSPECT_PARAMS : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },

        edit() {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },

        /**
         *
         */
        search() {
            if (this.$route.name == "prospect") {
                store.commit(INIT_PROSPECT_PARAMS);

                store.commit(SET_PROSPECT_PARAMS, this.filter);
                store.dispatch(FETCH_PROSPECTS);
            } else {
                this.$router.push({
                    name: "prospect",
                    params: {
                        project: this.project.slug,
                    },
                    query: this.query,
                });
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospectsParamExists",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKey() {
            return "withCategory_" + this.label.category_id;
        },

        /**
         *
         */
        withoutKey() {
            return "withoutCategory_" + this.label.category_id;
        },

        /**
         *
         */
        value() {
            return this.label.id;
        },

        /**
         *
         */
        isExcluded() {
            return this.prospectsParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isFiltered() {
            return (
                this.prospectsParamExists(this.withKey, this.value) ||
                this.prospectsParamExists(this.withoutKey, this.value)
            );
        },

        /**
         *
         */
        isChecked() {
            return (
                this.isFiltered ||
                (this.prospectsParamExists(this.withKey) &&
                    this.prospectsParamValue(this.withKey).length == 0)
            );
        },

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },

        /**
         *
         */
        excludeStyle() {
            return {
                color: this.isExcluded ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filter() {
            return {
                [this.withKey]: [this.label.id],
            };
        },

        /**
         *
         */
        query() {
            return {
                filters: JSON.stringify(this.filter),
            };
        },
    },
};
</script>
