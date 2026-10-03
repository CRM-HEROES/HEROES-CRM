<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="tab = 1"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="line.name"
                            required
                    /></v-field>
                    <v-field :label="$t('line.operator.choose')" required
                        ><select v-model="line.operator" required>
                            <option value="" disabled></option>
                            <option
                                v-for="operator in lineOperators"
                                :key="operator.value"
                                :value="operator.value"
                                v-text="operator.label"
                            ></option></select
                    ></v-field>
                    <item @click="tab = 2">
                        <icon class="fa fa-user" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t('line.assign_to.title', {
                                    user: assignedUser
                                        ? assignedUser.name
                                        : 'un agent ...',
                                })
                            "
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
                <buttons>
                    <button
                        :disabled="!line.name || !line.operator"
                        v-text="$t('next')"
                    ></button>
                </buttons>
            </form>
        </template>

        <template #2>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeLine"
            >
                <item @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('line.configuration.title')"
                    ></div>
                </item>
                <item-list gap="5px" class="hc-flex-1">
                    <operator-config-fields
                        ref="operatorConfigFields"
                        :operator="line.operator"
                        :config="line.config"
                        :fields="operatorFields"
                    />
                </item-list>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingLine" />
            </form>
        </template>

        <template #3>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('line.assign_to.pick_title')"
                    ></div>
                </item>
                <search v-model="userKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <item @click="(line.user_id = null), (tab = 0)">
                        <icon class="fa fa-times" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('none')"
                        ></div>
                    </item>
                    <to-user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        @click="(line.user_id = user.id), (tab = 0)"
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
import { ADD_LINE } from "@/actions/project/line";
import { CLOSE_MODAL } from "@/actions/modal";

// Constants
import lineOperators from "@/constants/lineOperators";

// Components
import OperatorConfigFields from "../OperatorConfigFields.vue";
import ToUserRow from "../ToUserRow.vue";

export default {
    components: {
        OperatorConfigFields,
        ToUserRow,
    },

    data() {
        return {
            line: this.newLine(),
            addingLine: false,
            userKeyword: "",
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newLine() {
            return {
                name: "",
                operator: "",
                user_id: null,
                config: {},
            };
        },

        /**
         *
         */
        async storeLine() {
            if (!(await this.validateOperatorConfig())) {
                return;
            }

            this.addingLine = true;

            try {
                this.normalizeConfig();
                await store.dispatch(ADD_LINE, this.line);
                this.line = this.newLine();
                this.tab = 0;
                store.commit(CLOSE_MODAL);
            } catch (error) {
                flashError({
                    title: "Ligne",
                    body: this.errorMessage(error),
                    duration: 7000,
                });
            } finally {
                this.addingLine = false;
            }
        },

        normalizeConfig() {
            Object.keys(this.line.config).forEach((key) => {
                if (
                    this.line.config[key] !== null &&
                    this.line.config[key] !== undefined
                ) {
                    this.line.config[key] = String(this.line.config[key]);
                }
            });
        },

        async validateOperatorConfig() {
            if (!this.$refs.operatorConfigFields) {
                return true;
            }

            return await this.$refs.operatorConfigFields.validate();
        },

        errorMessage(error) {
            const errors = error.response?.data?.errors;

            if (errors) {
                const firstError = Object.values(errors)[0];

                if (Array.isArray(firstError) && firstError.length > 0) {
                    return firstError[0];
                }
            }

            return (
                error.response?.data?.message ||
                "Impossible d'enregistrer la ligne."
            );
        },
    },

    computed: {
        ...mapGetters(["users"]),

        lineOperators() {
            return lineOperators;
        },

        /**
         *
         */
        operatorFields() {
            const operator = lineOperators.find(
                (o) => o.value === this.line.operator
            );
            return operator ? operator.fields : [];
        },

        /**
         *
         */
        assignedUser() {
            return this.users.find((u) => u.id == this.line.user_id);
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
