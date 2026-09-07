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
                    <v-field
                        v-for="field in operatorFields"
                        :key="field.key"
                        :label="field.label"
                        required
                        v-slot="{ label }"
                        ><input
                            :type="field.type"
                            :placeholder="label + ' ...'"
                            v-model="lineToUpdate.config[field.key]"
                            required
                    /></v-field>
                    <kavkom-diagnostic
                        v-if="lineToUpdate.operator === 'kavkom'"
                        :config="lineToUpdate.config"
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
import KavkomDiagnostic from "../KavkomDiagnostic.vue";
import ToUserRow from "../ToUserRow.vue";

export default {
    components: {
        KavkomDiagnostic,
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
            this.updatingLine = true;

            try {
                await store.dispatch(UPDATE_LINE, this.lineToUpdate);
            } finally {
                this.updatingLine = false;
                store.commit(CLOSE_MODAL);
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
