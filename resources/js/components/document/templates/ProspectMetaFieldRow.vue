<template>
    <field-template type="html" :content="'{prospect.meta.' + field.slug + '}'">
        <item class="hc-document-prospect-default-field">
            <icon class="fa fa-columns" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="field.name"></div>
            <icon
                v-if="can('all.project.field.update')"
                tag="a"
                class="fa fa-cog"
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

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import FieldTemplate from "../components/FieldTemplate.vue";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_FIELD } from "@/actions/project/field";

export default {
    components: {
        FieldTemplate,
    },

    props: {
        field: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "field-update");
            store.commit(SET_FIELD, this.field);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
