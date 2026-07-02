<template>
    <audio ref="audio" :src="reminderAudioLink" preload="auto"></audio>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

import EventService from "@/apis/project/event";

import { SET_EVENT } from "@/actions/project/event";
import { SET_PROSPECT } from "@/actions/project/prospect";
import { OPEN_SLIDE } from "@/actions/slide";

export default {
    props: {
        // interval between reminders
        reminderInterval: {
            type: Number,
            default: 300,
        },

        // time before the event date
        // at which reminders start to be displayed
        reminderTime: {
            type: Number,
            default: 3600,
        },

        // reminder flash duration
        alertDuration: {
            type: Number,
            default: 20,
        },
    },

    mounted() {
        setTimeout(() => {
            this.fetchEvents();
            // Fetch events every i minutes
            let i = 15;
            this.interval = setInterval(this.fetchEvents, 1000 * 60 * i);
        }, 1000);
    },

    unmounted() {
        clearInterval(this.interval);

        this.events.forEach((event) => {
            if (event.reminderTimeout) {
                clearTimeout(event.reminderTimeout);
            }
            if (event.reminderInterval) {
                clearInterval(event.reminderInterval);
            }
        });
    },

    data() {
        return {
            // list of events
            events: [],
            interval: null,
        };
    },

    methods: {
        /**
         * Create a reminder for an event
         */
        createReminder(event) {
            var remainingTimes = this.remainingTimes(event);

            // do not create reminder
            // for past event
            if (remainingTimes < 0) {
                return;
            }

            let remindAfterTimeMs =
                remainingTimes < this.reminderTimeMs
                    ? remainingTimes % this.reminderIntervalMs
                    : remainingTimes - this.reminderTimeMs;

            // start reminding the user
            // from reminderTimeMs ms before the appointment date,
            // if this time has already been exceeded,
            // start the reminder "now"

            event.reminderTimeout = setTimeout(() => {
                let callback = () => {
                    var t = this.remainingTimes(event);

                    // If the date of the appointment has passed,
                    // no longer display the reminder
                    if (t < 0) {
                        clearInterval(event.reminderInterval);
                        event.reminderInterval = null;
                        this.showEvent(event);
                        return;
                    }

                    this.remind(event, t);
                };

                callback();

                // and reset the reminder every reminderIntervalMs ms
                event.reminderInterval = setInterval(
                    callback,
                    this.reminderIntervalMs
                );
                event.reminderTimeout = null;
            }, remindAfterTimeMs);
        },

        /**
         * Reminder flash and notification
         */
        remind(event, remainingTime) {
            if (!this.projectUserSettingsEventsReminder) {
                return;
            }

            this.flash(event, remainingTime);
            this.notify(event, remainingTime);
            this.$refs.audio.play();
        },

        /**
         * Pop up the reminder flash
         */
        flash(event, remainingTime) {
            flash({
                title: this.eventTitle(event, remainingTime),
                body: [
                    this.eventDescription(event),
                    this.eventLocation(event),
                    this.eventGMeetLink(event),
                ]
                    .filter((e) => e)
                    .join("<br>"),
                backgroundColor: event.calendar.bgcolor,
                color: event.calendar.color,
                duration: this.alertDurationMs,
                icon: this.eventIcon(event),
                action: async () => {
                    if (event.prospect) {
                        await store.commit(SET_PROSPECT, event.prospect);
                    }
                    store.commit(SET_EVENT, event);
                    store.commit(OPEN_SLIDE, "prospect-manage-events");
                },
            });
        },

        /**
         * Browser notification
         */
        notify(event, remainingTime) {
            if (!("Notification" in window)) {
                return;
            }

            if (Notification.permission === "granted") {
                return new Notification(this.eventTitle(event, remainingTime));
            }

            if (Notification.permission !== "denied") {
                Notification.requestPermission().then((permission) => {
                    if (permission === "granted") {
                        return new Notification(
                            this.eventTitle(event, remainingTime)
                        );
                    }
                });
            }
        },

        /**
         * Calculate the remaining time in ms
         * for the event date
         */
        remainingTimes(event) {
            return new Date(event.started_at).getTime() - new Date().getTime();
        },

        /**
         * The title of the event
         * to put in the flash
         */
        eventTitle(event, remainingTime) {
            return (
                /*event.calendar.name +
                " " +*/
                event.name +
                (remainingTime == 0
                    ? " maintenant."
                    : " dans " + Math.round(remainingTime / 60000) + "mn.")
            );
        },

        /**
         * The description of the event
         * to put in the flash
         */
        eventDescription(event) {
            return event.description;
        },

        /**
         * The location of the event
         * to put in the flash
         */
        eventLocation(event) {
            return event.location
                ? '<a href="' +
                      this.eventLocationGMapUrl(event) +
                      '" style="color: inherit; text-decoration: none" target="_blank">' +
                      event.location +
                      "</a>"
                : "";
        },

        /**
         * The location of the event
         * to put in the flash
         */
        eventLocationGMapUrl(event) {
            return event.location
                ? "https://www.google.com/maps/search/?api=1&query=" +
                      encodeURI(event.location)
                : "";
        },

        /**
         * The location of the event
         * to put in the flash
         */
        eventGMeetLink(event) {
            return null; /*event.google_accounts.length > 0 &&
                event.google_accounts[0].pivot.meta &&
                event.google_accounts[0].pivot.meta.hangoutLink
                ? "<br>" +
                      '<i class="fa fa-video-camera"></i> ' +
                      '<a href="' +
                      event.google_accounts[0].pivot.meta.hangoutLink +
                      '" style="color: inherit" target="_blank">Google meet</a>'
                : "";*/
        },

        /**
         * Event icon
         */
        eventIcon(event) {
            // Physical calendar
            if (event.calendar.type == "physical") {
                return "fa fa-map-marker";
                // Phone calendar
            } else if (event.calendar.type == "telephone") {
                return "fa fa-phone";
                // GMeet calendar
            } else if (event.calendar.type == "hangout") {
                return "fa fa-video";
                // Task calendar
            } else if (event.calendar.type == "task") {
                return "fa fa-tasks";
                // Other
            } else {
                return "fa fa-calendar";
            }
        },

        /**
         * Fetch events
         */
        async fetchEvents() {
            const datetime = dateToString(new Date());
            const date = datetime.substring(0, 10);
            const time = datetime.substring(11, 16);

            const { data } = await EventService.get(this.project.slug, {
                params: {
                    filters: JSON.stringify({
                        withUsers: { ids: [this.user.id] },
                        startedAt: date + " " + time + ":00",
                        endedAt: date + " 23:59:59",
                    }),
                },
            });

            this.events = data;
        },

        /**
         *
         * @param {*} event
         */
        showEvent(event) {
            store.commit(SET_EVENT, event);
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },
    },

    watch: {
        /**
         */
        events(newValue, oldValue) {
            // create reminder for each events
            oldValue.forEach((event) => {
                if (event.reminderTimeout) {
                    clearTimeout(event.reminderTimeout);
                }
                if (event.reminderInterval) {
                    clearInterval(event.reminderInterval);
                }
            });
            newValue.forEach((event) => {
                this.createReminder(event);
            });
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters(["project", "projectUserSettingsEventsReminder"]),

        reminderIntervalMs() {
            return this.reminderInterval * 1000;
        },

        reminderTimeMs() {
            return this.reminderTime * 1000;
        },

        alertDurationMs() {
            return this.alertDuration * 1000;
        },

        reminderAudioLink() {
            return "/audios/event-reminder.wav";
        },
    },
};
</script>
