<template>
    <div class="hc-flex-column" style="height: 100%; position: relative">
        <search v-model="fieldKeyword" />
        <div
            class="hc-flex-column"
            padding="12px"
            style="position: relative; padding: 12px; overflow: auto"
        >
            <order-field-row
                v-for="field in orderDefaultFields"
                :key="field.id"
                :field="field"
                v-model="prospectOrder[field.slug]"
            />
            <order-field-row
                v-for="field in orderMetaFields"
                :key="field.id"
                :field="field"
                v-model="meta[field.slug]"
            />
        </div>
        <buttons>
            <button
                @click.prevent="addField"
                v-text="$t('prospect.order.tabs.fields.add')"
                class="hc-button-grey"
            ></button>
            <button @click.prevent="updateOrder" v-text="$t('update')"></button>
        </buttons>
        <loading :loading="updatingOrder" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_NEW_FIELD_FOR } from "@/actions/project/field";
import { UPDATE_PROSPECT_ORDER } from "@/actions/project/prospect/order";

import OrderFieldRow from "./Field/OrderFieldRow.vue";

export default {
    components: {
        OrderFieldRow,
    },

    props: {
        prospectOrder: {
            type: Object,
        },
    },

    data() {
        return {
            fieldKeyword: "",
            meta: {},
            updatingOrder: false,
        };
    },

    methods: {
        async updateOrder() {
            this.updatingOrder = true;

            try {
                await store.dispatch(UPDATE_PROSPECT_ORDER, {
                    id: this.prospectOrder.id,
                    name: this.prospectOrder.name,
                    description: this.prospectOrder.description,
                    meta: this.meta,
                });

                this.$emit("order-updated", this.prospectOrder);
            } finally {
                this.updatingOrder = false;
            }
        },

        addField() {
            store.commit(OPEN_MODAL, "field-add");
            store.commit(SET_NEW_FIELD_FOR, "order");
        },
    },

    watch: {
        prospectOrder(newValue, oldValue) {
            this.meta =
                this.prospectOrder && this.prospectOrder.meta
                    ? this.prospectOrder.meta
                    : {};
        },
    },

    computed: {
        ...mapGetters(["project", "fields"]),

        /**
         *
         */
        orderDefaultFields() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.fields.filter(
                (field) =>
                    removeStringAccent(field.name).indexOf(keyword) >= 0 &&
                    !field.meta &&
                    field.for == "order"
            );
        },

        /**
         *
         */
        orderMetaFields() {
            const keyword = removeStringAccent(this.fieldKeyword);

            return this.fields.filter(
                (field) =>
                    removeStringAccent(field.name).indexOf(keyword) >= 0 &&
                    field.meta &&
                    field.for == "order"
            );
        },
    },
};
</script>
