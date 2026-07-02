<template>
    <modal
        name="order-step-add"
        :title="$t('order.step.add.title')"
        @open="(step = this.newStep()), (orderStepUsers = [])"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <form
                    class="hc-flex-column"
                    style="height: 100%"
                    @submit.prevent="storeStep"
                >
                    <item-list gap="5px">
                        <v-field :label="$t('name')" required v-slot="{ label }"
                            ><input
                                :placeholder="label + ' ...'"
                                v-model="step.name"
                                :style="{
                                    color: step.color,
                                    backgroundColor: step.bgcolor,
                                }"
                                required
                        /></v-field>
                        <v-field :label="$t('description')" v-slot="p">
                            <textarea
                                v-model="step.description"
                                :placeholder="p.label + ' ...'"
                            ></textarea>
                        </v-field>
                        <v-field :label="$t('color')"
                            ><input type="color" v-model="step.color"
                        /></v-field>
                        <color-palette v-model="step.color" />
                        <v-field :label="$t('bgcolor')"
                            ><input type="color" v-model="step.bgcolor"
                        /></v-field>
                        <color-palette v-model="step.bgcolor" />
                    </item-list>
                    <item @click="tab = 1">
                        <icon class="fa fa-users" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                        <div
                            v-if="orderStepUsers.length"
                            class="hc-item-count"
                            v-text="orderStepUsers.length"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <buttons>
                        <button v-text="$t('add')"></button>
                    </buttons>
                    <loading :loading="addingStep" />
                </form>
            </template>

            <template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                    </item>
                    <search v-model="userKeyword" />
                    <item-list
                        class="hc-flex-1"
                        padding="5px"
                        style="max-height: 400px"
                    >
                        <user-row
                            v-for="user in filteredUsers"
                            :key="user.id"
                            :user="user"
                            :order-step="orderStep"
                        />
                    </item-list>
                </div>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Steps
import { ADD_ORDER_STEP } from "@/actions/project/order/step";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_ORDER_STEP } from "@/actions/project/user/order-step";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            step: this.newStep(),
            addingStep: false,
            userKeyword: "",
            orderStepUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newStep() {
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
        async storeStep() {
            this.addingStep = true;

            try {
                const orderStep = await store.dispatch(
                    ADD_ORDER_STEP,
                    this.step
                );

                if (this.orderStepUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_ORDER_STEP, {
                        users: this.orderStepUsers,
                        orderSteps: [orderStep.id],
                    });
                }
            } finally {
                this.addingStep = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["users"]),

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
