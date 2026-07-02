<template>
    <modal
        name="order-step-update"
        :title="stepToUpdate ? stepToUpdate.name : ''"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <form
                    class="hc-flex-column"
                    style="height: 100%"
                    v-if="stepToUpdate"
                    @submit.prevent="update"
                >
                    <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                        <v-field :label="$t('name')" required v-slot="p"
                            ><input
                                ref="stepName"
                                :placeholder="p.step + ' ...'"
                                v-model="stepToUpdate.name"
                                :style="{
                                    color: stepToUpdate.color,
                                    backgroundColor: stepToUpdate.bgcolor,
                                }"
                                required
                        /></v-field>
                        <v-field :label="$t('description')" v-slot="p">
                            <textarea
                                v-model="stepToUpdate.description"
                                :placeholder="p.label + ' ...'"
                            ></textarea>
                        </v-field>
                        <v-field :label="$t('color')"
                            ><input type="color" v-model="stepToUpdate.color"
                        /></v-field>
                        <color-palette v-model="stepToUpdate.color" />
                        <v-field :label="$t('bgcolor')"
                            ><input type="color" v-model="stepToUpdate.bgcolor"
                        /></v-field>
                        <color-palette v-model="stepToUpdate.bgcolor" />
                    </item-list>
                    <item @click="tab = 1">
                        <icon class="fa fa-users" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <buttons>
                        <button
                            v-if="can('all.project.order.step.delete')"
                            @click.prevent="remove"
                            class="hc-button-danger"
                            v-text="$t('delete')"
                        ></button>
                        <button v-text="$t('update')"></button>
                    </buttons>
                    <loading :loading="updatingStep || removingStep" />
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
                            v-model="stepUsers"
                        />
                    </item-list>
                    <loading :loading="fetchingOrderStepUsers" />
                </div>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserOrderStepService from "@/apis/project/user/order-step";

// Steps
import {
    UPDATE_ORDER_STEP,
    REMOVE_ORDER_STEP,
} from "@/actions/project/order/step";
import { CLOSE_MODAL } from "@/actions/modal";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            updatingStep: false,
            removingStep: false,
            fetchingStep: false,
            fetchingOrderStepUsers: false,
            stepToUpdate: this.orderStep,
            userKeyword: "",
            stepUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingStep = true;

            try {
                await store.dispatch(UPDATE_ORDER_STEP, this.stepToUpdate);
            } finally {
                this.updatingStep = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingStep = true;

                try {
                    await store.dispatch(
                        REMOVE_ORDER_STEP,
                        this.stepToUpdate.id
                    );
                } finally {
                    this.removingStep = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async fetchOrderStepUsers() {
            this.fetchingOrderStepUsers = true;

            try {
                const { data } = await UserOrderStepService.users(
                    this.project.slug,
                    this.orderStep.id
                );
                this.stepUsers = data.map((user) => user.id);
            } finally {
                this.fetchingOrderStepUsers = false;
            }
        },
    },

    watch: {
        async orderStep(newValue) {
            if (newValue) {
                this.stepToUpdate = newValue;

                /*this.fetchingStep = true;
                this.stepToUpdate = await store.dispatch(
                    SHOW_ORDER_STEP,
                    newValue.id
                );
                this.fetchingStep = false;*/
            }
        },

        tab(newValue) {
            if (newValue == 1) {
                this.fetchOrderStepUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "orderStep", "users", "can"]),

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
