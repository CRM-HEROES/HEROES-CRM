<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="goToConfig"
            >
                <item-list gap="5px">
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
                    <v-field :label="$t('line.assign_to.pick_title')" required>
                        <select
                            v-model="line.user_id"
                            :disabled="
                                !line.operator || availableUsers.length === 0
                            "
                            required
                        >
                            <option :value="null" disabled></option>
                            <option
                                v-if="line.operator && availableUsers.length === 0"
                                :value="null"
                                disabled
                            >
                                Aucun agent disponible
                            </option>
                            <option
                                v-for="user in availableUsers"
                                :key="user.id"
                                :value="user.id"
                                v-text="user.name"
                            ></option>
                        </select>
                    </v-field>
                </item-list>
                <buttons>
                    <button
                        :disabled="
                            !line.operator || !line.user_id || prefillingConfig
                        "
                        v-text="$t('next')"
                    ></button>
                </buttons>
                <loading :loading="prefillingConfig" />
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
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_LINE, SHOW_LINE } from "@/actions/project/line";
import { CLOSE_MODAL } from "@/actions/modal";

// Constants
import lineOperators from "@/constants/lineOperators";

// Components
import OperatorConfigFields from "../OperatorConfigFields.vue";

export default {
    components: {
        OperatorConfigFields,
    },

    data() {
        return {
            line: this.newLine(),
            addingLine: false,
            prefillingConfig: false,
            configPrefilledFor: "",
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

        async goToConfig() {
            this.prepareLine();
            await this.prefillOperatorConfig();
            this.tab = 1;
        },

        /**
         *
         */
        async storeLine() {
            this.prepareLine();

            if (!(await this.validateOperatorConfig())) {
                return;
            }

            this.addingLine = true;

            try {
                this.normalizeConfig();
                await store.dispatch(ADD_LINE, this.line);
                this.line = this.newLine();
                this.configPrefilledFor = "";
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

        prepareLine() {
            this.line.name = this.generatedLineName;
        },

        async prefillOperatorConfig() {
            const operator = this.line.operator;

            if (!operator || this.configPrefilledFor === operator) {
                return;
            }

            const sourceLine = this.lines.find(
                (line) => line.operator === operator
            );

            this.configPrefilledFor = operator;

            if (!sourceLine) {
                return;
            }

            this.prefillingConfig = true;

            try {
                const fullLine = await store.dispatch(SHOW_LINE, sourceLine.id);

                if (this.line.operator !== operator) {
                    return;
                }

                if (
                    this.project &&
                    fullLine.project_id &&
                    fullLine.project_id != this.project.id
                ) {
                    return;
                }

                this.line.config = {
                    ...this.reusableConfig(fullLine.config),
                    ...(this.line.config ?? {}),
                };
            } finally {
                this.prefillingConfig = false;
            }
        },

        reusableConfig(config) {
            const reusable = { ...(config ?? {}) };
            delete reusable.agent_id;
            return reusable;
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

    watch: {
        "line.operator"(value, oldValue) {
            if (value === oldValue) {
                return;
            }

            this.line.user_id = null;
            this.line.config = {};
            this.configPrefilledFor = "";
            this.prefillOperatorConfig();
        },
    },

    computed: {
        ...mapGetters(["project", "users", "lines"]),

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

        availableUsers() {
            if (!this.line.operator) {
                return [];
            }

            const assignedUserIds = this.lines
                .filter((line) => line.operator === this.line.operator)
                .map((line) => line.user_id)
                .filter((userId) => userId !== null && userId !== undefined)
                .map((userId) => String(userId));

            return this.users.filter(
                (user) => assignedUserIds.indexOf(String(user.id)) < 0
            );
        },

        generatedLineName() {
            const operator = lineOperators.find(
                (operator) => operator.value === this.line.operator
            );
            const operatorName = operator ? operator.label : this.line.operator;
            const userName = this.assignedUser ? this.assignedUser.name : "Agent";

            return [operatorName, userName]
                .filter(Boolean)
                .join(" - ")
                .slice(0, 100);
        },
    },
};
</script>
