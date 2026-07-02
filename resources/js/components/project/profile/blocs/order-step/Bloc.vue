<template>
    <bloc
        icon="fa fa-step-forward"
        :name="$t('project.profile.blocs.order_steps')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="addOrderStep"
            />
            <icon v-if="orderSteps.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="orderSteps.length > 0"
            >
                <order-step-row
                    v-for="(orderStep, i) in orderSteps"
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
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_MODAL, "order-step-add");
        },
    },

    computed: {
        ...mapGetters(["project", "orderSteps"]),
    },
};
</script>
