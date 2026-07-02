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
                :value="isGroupChecked(group)"
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
    BULK_ADD_USER_GROUP,
    BULK_REMOVE_USER_GROUP,
    ADD_USER_GROUP,
    REMOVE_USER_GROUP,
} from "@/actions/project/user/group";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import GroupRow from "./GroupRow.vue";

export default {
    components: {
        GroupRow,
    },

    data() {
        return {
            groupKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} group
         */
        isGroupChecked(group) {
            return (
                // this.can("all") ||
                this.userGroups.findIndex((l) => l.id == group.id) >= 0
            );
        },

        /**
         *
         */
        addGroup() {
            store.commit(OPEN_MODAL, "group-add");
        },
    },

    computed: {
        ...mapGetters(["groups", "user", "userGroups", "can"]),

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
                return (
                    // this.can("all") ||
                    this.userGroups.length == this.groups.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.groups.forEach((f) => {
                        store.commit(ADD_USER_GROUP, f);
                    });
                    store.dispatch(BULK_ADD_USER_GROUP, {
                        users: [this.user.id],
                        groups: this.groups.map((f) => f.id),
                    });
                } else {
                    this.groups.forEach((f) => {
                        store.commit(REMOVE_USER_GROUP, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_GROUP, {
                        users: [this.user.id],
                        groups: this.groups.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
