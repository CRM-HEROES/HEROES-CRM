<template>
    <modal
        name="order-action-add"
        :title="$t('order.action.add.title')"
        @open="action = this.newAction()"
    >
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="storeAction"
        >
            <item-list gap="5px">
                <v-field :label="$t('name')" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="action.name"
                        :style="{
                            color: action.color,
                            backgroundColor: action.bgcolor,
                        }"
                        required
                /></v-field>
                <v-field :label="$t('description')" v-slot="p">
                    <textarea
                        v-model="action.description"
                        :placeholder="p.label + ' ...'"
                    ></textarea>
                </v-field>
                <v-field :label="$t('color')"
                    ><input type="color" v-model="action.color"
                /></v-field>
                <color-palette v-model="action.color" />
                <v-field :label="$t('bgcolor')"
                    ><input type="color" v-model="action.bgcolor"
                /></v-field>
                <color-palette v-model="action.bgcolor" />
            </item-list>
            <buttons>
                <button v-text="$t('add')"></button>
            </buttons>
            <loading :loading="addingAction" />
        </form>
    </modal>
</template>

<script>
import store from "@/store";

// Actions
import { ADD_ORDER_ACTION } from "@/actions/project/order/action";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            action: this.newAction(),
            addingAction: false,
        };
    },

    methods: {
        /**
         *
         */
        newAction() {
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
        async storeAction() {
            this.addingAction = true;

            try {
                await store.dispatch(ADD_ORDER_ACTION, this.action);
            } finally {
                this.addingAction = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },
};
</script>
