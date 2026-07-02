<template>
    <field-template type="html" :content="'{prospect.' + field.slug + '}'">
        <item class="hc-document-prospect-default-field">
            <icon class="fa fa-columns" icon-size="11" :size="30" />
            <div class="hc-item-main-content" v-text="field.name"></div>
            <div
                class="hc-document-field-page"
                v-if="page"
                v-text="'Page ' + page"
            ></div>
            <icon
                v-if="can('all.project.field.update')"
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
.hc-document-prospect-default-field .hc-item-main-content {
    font-size: 11px;
}
</style>

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
        ...mapGetters(["can", "documentFields", "documentPages"]),

        page() {
            const field = this.documentFields.find(
                (f) =>
                    f.content.indexOf(
                        this.field.meta
                            ? `{prospect.meta.${this.field.slug}}`
                            : `{prospect.${this.field.slug}}`
                    ) >= 0
            );

            if (!field) {
                return null;
            }

            const page = this.documentPages.find((p) => p.id == field.page_id);

            if (!page) {
                return null;
            }

            return page.page;
        },
    },
};
</script>
