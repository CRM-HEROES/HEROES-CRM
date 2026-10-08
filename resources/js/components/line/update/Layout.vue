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
                            disabled
                            required
                        >
                            <option :value="null" disabled></option>
                            <option
                                v-if="
                                    lineToUpdate.operator &&
                                    fetchingAvailableUsers
                                "
                                :value="null"
                                disabled
                            >
                                Chargement des agents...
                            </option>
                            <option
                                v-if="
                                    lineToUpdate.operator &&
                                    !fetchingAvailableUsers &&
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
                            prefillingConfig ||
                            fetchingAvailableUsers
                        "
                        v-text="$t('next')"
                    ></button>
                </buttons>
                <loading
                    :loading="
                        removingLine || prefillingConfig || fetchingAvailableUsers
                    "
                />
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
                    <button v-text="submitLabel"></button>
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
import {
    ADD_LINE,
    SHOW_LINE,
    UPDATE_LINE,
    REMOVE_LINE,
    FETCH_LINE_AVAILABLE_USERS,
} from "@/actions/project/line";
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
            fetchingAvailableUsers: false,
            availableUsers: [],
            configPrefilledFor: "",
            tab: 0,
        };
    },

    created() {
        this.lineToUpdate = this.cloneLine(this.line);
        this.resetAvailableUsersFromLocal();
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
            if (
                !this.lineToUpdate.operator ||
                !this.lineToUpdate.user_id ||
                this.fetchingAvailableUsers
            ) {
                return;
            }

            this.prepareLine();
            await this.loadOperatorConfig();
            this.tab = 1;
        },

        /**
         *
         */
        async update() {
            this.prepareLine();

            if (
                this.isCreatingNewLine &&
                this.hasExistingConfigForSelection()
            ) {
                flashError({
                    title: "Ligne",
                    body: "Cet agent a déjà une configuration pour cet opérateur.",
                    duration: 7000,
                });
                return;
            }

            if (!(await this.validateOperatorConfig())) {
                return;
            }

            this.updatingLine = true;

            try {
                this.normalizeConfig();

                if (this.isCreatingNewLine) {
                    await store.dispatch(ADD_LINE, this.linePayload());
                } else {
                    await store.dispatch(UPDATE_LINE, {
                        ...this.linePayload(),
                        id: this.lineToUpdate.id,
                    });
                }

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

        linePayload() {
            return {
                name: this.lineToUpdate.name,
                operator: this.lineToUpdate.operator,
                user_id: this.lineToUpdate.user_id,
                config: { ...(this.lineToUpdate.config ?? {}) },
            };
        },

        /**
         * Fetch the operator configuration with an HTTP request when the
         * user clicks on "next".
         *
         * - UPDATE (operator kept): load all the data of the line.
         * - AJOUT (new operator): load the credentials already configured
         *   for this operator in the project, shared by every agent.
         */
        async loadOperatorConfig() {
            if (!this.lineToUpdate) {
                return;
            }

            const operator = this.lineToUpdate.operator;

            if (!operator || this.configPrefilledFor === operator) {
                return;
            }

            const sourceId = this.operatorConfigSourceId(operator);

            if (!sourceId) {
                this.configPrefilledFor = operator;
                return;
            }

            this.prefillingConfig = true;

            try {
                const fullLine = await store.dispatch(SHOW_LINE, sourceId);

                // Operator changed while the request was in flight
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

                if (this.isOperatorUpdated(operator)) {
                    // UPDATE: keep every value stored on the line
                    this.lineToUpdate.config = {
                        ...(fullLine.config ?? {}),
                    };
                } else {
                    // AJOUT: reuse the project credentials of the operator,
                    // shared by all the agents (without agent_id)
                    this.lineToUpdate.config = {
                        ...this.reusableConfig(fullLine.config),
                        ...(this.lineToUpdate.config ?? {}),
                    };
                }

                this.configPrefilledFor = operator;
            } catch (error) {
                flashError({
                    title: "Ligne",
                    body: this.errorMessage(
                        error,
                        "Impossible de charger la configuration de l'opérateur."
                    ),
                    duration: 7000,
                });
            } finally {
                this.prefillingConfig = false;
            }
        },

        /**
         * Id of the line whose configuration is used to prefill the
         * operator fields.
         */
        operatorConfigSourceId(operator) {
            if (this.isOperatorUpdated(operator)) {
                return this.lineToUpdate.id;
            }

            const sourceLine = this.lines.find(
                (line) =>
                    line.operator === operator && line.id != this.lineToUpdate.id
            );

            return sourceLine ? sourceLine.id : null;
        },

        /**
         * Whether the line keeps the operator it was stored with.
         */
        isOperatorUpdated(operator) {
            return !!this.line && this.line.operator === operator;
        },

        async loadAvailableUsers() {
            if (!this.lineToUpdate) {
                return;
            }

            const operator = this.lineToUpdate.operator;

            if (!operator) {
                this.availableUsers = [];
                this.fetchingAvailableUsers = false;
                return;
            }

            this.fetchingAvailableUsers = true;

            try {
                const users = await store.dispatch(FETCH_LINE_AVAILABLE_USERS, {
                    operator,
                    exclude_line_id: this.lineToUpdate.id,
                });

                if (!this.lineToUpdate || this.lineToUpdate.operator !== operator) {
                    return;
                }

                this.availableUsers = users;

                if (
                    !this.isOperatorUpdated(operator) &&
                    !this.availableUsers.some(
                        (user) => user.id == this.lineToUpdate.user_id
                    )
                ) {
                    this.lineToUpdate.user_id = null;
                }
            } catch (error) {
                this.resetAvailableUsersFromLocal(operator);

                flashError({
                    title: "Ligne",
                    body: this.errorMessage(
                        error,
                        "Impossible de charger les agents disponibles."
                    ),
                    duration: 7000,
                });
            } finally {
                if (this.lineToUpdate && this.lineToUpdate.operator === operator) {
                    this.fetchingAvailableUsers = false;
                }
            }
        },

        resetAvailableUsersFromLocal(operator = null) {
            this.availableUsers = this.localAvailableUsers(
                operator ?? this.lineToUpdate?.operator,
                this.lineToUpdate?.id
            );
        },

        localAvailableUsers(operator, excludeLineId = null) {
            if (!operator) {
                return [];
            }

            const assignedUserIds = this.lines
                .filter(
                    (line) =>
                        line.operator === operator && line.id != excludeLineId
                )
                .map((line) => line.user_id)
                .filter((userId) => userId !== null && userId !== undefined)
                .map((userId) => String(userId));

            return this.users.filter(
                (user) => assignedUserIds.indexOf(String(user.id)) < 0
            );
        },

        hasExistingConfigForSelection() {
            return this.lines.some(
                (line) =>
                    line.operator === this.lineToUpdate.operator &&
                    line.user_id == this.lineToUpdate.user_id &&
                    line.id != this.lineToUpdate.id
            );
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

        errorMessage(error, fallback = "Impossible d'enregistrer la ligne.") {
            const errors = error.response?.data?.errors;

            if (errors) {
                const firstError = Object.values(errors)[0];

                if (Array.isArray(firstError) && firstError.length > 0) {
                    return firstError[0];
                }
            }

            return error.response?.data?.message || fallback;
        },
    },

    watch: {
        async line(newValue) {
            if (newValue) {
                this.fetchingLine = true;
                this.lineToUpdate = this.cloneLine(newValue);
                this.tab = 0;
                this.configPrefilledFor = "";
                this.resetAvailableUsersFromLocal();

                try {
                    this.lineToUpdate = this.cloneLine(
                        await store.dispatch(SHOW_LINE, newValue.id)
                    );
                    this.resetAvailableUsersFromLocal();
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

            this.resetAvailableUsersFromLocal(value);

            if (
                !this.availableUsers.some(
                    (user) => user.id == this.lineToUpdate.user_id
                )
            ) {
                this.lineToUpdate.user_id = null;
            }

            this.lineToUpdate.config = {};
            this.configPrefilledFor = "";

            if (!this.isOperatorUpdated(value)) {
                this.loadAvailableUsers();
            } else {
                this.fetchingAvailableUsers = false;
            }

            this.loadOperatorConfig();
        },
    },

    computed: {
        ...mapGetters(["project", "line", "can", "users", "lines"]),

        lineOperators() {
            return lineOperators;
        },

        isCreatingNewLine() {
            return (
                !!this.lineToUpdate &&
                !this.isOperatorUpdated(this.lineToUpdate.operator)
            );
        },

        submitLabel() {
            return this.isCreatingNewLine ? this.$t("add") : this.$t("update");
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

        /**
         * The agent cannot be changed when updating a line (operator kept).
         * It stays selectable while a new operator is being configured.
         */
        isAgentSelectionDisabled() {
            return (
                this.isOperatorUpdated(this.lineToUpdate.operator) ||
                !this.lineToUpdate.operator ||
                this.fetchingAvailableUsers ||
                this.availableUsers.length === 0
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
