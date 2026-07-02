<template>
    <field-template type="prospect-label" :content="label.id">
        <item class="hc-document-label">
            <icon class="fa fa-tag" icon-size="11" :size="30" :style="style" />
            <div class="hc-item-main-content" v-text="label.name"></div>
            <icon
                v-if="can('all.project.category.label.update')"
                tag="a"
                class="fa fa-cog hc-show-on-hover"
                :size="30"
                @mousedown.stop
                @click.prevent.stop="edit"
            />
            <icon
                class="fa fa-arrows"
                icon-size="11"
                :size="30"
                color="#CCCCCC"
            />
        </item>
    </field-template>
</template>

<style>
.hc-document-label .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import FieldTemplate from "../components/FieldTemplate.vue";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_LABEL } from "@/actions/project/label";

export default {
    components: {
        FieldTemplate,
    },

    props: {
        label: {
            type: Object,
        },
    },

    methods: {
        edit() {
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
