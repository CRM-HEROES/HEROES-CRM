<template>
    <modal name="label-update" :title="label ? label.name : ''">
        <tab-layout :count="3" :tab="tab" class="hc-flex-1">
            <template #1>
                <form
                    class="hc-flex-column"
                    v-if="labelToUpdate"
                    @submit.prevent="update"
                    style="height: 100%"
                >
                    <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                        <v-field :label="$t('name')" required v-slot="p"
                            ><input
                                ref="labelName"
                                required
                                :placeholder="p.label + ' ...'"
                                v-model="labelToUpdate.name"
                        /></v-field>
                        <v-field :label="$t('description')" v-slot="p">
                            <textarea
                                v-model="labelToUpdate.description"
                                :placeholder="p.label + ' ...'"
                            ></textarea>
                        </v-field>
                        <v-field :label="$t('color')"
                            ><input type="color" v-model="labelToUpdate.color"
                        /></v-field>
                        <color-palette v-model="labelToUpdate.color" />
                        <v-field :label="$t('bgcolor')"
                            ><input
                                type="color"
                                v-model="labelToUpdate.bgcolor"
                        /></v-field>
                        <color-palette v-model="labelToUpdate.bgcolor" />
                        <item tag="label" style="padding-right: 5px">
                            <icon class="fa fa-eye-slash" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('label.archive')"
                                v-tooltip="$t('label.archive_tooltip')"
                            ></div>
                            <checkbox v-model="labelToUpdate.archive" />
                        </item>
                    </item-list>
                    <item @click="tab = 2">
                        <icon class="fa fa-users" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <buttons>
                        <button
                            v-if="can('all.project.category.label.delete')"
                            @click.prevent="remove"
                            class="hc-button-danger"
                            v-text="$t('delete')"
                        ></button>
                        <button
                            @click.prevent="tab = 1"
                            style="background-color: #2abb47"
                            v-text="$t('label.combine_with')"
                            v-tuto="{
                                key: 'project.category.label.combine',
                                name: $t(
                                    'tutorial.project_field_to_category.name'
                                ),
                                body: $t(
                                    'tutorial.project_field_to_category.body'
                                ),
                                timeout: 500,
                            }"
                        ></button>
                        <button
                            @click.prevent="update"
                            v-text="$t('update')"
                        ></button>
                    </buttons>
                    <!--loading
                        :loading="
                            fetchingLabel || updatingLabel || removingLabel
                        "
                    /-->
                </form>
            </template>

            <template #2>
                <item @click="tab = 0" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.combine_with') + ' ...'"
                    ></div>
                </item>
                <search v-model="labelKeyword" />
                <item-list
                    class="hc-flex-1"
                    padding="5px"
                    style="max-height: 280px"
                >
                    <template v-for="l in filteredLabels" :key="l.id"
                        ><combine-label-row
                            v-if="l.id != label.id"
                            :label="l"
                            @click="combine(l)"
                    /></template>
                </item-list>
                <loading :loading="combiningLabel" />
            </template>

            <template #3>
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
                            :label="label"
                            :disabled="
                                categoryUsers.findIndex((u) => u == user.id) >=
                                0
                            "
                            v-model="labelUsers"
                        />
                    </item-list>
                    <loading
                        :loading="fetchingLabelUsers || fetchingCategoryUsers"
                    />
                </div>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserLabelService from "@/apis/project/user/label";
import UserCategoryService from "@/apis/project/user/category";

// Actions
import {
    SHOW_LABEL,
    UPDATE_LABEL,
    REMOVE_LABEL,
} from "@/actions/project/label";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

import CombineLabelRow from "./CombineLabelRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        CombineLabelRow,
        UserRow,
    },

    data() {
        return {
            updatingLabel: false,
            removingLabel: false,
            fetchingLabel: false,
            combiningLabel: false,
            fetchingLabelUsers: false,
            fetchingCategoryUsers: false,
            labelToUpdate: this.label,
            labelKeyword: "",
            userKeyword: "",
            labelUsers: [],
            categoryUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        async update() {
            store.commit(CLOSE_MODAL);
            this.updatingLabel = true;

            try {
                await store.dispatch(UPDATE_LABEL, this.labelToUpdate);
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingLabel = false;
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingLabel = true;

                try {
                    await store.dispatch(REMOVE_LABEL, {
                        label: this.labelToUpdate,
                    });
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingLabel = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async combine(label) {
            this.combiningLabel = true;

            try {
                await store.dispatch(REMOVE_LABEL, {
                    label: this.labelToUpdate,
                    params: {
                        combine: label.id,
                    },
                });
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.combiningLabel = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        async fetchLabelUsers() {
            this.fetchingLabelUsers = true;

            try {
                const { data } = await UserLabelService.users(
                    this.project.slug,
                    this.label.id
                );
                this.labelUsers = data.map((user) => user.id);
            } finally {
                this.fetchingLabelUsers = false;
            }
        },

        /**
         *
         */
        async fetchCategoryUsers() {
            this.fetchingCategoryUsers = true;

            try {
                const { data } = await UserCategoryService.users(
                    this.project.slug,
                    this.label.category_id
                );
                this.categoryUsers = data.map((user) => user.id);
            } finally {
                this.fetchingCategoryUsers = false;
            }
        },
    },

    watch: {
        async label(newValue) {
            this.labelToUpdate = { ...newValue, archive: false };

            this.fetchingLabel = true;

            try {
                this.labelToUpdate = await store.dispatch(SHOW_LABEL, {
                    category: newValue.category_id,
                    slug: newValue.id,
                });
            } finally {
                this.fetchingLabel = false;
            }
        },

        tab(newValue) {
            if (newValue == 2) {
                this.fetchLabelUsers();
                this.fetchCategoryUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "label", "categories", "users", "can"]),

        /**
         *
         */
        filteredLabels() {
            if (!this.label) {
                return [];
            }

            const category = this.categories.find(
                (c) => c.id == this.label.category_id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            const keyword = removeStringAccent(this.labelKeyword);

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

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
