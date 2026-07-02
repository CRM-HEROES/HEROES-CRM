<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.event.title')"
        icon="fa fa-calendar"
        style="width: 340px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <item-list style="height: 100%; overflow: auto">
                    <item-list class="hc-flex-1" padding="12px">
                        <item
                            class="hc-prospect-event-item hc-prospect-event-item-calendar"
                            @click="(frameTab = 0), (tab = 1)"
                            :style="calendarStyle"
                        >
                            <icon :class="calendarIcon" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.event.calendar.title'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-prospect-event-item-value"
                                    v-text="
                                        calendar
                                            ? calendar.name
                                            : $t(
                                                  'campaign.action.prospect.event.calendar.placeholder'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <item
                            class="hc-prospect-event-item"
                            @click="(frameTab = 1), (tab = 1)"
                        >
                            <icon class="fa fa-user-tie" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.event.user.title'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-prospect-event-item-value"
                                    v-text="
                                        user
                                            ? user.name
                                            : $t(
                                                  'campaign.action.prospect.event.user.placeholder'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <item
                            class="hc-prospect-event-item hc-prospect-event-item-date"
                        >
                            <icon class="fa fa-clock" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.event.date.title'
                                        )
                                    "
                                ></span>
                                <div
                                    class="hc-prospect-event-item-value hc-flex-row"
                                    style="gap: 2px; height: 30px"
                                >
                                    <select
                                        style="-webkit-appearance: none"
                                        v-model="action.value.min_days"
                                        @click.stop
                                    >
                                        <option
                                            value=""
                                            v-text="
                                                $t(
                                                    'campaign.action.prospect.event.date.min'
                                                )
                                            "
                                        ></option>
                                        <option
                                            v-for="i in 30"
                                            :value="i"
                                            v-text="i"
                                        ></option>
                                    </select>
                                    <select
                                        style="-webkit-appearance: none"
                                        v-model="action.value.max_days"
                                        @click.stop
                                    >
                                        <option
                                            value=""
                                            v-text="
                                                $t(
                                                    'campaign.action.prospect.event.date.max'
                                                )
                                            "
                                        ></option>
                                        <template v-for="i in 30">
                                            <option
                                                v-if="
                                                    i >= action.value.min_days
                                                "
                                                :value="i"
                                                v-text="i"
                                            ></option>
                                        </template>
                                    </select>
                                    <select
                                        style="flex: 1"
                                        v-model="action.value.duration"
                                        @click.stop
                                    >
                                        <option
                                            value=""
                                            v-text="
                                                $t(
                                                    'campaign.action.prospect.event.duration'
                                                )
                                            "
                                        ></option>
                                        <option
                                            v-for="i in 8"
                                            :value="i * 15"
                                            v-text="i * 15 + ' mn'"
                                        ></option>
                                    </select>
                                </div>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                    </item-list>
                    <buttons>
                        <campaign-template
                            tag="button"
                            :field="action"
                            @dragging="dragging"
                            @dragged="dragged"
                            v-text="$t('add')"
                            :disabled="
                                !action.value.calendar ||
                                !action.value.user ||
                                !action.value.min_days ||
                                !action.value.max_days
                            "
                        ></campaign-template>
                    </buttons>
                </item-list>
            </template>

            <template #2>
                <frame-layout :count="2" :tab="frameTab" class="hc-flex-1">
                    <template #1>
                        <div
                            class="hc-flex-column"
                            style="height: 100%; position: relative"
                        >
                            <item
                                @click="tab = 0"
                                style="border-bottom: 1px solid #eee"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.event.calendar.placeholder'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="calendarKeyword" />
                            <item-list
                                class="hc-flex-1"
                                padding="12px"
                                style="position: relative"
                            >
                                <calendar-row
                                    v-for="calendar in filteredCalendars"
                                    :key="calendar.id"
                                    :calendar="calendar"
                                    @click="
                                        (action.value.calendar = calendar.id),
                                            (tab = 0)
                                    "
                                />
                                <loading :loading="fetchingCalendars" />
                            </item-list>
                            <buttons>
                                <a
                                    @click.prevent="addCalendar"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                    <template #2>
                        <div
                            class="hc-flex-column"
                            style="height: 100%; position: relative"
                        >
                            <item
                                @click="tab = 0"
                                style="border-bottom: 1px solid #eee"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.event.user.placeholder'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="userKeyword" />
                            <item-list
                                class="hc-flex-1"
                                padding="12px"
                                style="position: relative"
                            >
                                <user-row
                                    v-for="user in filteredUsers"
                                    :key="user.id"
                                    :user="user"
                                    @click="
                                        (action.value.user = user.id), (tab = 0)
                                    "
                                />
                                <loading :loading="fetchingUsers" />
                            </item-list>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-prospect-event-item {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-prospect-event-item-title {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}

.hc-prospect-event-item-value {
    color: #000000;
}

.hc-prospect-event-item-calendar {
    padding: 2px 0 !important;
    align-items: center;
}

.hc-prospect-event-item-calendar * {
    color: inherit !important;
    background-color: inherit !important;
}

.hc-prospect-event-item-date input,
.hc-prospect-event-item-date select {
    border: none;
    padding: 0 8px;
    border-radius: 5px;
    background: #e8e8e8;
}

.hc-prospect-event-item-location .hc-text-editor-buttons {
    justify-content: left;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { FETCH_CALENDARS } from "@/actions/project/calendar";
import { FETCH_USERS } from "@/actions/project/user";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import CalendarRow from "./CalendarRow.vue";
import UserRow from "./UserRow.vue";

import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        CalendarRow,
        UserRow,
        CampaignTemplate,
    },

    data() {
        return {
            name: "campaign-action-prospect-event",
            tab: 0,
            frameTab: 0,
            calendarKeyword: "",
            userKeyword: "",

            fetchingCalendars: false,
            fetchingUsers: false,

            action: {
                action: "prospect-event",
                name: "Créer un RDV",
                value: {
                    calendar: null,
                    user: null,
                    min_days: 1,
                    max_days: 1,
                    duration: 15,
                },
                category: "action",
                style: {
                    width: "200px",
                },
            },
        };
    },

    methods: {
        /**
         *
         */
        addCalendar() {
            store.commit(OPEN_MODAL, "calendar-add");
        },

        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },

    watch: {
        async tab(newValue) {
            if (newValue == 1) {
                // Calendars
                if (this.frameTab == 0) {
                    if (this.calendars.length == 0) {
                        this.fetchingCalendars = true;
                    }

                    try {
                        await store.dispatch(FETCH_CALENDARS);
                    } finally {
                        this.fetchingCalendars = false;
                    }
                    // Users
                } else if (this.frameTab == 1) {
                    this.fetchingUsers = true;

                    try {
                        // store.dispatch(FETCH_USERS);
                    } finally {
                        this.fetchingUsers = false;
                    }
                }
            }
        },

        user() {
            this.action.name = this.actionName;
        },

        calendar() {
            this.action.name = this.actionName;
            this.action.style.color = this.calendar.color;
            this.action.style.backgroundColor = this.calendar.bgcolor;
        },

        minDays() {
            this.action.name = this.actionName;

            if (this.minDays > this.maxDays) {
                this.action.value.max_days = this.minDays;
            }
        },

        maxDays() {
            this.action.name = this.actionName;
        },

        duration() {
            this.action.name = this.actionName;
        },
    },

    computed: {
        ...mapGetters(["project", "calendars", "users", "roles"]),

        /**
         *
         */
        user() {
            return this.allUsers.find((u) => u.id == this.action.value.user);
        },

        /**
         *
         */
        calendar() {
            return this.calendars.find(
                (c) => c.id == this.action.value.calendar
            );
        },

        /**
         *
         */
        minDays() {
            return this.action.value.min_days;
        },

        /**
         *
         */
        maxDays() {
            return this.action.value.max_days;
        },

        /**
         *
         */
        duration() {
            return this.action.value.duration;
        },

        /**
         *
         */
        actionName() {
            if (!this.calendar) {
                return;
            }

            if (!this.user) {
                return;
            }

            if (!this.minDays) {
                return;
            }

            if (!this.maxDays) {
                return;
            }

            return this.$t("campaign.action.prospect.event.name", {
                calendar: this.calendar.name,
                user: this.user.name,
                min_days: this.minDays,
                max_days: this.maxDays,
                duration: this.duration,
            });
        },

        /**
         *
         */
        calendarStyle() {
            return {
                color: this.calendar ? this.calendar.color : "#000000",
                backgroundColor: this.calendar
                    ? this.calendar.bgcolor
                    : "#EEEEEE",
            };
        },

        /**
         *
         */
        calendarIcon() {
            if (this.calendar) {
                if (this.calendar.type == "physical") {
                    return "fa fa-map-marker";
                } else if (this.calendar.type == "telephone") {
                    return "fa fa-phone";
                } else if (this.calendar.type == "hangout") {
                    return "fa fa-video";
                } else if (this.calendar.type == "task") {
                    return "fa fa-tasks";
                }
            }

            return "fa fa-calendar";
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

        allUsers() {
            return [
                {
                    id: "random",
                    name: this.$t("campaign.action.prospect.event.user.random"),
                },
                {
                    id: "prospect-creator",
                    name: this.$t(
                        "campaign.action.prospect.event.user.prospect_creator"
                    ),
                },
                {
                    id: "prospect-affected-user",
                    name: this.$t(
                        "campaign.action.prospect.event.user.prospect_affected_user"
                    ),
                },
                ...this.users,
                ...this.roles.map((role) => ({
                    id: "role." + role.id,
                    name: this.$t("campaign.action.prospect.event.user.role", {
                        role: role.name,
                    }),
                })),
            ];
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.allUsers.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
