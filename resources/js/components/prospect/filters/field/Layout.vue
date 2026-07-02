<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="fieldKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <field-row
                v-for="field in filteredFields"
                :key="field.id"
                :field="field"
            />
        </item-list>
        <buttons v-if="can('all.project.field.add')">
            <a @click.prevent="addField" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import FieldRow from "./FieldRow.vue";

export default {
    components: {
        FieldRow,
    },

    data() {
        return {
            tab: 0,
            fieldKeyword: "",
        };
    },

    computed: {
        ...mapGetters([
            "fields",
            "prospect",
            "prospectFullName",
            "prospectsParamExists",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyField() {
            return "withFiles";
        },

        /**
         *
         */
        filteredFields() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.fields.filter(
                (field) =>
                    field.for == "prospect" &&
                    removeStringAccent(field.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
