<template>
    <modal
        name="order-status-add"
        :title="$t('order.status.add.title')"
        @open="status = this.newStatus()"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="storeStatus"
        >
            <item-list gap="5px">
                <v-field :label="$t('name')" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="status.name"
                        :style="{
                            color: status.color,
                            backgroundColor: status.bgcolor,
                        }"
                        required
                /></v-field>
                <v-field :label="$t('description')" v-slot="p">
                    <textarea
                        v-model="status.description"
                        :placeholder="p.label + ' ...'"
                    ></textarea>
                </v-field>
                <v-field :label="$t('color')"
                    ><input type="color" v-model="status.color"
                /></v-field>
                <color-palette v-model="status.color" />
                <v-field :label="$t('bgcolor')"
                    ><input type="color" v-model="status.bgcolor"
                /></v-field>
                <color-palette v-model="status.bgcolor" />
            </item-list>
            <buttons>
                <button v-text="$t('add')"></button>
            </buttons>
            <loading :loading="addingStatus" />
        </form>
    </modal>
</template>

<script>
import store from "@/store";

// Statuses
import { ADD_ORDER_STATUS } from "@/actions/project/order/status";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            status: this.newStatus(),
            addingStatus: false,
        };
    },

    methods: {
        /**
         *
         */
        newStatus() {
            return {
                name: "",
                description: "",
                color: "#000000",
                bgcolor: "#FFFFFF",
            };
        },

        /**
         *
         */
        async storeStatus() {
            this.addingStatus = true;

            try {
                await store.dispatch(ADD_ORDER_STATUS, this.status);
            } finally {
                this.addingStatus = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },
};
</script>
