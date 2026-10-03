<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="lineToUpdate"
                @submit.prevent="tab = 1"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="lineToUpdate.name"
                            required
                    /></v-field>
                    <v-field :label="$t('line.operator.choose')" required
                        ><select v-model="lineToUpdate.operator" required>
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
                        v-if="can('all.project.line.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button
                        :disabled="!lineToUpdate.name || !lineToUpdate.operator"
                        v-text="$t('next')"
                    ></button>
                </buttons>
                <loading :loading="removingLine" />
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

        <template #3>
            <div class="hc-flex-column" style="height: 100%" v-if="lineToUpdate">
                <item @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('line.assign_to.pick_title')"
                    ></div>
                </item>
                <search v-model="userKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <item @click="(lineToUpdate.user_id = null), (tab = 0)">
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
                        @click="(lineToUpdate.user_id = user.id), (tab = 0)"
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
import { SHOW_LINE, UPDATE_LINE, REMOVE_LINE } from "@/actions/project/line";
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
            updatingLine: false,
            removingLine: false,
            fetchingLine: false,
            lineToUpdate: this.cloneLine(this.line),
            userKeyword: "",
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

        /**
         *
         */
        async update() {
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
                this.lineToUpdate = this.cloneLine(newValue);
                this.tab = 0;

                this.fetchingLine = true;

                try {
                    this.lineToUpdate = this.cloneLine(
                        await store.dispatch(SHOW_LINE, newValue.id)
                    );
                } finally {
                    this.fetchingLine = false;
                }
            }
        },
    },

    computed: {
        ...mapGetters(["line", "can", "users"]),

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
