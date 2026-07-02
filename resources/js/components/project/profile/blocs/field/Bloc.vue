<template>
    <bloc icon="fa fa-columns" :name="$t('project.profile.blocs.fields')">
        <template #options>
            <icon tag="a" class="fa fa-plus" @click.prevent.stop="addField" />
        </template>
        <template #body>
            <tab :count="3" :tab="tab" @tab:change="(t) => (tab = t)">
                <template v-for="(fieldType, i) in fieldTypes" v-slot:[i+1]>
                    <div v-text="fieldType.name"></div>
                </template>
            </tab>

            <tab-layout :count="3" :tab="tab" class="hc-flex-1">
                <template v-for="(fieldType, i) in fieldTypes" v-slot:[i+1]>
                    <div
                        style="
                            padding: 10px 10px;
                            float: left;
                            width: 100%;
                            max-height: 500px;
                            overflow: auto;
                        "
                    >
                        <field-row
                            v-for="(field, i) in fields.filter(
                                (f) => f.for == fieldType.type
                            )"
                            :key="field.id"
                            :field="field"
                        /></div
                ></template>
            </tab-layout>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import FieldRow from "./FieldRow.vue";

export default {
    components: {
        Bloc,
        FieldRow,
    },

    data() {
        return {
            tab: 0,
            fieldTypes: [
                {
                    type: "prospect",
                    name: this.$t("prospects"),
                },
                {
                    type: "project",
                    name: this.$t("projects"),
                },
                {
                    type: "product",
                    name: this.$t("products"),
                },
                {
                    type: "user",
                    name: this.$t("users"),
                },
            ],
        };
    },

    methods: {
        /**
         * Add field
         * See: @/components/field/add/Modal.vue
         */
        addField() {
            store.commit(OPEN_MODAL, "field-add");
        },
    },

    computed: {
        ...mapGetters(["project", "fields"]),
    },
};
</script>
