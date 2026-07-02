<template>
    <bloc
        icon="fa fa-person-digging"
        :name="$t('project.profile.blocs.order_actions')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="addOrderAction"
            />
            <icon v-if="orderActions.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="orderActions.length > 0"
            >
                <order-action-row
                    v-for="(orderAction, i) in orderActions"
                    :key="orderAction.id"
                    :orderAction="orderAction"
                />
            </div>
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
import OrderActionRow from "./OrderActionRow.vue";

export default {
    components: {
        Bloc,
        OrderActionRow,
    },

    methods: {
        /**
         * Add orderAction
         * See: @/components/orderAction/add/Modal.vue
         */
        addOrderAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },
    },

    computed: {
        ...mapGetters(["project", "orderActions"]),
    },
};
</script>
