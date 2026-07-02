<template>
    <item tag="label">
        <icon class="fa fa-users" :style="style" />
        <div class="hc-item-main-content" v-text="group.name"></div>
        <icon
            v-if="can('all.project.group.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="group.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_GROUP } from "@/actions/project/group";
import { UPDATE_PROSPECT_BULK_GROUPS } from "@/actions/project/prospect";

export default {
    props: {
        group: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "group-update");
            store.commit(SET_GROUP, this.group);
        },
    },

    computed: {
        ...mapGetters(["prospectBulkGroups", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.group.bgcolor,
            };
        },

        selected: {
            get() {
                return this.prospectBulkGroups;
            },
            set(value) {
                store.commit(UPDATE_PROSPECT_BULK_GROUPS, value);
            },
        },
    },
};
</script>
