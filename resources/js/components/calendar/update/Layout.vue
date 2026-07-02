<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1">
        <template #1>
            <!-- Update form -->
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="calendarToUpdate"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="p"
                        ><input
                            ref="calendarName"
                            :placeholder="p.calendar + ' ...'"
                            v-model="calendarToUpdate.name"
                            :style="{
                                color: calendarToUpdate.color,
                                backgroundColor: calendarToUpdate.bgcolor,
                            }"
                            required
                    /></v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="calendarToUpdate.color"
                    /></v-field>
                    <color-palette v-model="calendarToUpdate.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="calendarToUpdate.bgcolor"
                    /></v-field>
                    <color-palette v-model="calendarToUpdate.bgcolor" />
                    <v-field :label="$t('type')">
                        <select v-model="calendarToUpdate.type">
                            <option
                                value="physical"
                                v-text="$t('calendar.types.physical')"
                            ></option>
                            <option
                                value="telephone"
                                v-text="$t('calendar.types.telephone')"
                            ></option>
                            <option
                                value="hangout"
                                v-text="$t('calendar.types.hangout')"
                            ></option>
                            <option
                                value="task"
                                v-text="$t('calendar.types.task')"
                            ></option>
                            <option
                                value="other"
                                v-text="$t('calendar.types.other')"
                            ></option>
                        </select>
                    </v-field>
                    <v-field
                        v-if="calendarToUpdate.type == 'physical'"
                        :label="$t('calendar.update.default_address')"
                    >
                        <google-map-input v-model="defaultAddress" />
                    </v-field>
                </item-list>
                <item @click="tab = 2">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('update')"></button>
                    <button
                        v-if="can('all.project.calendar.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button
                        @click.prevent="tab = 1"
                        class="hc-button-danger"
                        v-text="$t('label.combine_with')"
                    ></button>
                </buttons>
                <loading :loading="updatingCalendar || removingCalendar" />
            </form>
        </template>

        <!-- Combine 2 calendars -->
        <template #2>
            <item @click="tab = 0" class="bordered">
                <icon class="fa fa-caret-left" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('calendar.combine_with') + ' ...'"
                ></div>
            </item>
            <search v-model="calendarKeyword" />
            <item-list
                class="hc-flex-1"
                padding="5px"
                style="max-height: 280px"
            >
                <template v-for="l in filteredCalendars" :key="l.id"
                    ><combine-calendar-row
                        v-if="l.id != calendar.id"
                        :calendar="l"
                        @click="combine(l)"
                /></template>
            </item-list>
            <loading :loading="combiningCalendar" />
        </template>

        <!-- Affected users -->
        <template #3>
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
                    <!-- All -->
                    <item tag="label">
                        <icon class="fa fa-check-square" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('all')"
                        ></div>
                        <checkbox v-model="all" />
                    </item>
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        :calendar="calendar"
                        v-model="calendarUsers"
                    />
                </item-list>
                <loading :loading="fetchingCalendarUsers" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserCalendarService from "@/apis/project/user/calendar";

// Actions
import { UPDATE_CALENDAR, REMOVE_CALENDAR } from "@/actions/project/calendar";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import {
    BULK_REMOVE_USER_CALENDAR,
    BULK_ADD_USER_CALENDAR,
} from "@/actions/project/user/calendar";

import CombineCalendarRow from "./CombineCalendarRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        CombineCalendarRow,
        UserRow,
    },

    data() {
        return {
            updatingCalendar: false,
            removingCalendar: false,
            fetchingCalendar: false,
            combiningCalendar: false,
            fetchingCalendarUsers: false,
            calendarToUpdate: this.calendar,
            calendarKeyword: "",
            userKeyword: "",
            defaultAddress: null,
            calendarUsers: [],
            tab: 0,
        };
    },

    created() {
        this.calendarToUpdate = this.calendar;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingCalendar = true;

            try {
                await store.dispatch(UPDATE_CALENDAR, this.calendarToUpdate);
                this.updateDefaultAddress();
            } finally {
                this.updatingCalendar = false;
                store.commit(CLOSE_MODAL);
                store.dispatch(FETCH_PROSPECTS);
            }
        },

        async updateDefaultAddress() {
            if (!this.defaultAddress) {
                return;
            }

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "calendar-default-address." + this.calendarToUpdate.id,
                value: this.defaultAddress,
            });
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingCalendar = true;

                try {
                    await store.dispatch(REMOVE_CALENDAR, {
                        calendar: this.calendarToUpdate,
                    });
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingCalendar = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async combine(calendar) {
            this.combiningCalendar = true;

            try {
                await store.dispatch(REMOVE_CALENDAR, {
                    calendar: this.calendarToUpdate,
                    params: {
                        combine: calendar.id,
                    },
                });
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.combiningCalendar = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        async fetchCalendarUsers() {
            this.fetchingCalendarUsers = true;

            try {
                const { data } = await UserCalendarService.users(
                    this.project.slug,
                    this.calendar.id
                );
                this.calendarUsers = data.map((user) => user.id);
            } finally {
                this.fetchingCalendarUsers = false;
            }
        },
    },

    watch: {
        async calendar(newValue) {
            if (newValue) {
                this.calendarToUpdate = newValue;

                /*this.fetchingCalendar = true;
                this.calendarToUpdate = await store.dispatch(
                    SHOW_CALENDAR,
                    newValue.id
                );
                this.fetchingCalendar = false;*/
            }
        },

        tab(newValue) {
            if (newValue == 2) {
                this.fetchCalendarUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "calendar", "calendars", "users", "can"]),

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.calendarUsers.length == this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    store.dispatch(BULK_ADD_USER_CALENDAR, {
                        users,
                        calendars: [this.calendar.id],
                    });
                    this.calendarUsers = users;
                } else {
                    store.dispatch(BULK_REMOVE_USER_CALENDAR, {
                        users: this.users.map((f) => f.id),
                        calendars: [this.calendar.id],
                    });
                    this.calendarUsers = [];
                }
            },
        },

        /**
         *
         */
        filteredCalendars() {
            const keyword = removeStringAccent(this.calendarKeyword);

            return this.calendars.filter(
                (calendar) =>
                    removeStringAccent(calendar.name).indexOf(keyword) >= 0
            );
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
