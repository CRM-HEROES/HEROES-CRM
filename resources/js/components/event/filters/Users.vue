<template>
    <div :class="['hc-agenda-users', agendaFilter ? '' : 'hide']">
        <div id="hc-agenda-filters-header" class="hc-flex-row">
            <div
                id="hc-agenda-filters-header-title"
                class="hc-flex-1"
                v-text="$t('event.agenda.filters.title')"
            ></div>
            <a
                @click="toggleAgendaFilter"
                id="hc-agenda-filters-header-close"
                class="hc-flex-column hc-flex-center"
            >
                &times;
            </a>
        </div>
        <tab-layout :count="2" :tab="tab" style="width: 100%">
            <template #1>
                <item-list class="hc-flex-column" style="height: 100%">
                    <item tag="a" @click.prevent="(tab = 1), (frameTab = 0)">
                        <icon class="fa fa-users icon-brown" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('event.agenda.filters.users')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item tag="a" @click.prevent="(tab = 1), (frameTab = 1)">
                        <icon class="fa fa-calendar icon-purple" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('event.agenda.filters.calendars')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item tag="a" @click.prevent="(tab = 1), (frameTab = 2)">
                        <icon class="fa fa-tags icon-brown" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('event.agenda.filters.labels')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item tag="a" @click.prevent="(tab = 1), (frameTab = 3)">
                        <icon class="fa fa-palette icon-brown" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                'Colorer avec le filtre ' +
                                (colorByLabel ? colorByLabel.name : '...')
                            "
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-thumbs-up" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t(
                                    'prospect.table.filters.appointments.confirmed'
                                )
                            "
                        ></div>
                        <checkbox
                            v-model="filterConfirmed"
                            style="margin-right: 5px"
                        />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-thumbs-down" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t(
                                    'prospect.table.filters.appointments.not_confirmed'
                                )
                            "
                        ></div>
                        <checkbox
                            v-model="filterNotConfirmed"
                            style="margin-right: 5px"
                        />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-check" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t('prospect.table.filters.appointments.done')
                            "
                        ></div>
                        <checkbox
                            v-model="filterDone"
                            style="margin-right: 5px"
                        />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-spinner" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t(
                                    'prospect.table.filters.appointments.not_done'
                                )
                            "
                        ></div>
                        <checkbox
                            v-model="filterNotDone"
                            style="margin-right: 5px"
                        />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-columns icon-blue" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('event.agenda.filters.show_we')"
                        ></div>
                        <checkbox v-model="showWeekEnd" />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-calendar icon-blue" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t('event.agenda.filters.show_other_projects')
                            "
                        ></div>
                        <checkbox v-model="showOtherProjects" />
                    </item>
                    <item tag="label">
                        <icon class="fa fa-clock" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('event.agenda.filters.min_hour')"
                        ></div>
                        <select v-model="minimumAgendaHour">
                            <option
                                v-for="i in 24"
                                :value="i - 1"
                                v-text="
                                    (i - 1 < 10 ? '0' : '') + (i - 1) + ':00'
                                "
                            ></option>
                        </select>
                    </item>
                    <item tag="label">
                        <icon class="fa fa-clock" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('event.agenda.filters.max_hour')"
                        ></div>
                        <select v-model="maximumAgendaHour">
                            <template v-for="i in 25">
                                <option
                                    v-if="i > minimumAgendaHour"
                                    :value="i - 1"
                                    v-text="
                                        (i - 1 < 10 ? '0' : '') +
                                        (i - 1) +
                                        ':00'
                                    "
                                ></option>
                            </template>
                        </select>
                    </item>
                </item-list>
            </template>

            <template #2>
                <frame-layout :count="4" :tab="frameTab" class="hc-flex-1">
                    <!-- Users -->
                    <template #1>
                        <tab-layout
                            :count="2"
                            :tab="userTab"
                            style="width: 100%"
                        >
                            <!-- Users -->
                            <template #1>
                                <div
                                    class="hc-flex-column"
                                    style="height: 100%"
                                >
                                    <!-- Back -->
                                    <item
                                        class="hc-item.bordered"
                                        tag="a"
                                        @click.prevent="tab = 0"
                                    >
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="
                                                $t('event.agenda.filters.users')
                                            "
                                        ></div>
                                    </item>
                                    <!-- Search user -->
                                    <search v-model="userKeyword" />
                                    <!-- Choose a role -->
                                    <item
                                        class="hc-item.bordered"
                                        tag="a"
                                        @click.prevent="userTab = 1"
                                    >
                                        <icon class="fa fa-user-tie" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="
                                                $t(
                                                    'event.agenda.filters.choose_role'
                                                )
                                            "
                                        ></div>
                                        <icon class="fa fa-caret-right" />
                                    </item>
                                    <!-- Users list -->
                                    <item-list class="hc-flex-1" padding="12px">
                                        <item tag="label">
                                            <icon class="fa fa-check-square" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="$t('all')"
                                            ></div>
                                            <checkbox v-model="allUsers" />
                                        </item>
                                        <user-row
                                            v-for="user in filteredUsers"
                                            :key="user.id"
                                            :user="user"
                                        />
                                    </item-list>
                                </div>
                            </template>

                            <!-- Roles -->
                            <template #2>
                                <div
                                    class="hc-flex-column"
                                    style="height: 100%"
                                >
                                    <!-- Back -->
                                    <item
                                        class="hc-item.bordered"
                                        tag="a"
                                        @click.prevent="userTab = 0"
                                    >
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="
                                                $t(
                                                    'event.agenda.filters.choose_role'
                                                )
                                            "
                                        ></div>
                                    </item>
                                    <!-- Search role -->
                                    <search v-model="roleKeyword" />
                                    <!-- Roles list -->
                                    <item-list class="hc-flex-1" padding="12px">
                                        <role-row
                                            v-for="role in filteredRoles"
                                            :key="role.id"
                                            :role="role"
                                            @select="selectRole"
                                        />
                                    </item-list>
                                </div>
                            </template>
                        </tab-layout>
                    </template>

                    <!-- Calendars -->
                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <item
                                class="hc-item.bordered"
                                tag="a"
                                @click.prevent="tab = 0"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t('event.agenda.filters.calendars')
                                    "
                                ></div>
                            </item>
                            <search v-model="calendarKeyword" />
                            <item-list class="hc-flex-1" padding="12px">
                                <calendar-row
                                    v-for="calendar in filteredCalendars"
                                    :key="calendar.id"
                                    :calendar="calendar"
                                />
                            </item-list>
                        </div>
                    </template>

                    <!-- Labels -->
                    <template #3>
                        <tab-layout
                            :count="2"
                            :tab="labelTab"
                            style="width: 100%"
                        >
                            <!-- Category -->
                            <template #1>
                                <div
                                    class="hc-flex-column"
                                    style="height: 100%"
                                >
                                    <!-- Back -->
                                    <item
                                        class="hc-item.bordered"
                                        tag="a"
                                        @click.prevent="tab = 0"
                                    >
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="
                                                $t(
                                                    'event.agenda.filters.labels'
                                                )
                                            "
                                        ></div>
                                    </item>
                                    <!-- Search category -->
                                    <search v-model="categoryKeyword" />
                                    <!-- Categories list -->
                                    <item-list class="hc-flex-1" padding="12px">
                                        <category-row
                                            v-for="c in filteredCategories"
                                            :key="c.id"
                                            :category="c"
                                            @click.prevent="
                                                (category = c), (labelTab = 1)
                                            "
                                        />
                                    </item-list>
                                </div>
                            </template>

                            <!-- Labels -->
                            <template #2>
                                <div
                                    v-if="category"
                                    class="hc-flex-column"
                                    style="height: 100%"
                                >
                                    <!-- Back -->
                                    <item
                                        class="hc-item.bordered"
                                        tag="a"
                                        @click.prevent="labelTab = 0"
                                    >
                                        <icon class="fa fa-caret-left" />
                                        <div
                                            class="hc-item-main-content"
                                            v-text="category.name"
                                        ></div>
                                    </item>
                                    <!-- Search role -->
                                    <search v-model="labelKeyword" />
                                    <!-- Roles list -->
                                    <item-list class="hc-flex-1" padding="12px">
                                        <label-row
                                            v-for="label in filteredLabels"
                                            :key="label.id"
                                            :label="label"
                                            v-model="filterLabels"
                                        />
                                    </item-list>
                                </div>
                            </template>
                        </tab-layout>
                    </template>

                    <!-- Color by labels -->
                    <template #4>
                        <div class="hc-flex-column" style="height: 100%">
                            <!-- Back -->
                            <item
                                class="hc-item-bordered"
                                tag="a"
                                @click.prevent="tab = 0"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="'Colorer les RDVs avec le filtre'"
                                ></div>
                            </item>
                            <!-- Search category -->
                            <search v-model="colorByCategoryKeyword" />
                            <!-- Categories list -->
                            <item-list class="hc-flex-1" padding="12px">
                                <item
                                    tag="a"
                                    @click.prevent="
                                        (colorByLabel = null), (tab = 0)
                                    "
                                >
                                    <icon class="fa fa-times icon-red" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="'Aucun'"
                                    ></div>
                                </item>
                                <color-by-category-row
                                    v-for="c in filteredColorByCategories"
                                    :key="c.id"
                                    :category="c"
                                    @click.prevent="
                                        (colorByLabel = c.id), (tab = 0)
                                    "
                                />
                            </item-list>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </div>
</template>

<style>
#hc-agenda-filters-header {
    border-bottom: 1px solid #eeeeee;
    display: flex;
    flex-direction: row;
    font-size: 13px;
    align-items: center;
    position: relative;
}

#hc-agenda-filters-header-title {
    font-weight: bold;
    font-size: 17px;
    padding-left: 20px;
    height: 40px;
    line-height: 40px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#hc-agenda-filters-header-close {
    height: 30px;
    text-align: center;
    width: 30px;
    font-weight: bold;
    font-size: 15px;
    color: #888888;
    cursor: pointer;
    margin: 5px;
    border-radius: 15px;
    border-radius: 15px;
    font-weight: 200;
    text-decoration: none;
}

#hc-agenda-filters-header-close:hover {
    background-color: #eeeeee;
    color: #555555;
    text-decoration: none;
}

.hc-agenda-users {
    width: 280px;
    height: 100%;
    overflow: auto;
    background-color: white;
    border-right: 1px solid #dddddd;
    display: flex;
    flex-direction: column;
    transition: all 100ms ease-out;
}

.hc-agenda-users.hide {
    width: 0;
    border: none;
}

.hc-agenda-users-list {
    padding: 5px 10px;
    width: 100%;
    flex: 1;
    overflow: auto;
}

.hc-agenda-users-list > ul {
    margin: 0;
    padding: 5px 0;
    list-style: none;
    width: 100%;
    border-bottom: 1px solid #eee;
}

.hc-agenda-users-list > ul > li {
    width: 100%;
    margin: 2px 0;
}

@media (max-width: 767px) {
    .hc-agenda-users {
        width: 440px;
    }
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Components
import UserRow from "./UserRow.vue";
import RoleRow from "./RoleRow.vue";
import CalendarRow from "./CalendarRow.vue";
import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";
import ColorByCategoryRow from "./ColorByCategoryRow.vue";

import {
    FETCH_EVENTS,
    SET_EVENTS_FIELDS,
    ADD_EVENT_PARAMS,
    REMOVE_EVENT_PARAMS,
    SET_AGENDA_FILTER,
    SHOW_AGENDA_WEEK_END,
    SHOW_AGENDA_OTHER_PROJECTS,
} from "@/actions/project/event";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

export default {
    components: {
        UserRow,
        RoleRow,
        CalendarRow,
        CategoryRow,
        LabelRow,
        ColorByCategoryRow,
    },

    data() {
        return {
            tab: 0,
            frameTab: 0,
            userTab: 0,
            labelTab: 0,
            category: null,
            userKeyword: "",
            roleKeyword: "",
            calendarKeyword: "",
            categoryKeyword: "",
            colorByCategoryKeyword: "",
            labelKeyword: "",
        };
    },

    methods: {
        toggleAgendaFilter() {
            store.commit(SET_AGENDA_FILTER, !this.agendaFilter);
        },

        selectRole(role) {
            this.users
                .filter((user) => user.roles.find((r) => r.id == role.id))
                .forEach((user) => {
                    store.commit(ADD_EVENT_PARAMS, {
                        key: "withUsers",
                        value: user.id,
                        multiple: true,
                    });
                });

            this.userTab = 0;
            store.dispatch(FETCH_EVENTS);
        },

        fetchEvents() {
            let fields = ["calendar", "prospect", "user", "users"];
            if (this.projectUserSettingsEventsColorByLabel) {
                fields.push(
                    "category->" + this.projectUserSettingsEventsColorByLabel
                );
            }

            store.commit(SET_EVENTS_FIELDS, fields.join(","));
            store.dispatch(FETCH_EVENTS);
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "users",
            "roles",
            "calendars",
            "categories",
            "eventsParamExists",
            "eventsParamsValue",
            "eventsParams",
            "agendaFilter",
            "agendaWeekEnd",
            "agendaOtherProjects",
            "projectUserSettingsAgendaMinimumHour",
            "projectUserSettingsAgendaMaximumHour",
            "projectUserSettingsEventsColorByLabel",
        ]),

        showWeekEnd: {
            get() {
                return this.agendaWeekEnd;
            },
            set(value) {
                store.commit(SHOW_AGENDA_WEEK_END, value);
            },
        },

        showOtherProjects: {
            get() {
                return this.agendaOtherProjects;
            },
            set(value) {
                store.commit(SHOW_AGENDA_OTHER_PROJECTS, value);
            },
        },

        /**
         *
         */
        filterConfirmed: {
            get() {
                return (
                    this.eventsParamExists("confirmed") &&
                    this.eventsParamsValue("confirmed") == 1
                );
            },
            set(value) {
                if (value) {
                    store.commit(ADD_EVENT_PARAMS, {
                        key: "confirmed",
                        value: 1,
                    });
                } else {
                    store.commit(REMOVE_EVENT_PARAMS, {
                        key: "confirmed",
                    });
                }
                store.dispatch(FETCH_EVENTS);
            },
        },

        /**
         *
         */
        filterNotConfirmed: {
            get() {
                return (
                    this.eventsParamExists("confirmed") &&
                    this.eventsParamsValue("confirmed") == 0
                );
            },
            set(value) {
                if (value) {
                    store.commit(ADD_EVENT_PARAMS, {
                        key: "confirmed",
                        value: 0,
                    });
                } else {
                    store.commit(REMOVE_EVENT_PARAMS, {
                        key: "confirmed",
                    });
                }
                store.dispatch(FETCH_EVENTS);
            },
        },

        /**
         *
         */
        filterDone: {
            get() {
                return (
                    this.eventsParamExists("done") &&
                    this.eventsParamsValue("done") == 1
                );
            },
            set(value) {
                if (value) {
                    store.commit(ADD_EVENT_PARAMS, {
                        key: "done",
                        value: 1,
                    });
                } else {
                    store.commit(REMOVE_EVENT_PARAMS, {
                        key: "done",
                    });
                }
                store.dispatch(FETCH_EVENTS);
            },
        },

        /**
         *
         */
        filterNotDone: {
            get() {
                return (
                    this.eventsParamExists("done") &&
                    this.eventsParamsValue("done") == 0
                );
            },
            set(value) {
                if (value) {
                    store.commit(ADD_EVENT_PARAMS, {
                        key: "done",
                        value: 0,
                    });
                } else {
                    store.commit(REMOVE_EVENT_PARAMS, {
                        key: "done",
                    });
                }
                store.dispatch(FETCH_EVENTS);
            },
        },

        /**
         *
         */
        filterLabels: {
            get() {
                const eventsParams = this.eventsParams;

                if (!eventsParams || !eventsParams.withProspects) {
                    return {};
                }

                return Object.fromEntries(
                    Object.entries(eventsParams.withProspects)
                        .filter(([k, v]) => k.indexOf("withCategory_") == 0)
                        .map(([k, v]) => [k.substring(0, 13), v])
                );
            },
            set(value) {
                store.commit(ADD_EVENT_PARAMS, {
                    key: "withProspects",
                    value: Object.fromEntries(
                        Object.entries(value).map(([k, v]) => [
                            "withCategory_" + k,
                            v,
                        ])
                    ),
                });
                store.dispatch(FETCH_EVENTS);
            },
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0
                )
                .sort((user1, user2) =>
                    user1.pivot &&
                    user2.pivot &&
                    user1.pivot.relevance_event > user2.pivot.relevance_event
                        ? -1
                        : 1
                );
        },

        /**
         *
         */
        filteredRoles() {
            const keyword = removeStringAccent(this.roleKeyword);

            return this.roles.filter(
                (role) => removeStringAccent(role.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredCalendars() {
            const keyword = removeStringAccent(this.calendarKeyword);

            return this.calendars
                .filter(
                    (calendar) =>
                        removeStringAccent(calendar.name).indexOf(keyword) >= 0
                )
                .sort((calendar1, calendar2) =>
                    calendar1.relevance > calendar2.relevance ? -1 : 1
                );
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    (removeStringAccent(category.name).indexOf(keyword) >= 0 ||
                        category.labels.find(
                            (label) =>
                                removeStringAccent(label.name).indexOf(
                                    keyword
                                ) >= 0
                        ))
            );
        },

        /**
         *
         */
        filteredColorByCategories() {
            const keyword = removeStringAccent(this.colorByCategoryKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    removeStringAccent(category.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredLabels() {
            if (!this.category) {
                return [];
            }

            const category = this.categories.find(
                (c) => c.id == this.category.id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            const keyword = removeStringAccent(this.labelKeyword);

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

        minimumAgendaHour: {
            get() {
                return this.projectUserSettingsAgendaMinimumHour;
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.minimum-hour",
                    value: parseInt(value),
                });
            },
        },

        maximumAgendaHour: {
            get() {
                return this.projectUserSettingsAgendaMaximumHour;
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.maximum-hour",
                    value: parseInt(value),
                });
            },
        },

        colorByLabel: {
            get() {
                return this.categories.find(
                    (c) => c.id == this.projectUserSettingsEventsColorByLabel
                );
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.color-by-label",
                    value: value,
                });
                store.dispatch(FETCH_EVENTS, {
                    key: "events.color-by-label",
                    value: value,
                });
                this.fetchEvents();
            },
        },

        /**
         *
         */
        allUsers: {
            get: function () {
                for (let i in this.filteredUsers) {
                    if (
                        !this.eventsParamExists(
                            "withUsers",
                            this.filteredUsers[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(ADD_EVENT_PARAMS, {
                        key: "withUsers",
                        value: this.filteredUsers.map((user) => user.id),
                    });
                    store.dispatch(FETCH_EVENTS);
                } else {
                    store.commit(ADD_EVENT_PARAMS, {
                        key: "withUsers",
                        value: [],
                    });

                    store.dispatch(FETCH_EVENTS);
                }
            },
        },
    },
};
</script>
