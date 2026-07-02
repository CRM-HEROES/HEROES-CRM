<template>
    <item tag="label">
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="group.name"></div>
        <icon
            v-if="can('all.project.group.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <checkbox :model-value="isChecked" @change="toggleRelation" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_GROUP } from "@/actions/project/group";
import {
    ADD_IMPORT_GROUP,
    REMOVE_IMPORT_GROUP,
} from "@/actions/project/import";

export default {
    props: {
        group: {
            type: Object,
        },
        isChecked: {
            type: Boolean,
        },
    },

    methods: {
        toggleRelation(e) {
            if (e.target.checked) {
                store.dispatch(ADD_IMPORT_GROUP, this.group.id);
            } else {
                store.dispatch(REMOVE_IMPORT_GROUP, this.group.id);
            }
        },

        edit(e) {
            store.commit(OPEN_MODAL, "group-update");
            store.commit(SET_GROUP, this.group);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.group.bgcolor,
            };
        },
    },
};
</script>
