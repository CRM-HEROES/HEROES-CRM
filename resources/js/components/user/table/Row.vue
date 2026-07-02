<template>
    <tr
        :class="[
            user.deleted_at ? 'deleted' : '',
            user.processed_at ? 'processed' : '',
        ]"
    >
        <td class="fixed">
            <!-- Select user -->
            <label class="hc-table-selector">
                <input
                    type="checkbox"
                    v-model="selected"
                    :value="user.id"
                    @click="toggleSelectedUser"
                />
                <span></span>
            </label>

            <div class="hc-table-row-options">
                <!-- View user -->
                <a class="hc-table-user-option-view" @click.prevent="showUser"
                    ><i class="fa fa-eye"></i
                ></a>
                <!-- Prospects table columns -->
                <a
                    class="hc-table-user-option-view"
                    @click.prevent="prospectsTableSetting"
                    ><i class="fa fa-columns"></i
                ></a>
                <!-- View user prospects -->
                <a
                    class="hc-table-user-option-view"
                    @click.prevent="showProspects"
                    ><i class="fa fa-users"></i
                ></a>
                <!-- Manage file -->
                <a
                    class="hc-table-user-option-view"
                    @click.prevent="manageFiles"
                    ><i class="fa fa-folder"></i
                ></a>
                <!-- Reset password -->
                <!--a
                    class="hc-table-user-option-view"
                    @click.prevent="resetPassword"
                    v-tooltip="'Envoyer un email de création de mot de passe'"
                >
                    <i class="fa fa-wifi"></i>
                    <loading :loading="resetingPassword" />
                </a-->
            </div>

            <!-- List of fixed columns -->
            <div class="hc-table-fixed-cells">
                <cell
                    v-for="column in fixedColumns"
                    tag="div"
                    :key="column.key"
                    :user="user"
                    :column="column"
                />
            </div>
        </td>

        <!-- List of none fixed columns -->
        <cell
            v-for="column in notFixedColumns"
            :key="column.key"
            :user="user"
            :column="column"
        />
    </tr>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_SELECTED_USERS,
    TOGGLE_SELECTED_USERS,
} from "@/actions/project/user";
import { SET_USER } from "@/actions/project/user";
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Cell from "./Cell.vue";

export default {
    components: {
        Cell,
    },

    props: {
        /**
         * User
         */
        user: {
            type: Object,
        },

        /**
         * List of fixed columns
         */
        fixedColumns: {
            type: Array,
        },

        /**
         * List of none fixed columns
         */
        notFixedColumns: {
            type: Array,
        },

        /**
         * Index of the row in the users table
         * we need it when we use SHIFT
         * to select multiple users
         * See: toggleSelectedUser
         */
        index: {
            type: Number,
        },
    },

    data() {
        return {
            resetingPassword: false,
        };
    },

    methods: {
        /**
         * Handle SHIFT
         * when clicking checkbox
         *
         * @param {*} event
         */
        toggleSelectedUser(event) {
            const index = this.index;
            const shift = event.shiftKey;
            const checked = event.currentTarget.checked;

            store.commit(TOGGLE_SELECTED_USERS, { index, shift, checked });
        },

        /**
         * Go to the user profile
         */
        showUser() {
            store.commit(SET_USER, this.user);
            this.$router.push({
                name: "user.show",
                params: {
                    project: this.project.slug,
                    user: this.user.id,
                },
            });
        },

        /**
         * Manage prospects table columns
         */
        prospectsTableSetting() {
            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "prospects-table-columns-setting");
        },

        manageFiles() {
            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-files");
        },

        /**
         * Go to the user prospects
         */
        showProspects() {
            this.$router.push({
                name: "prospect",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify({ scopeUser: this.user.id }),
                },
            });
        },

        async resetPassword() {
            this.resetingPassword = true;
            await axios
                .post("/password/email", {
                    email: this.user.email,
                })
                .then((response) => {})
                .catch(({ response: { data } }) => {
                    flashError({
                        title: this.$t("auth.init_password.title"),
                        body: this.$t("auth.init_password.error"),
                    });
                })
                .finally(() => {
                    this.resetingPassword = false;
                });
        },
    },

    computed: {
        /**
         * Select a row
         */
        selected: {
            get() {
                return this.usersSelected;
            },
            set(value) {
                store.commit(UPDATE_SELECTED_USERS, value);
            },
        },

        ...mapGetters(["project", "usersSelected", "usersParamExists"]),
    },
};
</script>
