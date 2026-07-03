<template>
    <div
        ref="event"
        :class="[
            'hc-agenda-event',
            !event.id || isDraggedEvent ? 'dragged' : '',
            draggedEvent && !isDraggedEvent && event.id == draggedEvent.id
                ? 'dragging'
                : '',
            resizedEvent && !isResizedEvent && event.id == resizedEvent.id
                ? 'resizing'
                : '',
            event.done ? 'done' : '',
            event.confirmed ? 'confirmed' : '',
            showContextMenu ? 'show-context-menu' : '',
            editable ? 'editable' : '',
            associated ? 'associated' : '',
            eventProject ? 'other-project' : '',
            projectUserSettingsEventsColorByLabel ? 'color-by-label' : '',
        ]"
        :style="style"
        @click="edit"
        @contextmenu.prevent="(e) => (showContextMenu = true)"
    >
        <!-- Header -->

        <div class="hc-agenda-event-header" @mousedown="dragStart">
            <icon :size="cellHeight" :icon-size="8" :class="icon" />
            <div class="hc-agenda-event-name">
                <router-link
                    @mousedown.stop
                    @click.stop
                    v-if="event.prospect"
                    v-text="event.name"
                    :to="{
                        name: 'prospect.show',
                        params: {
                            project: project.slug,
                            prospect: event.prospect.id,
                        },
                    }"
                ></router-link>
                <div v-else v-text="event.name"></div>
            </div>
            <div class="hc-agenda-event-time" v-text="time"></div>
            <icon
                :size="cellHeight"
                :icon-size="10"
                :class="[
                    'hc-agenda-event-done',
                    'fa',
                    'fa-check',
                    event.done ? 'done' : '',
                ]"
                @mousedown.prevent.stop
                @click.prevent.stop="toggleDone"
            />
            <icon
                :size="cellHeight"
                :icon-size="10"
                :class="[
                    'hc-agenda-event-confirmed',
                    'fa',
                    'fa-thumbs-up',
                    event.confirmed ? 'confirmed' : '',
                ]"
                @mousedown.prevent.stop
                @click.prevent.stop="toggleConfirmed"
            />
            <!--icon
                v-if="!event.creator_id"
                :size="cellHeight"
                :icon-size="10"
                class="fa fa-robot"
            /-->
            <icon
                v-if="(editable || associated) && !eventProject"
                tag="a"
                :size="cellHeight"
                :icon-size="10"
                class="fa fa-ellipsis-v"
                @mousedown.stop
                @click.prevent.stop="showContextMenu = true"
            />
        </div>

        <!-- Body -->

        <div class="hc-agenda-event-body">
            <div class="hc-agenda-event-user" v-if="event.user">
                <i class="fa fa-user"></i>
                <span v-text="event.user.name"></span>
                <b
                    class="hc-agenda-event-date"
                    v-text="
                        event.started_at.substring(11, 16) +
                        ' - ' +
                        event.ended_at.substring(11, 16)
                    "
                ></b>
            </div>
            <div class="hc-agenda-event-location" v-if="event.location">
                <i class="fa fa-map-marker"></i
                ><span v-text="event.location"></span>
            </div>
            <div v-html="event.description"></div>
        </div>

        <!-- Footer -->

        <div class="hc-agenda-event-footer" v-if="editable || eventProject">
            <div class="hc-agenda-event-route" v-text="direction"></div>

            <!-- Project -->

            <div v-if="eventProject" class="hc-agenda-event-project">
                <span v-text="eventProject.name"></span>
            </div>

            <template v-else>
                <template v-if="event.prospect">
                    <icon
                        :size="cellHeight"
                        :icon-size="12"
                        class="fa fa-envelope"
                        v-tooltip="'Message'"
                        @click.prevent.stop="manageMessages"
                    />
                    <icon
                        :size="cellHeight"
                        :icon-size="12"
                        class="fa fa-folder"
                        v-tooltip="'Fichier'"
                        @click.prevent.stop="manageFiles"
                    />
                    <icon
                        v-if="event.prospect.mobile_phone_number"
                        :size="cellHeight"
                        :icon-size="12"
                        class="fa fa-comment"
                        v-tooltip="'SMS'"
                        @click.prevent.stop="manageSms"
                    />
                    <icon
                        v-if="
                            event.prospect.phone_number ||
                            event.prospect.mobile_phone_number
                        "
                        :size="cellHeight"
                        :icon-size="12"
                        class="fa fa-phone"
                        v-tooltip="'Appeler'"
                        @click.prevent.stop="manageInteractions"
                    />
                    <icon
                        :size="cellHeight"
                        :icon-size="12"
                        class="fa fa-shopping-cart"
                        v-tooltip="'Devis'"
                        @click.prevent.stop="manageOrders"
                    />
                    <icon
                        :size="cellHeight"
                        :icon-size="12"
                        class="fa fa-file-pdf"
                        v-tooltip="'Document'"
                        @click.prevent.stop="manageDocuments"
                    />
                </template>
                <icon
                    v-if="event.location"
                    :size="cellHeight"
                    v-tooltip="'Waze'"
                    tag="a"
                    @click.stop
                    :href="wazeMapURL"
                >
                    <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
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
                <icon
                    v-if="event.location"
                    :size="cellHeight"
                    tag="a"
                    target="_blank"
                    @click.stop
                    :href="gmapURL"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 92.3 132.3"
                    >
                        <path
                            fill="#1a73e8"
                            d="M60.2 2.2C55.8.8 51 0 46.1 0 32 0 19.3 6.4 10.8 16.5l21.8 18.3L60.2 2.2z"
                        ></path>
                        <path
                            fill="#ea4335"
                            d="M10.8 16.5C4.1 24.5 0 34.9 0 46.1c0 8.7 1.7 15.7 4.6 22l28-33.3-21.8-18.3z"
                        ></path>
                        <path
                            fill="#4285f4"
                            d="M46.2 28.5c9.8 0 17.7 7.9 17.7 17.7 0 4.3-1.6 8.3-4.2 11.4 0 0 13.9-16.6 27.5-32.7-5.6-10.8-15.3-19-27-22.7L32.6 34.8c3.3-3.8 8.1-6.3 13.6-6.3"
                        ></path>
                        <path
                            fill="#fbbc04"
                            d="M46.2 63.8c-9.8 0-17.7-7.9-17.7-17.7 0-4.3 1.5-8.3 4.1-11.3l-28 33.3c4.8 10.6 12.8 19.2 21 29.9l34.1-40.5c-3.3 3.9-8.1 6.3-13.5 6.3"
                        ></path>
                        <path
                            fill="#34a853"
                            d="M59.1 109.2c15.4-24.1 33.3-35 33.3-63 0-7.7-1.9-14.9-5.2-21.3L25.6 98c2.6 3.4 5.3 7.3 7.9 11.3 9.4 14.5 6.8 23.1 12.8 23.1s3.4-8.7 12.8-23.2"
                        ></path>
                    </svg>
                </icon>
            </template>
        </div>

        <!-- Resize -->

        <div
            v-if="editable && !agendaList"
            class="hc-agenda-event-resize"
            @mousedown.stop="resizeStart"
        ></div>

        <!-- Context menu overlay -->

        <div
            v-if="(editable || associated) && !eventProject"
            class="hc-agenda-event-menu-overlay"
            @click.prevent.stop="showContextMenu = false"
        ></div>

        <!-- Context menu -->

        <item-list
            v-if="(editable || associated) && !eventProject"
            class="hc-agenda-event-menu"
            @click.prevent.stop="showContextMenu = false"
        >
            <item
                v-if="event.prospect"
                tag="router-link"
                :to="{
                    name: 'prospect.show',
                    params: {
                        project: this.project.slug,
                        prospect: event.prospect.id,
                    },
                }"
            >
                <icon
                    :size="24"
                    :icon-size="9"
                    class="fa fa-user"
                    color="#489f1f"
                />
                <div
                    class="hc-item-main-content"
                    v-text="$t('event.view_prospect')"
                ></div>
            </item>

            <template v-if="editable">
                <item @click="edit">
                    <icon
                        :size="24"
                        :icon-size="9"
                        class="fa fa-calendar"
                        color="#9e3cda"
                    />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('event.view_and_edit')"
                    ></div>
                </item>
                <item v-if="event.prospect" @click="addEvent">
                    <icon
                        :size="24"
                        :icon-size="9"
                        class="fa fa-calendar-plus"
                        color="#9e3cda"
                    />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('event.new')"
                    ></div>
                </item>
                <item @click="dragStart" v-if="!agendaList">
                    <icon
                        :size="24"
                        :icon-size="9"
                        class="fa fa-arrow-right"
                        color="#e5961e"
                    />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('event.move')"
                    ></div>
                </item>
                <item @click="remove">
                    <icon
                        :size="24"
                        :icon-size="9"
                        class="fa fa-trash"
                        color="#ed5e3e"
                    />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('delete')"
                    ></div>
                </item>
                <item @click="toggleConfirmed">
                    <icon
                        :size="24"
                        :icon-size="9"
                        class="fa fa-thumbs-up"
                        color="#1e6ee5"
                    />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            event.confirmed
                                ? 'Marquer comme non confirmé'
                                : 'Marquer comme confirmé'
                        "
                    ></div>
                </item>
                <item @click="toggleDone">
                    <icon
                        :size="24"
                        :icon-size="9"
                        class="fa fa-check-circle"
                        color="#489f1f"
                    />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            event.done
                                ? 'Marquer comme non fait'
                                : 'Marquer comme fait'
                        "
                    ></div>
                </item>
            </template>
        </item-list>

        <loading :loading="removingEvent" />
    </div>
</template>

<style>
.hc-agenda-event {
    background-color: #eee;
    border-radius: 7px;
    box-shadow: -3px 3px 5px -1px #0002;
    color: #000;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    font-size: 11px;
    opacity: 0.6;
    overflow: hidden;
    pointer-events: none;
    position: absolute;
    transform-origin: top center;
    transition: all 50ms ease-out;
    width: 100%;
}

.hc-agenda-event.editable {
    opacity: 1;
    pointer-events: all;
}

.hc-agenda-event.associated {
    opacity: 1;
    pointer-events: all;
}

.hc-agenda-event.other-project {
    pointer-events: all;
    opacity: 0.7;
}

.agenda-list .hc-agenda-event {
    position: relative;
    margin-top: 5px;
    top: unset !important;
    height: auto !important;
    width: 100% !important;
    right: unset !important;
}

.hc-agenda-event.done:not(.color-by-label):not(:hover) {
    background-color: #d7d7d7 !important;
    color: #000000 !important;
}

/*
.hc-agenda-event.done {
    background-color: white !important;
    color: #000000 !important;
    outline-width: 1px !important;
}
*/
.hc-agenda-event.dragged {
    transform: scale(1.01);
    box-shadow: 0 4px 5px -2px #0006;
    pointer-events: none;
}

.hc-agenda-event:hover {
    z-index: 100 !important;
}

.hc-agenda-event * {
    color: inherit;
}

.dragged-event .hc-agenda-event {
    pointer-events: none;
}

.hc-agenda-event-header {
    width: 100%;
    height: 20px;
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: row;
    background-color: inherit;
}

.hc-agenda-event.editable .hc-agenda-event-header {
    cursor: move;
}

.hc-agenda-event-done,
.hc-agenda-event-confirmed {
    opacity: 0.4;
    cursor: pointer;
    text-align: center;
    border-radius: 50%;
}

.hc-agenda-event-done.done,
.hc-agenda-event-confirmed.confirmed {
    opacity: 1;
    background-color: #0002;
}

.hc-agenda-event-done:not(.done):hover,
.hc-agenda-event-confirmed:not(.confirmed):hover {
    opacity: 0.6;
}

.hc-agenda-event-done.done:hover,
.hc-agenda-event-confirmed.confirmed:hover {
    opacity: 0.9;
}

.hc-agenda-event-name {
    flex: 1;
    line-height: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: bold;
}

.hc-agenda-event-name > * {
    text-decoration: none;
}

.hc-agenda-event-name:hover {
    color: inherit;
    text-decoration: underline;
}

.hc-agenda-event-time {
    line-height: 20px;
    padding: 0 3px;
}

.hc-agenda-event-body {
    width: 100%;
    padding: 3px 7px;
    opacity: 0.8;
    overflow: hidden;
}

.hc-agenda-event-date {
    display: none;
    font-size: 10px;
}

.agenda-list .hc-agenda-event-date {
    display: block;
}

.hc-agenda-event-date,
.hc-agenda-event-user,
.hc-agenda-event-location {
    font-size: 10px;
    display: flex;
    flex-direction: row;
}

.hc-agenda-event-date > span,
.hc-agenda-event-user > span,
.hc-agenda-event-location > span {
    flex: 1;
}

.hc-agenda-event-date > i,
.hc-agenda-event-user > i,
.hc-agenda-event-location > i {
    margin-right: 5px;
    font-size: 8px;
    display: flex;
    flex-direction: row;
    align-items: center;
}

.hc-agenda-event-footer {
    width: 100%;
    height: 26px;
    background-color: inherit;
    position: absolute;
    bottom: 0;
    left: 0;
    display: flex;
    flex-direction: row;
}

.agenda-list .hc-agenda-event-footer {
    position: relative;
}

.hc-agenda-event-route {
    padding: 0 5px;
    line-height: 30px;
    flex: 1;
    white-space: nowrap;
}

.hc-agenda-event-resize {
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 4px;
    cursor: n-resize;
    z-index: 3;
}

.hc-agenda-event-menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 5;
    cursor: default;
    display: none;
    background-color: #0002;
}

.show-context-menu .hc-agenda-event-menu-overlay {
    display: block;
}

.hc-agenda-event-menu {
    position: absolute;
    right: 2px;
    top: 20px;
    background-color: white;
    box-shadow: 0 5px 10px -4px #0003;
    border-radius: 5px;
    width: auto;
    height: auto;
    padding: 5px;
    z-index: 6;
    visibility: hidden;
    opacity: 0;
    transform-origin: top right;
    transform: scale(0.95);
    transition: all 50ms ease-out;
}

.show-context-menu {
    z-index: 101 !important;
    overflow: visible;
}

.show-context-menu .hc-agenda-event-menu {
    visibility: visible;
    opacity: 1;
    transform: scale(1);
}

.hc-agenda-event-menu .hc-item-main-content {
    color: black !important;
}

.hc-agenda-event-project {
    flex: 1;
    text-align: right;
    display: flex;
    align-items: center;
    justify-content: right;
    padding-right: 5px;
}

.hc-agenda-event-project > span {
    border-radius: 3px;
    padding: 3px 4px;
    background: #0007;
    color: white;
    line-height: 12px;
    font-size: 10px;
}

@media (max-width: 767px) {
    .hc-agenda-event-header {
        height: 30px;
    }

    .hc-agenda-event-name,
    .hc-agenda-event-time {
        line-height: 30px;
    }

    .hc-agenda-event-menu {
        top: 30px;
    }

    .hc-agenda-event-footer {
        height: 30px;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    SET_EVENT,
    SET_DRAGGED_EVENT,
    SET_RESIZED_EVENT,
    UPDATE_EVENT,
    REMOVE_EVENT,
} from "@/actions/project/event";

import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { OPEN_SLIDE, OPEN_LEFT_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";

export default {
    props: {
        event: {
            type: Object,
        },
        agendaDate: {
            type: String,
        },
        isDraggedEvent: {
            type: Boolean,
            default: false,
        },
        isResizedEvent: {
            type: Boolean,
            default: false,
        },
        dailyDirections: {
            type: Object,
        },
    },

    data() {
        return {
            showContextMenu: false,
            removingEvent: false,
        };
    },

    mounted() {
        const url = new URL(window.location.href);
        const searchParams = new URLSearchParams(url.search);
        const event = searchParams.get("event");

        if (event && event == this.event.id) {
            this.$refs.event.scrollIntoView({
                behavior: "auto",
                block: "center",
                inline: "center",
            });
        }
    },

    methods: {
        dragStart() {
            if (!this.editable || this.agendaList) {
                return;
            }

            store.commit(SET_DRAGGED_EVENT, { ...this.event });
        },

        resizeStart() {
            if (!this.editable) {
                return;
            }

            store.commit(SET_RESIZED_EVENT, { ...this.event });
        },

        edit() {
            if (this.eventProject) {
                const routeData = this.$router.resolve({
                    name: "event",
                    params: {
                        project: this.eventProject.slug,
                    },
                    query: {
                        filters: JSON.stringify({
                            startedAt: this.eventsParamsValue("startedAt"),
                            endedAt: this.eventsParamsValue("endedAt"),
                        }),
                        event: this.event.id,
                        "display-mode": this.agendaDisplayMode,
                    },
                });
                window.location.href = routeData.href;

                return;
            }

            if (!this.editable && !this.associated) {
                return;
            }

            store.commit(SET_PROSPECT, null);
            store.commit(SET_EVENT, this.event);
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         * Manage prospect events
         * See: @/components/prospect/event/Slide.vue
         */
        addEvent() {
            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(120);

            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(SET_EVENT, {
                prospect: this.event.prospect,
                // user: this.$store.state.auth.user,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        remove() {
            if (!this.editable) {
                return;
            }

            hcConfirm(
                "Voulez-vous vraiment supprimer ce RDV ?<br>" +
                    "<br>" +
                    (this.event.prospect
                        ? "<b>Prospect</b>: " +
                          [
                              this.event.prospect.first_name,
                              "",
                          ].join(" ") +
                          "<br>"
                        : "") +
                    "<b>Date</b>: " +
                    dayjs(this.event.started_at).format("ddd, DD MMM YYYY") +
                    "<br>" +
                    "<b>Calendrier</b>: " +
                    this.calendar.name,
                async () => {
                    this.removingEvent = true;

                    try {
                        await store.dispatch(REMOVE_EVENT, this.event.id);
                    } finally {
                        this.removingEvent = false;
                    }
                }
            );
        },

        toggleDone() {
            if (!this.editable) {
                return;
            }

            const data = {
                id: this.event.id,
                done: !this.event.done,
            };

            store.commit(UPDATE_EVENT, data);
            store.dispatch(UPDATE_EVENT, data);
        },

        toggleConfirmed() {
            if (!this.editable) {
                return;
            }

            const data = {
                id: this.event.id,
                confirmed: !this.event.confirmed,
            };

            store.commit(UPDATE_EVENT, data);
            store.dispatch(UPDATE_EVENT, data);
        },

        /**
         * Manage prospect files
         * See: @/components/prospect/file/Slide.vue
         */
        manageFiles() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-files");
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         * Manage prospect orders
         * See: @/components/prospect/order/Slide.vue
         */
        manageOrders() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },

        /**
         * Manage prospect interactions
         * See: @/components/prospect/interaction/Slide.vue
         */
        manageInteractions() {
            store.commit(SET_INTERACTION_PROSPECT, this.event.prospect);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
        },

        /**
         * Manage prospect sms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageSms() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },

        /**
         * Manage prospect documents
         * See: @/components/prospect/document/Slide.vue
         */
        manageDocuments() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-documents");
        },
    },

    watch: {
        showContextMenu(newValue) {
            this.$emit("context-menu", newValue);
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "calendars",
            "draggedEvent",
            "resizedEvent",
            "agendaList",
            "eventsParamsValue",
            "projectUserSettingsAgendaMinimumHour",
            "projectUserSettingsAgendaMaximumHour",
            "projectUserSettingsEventsColorByLabel",
            "agendaDisplayMode",
            "can",
        ]),

        cellHeight() {
            return deviceType() == "desktop" ? 20 : 30;
        },

        calendar() {
            const calendar = this.calendars.find(
                (c) => c.id == this.event.calendar_id
            );
            return calendar ? calendar : this.event.calendar;
        },

        eventProject() {
            return this.event.calendar && this.event.calendar.project
                ? this.event.calendar.project
                : null;
        },

        startHour() {
            return parseInt(this.event.started_at.substring(11, 13));
        },

        startMinute() {
            return parseInt(this.event.started_at.substring(14, 16));
        },

        endHour() {
            return parseInt(this.event.ended_at.substring(11, 13));
        },

        endMinute() {
            return parseInt(this.event.ended_at.substring(14, 16));
        },

        startInMinutes() {
            return (
                (this.event.started_at.substring(0, 10) == this.agendaDate
                    ? this.startHour - this.projectUserSettingsAgendaMinimumHour
                    : 0) *
                    60 +
                this.startMinute
            );
        },

        endInMinutes() {
            return (
                ((this.event.ended_at.substring(0, 10) == this.agendaDate
                    ? this.endHour
                    : this.projectUserSettingsAgendaMaximumHour) -
                    this.projectUserSettingsAgendaMinimumHour) *
                    60 +
                this.endMinute
            );
        },

        delayInMinutes() {
            return this.endInMinutes - this.startInMinutes;
        },

        colorByLabel() {
            if (
                this.projectUserSettingsEventsColorByLabel &&
                this.event.prospect &&
                this.event.prospect.labels
            ) {
                return this.event.prospect.labels.find(
                    (l) =>
                        l.category_id ==
                        this.projectUserSettingsEventsColorByLabel
                );
            }

            return null;
        },

        top() {
            return (this.startInMinutes * this.cellHeight) / 15 + "px";
        },

        height() {
            return (this.delayInMinutes * this.cellHeight) / 15 + "px";
        },

        right() {
            return this.event.indexPos / 2 + "%";
        },

        width() {
            return 100 - this.event.indexPos * 5 + "%";
        },

        zIndex() {
            return this.startInMinutes / 15;
        },

        color() {
            if (this.colorByLabel) {
                return this.colorByLabel.color;
            }

            if (!this.calendar) {
                return null;
            }

            return this.calendar.color;
        },

        backgroundColor() {
            if (this.colorByLabel) {
                return this.colorByLabel.bgcolor;
            }

            if (!this.calendar) {
                return null;
            }

            return this.calendar.bgcolor;
        },

        style() {
            return {
                top: this.top,
                right: this.right,
                width: this.width,
                height: this.height,
                zIndex: this.zIndex,
                color: this.color,
                backgroundColor: this.backgroundColor,
            };
        },

        time() {
            const hour = parseInt(this.event.started_at.substring(11, 13));
            const minute = parseInt(this.event.started_at.substring(14, 16));

            return (
                hour +
                "h" +
                (minute == 0 ? "" : minute < 10 ? "0" + minute : minute)
            );
        },

        /**
         * Icon
         * that will be shown
         * in the event item
         * in terms of
         * the type of the event calendar
         */
        icon() {
            if (!this.calendar) {
                return "fa fa-calendar";
                // Physical calendar
            } else if (this.calendar.type == "physical") {
                return "fa fa-map-marker";
                // Phone calendar
            } else if (this.calendar.type == "telephone") {
                return "fa fa-phone";
                // GMeet calendar
            } else if (this.calendar.type == "hangout") {
                return "fa fa-video";
                // Task calendar
            } else if (this.calendar.type == "task") {
                return "fa fa-tasks";
                // Other
            } else {
                return "fa fa-calendar";
            }
        },

        eventDailyDirection() {
            if (!this.dailyDirections) {
                return null;
            }

            // Check if daily direction is fetched
            if (!this.dailyDirections[this.event.user_id]) {
                return null;
            }

            const userDirections = this.dailyDirections[this.event.user_id];

            let index;
            const events = /*JSON.parse*/ userDirections.events;
            if (
                !events ||
                (index = events.findIndex((e) => e == this.event.id)) <= 0
            ) {
                return null;
            }

            const direction = userDirections.direction
                ? /*JSON.parse*/ userDirections.direction
                : null;
            if (!direction || direction.status !== "OK") {
                return null;
            }

            return direction.routes[0].legs[index - 1];
        },

        direction() {
            let direction = this.eventDailyDirection;

            return direction
                ? direction.distance.text +
                      " - " +
                      direction.duration.text
                          .replace(/ heures?/, "h")
                          .replace(/ minutes?/, "mn")
                : "";
        },

        editable() {
            if (this.eventProject) {
                return false;
            }

            if (this.can("all")) {
                return true;
            }

            if (this.event.user_id == this.user.id) {
                return true;
            }

            if (this.event.creator_id == this.user.id) {
                return true;
            }

            return false;
        },

        associated() {
            if (
                Array.isArray(this.event.users) &&
                this.event.users.find((u) => u.id == this.user.id)
            ) {
                return true;
            }

            return false;
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
    },
};
</script>
