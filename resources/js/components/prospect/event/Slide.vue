<template>
    <slide
        name="prospect-manage-events"
        :title="
            eventProspect
                ? [
                      prospectEvent.prospect.first_name,
                      prospectEvent.prospect.last_name,
                  ]
                      .filter((n) => n)
                      .join(' ')
                : project.slug == 'professionnel'
                ? 'Missions'
                : $t('event.title')
        "
        :url="
            eventProspect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: eventProspect.id,
                      },
                  }
                : null
        "
        icon="fa fa-calendar"
        style="width: 400px"
        @open="checkGoogleCalendarAccount(), fetchProspectLabels()"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <item-list style="height: 100%; overflow: auto">
                    <!-- Prospect actions -->

                    <item style="justify-content: end">
                        <!-- Google Calendar -->

                        <icon
                            v-if="event.location"
                            :size="30"
                            v-tooltip="
                                googleCalendar
                                    ? googleCalendar.name
                                    : 'Associer un compte Google Calendar'
                            "
                            tag="a"
                            @click.prevent.stop="toggleGoogleCalendarAccount"
                        >
                            <svg
                                style="height: 20px !important"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 30 30"
                            >
                                <path
                                    style="fill: #ffffff"
                                    d="M24.1,8.8L17,8L8.3,8.8l-0.8,7.9l0.8,7.9l7.9,1l7.9-1l0.8-8.1L24.1,8.8z"
                                />
                                <path
                                    style="fill: #4070b7"
                                    d="M11.6,21c-0.6-0.4-1-1-1.2-1.8l1.4-0.6c0.1,0.5,0.3,0.8,0.7,1.1c0.3,0.3,0.7,0.4,1.1,0.4 c0.4,0,0.8-0.1,1.2-0.4c0.3-0.3,0.5-0.6,0.5-1c0-0.4-0.2-0.8-0.5-1.1c-0.3-0.3-0.8-0.4-1.3-0.4h-0.8v-1.4h0.7 c0.4,0,0.8-0.1,1.1-0.4c0.3-0.2,0.4-0.6,0.4-1c0-0.4-0.1-0.7-0.4-0.9c-0.3-0.2-0.6-0.3-1-0.3c-0.4,0-0.7,0.1-1,0.3 c-0.2,0.2-0.4,0.5-0.5,0.8l-1.4-0.6c0.2-0.5,0.5-1,1-1.3c0.5-0.4,1.1-0.6,1.9-0.6c0.6,0,1.1,0.1,1.5,0.3c0.4,0.2,0.8,0.5,1,0.9 c0.3,0.4,0.4,0.8,0.4,1.3c0,0.5-0.1,0.9-0.3,1.2c-0.2,0.3-0.5,0.6-0.9,0.8v0.1c0.4,0.2,0.8,0.5,1.1,0.9c0.3,0.4,0.4,0.8,0.4,1.4 s-0.1,1-0.4,1.4c-0.3,0.4-0.6,0.8-1.1,1c-0.5,0.2-1,0.4-1.6,0.4C12.8,21.6,12.2,21.4,11.6,21z"
                                />
                                <path
                                    style="fill: #4070b7"
                                    d="M20,14.2l-1.5,1.1l-0.8-1.1l2.7-1.9h1v9.2H20V14.2z"
                                />
                                <path
                                    style="fill: #ea4535"
                                    d="M24.1,31.7l7.1-7.1L27.7,23l-3.6,1.6l-1.6,3.6L24.1,31.7z"
                                />
                                <path
                                    style="fill: #36a852"
                                    d="M6.7,28.1l1.6,3.6h15.8v-7.1H8.3L6.7,28.1z"
                                />
                                <path
                                    style="fill: #557ebf"
                                    d="M3.6,1.7C2.3,1.7,1.2,2.7,1.2,4v20.5l3.6,1.6l3.6-1.6V8.8h15.8l1.6-3.6l-1.6-3.6H3.6z"
                                />
                                <path
                                    style="fill: #1c8140"
                                    d="M1.2,24.6v4.7c0,1.3,1.1,2.4,2.4,2.4h4.7v-7.1H1.2z"
                                />
                                <path
                                    style="fill: #f9bc15"
                                    d="M24.1,8.8v15.8h7.1V8.8l-3.6-1.6L24.1,8.8z"
                                />
                                <path
                                    style="fill: #3968b2"
                                    d="M31.2,8.8V4c0-1.3-1.1-2.4-2.4-2.4h-4.7v7.1H31.2z"
                                />
                            </svg>
                            <loading :loading="removingGoogleCalendar" />
                        </icon>

                        <!-- Waze -->

                        <icon
                            v-if="event.location"
                            :size="30"
                            v-tooltip="'Waze'"
                            tag="a"
                            @click.stop
                            :href="wazeMapURL"
                        >
                            <svg
                                viewBox="0 0 50 50"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M27.5,2.674C21.319,2.674 15.556,5.451 11.667,10.382C8.958,13.854 7.5,18.16 7.5,22.535L7.5,26.215C7.5,27.812 6.875,29.34 5.833,30.451C5,31.285 3.958,31.91 2.847,32.188C3.264,33.229 4.236,34.826 5.972,36.563C7.431,38.09 9.167,39.34 11.042,40.243L11.042,40.174C12.222,38.368 14.167,37.326 16.319,37.326C16.736,37.326 17.083,37.396 17.5,37.465C20,37.951 21.944,39.896 22.431,42.326L27.569,42.326C32.917,42.326 37.986,40.104 41.667,36.493C47.361,30.799 49.097,22.257 45.972,14.896C42.847,7.465 35.625,2.674 27.5,2.674Z"
                                    style="fill: white"
                                />
                                <path
                                    d="M27.5,0.174C20.625,0.174 14.167,3.229 9.792,8.715C6.667,12.674 5,17.535 5,22.604L5,26.215C5,28.09 3.681,29.826 1.111,29.965C0.486,29.965 0,30.451 -0.069,31.076C-0.139,32.743 1.667,35.868 4.167,38.368C5.903,40.104 7.917,41.493 10.069,42.604C9.375,46.424 12.361,49.896 16.25,49.896L16.319,49.896C19.306,49.896 21.806,47.813 22.431,44.965L27.639,44.965C28.194,47.813 30.694,49.896 33.75,49.896C34.444,49.896 35.208,49.757 35.903,49.549C37.639,48.993 38.958,47.674 39.583,45.937C40.139,44.34 40.069,42.743 39.583,41.424C40.972,40.521 42.222,39.549 43.403,38.368C47.639,34.201 50,28.507 50,22.604C50,16.632 47.639,11.076 43.403,6.84C39.167,2.465 33.472,0.174 27.5,0.174ZM27.5,2.674C35.556,2.674 42.847,7.535 45.972,14.965C49.097,22.396 47.361,30.937 41.667,36.563C37.986,40.243 32.917,42.396 27.569,42.396L22.431,42.396C21.944,39.896 20,38.021 17.5,37.535C17.083,37.465 16.736,37.396 16.319,37.396C14.236,37.396 12.222,38.438 11.042,40.243L11.042,40.313C9.167,39.34 7.5,38.09 5.972,36.632C4.236,34.896 3.264,33.229 2.847,32.257C4.028,31.979 5,31.354 5.833,30.521C6.875,29.34 7.5,27.882 7.5,26.285L7.5,22.604C7.5,18.229 8.958,13.924 11.667,10.451C15.556,5.382 21.319,2.674 27.5,2.674Z"
                                />
                                <path
                                    d="M37.5,15.035C36.111,15.035 35,16.146 35,17.535C35,18.924 36.111,20.035 37.5,20.035C38.889,20.035 40,18.924 40,17.535C40,16.146 38.889,15.035 37.5,15.035Z"
                                />
                                <path
                                    d="M22.5,15.035C21.111,15.035 20,16.146 20,17.535C20,18.924 21.111,20.035 22.5,20.035C23.889,20.035 25,18.924 25,17.535C25,16.146 23.889,15.035 22.5,15.035Z"
                                />
                                <path
                                    d="M22.083,24.965C21.181,24.965 20.556,25.868 20.972,26.701C22.639,30.174 26.111,32.396 30,32.396C33.889,32.396 37.361,30.174 39.028,26.701C39.375,25.868 38.819,24.965 37.917,24.965C37.431,24.965 37.014,25.243 36.806,25.66C35.556,28.229 32.917,29.896 30.069,29.896C27.153,29.896 24.514,28.229 23.333,25.66C23.056,25.243 22.639,24.965 22.083,24.965Z"
                                />
                            </svg>
                        </icon>

                        <!-- Call -->

                        <icon
                            tag="a"
                            class="fa fa-phone icon-orange"
                            v-if="
                                eventProspect &&
                                (eventProspect.phone_number ||
                                    eventProspect.mobile_phone_number)
                            "
                            @click="manageInteractions"
                        />

                        <!-- SMS -->

                        <icon
                            tag="a"
                            class="fa fa-comments icon-purple"
                            v-if="
                                eventProspect &&
                                eventProspect.mobile_phone_number
                            "
                            @click="manageSms"
                        />

                        <!-- Messages -->

                        <icon
                            v-if="eventProspect"
                            tag="a"
                            class="fa fa-envelope icon-green"
                            @click="manageMessages"
                        />

                        <!-- Files -->

                        <icon
                            v-if="eventProspect"
                            tag="a"
                            class="fa fa-folder icon-blue"
                            @click="manageFiles"
                        />

                        <!-- Orders -->

                        <icon
                            v-if="eventProspect"
                            tag="a"
                            class="fa fa-shopping-cart icon-cyan"
                            @click="manageOrders"
                        />

                        <!-- Document -->

                        <icon
                            :size="30"
                            :icon-size="12"
                            class="fa fa-file-pdf icon-garnet"
                            v-tooltip="'Document'"
                            @click.prevent.stop="manageDocuments"
                        />

                        <!-- GMeet -->

                        <icon
                            v-if="hangout"
                            tag="a"
                            target="_blank"
                            :href="hangout"
                            class="fa fa-video icon-red"
                        />

                        <!-- Google Map -->

                        <icon
                            v-if="event.location"
                            class="fa fa-map"
                            tag="a"
                            @click.stop
                            :href="gmapURL"
                        />

                        <!-- Setting -->

                        <icon
                            class="fa fa-cog"
                            v-tooltip="'Paramètres'"
                            @click.prevent.stop="(frameTab = 8), (tab = 1)"
                        />
                    </item>

                    <item-list
                        :class="[
                            'hc-flex-1',
                            editable ? '' : 'hc-prospect-event-associated',
                        ]"
                        padding="12px"
                    >
                        <!-- Event calendar -->

                        <item
                            class="hc-prospect-event-item hc-prospect-event-item-calendar"
                            @click="(frameTab = 0), (tab = 1)"
                            :style="calendarStyle"
                        >
                            <icon :class="calendarIcon" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.calendar')"
                                ></span>
                                <span
                                    class="hc-prospect-event-item-value"
                                    v-text="
                                        prospectEvent.calendar
                                            ? prospectEvent.calendar.name
                                            : 'Choisir un calendrier ...'
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Event user -->

                        <item
                            class="hc-prospect-event-item"
                            @click="(frameTab = 1), (tab = 1)"
                        >
                            <icon
                                class="fa fa-user-tie icon-orange"
                                :size="40"
                            />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.user')"
                                ></span>
                                <span
                                    class="hc-prospect-event-item-value"
                                    v-text="
                                        prospectEvent.user
                                            ? prospectEvent.user.name
                                            : 'Choisir un utilisateur ...'
                                    "
                                ></span>
                            </div>
                            <icon
                                v-if="prospectEvent.user"
                                style="overflow: visible"
                                class="fa fa-user-plus icon-purple"
                                @click.prevent.stop="(frameTab = 5), (tab = 1)"
                                v-tooltip="'Inviter d\'autres utilisateurs'"
                            >
                                <span
                                    style="
                                        position: absolute;
                                        top: -5px;
                                        right: -5px;
                                        height: 18px;
                                        line-height: 18px;
                                        padding: 0px 6px;
                                        background-color: #ef911d;
                                        color: white;
                                        font-family: monospace;
                                        font-size: 12px;
                                        border-radius: 9px;
                                        font-weight: normal;
                                    "
                                    v-if="
                                        prospectEvent.users &&
                                        prospectEvent.users.length > 0
                                    "
                                    v-text="prospectEvent.users.length"
                                ></span>
                            </icon>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Event vehicle -->

                        <item
                            v-if="showVehicle"
                            class="hc-prospect-event-item hc-prospect-event-item-calendar"
                            @click="(frameTab = 7), (tab = 1)"
                        >
                            <icon class="fa fa-car" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.vehicle')"
                                ></span>
                                <span
                                    class="hc-prospect-event-item-value"
                                    v-text="
                                        prospectEvent.vehicle
                                            ? prospectEvent.vehicle.name
                                            : 'Choisir un véhicule ...'
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Event prospect -->

                        <item
                            class="hc-prospect-event-item"
                            @click="(frameTab = 2), (tab = 1)"
                        >
                            <icon class="fa fa-user icon-orange" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.prospect')"
                                ></span>
                                <span
                                    class="hc-prospect-event-item-value"
                                    v-text="
                                        prospectEvent.prospect
                                            ? [
                                                  prospectEvent.prospect
                                                      .first_name,
                                                  prospectEvent.prospect
                                                      .last_name,
                                              ]
                                                  .filter((n) => n)
                                                  .join(' ')
                                            : 'Choisir un prospect ...'
                                    "
                                ></span>
                            </div>
                            <icon
                                v-if="prospectEvent.prospect"
                                class="fa fa-pen icon-blue"
                                @click.prevent.stop="(frameTab = 9), (tab = 1)"
                                v-tooltip="$t('update')"
                            />
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Event prospect order -->

                        <item
                            v-if="
                                (prospectEvent.prospect ||
                                    prospectEvent.prospect_id) &&
                                showOrder
                            "
                            class="hc-prospect-event-item"
                            @click="(frameTab = 3), (tab = 1)"
                        >
                            <icon
                                class="fa fa-shopping-cart icon-cyan"
                                :size="40"
                            />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.order')"
                                ></span>
                                <span
                                    class="hc-prospect-event-item-value"
                                    v-text="
                                        prospectEvent.order
                                            ? prospectEvent.order.name
                                            : 'Choisir un devis ...'
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>

                        <!-- Event date -->

                        <item
                            @click=""
                            class="hc-prospect-event-item hc-prospect-event-item-date"
                            style="align-items: center"
                        >
                            <icon class="fa fa-clock icon-blue" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.date_time')"
                                ></span>
                                <div
                                    class="hc-prospect-event-item-value hc-flex-row"
                                    style="gap: 0; height: 30px"
                                >
                                    <!-- Date -->
                                    <input
                                        type="date"
                                        style="
                                            width: 100px;
                                            margin-right: 8px;
                                            background-color: #fff;
                                            border: 1px solid #ddd;
                                        "
                                        v-model="eventDate"
                                        @click.stop
                                    />
                                    <!-- Started at -->
                                    <div class="hc-prospect-event-hour">
                                        <label>
                                            <select
                                                v-model="eventStartHour"
                                                @click.stop
                                            >
                                                <option
                                                    value=""
                                                    v-text="
                                                        $t('event.started_at')
                                                    "
                                                ></option>
                                                <option
                                                    v-for="hour in eventHours"
                                                    :value="hour"
                                                    v-text="hour"
                                                ></option>
                                            </select>
                                            <span
                                                v-text="eventStartHour"
                                            ></span>
                                        </label>
                                        <div
                                            class="hc-prospect-event-hour-incr-decr"
                                        >
                                            <a
                                                class="fa fa-caret-up"
                                                @click.prevent.stop="
                                                    incrementStartedAt
                                                "
                                            ></a>
                                            <a
                                                class="fa fa-caret-down"
                                                @click.prevent.stop="
                                                    decrementStartedAt
                                                "
                                            ></a>
                                        </div>
                                    </div>
                                    <!-- Ended at -->
                                    <div
                                        class="hc-prospect-event-hour"
                                        v-if="!showEndedAtDate"
                                    >
                                        <label>
                                            <select
                                                v-model="eventEndHour"
                                                @click.stop
                                            >
                                                <option
                                                    value=""
                                                    v-text="
                                                        $t('event.ended_at')
                                                    "
                                                ></option>
                                                <template
                                                    v-for="hour in eventHours"
                                                >
                                                    <option
                                                        v-if="
                                                            hour >
                                                            eventStartHour
                                                        "
                                                        :value="hour"
                                                        v-text="hour"
                                                    ></option>
                                                </template>
                                            </select>
                                            <span v-text="eventEndHour"></span>
                                        </label>
                                        <div
                                            class="hc-prospect-event-hour-incr-decr"
                                        >
                                            <a
                                                class="fa fa-caret-up"
                                                @click.prevent.stop="
                                                    incrementEndedAt
                                                "
                                            ></a>
                                            <a
                                                class="fa fa-caret-down"
                                                @click.prevent.stop="
                                                    decrementEndedAt
                                                "
                                            ></a>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="hc-prospect-event-item-value hc-flex-row"
                                    style="gap: 0; height: 30px"
                                    v-if="showEndedAtDate"
                                >
                                    <input
                                        type="date"
                                        style="
                                            width: 100px;
                                            margin-right: 8px;
                                            background-color: #fff;
                                            border: 1px solid #ddd;
                                        "
                                        v-model="eventEndedAtDate"
                                        @click.stop
                                    />
                                    <div class="hc-prospect-event-hour">
                                        <label>
                                            <select
                                                v-model="eventEndedAtHour"
                                                @click.stop
                                            >
                                                <option
                                                    value=""
                                                    v-text="
                                                        $t('event.ended_at')
                                                    "
                                                ></option>
                                                <template
                                                    v-for="hour in eventHours"
                                                >
                                                    <option
                                                        v-if="
                                                            eventEndedAtDate >
                                                                eventDate ||
                                                            hour >
                                                                eventStartHour
                                                        "
                                                        :value="hour"
                                                        v-text="hour"
                                                    ></option>
                                                </template>
                                            </select>
                                            <span v-text="eventEndHour"></span>
                                        </label>
                                        <div
                                            class="hc-prospect-event-hour-incr-decr"
                                        >
                                            <a
                                                class="fa fa-caret-up"
                                                @click.prevent.stop="
                                                    incrementEndedAt
                                                "
                                            ></a>
                                            <a
                                                class="fa fa-caret-down"
                                                @click.prevent.stop="
                                                    decrementEndedAt
                                                "
                                            ></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <icon
                                :class="
                                    showEndedAtDate
                                        ? 'fa fa-caret-up'
                                        : 'fa fa-caret-down'
                                "
                                @click.prevent.stop="
                                    showEndedAtDate = !showEndedAtDate
                                "
                                :size="28"
                            />
                            <icon
                                tag="a"
                                class="fa fa-cog"
                                @click.prevent.stop="(frameTab = 8), (tab = 1)"
                                :size="28"
                            />
                        </item>

                        <!-- Event location -->

                        <item class="hc-prospect-event-item">
                            <icon
                                class="fa fa-map-marker icon-garnet"
                                :size="40"
                            />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.location')"
                                ></span>
                                <div class="hc-prospect-event-item-value">
                                    <google-map-input
                                        v-model="prospectEvent.location"
                                        style="height: 26px; padding: 0px"
                                    />
                                </div>
                            </div>
                            <icon
                                :class="
                                    projectUserSettingsEventsShowDropOffLocation
                                        ? 'fa fa-caret-up'
                                        : 'fa fa-caret-down'
                                "
                                @click.prevent.stop="toggleDropOffLocation"
                                :size="28"
                            />
                        </item>

                        <!-- Event drop off location -->

                        <item
                            class="hc-prospect-event-item"
                            v-if="projectUserSettingsEventsShowDropOffLocation"
                        >
                            <icon
                                class="fa fa-map-marker icon-garnet"
                                :size="40"
                            />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.drop_off_location')"
                                ></span>
                                <div class="hc-prospect-event-item-value">
                                    <google-map-input
                                        v-model="
                                            prospectEvent.drop_off_location
                                        "
                                        style="height: 26px; padding: 0px"
                                    />
                                </div>
                            </div>
                        </item>

                        <!-- Event confirmed -->

                        <item tag="label">
                            <icon
                                class="fa fa-thumbs-up icon-green"
                                :size="40"
                            />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('event.confirmed')"
                            ></div>
                            <checkbox
                                style="padding-right: 5px"
                                v-model="prospectEvent.confirmed"
                                @change="
                                    prospectEvent.id
                                        ? updateEventConfirmed()
                                        : null
                                "
                            />
                        </item>

                        <!-- Event done -->

                        <item tag="label">
                            <icon class="fa fa-check icon-green" :size="40" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('event.done')"
                            ></div>
                            <checkbox
                                style="padding-right: 5px"
                                v-model="prospectEvent.done"
                                @change="
                                    prospectEvent.id ? updateEventDone() : null
                                "
                            />
                        </item>

                        <!-- Send email to prospect -->

                        <item
                            tag="label"
                            v-if="
                                (prospectEvent.prospect ||
                                    prospectEvent.prospect_id) &&
                                googleCalendar
                            "
                        >
                            <icon
                                class="fa fa-paper-plane icon-blue"
                                :size="40"
                            />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('event.email_to_prospect')"
                            ></div>
                            <checkbox
                                style="padding-right: 5px"
                                v-model="prospectEvent.email_to_prospect"
                            />
                        </item>

                        <!-- Prospect labels -->

                        <item
                            v-if="eventProspect && showLabels"
                            class="hc-prospect-event-item"
                            @click.prevent="addProspectLabel"
                        >
                            <icon class="fa fa-tags" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.labels')"
                                ></span>
                                <div class="hc-prospect-event-item-value">
                                    <tag
                                        v-for="item in prospectLabels"
                                        :key="item.id"
                                        :text="item.name"
                                        :color="item.color"
                                        :bgcolor="item.bgcolor"
                                    />
                                </div>
                            </div>
                            <icon
                                tag="a"
                                class="fa fa-plus icon-green"
                                :size="40"
                            />
                        </item>

                        <!-- Event name -->

                        <item class="hc-prospect-event-item">
                            <icon class="fa fa-info icon-blue" :size="40" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.name')"
                                ></span>
                                <div class="hc-prospect-event-item-value">
                                    <input
                                        style="height: 26px; padding: 0"
                                        :placeholder="$t('event.add_title')"
                                        v-model.lazy="prospectEvent.name"
                                    />
                                </div>
                            </div>
                        </item>

                        <!-- Event description -->

                        <item
                            class="hc-prospect-event-item hc-prospect-event-item-location"
                        >
                            <icon
                                class="fa fa-align-left icon-blue"
                                :size="40"
                            />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                style="overflow: visible"
                            >
                                <span
                                    class="hc-prospect-event-item-title"
                                    v-text="$t('event.description')"
                                ></span>
                                <div class="hc-prospect-event-item-value">
                                    <text-editor
                                        :placeholder="
                                            $t('event.add_description')
                                        "
                                        :min-height="120"
                                        v-model.lazy="prospectEvent.description"
                                    />
                                    <icon
                                        class="fa fa-file-text"
                                        :size="30"
                                        style="
                                            position: absolute;
                                            bottom: 0;
                                            right: 0;
                                        "
                                        @click.prevent.stop="
                                            (frameTab = 6), (tab = 1)
                                        "
                                    />
                                </div>
                            </div>
                        </item>

                        <!-- Other infos -->

                        <draggable
                            tag="div"
                            :list="eventMetaFields"
                            item-key="id"
                            :disabled="false"
                            @end="updateFieldsOrder"
                        >
                            <template #item="{ element }">
                                <event-field-row
                                    :field="element"
                                    v-model="meta[element.slug]"
                                />
                            </template>
                        </draggable>
                    </item-list>

                    <buttons v-if="editable">
                        <!-- New field -->

                        <button
                            @click.prevent="addField"
                            v-text="'Nouveau champ'"
                            v-tooltip="'Nouveau champ'"
                            class="hc-button-grey"
                        ></button>

                        <!-- Delete event -->

                        <button
                            v-if="prospectEvent.id"
                            @click.prevent="remove"
                            class="hc-button-danger"
                            v-text="$t('delete')"
                        ></button>

                        <!-- Update or add event -->

                        <button
                            @click.prevent="
                                prospectEvent.id ? updateEvent() : storeEvent()
                            "
                            v-text="
                                prospectEvent.id
                                    ? 'Validation RDV'
                                    : 'Créer le RDV'
                            "
                            v-tooltip="
                                prospectEvent.id
                                    ? 'Validation RDV'
                                    : 'Créer le RDV'
                            "
                        ></button>
                    </buttons>
                </item-list>
            </template>

            <template #2>
                <frame-layout :count="10" :tab="frameTab" class="hc-flex-1">
                    <!-- List of calendars -->

                    <template #1>
                        <select-calendar
                            class="hc-flex-column"
                            style="height: 100%"
                            @back="tab = 0"
                            @calendar-selected="
                                (calendar) => {
                                    prospectEvent.calendar = calendar;
                                    tab = 0;
                                }
                            "
                        />
                    </template>

                    <!-- List of users -->

                    <template #2>
                        <select-user
                            class="hc-flex-column"
                            style="height: 100%"
                            @back="tab = 0"
                            @user-selected="setUser"
                        />
                    </template>

                    <!-- List of prospects -->

                    <template #3>
                        <select-prospect
                            @back="tab = 0"
                            @prospect-selected="setEventProspect"
                        />
                    </template>

                    <!-- List of orders if a prospect is selected -->

                    <template #4>
                        <select-order
                            class="hc-flex-1"
                            style="height: 100%"
                            :prospect="prospectEvent.prospect"
                            @back="tab = 0"
                            @order-selected="
                                (order) => {
                                    prospectEvent.order = order;
                                    tab = 0;
                                }
                            "
                        />
                    </template>

                    <!-- Date -->

                    <template #5>
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
                                    v-text="$t('event.choose_hour')"
                                ></div>
                            </item>
                        </div>
                    </template>

                    <!-- Attendee -->

                    <template #6>
                        <select-attendee
                            class="hc-flex-column"
                            style="height: 100%"
                            :prospect-event="prospectEvent"
                            @back="tab = 0"
                            @add="addAttendee"
                            @remove="removeAttendee"
                        />
                    </template>

                    <!-- Event description template -->

                    <template #7>
                        <select-event-description-template
                            class="hc-flex-column"
                            style="height: 100%"
                            @back="tab = 0"
                            @event-description-template-selected="
                                setEventDescriptionTemplate
                            "
                        />
                    </template>

                    <!-- Event vehicle -->

                    <template #8>
                        <select-vehicle
                            class="hc-flex-column"
                            style="height: 100%"
                            @back="tab = 0"
                            @vehicle-selected="
                                (vehicle) => {
                                    prospectEvent.vehicle = vehicle;
                                    tab = 0;
                                }
                            "
                        />
                    </template>

                    <!-- Event hour -->

                    <template #9>
                        <div style="height: 100%">
                            <item
                                @click="tab = 0"
                                style="border-bottom: 1px solid #eee"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content hc-flex-column"
                                    v-text="'Paramètres'"
                                ></div>
                            </item>

                            <item-list class="hc-flex-1" padding="5px">
                                <v-field :label="'Heure minimum'">
                                    <select v-model="minimumAgendaHour">
                                        <option
                                            v-for="i in 23"
                                            :key="i - 1"
                                            :value="i - 1"
                                            v-text="
                                                (i <= 10 ? '0' : '') +
                                                (i - 1) +
                                                ':00'
                                            "
                                        ></option>
                                    </select>
                                </v-field>
                                <v-field :label="'Heure maximum'">
                                    <select v-model="maximumAgendaHour">
                                        <template v-for="i in 23">
                                            <option
                                                v-if="i > minimumAgendaHour"
                                                :key="i - 1"
                                                :value="i - 1"
                                                v-text="
                                                    (i <= 10 ? '0' : '') +
                                                    (i - 1) +
                                                    ':00'
                                                "
                                            ></option>
                                        </template>
                                    </select>
                                </v-field>
                                <item tag="label">
                                    <icon class="fa fa-car" />
                                    <div
                                        class="hc-item-main-content hc-flex-column"
                                        v-text="'Afficher \'Vehicule\''"
                                    ></div>
                                    <checkbox v-model="showVehicle" />
                                </item>
                                <item tag="label">
                                    <icon class="fa fa-shopping-cart" />
                                    <div
                                        class="hc-item-main-content hc-flex-column"
                                        v-text="'Afficher \'Devis\''"
                                    ></div>
                                    <checkbox v-model="showOrder" />
                                </item>
                                <item tag="label">
                                    <icon class="fa fa-tags" />
                                    <div
                                        class="hc-item-main-content hc-flex-column"
                                        v-text="'Afficher \'Filtres\''"
                                    ></div>
                                    <checkbox v-model="showLabels" />
                                </item>
                            </item-list>
                        </div>
                    </template>

                    <!-- Update prospect -->

                    <template #10>
                        <update-prospect
                            @back="tab = 0"
                            @prospect-updated="
                                (p) => {
                                    (tab = 0),
                                        (prospectEvent.prospect = {
                                            ...prospectEvent.prospect,
                                            ...p,
                                        });
                                }
                            "
                        />
                    </template>
                </frame-layout>
            </template>
        </tab-layout>

        <loading :loading="addingEvent || updatingEvent || removingEvent" />
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
    white-space: normal;
    position: relative;
}

.hc-prospect-event-item-value > .hc-tag {
    margin-top: 1px;
}

.hc-prospect-event-item-calendar {
    padding: 1px 0 !important;
    align-items: center;
}

.hc-prospect-event-item-calendar * {
    color: inherit !important;
    background-color: inherit !important;
}

.hc-prospect-event-item-date input {
    border: none;
}

.hc-prospect-event-hour {
    display: flex;
    flex-direction: row;
}

.hc-prospect-event-hour > label {
    position: relative;
    height: 30px;
}

.hc-prospect-event-item-date input {
    padding: 0 8px;
    border-radius: 5px;
}

.hc-prospect-event-hour > label > select {
    -webkit-appearance: none;
    border: none;
    width: 50px;
    height: 30px;
    padding: 0 8px;
}

.hc-prospect-event-hour > label > span {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
    width: 100%;
    height: 100%;
    padding: 0 8px;
    line-height: 30px;
    background: #e8e8e8;
    border-radius: 5px;
}

.hc-prospect-event-item-location .hc-text-editor-buttons {
    justify-content: left;
}

.hc-prospect-event-associated > * {
    pointer-events: none;
}

.hc-prospect-event-hour-incr-decr {
    width: 24px;
    height: 30px;
    display: flex;
    flex-direction: column;
}

.hc-prospect-event-hour-incr-decr > a {
    width: 100%;
    flex: 1;
    text-align: center;
    line-height: 15px;
    font-size: 12px;
    text-decoration: none;
    color: #555;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import EventService from "@/apis/project/event";
import FieldService from "@/apis/project/field";
import ProspectLabelService from "@/apis/project/prospect/label";
import GoogleCalendarService from "@/apis/google/calendar";

import { CLOSE_SLIDE, OPEN_LEFT_SLIDE, OPEN_SUB_SLIDE } from "@/actions/slide";
import { ADD_EVENT, UPDATE_EVENT, REMOVE_EVENT } from "@/actions/project/event";
import {
    ADD_PROSPECT_EVENT,
    UPDATE_PROSPECT_EVENT,
    REMOVE_PROSPECT_EVENT,
} from "@/actions/project/prospect/event";

import { SET_PROSPECT, UPDATE_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { ADD_PROSPECT_USER } from "@/actions/project/prospect/user";
import { SET_NEW_EVENT } from "@/actions/project/event";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_NEW_FIELD_FOR, UPDATE_FIELD } from "@/actions/project/field";

// Components
import SelectProspect from "../select/Select.vue";
import UpdateProspect from "../select/Update.vue";
import SelectUser from "./user/Users.vue";
import SelectCalendar from "./calendar/Calendars.vue";
import SelectVehicle from "./vehicle/Vehicles.vue";
import SelectOrder from "./order/Orders.vue";
import SelectAttendee from "./attendee/Attendees.vue";
import SelectEventDescriptionTemplate from "./event-description-template/EventDescriptionTemplates.vue";
import EventFieldRow from "./field/EventFieldRow.vue";

export default {
    components: {
        SelectProspect,
        UpdateProspect,
        SelectCalendar,
        SelectVehicle,
        SelectUser,
        SelectOrder,
        SelectAttendee,
        SelectEventDescriptionTemplate,
        EventFieldRow,
    },

    data() {
        return {
            tab: 0,
            frameTab: 0,

            meta: {},
            prospectEvent: this.newEvent(),
            prospectLabels: [],
            previousProspect: null,

            addingEvent: false,
            updatingEvent: false,
            removingEvent: false,

            googleCalendar: undefined,
            removingGoogleCalendar: false,
            showEndedAtDate: false,
        };
    },

    methods: {
        /**
         *
         */
        newEvent() {
            return {
                calendar: null,
                user: null,
                prospect: null,
                order: null,
                started_at: dateToString(new Date()),
                ended_at: null,
                name: "",
                location: "",
                drop_off_location: "",
                description: "",
                confirmed: false,
                done: false,
                email_to_prospect: false,
            };
        },

        setEventProspect(prospect) {
            this.prospectEvent.prospect = prospect;
            this.eventDefaultValues(true);
            this.tab = 0;
        },

        setUser(user) {
            this.prospectEvent.user = user;
            this.tab = 0;
        },

        /**
         *
         */
        addAttendee(attendee) {
            if (!this.prospectEvent.users) {
                this.prospectEvent.users = [];
            }

            this.prospectEvent.users = [...this.prospectEvent.users, attendee];

            if (this.prospectEvent.id) {
                EventService.addUser(
                    this.project.slug,
                    this.prospectEvent.id,
                    attendee.id
                );
            }
        },

        /**
         *
         */
        removeAttendee(attendee) {
            this.prospectEvent.users = this.prospectEvent.users.filter(
                (u) => u.id != attendee.id
            );

            if (this.prospectEvent.id) {
                EventService.removeUser(
                    this.project.slug,
                    this.prospectEvent.id,
                    attendee.id
                );
            }
        },

        /**
         *
         */
        setEventDescriptionTemplate(eventDescriptionTemplate) {
            this.prospectEvent.description = eventDescriptionTemplate.body;
            this.tab = 0;
        },

        /**
         *
         */
        async storeEvent() {
            const data = {
                calendar_id: this.prospectEvent.calendar.id,
                user_id: this.prospectEvent.user.id,
                started_at: this.prospectEvent.started_at,
                ended_at: this.prospectEvent.ended_at,
                name: this.prospectEvent.name,
                location: this.prospectEvent.location,
                drop_off_location: this.prospectEvent.drop_off_location,
                description: this.prospectEvent.description,
                confirmed: this.prospectEvent.confirmed,
                done: this.prospectEvent.done,
                email_to_prospect: this.prospectEvent.email_to_prospect,
            };

            if (this.prospectEvent.prospect) {
                data.prospect_id = this.prospectEvent.prospect.id;
            }

            if (this.prospectEvent.vehicle) {
                data.vehicle_id = this.prospectEvent.vehicle.id;
            }

            if (this.prospectEvent.order) {
                data.order_id = this.prospectEvent.order.id;
            }

            if (this.prospectEvent.users) {
                data.users = this.prospectEvent.users.map((u) => u.id);
            }

            if (this.prospect) {
                store.commit(ADD_PROSPECT_USER, this.prospectEvent.user);

                if (this.prospectEvent.users) {
                    data.users = this.prospectEvent.users.forEach((u) =>
                        store.commit(ADD_PROSPECT_USER, u)
                    );
                }
            }

            data.meta = this.meta;

            this.addingEvent = true;

            try {
                const d = await store.dispatch(
                    /*this.prospect && this.prospect.id
                        ? ADD_PROSPECT_EVENT
                        : */ ADD_EVENT,
                    data
                );

                if (this.prospect && !this.prospect.street) {
                    store.commit(UPDATE_PROSPECT, {
                        id: this.prospect.id,
                        street: this.prospectEvent.location,
                    });
                }

                if (this.prospect && this.prospect.id == data.prospect_id) {
                    store.commit(ADD_PROSPECT_EVENT, {
                        ...this.prospectEvent,
                        id: d.id,
                    });
                }
            } finally {
                this.addingEvent = false;
                store.commit(CLOSE_SLIDE);
                store.commit(SET_NEW_EVENT, null);
            }
        },

        /**
         *
         */
        async updateEvent() {
            const data = {
                id: this.prospectEvent.id,
                calendar_id: this.prospectEvent.calendar.id,
                user_id: this.prospectEvent.user.id,
                started_at: this.prospectEvent.started_at,
                ended_at: this.prospectEvent.ended_at,
                name: this.prospectEvent.name,
                location: this.prospectEvent.location,
                drop_off_location: this.prospectEvent.drop_off_location,
                description: this.prospectEvent.description,
                confirmed: this.prospectEvent.confirmed,
                done: this.prospectEvent.done,
            };

            if (this.prospectEvent.prospect) {
                data.prospect_id = this.prospectEvent.prospect.id;
            }

            if (this.prospectEvent.vehicle) {
                data.vehicle_id = this.prospectEvent.vehicle.id;
            }

            if (this.prospectEvent.order) {
                data.order_id = this.prospectEvent.order.id;
            }

            data.meta = this.meta;

            this.updatingEvent = true;

            try {
                await store.dispatch(
                    /*this.prospect ? UPDATE_PROSPECT_EVENT : */ UPDATE_EVENT,
                    data
                );

                if (this.prospect && this.prospect.id != data.prospect_id) {
                    store.commit(REMOVE_PROSPECT_EVENT, this.prospectEvent.id);
                } else {
                    store.commit(
                        this.prospect ? UPDATE_PROSPECT_EVENT : UPDATE_EVENT,
                        this.prospectEvent
                    );
                }
            } finally {
                this.updatingEvent = false;
                store.commit(CLOSE_SLIDE);
            }
        },

        /**
         *
         */
        async updateEventDone() {
            const { id, done } = this.prospectEvent;

            store.dispatch(
                this.prospect ? UPDATE_PROSPECT_EVENT : UPDATE_EVENT,
                { id, done }
            );
        },

        /**
         *
         */
        async updateEventConfirmed() {
            const { id, confirmed } = this.prospectEvent;

            store.dispatch(
                this.prospect ? UPDATE_PROSPECT_EVENT : UPDATE_EVENT,
                { id, confirmed }
            );
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingEvent = true;

                try {
                    await store.dispatch(
                        this.prospect ? REMOVE_PROSPECT_EVENT : REMOVE_EVENT,
                        this.prospectEvent.id
                    );
                } finally {
                    this.removingEvent = false;
                    store.commit(CLOSE_SLIDE);
                }
            });
        },

        /**
         *
         */
        async checkGoogleCalendarAccount() {
            if (this.googleCalendar === undefined) {
                const { data } = await GoogleCalendarService.get();
                this.googleCalendar = data;
            }
        },

        /**
         *
         */
        addGoogleCalendarAccount() {
            window.location.href = "/google/calendar/auth";
        },

        /**
         *
         */
        async removeGoogleCalendarAccount() {
            this.removingGoogleCalendar = true;
            const { data } = await GoogleCalendarService.destroy(
                this.googleCalendar.id
            );
            this.removingGoogleCalendar = false;
            this.googleCalendar = undefined;
        },

        /**
         *
         */
        async fetchProspectLabels() {
            if (!this.eventProspect) {
                return;
            }

            const { data } = await ProspectLabelService.get(
                this.project.slug,
                this.eventProspect.id
            );
            this.prospectLabels = data;
            store.commit(UPDATE_EVENT, {
                ...this.event,
                prospect: {
                    ...this.eventProspect,
                    labels: data,
                },
            });
        },

        /**
         *
         */
        addProspectLabel() {
            store.commit(SET_PROSPECT, this.eventProspect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-labels");
        },

        /**
         *
         */
        toggleGoogleCalendarAccount() {
            if (this.googleCalendar) {
                hcConfirm(
                    this.$t("event.google_calendar_remove_account_confirm"),
                    this.removeGoogleCalendarAccount
                );
            } else {
                this.addGoogleCalendarAccount();
            }
        },

        /**
         * Manage prospect files
         * See: @/components/prospect/file/Slide.vue
         */
        manageFiles() {
            store.commit(SET_PROSPECT, this.eventProspect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-files");
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages() {
            store.commit(SET_PROSPECT, this.eventProspect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-messages");
        },

        /**
         * Manage prospect orders
         * See: @/components/prospect/order/Slide.vue
         */
        manageOrders() {
            store.commit(SET_PROSPECT, this.eventProspect);
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-orders");
        },

        /**
         * Manage prospect documents
         * See: @/components/prospect/document/Slide.vue
         */
        manageDocuments() {
            store.commit(SET_PROSPECT, this.eventProspect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-documents");
        },

        /**
         * Manage prospect interactions
         * See: @/components/prospect/interaction/Slide.vue
         */
        manageInteractions() {
            store.commit(SET_INTERACTION_PROSPECT, this.eventProspect);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
        },

        /**
         * Manage prospect sms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageSms() {
            store.commit(SET_PROSPECT, this.eventProspect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-sms");
        },

        /**
         * Update event default fields values
         */
        eventDefaultValues(updateNameAndLocation) {
            // If prospect is associated to the current event
            if (
                // !this.prospectEvent.id &&
                this.prospectEvent &&
                (!this.prospectEvent.id ||
                    !this.prospectEvent.location ||
                    updateNameAndLocation) &&
                this.prospectEvent.prospect
            ) {
                // Update event location by default
                // by the prospect address
                this.prospectEvent.location = [
                    this.prospectEvent.prospect.street,
                    this.prospectEvent.prospect.street_bis,
                    this.prospectEvent.prospect.postal_code,
                    this.prospectEvent.prospect.city,
                    this.prospectEvent.prospect.county,
                    this.prospectEvent.prospect.state,
                    this.prospectEvent.prospect.country,
                ]
                    .filter((c) => c)
                    .join(" ");

                // Update event name by default
                // by the prospect full name
                this.prospectEvent.name = [
                    this.prospectEvent.prospect.first_name,
                    this.prospectEvent.prospect.last_name,
                ]
                    .filter((c) => c)
                    .join(" ");

                // Update event description
                // Add the prospect profile link
                // to the event description by default
                const routeData = this.$router.resolve({
                    name: "prospect.show",
                    params: {
                        project: this.project.slug,
                        prospect: this.prospectEvent.prospect.id,
                    },
                });
                this.prospectEvent.description =
                    'Fiche prospect <a href="' +
                    routeData.href +
                    '">' +
                    [
                        this.prospectEvent.prospect.first_name,
                        this.prospectEvent.prospect.last_name,
                    ]
                        .filter((c) => c)
                        .join(" ") +
                    "</a>";
            }
        },

        /**
         *
         */
        defaultEmailToProspect() {
            if (
                this.eventProspect &&
                this.eventCalendar &&
                this.eventCalendar.type == "hangout" &&
                this.googleCalendar
            ) {
                this.prospectEvent.email_to_prospect = true;
            }
        },

        /**
         *
         */
        incrementStartedAt() {
            if (!this.eventStartHour) {
                return;
            }

            let [hour, minute] = this.eventStartHour.split(":");
            hour = parseInt(hour);
            minute = parseInt(minute);

            if (minute == 55) {
                minute = 0;
                hour++;
            } else {
                minute += 5;
            }

            this.eventStartHour =
                (hour < 10 ? "0" : "") +
                hour +
                ":" +
                (minute < 10 ? "0" : "") +
                minute;
        },

        /**
         *
         */
        decrementStartedAt() {
            if (!this.eventStartHour) {
                return;
            }

            let [hour, minute] = this.eventStartHour.split(":");
            hour = parseInt(hour);
            minute = parseInt(minute);

            if (minute == 0) {
                minute = 55;
                hour--;
            } else {
                minute -= 5;
            }

            this.eventStartHour =
                (hour < 10 ? "0" : "") +
                hour +
                ":" +
                (minute < 10 ? "0" : "") +
                minute;
        },

        /**
         *
         */
        incrementEndedAt() {
            if (!this.eventEndHour) {
                return;
            }

            let [hour, minute] = this.eventEndHour.split(":");
            hour = parseInt(hour);
            minute = parseInt(minute);

            if (minute == 55) {
                minute = 0;
                hour++;
            } else {
                minute += 5;
            }

            this.eventEndHour =
                (hour < 10 ? "0" : "") +
                hour +
                ":" +
                (minute < 10 ? "0" : "") +
                minute;
        },

        /**
         *
         */
        decrementEndedAt() {
            if (!this.eventEndHour) {
                return;
            }

            let [hour, minute] = this.eventEndHour.split(":");
            hour = parseInt(hour);
            minute = parseInt(minute);

            if (minute == 0) {
                minute = 55;
                hour--;
            } else {
                minute -= 5;
            }

            this.eventEndHour =
                (hour < 10 ? "0" : "") +
                hour +
                ":" +
                (minute < 10 ? "0" : "") +
                minute;
        },

        addField() {
            store.commit(OPEN_MODAL, "field-add");
            store.commit(SET_NEW_FIELD_FOR, "event");
        },

        /**
         *
         */
        updateFieldsOrder() {
            const orders = this.eventMetaFields.map((field, i) => ({
                field: field.id,
                order: i,
            }));

            orders.forEach((order) => {
                store.commit(UPDATE_FIELD, {
                    id: order.field,
                    order: order.order,
                });
            });

            FieldService.orders(this.project.slug, {
                orders,
            });
        },

        /**
         *
         */
        toggleDropOffLocation() {
            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "events.show-drop-off-location",
                value: !this.projectUserSettingsEventsShowDropOffLocation,
            });
        },
    },

    watch: {
        /**
         * Event
         */
        async event(newValue) {
            // Fetch event
            if (newValue) {
                this.prospectEvent = newValue;
                if (newValue.id) {
                    const { data } = await EventService.show(
                        this.project.slug,
                        newValue.id
                    );
                    this.prospectEvent = data;
                }
                this.showEndedAtDate = this.eventDate != this.eventEndedAtDate;

                this.meta = this.prospectEvent.meta
                    ? this.prospectEvent.meta
                    : {};
                // Create new event
            } else {
                this.prospectEvent = this.newEvent();
            }

            // Select calendar
            // if current event is not associated
            // to a calendar
            if (this.prospectEvent.calendar) {
                this.tab = 0;
                // else, edit event
            } else if (this.prospectEvent.calendar_id) {
                this.prospectEvent.calendar = this.calendars.find(
                    (c) => c.id == this.prospectEvent.calendar_id
                );
                this.tab = 0;
                // else, edit event
            } else if (this.projectUserSettingsEventsDefaultCalendar) {
                this.prospectEvent.calendar = this.calendars.find(
                    (c) => c.id == this.projectUserSettingsEventsDefaultCalendar
                );
            } else {
                this.tab = 1;
                this.frameTab = 0;
            }

            // Default event user
            if (!this.prospectEvent.user) {
                if (this.projectUserSettingsEventsDefaultUser) {
                    this.prospectEvent.user = this.users.find(
                        (u) => u.id == this.projectUserSettingsEventsDefaultUser
                    );
                } else {
                    this.prospectEvent.user = this.$store.state.auth.user;
                }
            }

            this.eventDefaultValues();
        },

        /**
         * Prospect associated to the event
         */
        eventProspect(newValue, oldValue) {
            this.defaultEmailToProspect();

            if (newValue && (!oldValue || newValue.id != oldValue.id)) {
                this.fetchProspectLabels();
            }

            if (oldValue) {
                this.previousProspect = oldValue;
            }
        },

        eventCalendar(newValue) {
            if (
                !this.prospectEvent.id &&
                newValue &&
                newValue.type == "hangout" &&
                !this.googleCalendar
            ) {
                flashWarning({
                    title: "Google Calendar",
                    body: "Veuillez associer un compte Google Calendar à votre compte afin de générer automatiquement un lien hangout",
                });
            }

            if (
                !this.prospectEvent.id &&
                newValue &&
                newValue.type == "physical" &&
                this.projectUserSetting(
                    "calendar-default-address." + newValue.id
                )
            ) {
                this.prospectEvent.location = this.projectUserSetting(
                    "calendar-default-address." + newValue.id
                );
            }

            this.defaultEmailToProspect();
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "prospect",
            "prospectFullName",
            "event",
            "projectUserSettingsAgendaMinimumHour",
            "projectUserSettingsAgendaMaximumHour",
            "projectUserSettingsEventsDefaultCalendar",
            "projectUserSettingsEventsDefaultUser",
            "projectUserSettingsEventsShowDropOffLocation",
            "projectUserSettingsEventsShowVehicle",
            "projectUserSettingsEventsShowOrder",
            "projectUserSettingsEventsShowLabels",
            "projectUserSetting",
            "calendars",
            "fields",
            "users",
            "can",
        ]),

        /**
         *
         */
        calendarStyle() {
            return {
                color: this.prospectEvent.calendar
                    ? this.prospectEvent.calendar.color
                    : "#000000",
                backgroundColor: this.prospectEvent.calendar
                    ? this.prospectEvent.calendar.bgcolor
                    : "#EEEEEE",
            };
        },

        /**
         *
         */
        calendarIcon() {
            if (this.prospectEvent.calendar) {
                if (this.prospectEvent.calendar.type == "physical") {
                    return "fa fa-map-marker";
                } else if (this.prospectEvent.calendar.type == "telephone") {
                    return "fa fa-phone";
                } else if (this.prospectEvent.calendar.type == "hangout") {
                    return "fa fa-video";
                } else if (this.prospectEvent.calendar.type == "task") {
                    return "fa fa-tasks";
                }
            }

            return "fa fa-calendar";
        },

        /**
         *
         */
        hangout() {
            if (
                this.prospectEvent.google_accounts &&
                this.prospectEvent.google_accounts.length > 0 &&
                this.prospectEvent.google_accounts[0].pivot &&
                this.prospectEvent.google_accounts[0].pivot.hangout
            ) {
                return this.prospectEvent.google_accounts[0].pivot.hangout;
            }

            return null;
        },

        /**
         * Event date without time
         */
        eventDate: {
            get: function () {
                if (!this.prospectEvent || !this.prospectEvent.started_at) {
                    return "";
                }

                return this.prospectEvent.started_at.substring(0, 10);
            },
            set: function (value) {
                if (!this.prospectEvent) {
                    return;
                }

                let hourStart = this.prospectEvent.started_at
                    ? this.prospectEvent.started_at.substring(10)
                    : null;
                let hourEnd = this.prospectEvent.ended_at
                    ? this.prospectEvent.ended_at.substring(10)
                    : null;

                this.prospectEvent.started_at =
                    value + (hourStart ? hourStart : " 08:00:00");
                this.prospectEvent.ended_at =
                    value + (hourEnd ? hourEnd : " 08:00:00");
            },
        },

        /**
         * Event ended at date
         */
        eventEndedAtDate: {
            get: function () {
                if (!this.prospectEvent || !this.prospectEvent.ended_at) {
                    return "";
                }

                return this.prospectEvent.ended_at.substring(0, 10);
            },
            set: function (value) {
                if (!this.prospectEvent) {
                    return;
                }

                if (value < this.eventDate) {
                    const date = this.prospectEvent.ended_at;
                    this.prospectEvent.ended_at = value;
                    this.prospectEvent.ended_at = date;
                    return;
                }

                let hourEnd = this.prospectEvent.ended_at
                    ? this.prospectEvent.ended_at.substring(10)
                    : null;

                this.prospectEvent.ended_at =
                    value + (hourEnd ? hourEnd : " 08:00:00");
            },
        },

        /**
         * Event start hour
         */
        eventStartHour: {
            get: function () {
                if (!this.prospectEvent || !this.prospectEvent.started_at) {
                    return "";
                }

                return this.prospectEvent.started_at.substring(11, 16);
            },
            set: function (value) {
                if (!this.prospectEvent) {
                    return;
                }

                if (!value) {
                    return;
                }

                let deltaMn = 60;

                if (this.prospectEvent.ended_at) {
                    deltaMn = parseInt(
                        (new Date(this.prospectEvent.ended_at).getTime() -
                            new Date(this.prospectEvent.started_at).getTime()) /
                            60000
                    );
                }

                this.prospectEvent.started_at =
                    this.prospectEvent.started_at.substring(0, 11) +
                    value +
                    this.prospectEvent.started_at.substring(16);

                /*if (
                    !this.prospectEvent.ended_at ||
                    this.prospectEvent.started_at >= this.prospectEvent.ended_at
                ) {*/
                var date = new Date(this.prospectEvent.started_at);
                date.setMinutes(date.getMinutes() + deltaMn);
                this.prospectEvent.ended_at = dateToString(date);
                /*}*/
            },
        },

        /**
         * Event end hour
         */
        eventEndHour: {
            get: function () {
                if (!this.prospectEvent || !this.prospectEvent.ended_at) {
                    return "";
                }

                return this.prospectEvent.ended_at.substring(11, 16);
            },
            set: function (value) {
                if (!this.prospectEvent) {
                    return;
                }

                if (!value) {
                    return;
                }

                this.prospectEvent.ended_at =
                    this.prospectEvent.started_at.substring(0, 11) +
                    value +
                    this.prospectEvent.started_at.substring(16);
            },
        },

        /**
         * Event end hour
         */
        eventEndedAtHour: {
            get: function () {
                if (!this.prospectEvent || !this.prospectEvent.ended_at) {
                    return "";
                }

                return this.prospectEvent.ended_at.substring(11, 16);
            },
            set: function (value) {
                if (!this.prospectEvent) {
                    return;
                }

                if (!value) {
                    return;
                }

                this.prospectEvent.ended_at =
                    this.prospectEvent.ended_at.substring(0, 11) +
                    value +
                    this.prospectEvent.ended_at.substring(16);
            },
        },

        /**
         * Selectable event hours
         */
        eventHours() {
            let eh = [];

            for (
                var i = 0;
                i <
                this.projectUserSettingsAgendaMaximumHour -
                    this.projectUserSettingsAgendaMinimumHour;
                ++i
            ) {
                for (var j = 0; j < 4; ++j) {
                    eh.push(
                        (i + this.projectUserSettingsAgendaMinimumHour < 10
                            ? "0"
                            : "") +
                            (i + this.projectUserSettingsAgendaMinimumHour) +
                            ":" +
                            (j == 0 ? "0" : "") +
                            j * 15
                    );
                }
            }

            return eh;
        },

        /**
         *
         */
        eventProspect() {
            return this.prospectEvent.prospect;
        },

        /**
         *
         */
        eventCalendar() {
            return this.prospectEvent.calendar;
        },

        /**
         *
         */
        editable() {
            if (!this.prospectEvent.id) {
                return true;
            }

            if (this.can("all")) {
                return true;
            }

            // Computed server-side: takes into account whether the user
            // is affected to the event itself or to its related prospect
            if (this.prospectEvent.editable !== undefined) {
                return this.prospectEvent.editable;
            }

            if (this.prospectEvent.user_id == this.user.id) {
                return true;
            }

            if (this.prospectEvent.creator_id == this.user.id) {
                return true;
            }

            return false;
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

        showVehicle: {
            get() {
                return this.projectUserSettingsEventsShowVehicle;
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.show-vehicle",
                    value,
                });
            },
        },

        showOrder: {
            get() {
                return this.projectUserSettingsEventsShowOrder;
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.show-order",
                    value,
                });
            },
        },

        showLabels: {
            get() {
                return this.projectUserSettingsEventsShowLabels;
            },
            set(value) {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.show-labels",
                    value,
                });
            },
        },

        /**
         *
         */
        gCalendarURL() {
            return this.googleCalendar
                ? "/google/calendar/auth"
                : "/google/calendar/auth";
        },

        gmapURL() {
            return (
                "https://www.google.com/maps/search/?api=1&query=" +
                encodeURI(this.event.location)
            );
        },

        wazeMapURL: function () {
            return "https://waze.com/ul?q=" + encodeURI(this.event.location);
        },

        /**
         *
         */
        eventMetaFields() {
            return this.fields
                .filter((field) => field.meta && field.for == "event")
                .sort((field1, field2) => field1.order - field2.order);
        },
    },
};
</script>
