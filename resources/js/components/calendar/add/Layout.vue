<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeCalendar"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="calendar.name"
                            :style="{
                                color: calendar.color,
                                backgroundColor: calendar.bgcolor,
                            }"
                            required
                    /></v-field>
                    <v-field :label="$t('color')">
                        <input type="color" v-model="calendar.color" />
                    </v-field>
                    <color-palette v-model="calendar.color" />
                    <v-field :label="$t('bgcolor')">
                        <input type="color" v-model="calendar.bgcolor" />
                    </v-field>
                    <color-palette v-model="calendar.bgcolor" />
                    <v-field :label="$t('type')">
                        <select v-model="calendar.type">
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
                </item-list>
                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <div
                        v-if="calendarUsers.length"
                        class="hc-item-count"
                        v-text="calendarUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingCalendar" />
            </form>
        </template>

        <template #2>
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
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        v-model="calendarUsers"
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
import { ADD_CALENDAR } from "@/actions/project/calendar";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_CALENDAR } from "@/actions/project/user/calendar";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            calendar: this.newCalendar(),
            addingCalendar: false,
            userKeyword: "",
            calendarUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newCalendar() {
            return {
                name: "",
                color: "#000000",
                bgcolor: "#FFFFFF",
                type: "physical",
            };
        },

        /**
         *
         */
        async storeCalendar() {
            this.addingCalendar = true;

            try {
                const calendar = await store.dispatch(
                    ADD_CALENDAR,
                    this.calendar
                );

                if (this.calendarUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_CALENDAR, {
                        users: this.calendarUsers,
                        calendars: [calendar.id],
                    });
                }
            } finally {
                this.addingCalendar = false;
                this.calendar = this.newCalendar();
                this.calendarUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["users"]),

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
