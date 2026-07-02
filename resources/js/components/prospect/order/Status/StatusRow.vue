<template>
    <item tag="label" @click="select">
        <icon class="fa fa-tags" :style="style" />
        <div class="hc-item-main-content" v-text="status.name"></div>
        <icon
            v-if="can('all.project.order.status.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <icon v-if="value" class="fa fa-check-circle" color="#09be0c" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_STATUS } from "@/actions/project/order/status";
import { UPDATE_PROSPECT_ORDER } from "@/actions/project/prospect/order";

export default {
    props: {
        status: {
            type: Object,
        },

        value: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        select() {
            store.dispatch(UPDATE_PROSPECT_ORDER, {
                id: this.prospectOrder.id,
                status_id: this.status.id,
            });
        },

        edit() {
            store.commit(OPEN_MODAL, "order-status-update");
            store.commit(SET_ORDER_STATUS, this.status);
        },
    },

    computed: {
        ...mapGetters(["prospectOrder", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.status.bgcolor,
            };
        },
    },
};
</script>
