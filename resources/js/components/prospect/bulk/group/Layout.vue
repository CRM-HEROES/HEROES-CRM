<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="groupKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <group-row
                v-for="group in filteredGroups"
                :key="group.id"
                :group="group"
            />
        </item-list>
        <buttons>
            <button
                @click.prevent="attach"
                :disabled="disabled"
                v-text="$t('prospect.table.bulk.add')"
            ></button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
                v-text="$t('prospect.table.bulk.remove')"
            ></button>
            <a
                v-if="can('all.project.group.add')"
                @click.prevent="addGroup"
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
    BULK_ADD_PROSPECT_GROUP,
    BULK_REMOVE_PROSPECT_GROUP,
} from "@/actions/project/prospect/group";
import {
    FETCH_PROSPECTS,
    UPDATE_SELECTED_PROSPECTS,
    UPDATE_PROSPECT_BULK_GROUPS,
} from "@/actions/project/prospect";
import { FETCH_GROUPS } from "@/actions/project/group";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import GroupRow from "./GroupRow.vue";

export default {
    components: {
        GroupRow,
    },

    data() {
        return {
            bulking: false,
            groupKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} group
         */
        fetchGroups() {
            store.dispatch(FETCH_GROUPS);
        },

        /**
         *
         */
        addGroup() {
            store.commit(OPEN_MODAL, "group-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const prospectsSelected = this.prospectsSelected;
            store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                await store.dispatch(BULK_ADD_PROSPECT_GROUP, {
                    prospects: prospectsSelected,
                    groups: this.prospectBulkGroups,
                });
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_PROSPECT_BULK_GROUPS, []);
                store.commit(CLOSE_SLIDE, "prospect-bulk-manage-groups");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const prospectsSelected = this.prospectsSelected;
            store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                await store.dispatch(BULK_REMOVE_PROSPECT_GROUP, {
                    prospects: prospectsSelected,
                    groups: this.prospectBulkGroups,
                });
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_PROSPECT_BULK_GROUPS, []);
                store.commit(CLOSE_SLIDE, "prospect-bulk-manage-groups");
            }
        },
    },

    computed: {
        ...mapGetters([
            "groups",
            "prospectsSelected",
            "prospectBulkGroups",
            "can",
        ]),

        /**
         *
         */
        disabled() {
            return this.prospectBulkGroups.length == 0;
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

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredGroups) {
                    if (
                        !this.prospectBulkGroups.find(
                            (group) => group == this.filteredGroups[i].id
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
                        UPDATE_PROSPECT_BULK_GROUPS,
                        this.filteredGroups.map((group) => group.id)
                    );
                } else {
                    store.commit(UPDATE_PROSPECT_BULK_GROUPS, []);
                }
            },
        },
    },
};
</script>
