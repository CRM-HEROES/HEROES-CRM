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
    UPDATE_GLOBAL_SELECTED_USERS,
    TOGGLE_GLOBAL_SELECTED_USERS,
} from "@/actions/user";
import { SET_GLOBAL_USER } from "@/actions/user";

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

    methods: {
        /**
         * Handle SHIFT
         * when cliicking checkbox
         *
         * @param {*} event
         */
        toggleSelectedUser(event) {
            const index = this.index;
            const shift = event.shiftKey;
            const checked = event.currentTarget.checked;

            store.commit(TOGGLE_GLOBAL_SELECTED_USERS, {
                index,
                shift,
                checked,
            });
        },

        /**
         * Go to the user profile
         */
        showUser() {
            store.commit(SET_GLOBAL_USER, this.user);
            this.$router.push({
                name: "global.user.show",
                params: {
                    user: this.user.id,
                },
            });
        },
    },

    computed: {
        /**
         * Select a row
         */
        selected: {
            get() {
                return this.globalUsersSelected;
            },
            set(value) {
                store.commit(UPDATE_GLOBAL_SELECTED_USERS, value);
            },
        },

        ...mapGetters([
            "project",
            "globalUsersSelected",
            "globalUsersParamExists",
        ]),
    },
};
</script>
