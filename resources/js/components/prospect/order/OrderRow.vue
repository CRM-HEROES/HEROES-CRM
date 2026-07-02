<template>
    <item class="hc-prospect-order">
        <icon class="fa fa-shopping-cart" />
        <div class="hc-item-main-content" v-text="order.name"></div>
        <icon
            v-if="can('all.prospect.order.delete')"
            tag="a"
            class="fa fa-trash"
            @click.prevent.stop="remove"
        />
        <tag
            v-if="status"
            :text="status.name"
            :color="status.color"
            :bgcolor="status.bgcolor"
        />
        <icon class="fa fa-caret-right" />
        <loading :loading="removingOrder" />
    </item>
</template>

<style>
.hc-prospect-order .fa-trash {
    display: none;
}
.hc-prospect-order:hover .fa-trash {
    display: flex;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { REMOVE_PROSPECT_ORDER } from "@/actions/project/prospect/order";

export default {
    props: {
        order: {
            type: Object,
        },
    },

    data() {
        return {
            removingOrder: false,
        };
    },

    methods: {
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingOrder = true;

                try {
                    await store.dispatch(REMOVE_PROSPECT_ORDER, this.order.id);
                } finally {
                    this.removingOrder = false;
                }
            });
        },
    },

    computed: {
        ...mapGetters(["orderStatuses", "can"]),

        total() {
            return 0;
        },

        /**
         *
         */
        status() {
            const status = this.orderStatuses.find(
                (status) => status.id == this.order.status_id
            );

            return status;
        },
    },
};
</script>
