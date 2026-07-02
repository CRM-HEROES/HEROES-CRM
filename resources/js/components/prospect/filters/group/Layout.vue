<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="groupKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-prospects-table-filter-group">
                <icon class="fa fa-filter" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.filters.groups.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeGroupStyle"
                    :title="$t('prospect.table.filters.groups.without')"
                    @click.prevent="toggleExcludeGroup"
                />
                <checkbox :model-value="isCheckedGroup" @change="changeGroup" />
            </item>
            <group-row
                v-for="group in filteredGroups"
                :key="group.id"
                :group="group"
            />
        </item-list>
        <buttons v-if="can('all.project.group.add')">
            <a @click.prevent="addGroup" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import GroupRow from "./GroupRow.vue";

export default {
    components: {
        GroupRow,
    },

    data() {
        return {
            tab: 0,
            groupKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addGroup() {
            store.commit(OPEN_MODAL, "group-add");
        },

        changeGroup(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyGroup,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyGroup,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        toggleExcludeGroup() {
            // Add or remove with param
            store.commit(
                this.isExcludedGroup
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyGroup,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedGroup
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyGroup,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },
    },

    computed: {
        ...mapGetters(["groups", "prospectsParamValue", "can"]),

        /**
         *
         */
        withKeyGroup() {
            return "withGroups";
        },

        /**
         *
         */
        withoutKeyGroup() {
            return "withoutGroups";
        },

        /**
         *
         */
        isCheckedGroup() {
            return (
                this.prospectsParamValue(this.withKeyGroup) === "" ||
                this.prospectsParamValue(this.withoutKeyGroup) === ""
            );
        },

        /**
         *
         */
        isExcludedGroup() {
            return this.prospectsParamValue(this.withoutKeyGroup) === "";
        },

        /**
         *
         */
        excludeGroupStyle() {
            return {
                color: this.isExcludedGroup ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredGroups() {
            const keyword = removeStringAccent(this.groupKeyword);

            return this.groups.filter(
                (group) => removeStringAccent(group.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
