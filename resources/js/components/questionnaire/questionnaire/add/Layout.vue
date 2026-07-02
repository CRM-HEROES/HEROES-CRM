<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeQuestionnaire"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="questionnaire.name"
                            required
                    /></v-field>
                    <!--item tag="label" style="padding-right: 5px">
                    <icon class="fa fa-user" />
                    <div class="hc-item-main-content" v-text="'Privé'"></div>
                    <checkbox v-model="questionnaire.private" />
                </item-->
                </item-list>
                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <div
                        v-if="questionnaireUsers.length"
                        class="hc-item-count"
                        v-text="questionnaireUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingQuestionnaire" />
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
                        v-model="questionnaireUsers"
                    />
                </item-list>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_QUESTIONNAIRE } from "@/actions/project/questionnaire";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_QUESTIONNAIRE } from "@/actions/project/user/questionnaire";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            questionnaire: this.newQuestionnaire(),
            addingQuestionnaire: false,
            userKeyword: "",
            questionnaireUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newQuestionnaire() {
            return {
                name: "",
                private: true,
            };
        },

        /**
         *
         */
        async storeQuestionnaire() {
            this.addingQuestionnaire = true;

            try {
                const questionnaire = await store.dispatch(
                    ADD_QUESTIONNAIRE,
                    this.questionnaire
                );

                if (this.questionnaireUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_QUESTIONNAIRE, {
                        users: this.questionnaireUsers,
                        questionnaires: [questionnaire.id],
                    });
                }
            } finally {
                this.addingQuestionnaire = false;
                this.questionnaire = this.newQuestionnaire();
                this.questionnaireUsers = [];
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
