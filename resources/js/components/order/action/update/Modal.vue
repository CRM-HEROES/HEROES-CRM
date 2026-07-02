<template>
    <modal
        name="order-action-update"
        :title="actionToUpdate ? actionToUpdate.name : ''"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <form
                    class="hc-flex-column"
                    style="height: 100%"
                    v-if="actionToUpdate"
                    @submit.prevent="update"
                >
                    <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                        <v-field :label="$t('name')" required v-slot="p"
                            ><input
                                ref="actionName"
                                :placeholder="p.action + ' ...'"
                                :style="{
                                    color: actionToUpdate.color,
                                    backgroundColor: actionToUpdate.bgcolor,
                                }"
                                v-model="actionToUpdate.name"
                                required
                        /></v-field>
                        <v-field :label="$t('description')" v-slot="p">
                            <textarea
                                v-model="actionToUpdate.description"
                                :placeholder="p.label + ' ...'"
                            ></textarea>
                        </v-field>
                        <v-field :label="$t('color')"
                            ><input type="color" v-model="actionToUpdate.color"
                        /></v-field>
                        <color-palette v-model="actionToUpdate.color" />
                        <v-field :label="$t('bgcolor')"
                            ><input
                                type="color"
                                v-model="actionToUpdate.bgcolor"
                        /></v-field>
                        <color-palette v-model="actionToUpdate.bgcolor" />
                        <item @click="tab = 1">
                            <icon class="fa fa-user-tie" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    defaultRole
                                        ? 'Associé par défaut au rôle &quot;' +
                                          defaultRole.name +
                                          '&quot;'
                                        : 'Associer par défaut au rôle ...'
                                "
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                    </item-list>
                    <buttons>
                        <button
                            v-if="can('all.project.order.action.delete')"
                            @click.prevent="remove"
                            class="hc-button-danger"
                            v-text="$t('delete')"
                        ></button>
                        <button v-text="$t('update')"></button>
                    </buttons>
                    <loading :loading="updatingAction || removingAction" />
                </form>
            </template>

            <template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="'Rôle associé par défaut'"
                        ></div>
                    </item>
                    <search v-model="roleKeyword" />
                    <item-list
                        class="hc-flex-1"
                        padding="5px"
                        style="max-height: 400px"
                    >
                        <item
                            tag="a"
                            @click.prevent="
                                (actionToUpdate.default_role_id = null),
                                    (tab = 0)
                            "
                        >
                            <icon class="fa fa-times icon-red" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('none')"
                            ></div>
                        </item>
                        <role-row
                            v-for="role in filteredRoles"
                            :key="role.id"
                            :role="role"
                            :order-action="actionToUpdate"
                            @click.prevent="
                                (actionToUpdate.default_role_id = role.id),
                                    (tab = 0)
                            "
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

// Actions
import {
    UPDATE_ORDER_ACTION,
    REMOVE_ORDER_ACTION,
} from "@/actions/project/order/action";
import { CLOSE_MODAL } from "@/actions/modal";

import RoleRow from "./RoleRow.vue";

export default {
    components: {
        RoleRow,
    },

    data() {
        return {
            updatingAction: false,
            removingAction: false,
            fetchingAction: false,
            roleKeyword: "",
            actionToUpdate: this.action,
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingAction = true;

            try {
                await store.dispatch(UPDATE_ORDER_ACTION, this.actionToUpdate);
            } finally {
                this.updatingAction = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingAction = true;

                try {
                    await store.dispatch(
                        REMOVE_ORDER_ACTION,
                        this.actionToUpdate.id
                    );
                } finally {
                    this.removingAction = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async orderAction(newValue) {
            if (newValue) {
                this.actionToUpdate = newValue;

                /*this.fetchingAction = true;
                this.actionToUpdate = await store.dispatch(
                    SHOW_ORDER_ACTION,
                    newValue.id
                );
                this.fetchingAction = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["orderAction", "roles", "can"]),

        /**
         *
         */
        filteredRoles() {
            const keyword = removeStringAccent(this.roleKeyword);

            return this.roles.filter(
                (role) => removeStringAccent(role.name).indexOf(keyword) >= 0
            );
        },

        defaultRole() {
            return this.orderAction.default_role_id
                ? this.roles.find(
                      (role) => role.id == this.orderAction.default_role_id
                  )
                : null;
        },
    },
};
</script>
