<template>
    <item tag="label">
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="label.name"></div>
        <icon
            v-if="can('all.project.category.label.update')"
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
import { SET_LABEL } from "@/actions/project/label";
import {
    ADD_IMPORT_LABEL,
    REMOVE_IMPORT_LABEL,
} from "@/actions/project/import";

export default {
    props: {
        label: {
            type: Object,
        },
        isChecked: {
            type: Boolean,
        },
    },

    methods: {
        toggleRelation(e) {
            if (e.target.checked) {
                store.dispatch(ADD_IMPORT_LABEL, this.label.id);
            } else {
                store.dispatch(REMOVE_IMPORT_LABEL, this.label.id);
            }
        },

        edit(e) {
            store.commit(OPEN_MODAL, "label-update");
            store.commit(SET_LABEL, this.label);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },
    },
};
</script>
