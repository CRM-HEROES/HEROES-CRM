<template>
    <bloc
        icon="fa fa-step-forward icon-cyan"
        :name="$t('user.profile.blocs.order_steps')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addOrderStep"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <order-step-row
                    v-for="(orderStep, i) in userOrderSteps"
                    :key="orderStep.id"
                    :orderStep="orderStep"
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
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import OrderStepRow from "./OrderStepRow.vue";

export default {
    components: {
        Bloc,
        OrderStepRow,
    },

    methods: {
        /**
         * Add orderStep
         * See: @/components/orderStep/add/Modal.vue
         */
        addOrderStep() {
            store.commit(OPEN_SLIDE, "user-manage-order-steps");
        },
    },

    computed: {
        ...mapGetters(["project", "userOrderSteps"]),
    },
};
</script>
