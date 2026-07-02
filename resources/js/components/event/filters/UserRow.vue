<template>
    <item tag="a" @click.prevent="select">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content hc-flex-column">
            <div class="hc-agenda-user-name" v-text="user.name"></div>
            <div class="hc-agenda-user-roles" v-text="roles"></div>
        </div>
        <checkbox @click.stop :model-value="isChecked" @change="change" />
    </item>
</template>

<style>
.hc-agenda-user-name {
    color: #000000;
}

.hc-agenda-user-roles {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_EVENTS,
    ADD_EVENT_PARAMS,
    REMOVE_EVENT_PARAMS,
    SET_AGENDA_LIST,
} from "@/actions/project/event";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

export default {
    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        select() {
            store.commit(REMOVE_EVENT_PARAMS, {
                key: this.withoutKey,
            });

            store.commit(REMOVE_EVENT_PARAMS, {
                key: this.withKey,
            });

            store.commit(ADD_EVENT_PARAMS, {
                key: this.withKey,
                value: this.value,
                multiple: true,
            });
            store.dispatch(FETCH_EVENTS);
        },

        change(event) {
            // Remove without param
            store.commit(REMOVE_EVENT_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_EVENT_PARAMS : REMOVE_EVENT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_EVENTS);
            this.$emit("change", event);

            const eventParamsUsers = this.eventsParamsValue("withUsers");

            store.commit(
                SET_AGENDA_LIST,
                eventParamsUsers && eventParamsUsers.length > 1
            );

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "events.agenda.filters.users",
                value: eventParamsUsers,
            });
        },
    },

    computed: {
        ...mapGetters(["eventsParamExists", "eventsParamsValue"]),

        roles() {
            if (!this.user.roles || this.user.roles.length == 0) {
                return "Aucun rôle";
            }

            return this.user.roles
                .map((role) => role.name)
                .filter((role) => role)
                .join(", ");
        },

        /**
         *
         */
        withKey() {
            return "withUsers";
        },

        /**
         *
         */
        value() {
            return this.user.id;
        },

        /**
         *
         */
        isChecked() {
            return this.eventsParamExists(this.withKey, this.value);
        },
    },
};
</script>
