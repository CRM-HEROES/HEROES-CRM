<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <item-list class="hc-flex-1" padding="5px">
                    <item @click="tab = 1">
                        <div
                            class="hc-item-main-content"
                            v-text="field ? field.name : 'Choisir un champ ...'"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <v-field :label="$t('value')"
                        ><input
                            :placeholder="value + ' ...'"
                            v-model.lazy="value"
                    /></v-field>
                </item-list>
                <buttons>
                    <button
                        @click.prevent="update"
                        :disabled="disabled"
                        v-text="$t('prospect.table.bulk.update')"
                    ></button>
                </buttons>
            </div>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="tab = 1" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div class="hc-item-main-content" v-text="$t('back')"></div>
                </item>
                <div
                    class="hc-flex-1 hc-flex-column"
                    style="height: 100%; overflow: hidden; position: relative"
                >
                    <search v-model="fieldKeyword" />
                    <item-list class="hc-flex-1" padding="12px">
                        <field-row
                            v-for="f in filteredFields"
                            :key="f.id"
                            :field="f"
                            @click.prevent="(field = f), (tab = 0)"
                        />
                    </item-list>
                </div>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { BULK_UPDATE_PROSPECT } from "@/actions/project/prospect";
import {
    FETCH_PROSPECTS,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";
import { CLOSE_SLIDE } from "@/actions/slide";

import FieldRow from "./FieldRow.vue";

export default {
    components: {
        FieldRow,
    },

    data() {
        return {
            bulking: false,
            tab: 1,
            fieldKeyword: "",
            field: null,
            value: "",
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.bulking = true;
            const prospectsSelected = this.prospectsSelected;
            store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                await store.dispatch(BULK_UPDATE_PROSPECT, {
                    prospects: prospectsSelected,
                    field: (this.field.meta ? "meta->" : "") + this.field.slug,
                    value: this.value,
                });
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulking = false;
                store.commit(CLOSE_SLIDE, this.name);
            }
        },
    },

    computed: {
        ...mapGetters(["prospectsSelected", "fields", "can"]),

        /**
         *
         */
        disabled() {
            return !this.field;
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
