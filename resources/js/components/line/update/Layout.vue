<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="lineToUpdate"
                @submit.prevent="goToConfig"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('line.operator.choose')" required
                        ><select v-model="lineToUpdate.operator" required>
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
                            v-model="lineToUpdate.user_id"
                            :disabled="
                                !lineToUpdate.operator ||
                                availableUsers.length === 0
                            "
                            required
                        >
                            <option :value="null" disabled></option>
                            <option
                                v-if="
                                    lineToUpdate.operator &&
                                    availableUsers.length === 0
                                "
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
                        v-if="can('all.project.line.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button
                        :disabled="
                            !lineToUpdate.operator ||
                            !lineToUpdate.user_id ||
                            prefillingConfig
                        "
                        v-text="$t('next')"
                    ></button>
                </buttons>
                <loading :loading="removingLine || prefillingConfig" />
            </form>
        </template>

        <template #2>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="lineToUpdate"
                @submit.prevent="update"
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
                        :operator="lineToUpdate.operator"
                        :config="lineToUpdate.config"
                        :fields="operatorFields"
                    />
                </item-list>
                <buttons>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading :loading="updatingLine" />
            </form>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SHOW_LINE, UPDATE_LINE, REMOVE_LINE } from "@/actions/project/line";
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
            updatingLine: false,
            removingLine: false,
            fetchingLine: false,
            lineToUpdate: this.cloneLine(this.line),
            prefillingConfig: false,
            configPrefilledFor: "",
            tab: 0,
        };
    },

    created() {
        this.lineToUpdate = this.cloneLine(this.line);
    },

    methods: {
        /**
         *
         */
        cloneLine(line) {
            return line
                ? { ...line, config: { ...(line.config ?? {}) } }
                : line;
        },

        async goToConfig() {
            this.prepareLine();
            await this.prefillOperatorConfig();
            this.tab = 1;
        },

        /**
         *
         */
        async update() {
            this.prepareLine();

            if (!(await this.validateOperatorConfig())) {
                return;
            }

            this.updatingLine = true;

            try {
                this.normalizeConfig();
                await store.dispatch(UPDATE_LINE, this.lineToUpdate);
                store.commit(CLOSE_MODAL);
            } catch (error) {
                flashError({
                    title: "Ligne",
                    body: this.errorMessage(error),
                    duration: 7000,
                });
            } finally {
                this.updatingLine = false;
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingLine = true;

                try {
                    await store.dispatch(REMOVE_LINE, this.lineToUpdate.id);
                } finally {
                    this.removingLine = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        prepareLine() {
            this.lineToUpdate.name = this.generatedLineName;
        },

        async prefillOperatorConfig() {
            if (!this.lineToUpdate) {
                return;
            }

            const operator = this.lineToUpdate.operator;

            if (!operator || this.configPrefilledFor === operator) {
                return;
            }

            const sourceLine = this.lines.find(
                (line) =>
                    line.operator === operator && line.id != this.lineToUpdate.id
            );

            this.configPrefilledFor = operator;

            if (!sourceLine) {
                return;
            }

            this.prefillingConfig = true;

            try {
                const fullLine = await store.dispatch(SHOW_LINE, sourceLine.id);

                if (this.lineToUpdate.operator !== operator) {
                    return;
                }

                if (
                    this.project &&
                    fullLine.project_id &&
                    fullLine.project_id != this.project.id
                ) {
                    return;
                }

                this.lineToUpdate.config = {
                    ...this.reusableConfig(fullLine.config),
                    ...(this.lineToUpdate.config ?? {}),
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
            Object.keys(this.lineToUpdate.config).forEach((key) => {
                if (
                    this.lineToUpdate.config[key] !== null &&
                    this.lineToUpdate.config[key] !== undefined
                ) {
                    this.lineToUpdate.config[key] = String(
                        this.lineToUpdate.config[key]
                    );
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
        async line(newValue) {
            if (newValue) {
                this.fetchingLine = true;
                this.lineToUpdate = this.cloneLine(newValue);
                this.tab = 0;
                this.configPrefilledFor = "";

                try {
                    this.lineToUpdate = this.cloneLine(
                        await store.dispatch(SHOW_LINE, newValue.id)
                    );
                } finally {
                    this.fetchingLine = false;
                }
            }
        },

        "lineToUpdate.operator"(value, oldValue) {
            if (
                this.fetchingLine ||
                !this.lineToUpdate ||
                !oldValue ||
                value === oldValue
            ) {
                return;
            }

            if (
                !this.availableUsers.some(
                    (user) => user.id == this.lineToUpdate.user_id
                )
            ) {
                this.lineToUpdate.user_id = null;
            }

            this.lineToUpdate.config = {};
            this.configPrefilledFor = "";
            this.prefillOperatorConfig();
        },
    },

    computed: {
        ...mapGetters(["project", "line", "can", "users", "lines"]),

        lineOperators() {
            return lineOperators;
        },

        /**
         *
         */
        operatorFields() {
            const operator = lineOperators.find(
                (o) => o.value === this.lineToUpdate.operator
            );
            return operator ? operator.fields : [];
        },

        /**
         *
         */
        assignedUser() {
            return this.users.find((u) => u.id == this.lineToUpdate.user_id);
        },

        availableUsers() {
            if (!this.lineToUpdate || !this.lineToUpdate.operator) {
                return [];
            }

            const assignedUserIds = this.lines
                .filter(
                    (line) =>
                        line.operator === this.lineToUpdate.operator &&
                        line.id != this.lineToUpdate.id
                )
                .map((line) => line.user_id)
                .filter((userId) => userId !== null && userId !== undefined)
                .map((userId) => String(userId));

            return this.users.filter(
                (user) => assignedUserIds.indexOf(String(user.id)) < 0
            );
        },

        generatedLineName() {
            const operator = lineOperators.find(
                (operator) => operator.value === this.lineToUpdate.operator
            );
            const operatorName = operator
                ? operator.label
                : this.lineToUpdate.operator;
            const userName = this.assignedUser ? this.assignedUser.name : "Agent";

            return [operatorName, userName]
                .filter(Boolean)
                .join(" - ")
                .slice(0, 100);
        },
    },
};
</script>
