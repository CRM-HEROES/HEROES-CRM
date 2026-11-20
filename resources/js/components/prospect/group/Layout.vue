<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="groupKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <selection-list
                v-model="all"
                :items="filteredGroups"
                :selected="prospectGroups"
                :is-selected="isGroupChecked"
                :render-when-empty="true"
            >
                <template #default="{ sortedItems }">
                    <group-row
                        v-for="group in sortedItems"
                        :key="group.id"
                        :group="group"
                        :value="isGroupChecked(group)"
                    />
                </template>
            </selection-list>
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
    FETCH_PROSPECT_GROUPS,
    BULK_ADD_PROSPECT_GROUP,
    BULK_REMOVE_PROSPECT_GROUP,
    ADD_PROSPECT_GROUP,
    REMOVE_PROSPECT_GROUP,
} from "@/actions/project/prospect/group";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import GroupRow from "./GroupRow.vue";
import SelectionList from "../filters/SelectionList.vue";

export default {
    components: {
        GroupRow,
        SelectionList,
    },

    data() {
        return {
            name: "prospect-manage-groups",
            tab: 0,
            groupKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} group
         */
        isGroupChecked(group) {
            return this.prospectGroups.findIndex((l) => l.id == group.id) >= 0;
        },

        /**
         *
         */
        addGroup() {
            store.commit(OPEN_MODAL, "group-add");
        },
    },

    watch: {
        prospect(newValue) {
            if (this.slideOpen(this.name)) {
                store.dispatch(FETCH_PROSPECT_GROUPS, newValue);
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "groups",
            "prospect",
            "prospectFullName",
            "prospectGroups",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        filteredGroups() {
            const keyword = removeStringAccent(this.groupKeyword);

            return this.groups.filter(
                (group) => removeStringAccent(group.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredGroups) {
                    if (
                        !this.prospectGroups.find(
                            (group) => group.id == this.filteredGroups[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    this.filteredGroups.forEach((f) => {
                        store.commit(ADD_PROSPECT_GROUP, f);
                    });
                    store.dispatch(BULK_ADD_PROSPECT_GROUP, {
                        prospects: [this.prospect.id],
                        groups: this.filteredGroups.map((f) => f.id),
                    });
                } else {
                    this.filteredGroups.forEach((f) => {
                        store.commit(REMOVE_PROSPECT_GROUP, f);
                    });
                    store.dispatch(BULK_REMOVE_PROSPECT_GROUP, {
                        prospects: [this.prospect.id],
                        groups: this.filteredGroups.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
