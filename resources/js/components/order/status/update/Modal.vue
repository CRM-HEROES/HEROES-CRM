<template>
    <modal
        name="order-status-update"
        :title="statusToUpdate ? statusToUpdate.name : ''"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            v-if="statusToUpdate"
            @submit.prevent="update"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field :label="$t('name')" required v-slot="p"
                    ><input
                        ref="statusName"
                        :placeholder="p.status + ' ...'"
                        :style="{
                            color: statusToUpdate.color,
                            backgroundColor: statusToUpdate.bgcolor,
                        }"
                        v-model="statusToUpdate.name"
                        required
                /></v-field>
                <v-field :label="$t('description')" v-slot="p">
                    <textarea
                        v-model="statusToUpdate.description"
                        :placeholder="p.label + ' ...'"
                    ></textarea>
                </v-field>
                <v-field :label="$t('color')"
                    ><input type="color" v-model="statusToUpdate.color"
                /></v-field>
                <color-palette v-model="statusToUpdate.color" />
                <v-field :label="$t('bgcolor')"
                    ><input type="color" v-model="statusToUpdate.bgcolor"
                /></v-field>
                <color-palette v-model="statusToUpdate.bgcolor" />
            </item-list>
            <buttons>
                <button
                    v-if="can('all.project.order.status.delete')"
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
                <button v-text="$t('update')"></button>
            </buttons>
            <loading :loading="updatingStatus || removingStatus" />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Statuses
import {
    SHOW_ORDER_STATUS,
    UPDATE_ORDER_STATUS,
    REMOVE_ORDER_STATUS,
} from "@/actions/project/order/status";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    data() {
        return {
            updatingStatus: false,
            removingStatus: false,
            fetchingStatus: false,
            statusToUpdate: this.status,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingStatus = true;

            try {
                await store.dispatch(UPDATE_ORDER_STATUS, this.statusToUpdate);
            } finally {
                this.updatingStatus = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingStatus = true;

                try {
                    await store.dispatch(
                        REMOVE_ORDER_STATUS,
                        this.statusToUpdate.id
                    );
                } finally {
                    this.removingStatus = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async orderStatus(newValue) {
            if (newValue) {
                this.statusToUpdate = newValue;

                /*this.fetchingStatus = true;
                this.statusToUpdate = await store.dispatch(
                    SHOW_ORDER_STATUS,
                    newValue.id
                );
                this.fetchingStatus = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["orderStatus", "can"]),
    },
};
</script>
