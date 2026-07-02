<template>
    <slide
        :name="name"
        :title="$t('event.user_daily_directions.title')"
        icon="fa fa-route"
        style="width: 600px"
        @first-open="initMap(), checkDefaultMapColorByCategory()"
    >
        <div class="hc-flex-column" style="height: 100%; position: relative">
            <div
                class="user-direction-panel-prospect-search"
                :class="
                    (prospectSearchOpen ? 'open' : '') +
                    (searchOpen ? ' search-open' : '') +
                    (prospects.length > 0 ? ' search-result' : '')
                "
            >
                <div>
                    <input
                        v-model="prospectKeyword"
                        :placeholder="
                            $t('event.user_daily_directions.find_prospect')
                        "
                        ref="prospectSearchInput"
                        @focus="openSearch"
                        @blur="closeSearch"
                    />
                    <a @click.prevent="closeProspectSearch">&times;</a>

                    <div class="user-direction-panel-prospect-search-result">
                        <ul>
                            <li
                                v-for="prospect in prospects"
                                :key="prospect.id"
                            >
                                <a
                                    @click.prevent="
                                        placeProspectMarker(prospect)
                                    "
                                    class="project-document-template-preview-prospect"
                                    v-text="getProspectName(prospect)"
                                >
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div
                class="user-direction-panel-prospect-search"
                :class="addressSearchOpen ? 'open' : ''"
            >
                <div>
                    <google-map-input
                        ref="addressSearchInput"
                        @changed="placeLocationMarker"
                        :placeholder="
                            $t('event.user_daily_directions.find_address')
                        "
                    >
                    </google-map-input>

                    <a @click.prevent="closeAddressSearch">&times;</a>
                </div>
            </div>

            <div
                id="hc-user-daily-direction-map"
                class="hc-flex-1"
                ref="map"
            ></div>
            <item>
                <icon
                    class="fa fa-user-plus"
                    color="#e5961e"
                    @click.prevent="openProspectSearch"
                />
                <icon
                    class="fa fa-map-marker"
                    color="#9e3cda"
                    @click.prevent="openAddressSearch"
                />

                <div class="hc-item-main-content"></div>

                <icon
                    v-if="selectedDirection"
                    class="fa fa-times icon-red"
                    :title="$t('event.user_daily_directions.remove_from_map')"
                    @click.prevent.stop="
                        removeDirection(
                            selectedDirection.userId,
                            selectedDirection.date
                        ),
                            (selectedDirection = null)
                    "
                    color="#ed5e3e"
                />

                <icon
                    v-if="marker && !selectedDirection"
                    id="user-direction-remove-selected-marker"
                    class="fa fa-trash icon-red"
                    color="#ed5e3e"
                    :title="$t('event.user_daily_directions.remove_marker')"
                    @click.prevent.stop="removeSelectedMarker"
                />

                <icon
                    v-if="prospect && prospect.mobile_phone_number"
                    id="user-direction-send-sms-event-prospect"
                    class="fa fa-comment icon-orange"
                    color="#e5961e"
                    :title="$t('event.user_daily_directions.send_sms')"
                    @click.prevent.stop="prospectSms"
                />

                <icon
                    v-if="
                        prospect &&
                        (prospect.phone_number || prospect.mobile_phone_number)
                    "
                    id="user-direction-call-event-prospect"
                    class="fa fa-phone icon-green"
                    color="#489f1f"
                    :title="$t('event.user_daily_directions.call')"
                    @click.prevent.stop="prospectInteractions"
                />

                <icon
                    v-if="prospect"
                    id="user-direction-file-prospect"
                    class="fa fa-folder"
                    color="#1e6ee5"
                    :title="$t('event.user_daily_directions.file')"
                    @click.prevent.stop="prospectFiles"
                />

                <icon
                    v-if="prospect"
                    id="user-direction-message-prospect"
                    class="gmi-item-option fa fa-envelope icon-green"
                    :title="$t('event.user_daily_directions.message')"
                    @click.prevent.stop="prospectMessages"
                />

                <icon
                    v-if="prospect"
                    id="user-direction-event-prospect"
                    class="gmi-item-option fa fa-calendar icon-purple"
                    :title="$t('event.user_daily_directions.event')"
                    @click.prevent.stop="prospectEvents"
                />

                <icon
                    v-if="prospect || event"
                    id="user-direction-search-prospect"
                    class="gmi-item-option fa fa-search icon-blue"
                    :title="$t('event.user_daily_directions.event')"
                    @click.prevent.stop="getProspects(event ? event : prospect)"
                />
            </item>
        </div>
    </slide>
</template>

<style>
.gm-style .gm-style-iw-c {
    border-radius: 3px;
    box-shadow: 0 3px 4px -3px #a59d8a;
    padding: 0;
    padding-top: 0 !important;
}

.gm-style-iw-d {
    overflow: auto !important;
}

.gm-style .gm-style-iw-c > button {
    width: 40px !important;
    height: 40px !important;
    top: 0 !important;
    right: 0 !important;
    text-align: center !important;
}

.gm-style .gm-style-iw-c > button > img {
    display: inline-block !important;
    margin: 0 !important;
}

.gmap-prospect-infowindow {
    position: relative;
    font-weight: 400;
    max-width: 320px;
    float: left;
    border-radius: 5px;
}

.gmap-prospect-infowindow-header {
    float: left;
    width: 100%;
    padding: 0px 40px 0px 0px;
    display: flex;
    flex-direction: row;
    height: 40px;
}

.gmap-prospect-infowindow-body {
    float: left;
    width: 100%;
    padding: 0px 10px 10px 10px;
    font-size: 12px;
}

.gmap-prospect-infowindow-name a:focus {
    outline: none;
    text-decoration: none;
}

.gmap-prospect-infowindow-header-btn {
    width: auto;
}

.gmap-prospect-infowindow-header-btn > * {
    float: right;
    font-size: 13px !important;
    width: 30px;
    height: 30px;
    text-align: center;
    line-height: 30px !important;
    color: #07647c;
    border-radius: 50%;
    cursor: pointer;
    margin-left: 0;
    color: inherit;
    text-decoration: none;
}

.gmap-prospect-infowindow-header-btn > *:focus {
    outline: none;
    text-decoration: none;
    color: inherit;
}

.gmap-prospect-infowindow-header-btn > *:hover {
    text-decoration: none;
}

.user-direction-panel-prospect-search {
    position: absolute;
    width: 100%;
    height: 60px;
    top: 0;
    left: 0;
    padding: 10px;
    text-align: center;
    z-index: 1;
    transform: translateY(-100%);
    transition: transform 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.user-direction-panel-prospect-search.open {
    transform: translateY(0);
}

.user-direction-panel-prospect-search > div {
    margin: auto;
    height: 100%;
    width: 380px;
    background-color: white;
    border-radius: 5px;
    display: flex;
    flex-direction: row;
    box-shadow: 0 3px 4px -1px #50506933;
    position: relative;
    overflow: hidden;
    max-width: 100%;
}

.user-direction-panel-prospect-search > div > input {
    border: none;
    flex: 1;
    padding: 0 10px;
    font-size: 13px;
}

.user-direction-panel-prospect-search > div > input:focus {
    outline: none;
}

.user-direction-panel-prospect-search > div > a {
    width: 40px;
    height: 40px;
    line-height: 36px;
    text-align: center;
    font-size: 20px;
    cursor: pointer;
}

.user-direction-panel-prospect-search > div > a:hover {
    text-decoration: none;
    background-color: #0001;
}

.user-direction-panel-prospect-search.search-open.search-result > div {
    overflow: visible;
}

.user-direction-panel-prospect-search-result {
    visibility: hidden;
    width: 100%;
    max-height: 300px;
    overflow: auto;
    position: absolute;
    left: 0;
    top: 40px;
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 7px 12px -5px #0002;
    z-index: 1;
    text-align: left;
}

.user-direction-panel-prospect-search-result > ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.user-direction-panel-prospect-search-result > ul > li {
    width: 100%;
}

.user-direction-panel-prospect-search-result > ul > li > a {
    cursor: pointer;
    width: 100%;
    height: auto;
    padding: 5px 10px;
    display: inline-block;
    color: #777;
    font-size: 12px;
    text-decoration: none;
}

.user-direction-panel-prospect-search-result
    > ul
    > li
    > a
    .gmi-item-main-content-first {
    font-size: 12px !important;
}
.user-direction-panel-prospect-search-result
    > ul
    > li
    > a
    .gmi-item-main-content-second {
    color: #1e6ee5 !important;
}

.search-open.search-result .user-direction-panel-prospect-search-result {
    visibility: visible;
}
</style>

<script>
import ProspectService from "@/apis/project/prospect";
import EventService from "@/apis/project/event";
import { mapGetters } from "vuex";
import store from "@/store";
import GoogleMapInit from "@/utils/google-map";

// Actions
import { OPEN_SUB_SLIDE, OPEN_LEFT_SLIDE } from "@/actions/slide";
import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { SET_EVENT } from "@/actions/project/event";
import { INIT_USER_DAILY_DIRECTIONS } from "@/actions/project/user/daily-direction";

import { GET_SETTING } from "@/actions/project/setting";

import { SET_MAP_COLOR_BY_LABEL } from "@/actions/project/map";
// Components

/**
 * List of prospect markers
 */
let hcMapUserDailyDirectionsProspectsMapMarkers = [];

/**
 * Circle defining the search radius
 * with respect to the position (latitude, longitude) indicated
 */
let hcMapUserDailyDirectionsProspectsMapCircle = null;

let hcMapUserDailyDirections = {};
let hcMapUserDailyDirectionsInfoWindow = {};

let hcMapUserDailyDirectionsInitialized = false;
let hcMapUserDailyDirectionsResolveInitPromise;
let hcMapUserDailyDirectionsRejectInitPromise;
// This promise handles the initialization
// status of the google maps script.
const initHcMapUserDailyDirectionsPromise = new Promise((resolve, reject) => {
    hcMapUserDailyDirectionsResolveInitPromise = resolve;
    hcMapUserDailyDirectionsRejectInitPromise = reject;
});

let hcCurrentUserDailyDirectionMarker = null;

export default {
    components: {},

    data() {
        return {
            name: "user-daily-direction",
            map: null,

            mapIconTemplate:
                '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="40px" viewBox="0 0 24 40" style="enable-background:new 0 0 24 40;" xml:space="preserve"><path style="fill:{{ color }};stroke:#FFFFFFAA;stroke-width:1;" d="M22.2,17.9L22.2,17.9c0.8-1.6,1.2-3.3,1.2-5.2c0-6.3-5.1-11.5-11.5-11.5S0.6,6.4,0.6,12.7 c0,1.9,0.5,3.6,1.2,5.2l0,0c2.1,4.3,5.6,8,7.6,12.3c1.5,3.3,2.2,7.2,2.5,9l0,0c0,0.1,0.1,0.1,0.1,0.1c0.1,0,0.1-0.1,0.1-0.1l0,0 c0.3-1.7,1-5.7,2.5-9C16.6,26,20.1,22.2,22.2,17.9z"/><text transform="matrix(1 0 0 1 8.8906 17.9366)" style="fill:#FFFFFF;font-size:12px;font-weight:bold;text-align:center;">{{ index }}</text><circle style="fill:{{ event-color }};stroke:#FFFFFF;stroke-width:1;" cx="19.1" cy="4.9" r="4.6"/></svg>',

            prospectIconTemplate:
                '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 26 37" width="26px" height="37px" style="enable-background:new 0 0 26 37;" xml:space="preserve"><path style="fill:{bgcolor}" d="M26,13c0-7.2-5.8-13-13-13S0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0c2.8,3,5.8,6.1,7.3,9.2s1,5.9,2.4,5.9 s0.9-2.8,2.4-5.9s4.5-6.2,7.3-9.2l0,0C24.7,19.4,26,16.3,26,13z"/><path style="fill:#00000015" d="M13,1c6.6,0,12,5.4,12,12c0,2.9-1.1,5.8-3.1,8l-0.4,0.4c-0.2,0.3-0.5,0.5-0.7,0.8c-2.5,2.6-5,5.4-6.3,8.2 c-0.7,1.5-1,2.9-1.2,3.9c-0.1,0.3-0.2,0.8-0.3,1.1c-0.1-0.3-0.2-0.7-0.3-1.1c-0.2-1-0.5-2.4-1.2-3.9c-1.3-2.8-3.9-5.6-6.3-8.2 C5,22,4.9,21.9,4.7,21.7L4.1,21c-2-2.2-3.1-5.1-3.1-8C1,6.4,6.4,1,13,1 M13,0C5.8,0,0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0 c2.8,3,5.8,6.1,7.3,9.2c1.5,3.1,1,5.9,2.4,5.9s0.9-2.8,2.4-5.9c1.5-3.1,4.5-6.2,7.3-9.2l0,0c2.1-2.3,3.3-5.3,3.3-8.7 C26,5.8,20.2,0,13,0L13,0z"/><circle style="opacity:0.3" cx="13" cy="13" r="5"/></svg>',

            // Prospect search

            prospectSearchOpen: false,
            searchOpen: false,
            addressSearchOpen: false,
            prospectCancelTokenSource: null,
            fetchingProspects: false,
            prospectKeyword: "",
            prospects: [],

            selectedProspect: null,
            event: null,
            selectedDirection: null,
            marker: null,

            mapProspects: [],
            /**
             * For optimization,
             * Cache already generated marker templates for each color
             */
            generatedMarkerTemplates: {},
        };
    },

    created() {
        store.commit(INIT_USER_DAILY_DIRECTIONS);
    },

    methods: {
        /**
         * Show google map
         */
        async initMap() {
            if (hcMapUserDailyDirectionsInitialized)
                return initHcMapUserDailyDirectionsPromise;

            hcMapUserDailyDirectionsInitialized = true;

            await GoogleMapInit();

            // MAP
            this.map = new google.maps.Map(this.$refs.map, {
                // Cambrai city as center of the map
                center: new google.maps.LatLng(50.1773063, 3.2337914),
                zoom: 18,
                fullscreenControl: false,
            });

            // Map circle
            hcMapUserDailyDirectionsProspectsMapCircle = new google.maps.Circle(
                {
                    strokeColor: "#7939B8",
                    strokeOpacity: 0,
                    strokeWeight: 2,
                    fillColor: "#7939B8",
                    fillOpacity: 0.1,
                    clickable: false,
                }
            );

            hcMapUserDailyDirectionsInfoWindow = new google.maps.InfoWindow({
                minWidth: 300,
            });

            hcMapUserDailyDirectionsResolveInitPromise();

            return initHcMapUserDailyDirectionsPromise;
        },

        /**
         * Get project prospects
         */
        async getProspects(from) {
            const radius = 10;

            hcMapUserDailyDirectionsProspectsMapCircle.setMap(this.map);
            hcMapUserDailyDirectionsProspectsMapCircle.setCenter(
                new google.maps.LatLng(from.latitude, from.longitude)
            );
            hcMapUserDailyDirectionsProspectsMapCircle.setRadius(radius * 1000);

            // Fields that we need to get
            let fields = [
                "id",
                "first_name",
                "last_name",
                "phone_number",
                "mobile_phone_number",
                "street",
                "postal_code",
                "city",
                "country",
                "latitude",
                "longitude",
            ];
            if (this.mapColorByLabel) {
                fields.push("category->" + this.mapColorByLabel);
            }

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify({
                            position: [
                                from.latitude,
                                from.longitude,
                                radius,
                            ].join(","),
                        }),
                        count: 500,
                        fields: fields.join(","),
                    },
                });

                this.mapProspects = data.data;
            } finally {
            }
        },

        /**
         * Fetch events
         */
        async addDirection(dir) {
            await this.initMap();

            if (!dir.direction) {
                return;
            }

            const direction = dir.direction;
            if (direction.status != "OK") {
                return;
            }

            let key = dir.user_id + "-" + dir.direction_at;

            if (!hcMapUserDailyDirections[key]) {
                let markerColor = dir.color;

                hcMapUserDailyDirections[key] = {
                    userId: dir.user_id,
                    date: dir.direction_at,
                    events: [],
                    direction: dir,
                    renderer: new google.maps.DirectionsRenderer({
                        optimizeWaypoints: false,
                        suppressMarkers: true,
                        map: this.map,
                    }),
                    markers: [],
                    markerColor: markerColor,
                };

                hcMapUserDailyDirections[key].renderer.setOptions({
                    polylineOptions: {
                        strokeColor: markerColor + "77",
                    },
                });
            }

            const events = dir.events;
            let eventResponse = await EventService.get(this.project.slug, {
                params: {
                    sort_by: "started_at",
                    filters: JSON.stringify({
                        ids: events,
                    }),
                },
            });

            hcMapUserDailyDirections[key].events = eventResponse.data;

            if (direction.routes) {
                direction.routes.forEach((route) => {
                    if (route.bounds.southwest) {
                        route.bounds = {
                            south: route.bounds.southwest.lat,
                            west: route.bounds.southwest.lng,
                            north: route.bounds.northeast.lat,
                            east: route.bounds.northeast.lng,
                        };
                        route.legs.forEach((leg) => {
                            leg.steps.forEach((step) => {
                                step.path =
                                    google.maps.geometry.encoding.decodePath(
                                        step.polyline.points
                                    );
                            });
                        });
                    }
                });

                hcMapUserDailyDirections[key].direction = direction;

                var legs = direction.routes[0].legs;

                hcMapUserDailyDirections[key].events[0].distance = "";
                hcMapUserDailyDirections[key].events[0].duration = "";

                for (var i = 0; i < legs.length; i++) {
                    var leg = legs[i];
                    var distance = leg.distance.text;
                    var duration = leg.duration.text
                        .replace(/\s+heures?/g, "h")
                        .replace(/\s+minutes?/g, "m");

                    hcMapUserDailyDirections[key].events[i + 1].distance =
                        distance;
                    hcMapUserDailyDirections[key].events[i + 1].duration =
                        duration;
                }

                hcMapUserDailyDirections[key].markers = this.getMarkers(
                    hcMapUserDailyDirections[key]
                );

                hcMapUserDailyDirections[key].renderer.setDirections(
                    hcMapUserDailyDirections[key].direction
                );
            } else {
                hcMapUserDailyDirections[key].direction = direction;
                let event = hcMapUserDailyDirections[key].events[0];

                let marker = this.createEventProspectMarker(
                    hcMapUserDailyDirections[key],
                    event,
                    1,
                    hcMapUserDailyDirections[key].markerColor,
                    event.calendar.bgcolor,
                    direction.candidates[0].geometry.location,
                    this.getInfoWindowContent(event)
                );

                hcMapUserDailyDirections[key].markers = [marker];
                this.map.setCenter(direction.candidates[0].geometry.location);
            }
        },

        /**
         * Remove direction
         */
        removeDirection(userId, date) {
            let key = userId + "-" + date;

            if (hcMapUserDailyDirections[key]) {
                hcMapUserDailyDirections[key].renderer.setMap(null);
                hcMapUserDailyDirections[key].markers.forEach((marker) => {
                    marker.setMap(null);
                });
                delete hcMapUserDailyDirections[key];
                // this.marker = null;

                return true;
            }

            return false;
        },

        /**
         * Generate random color
         */
        randColor() {
            let sum = parseInt(Math.random() * 40 + 230);
            let r = parseInt(Math.random() * Math.min(sum, 255));
            let g = parseInt(Math.random() * Math.min(sum - r, 255));
            let b = sum - r - g;

            return (
                "#" +
                ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)
            );
        },

        /**
         * Get events markers
         */
        getMarkers(direction) {
            let markers = [];

            if (direction.direction) {
                var legs = direction.direction.routes[0].legs;
                for (var j = 0; j < legs.length; j++) {
                    var locationLeg = legs[j];

                    let marker = this.createEventProspectMarker(
                        direction,
                        direction.events[j],
                        j + 1,
                        direction.markerColor,
                        direction.events[j].calendar.bgcolor,
                        locationLeg.start_location,
                        this.getInfoWindowContent(direction.events[j])
                    );

                    markers.push(marker);

                    if (j == legs.length - 1 && direction.events.length > 1) {
                        let marker = this.createEventProspectMarker(
                            direction,
                            direction.events[j + 1],
                            j + 2,
                            direction.markerColor,
                            direction.events[j + 1].calendar.bgcolor,
                            locationLeg.end_location,
                            this.getInfoWindowContent(direction.events[j + 1])
                        );

                        markers.push(marker);
                    }
                }
            }

            return markers;
        },

        /**
         *
         * @param {*} event
         */
        getInfoWindowContent(event) {
            let prospect = event.prospect;
            let calendar = event.calendar;

            var html =
                '<div class="gmap-prospect-infowindow" style="background-color: ' +
                calendar.bgcolor +
                "; color: " +
                calendar.color +
                '">';

            // header

            if (prospect) {
                html += '<div class="gmap-prospect-infowindow-header">';

                html += '<div class="gmap-prospect-infowindow-header-btn">';

                html +=
                    '<a href="#" class="gmap-prospect-infowindow-call fa fa-dot-circle-o" title="Chercher des prospects autour ..." onclick="document.getElementById(\'user-direction-prospects-search\').click(); return false;"></a>';

                html +=
                    '<a target="_blank" href="https://www.google.com/maps/?q=' +
                    encodeURI(event.location) +
                    '" class="gmap-prospect-infowindow-position fa fa-map-marker"></a>';

                html +=
                    '<a href="#" class="gmap-prospect-infowindow-call fa fa-calendar" onclick="document.getElementById(\'user-direction-event-prospect\').click(); return false;"></a>';

                html +=
                    '<a href="#" class="gmap-prospect-infowindow-call fa fa-folder" onclick="document.getElementById(\'user-direction-file-prospect\').click(); return false;"></a>';

                html +=
                    '<a href="#" class="gmap-prospect-infowindow-call fa fa-envelope" onclick="document.getElementById(\'user-direction-message-prospect\').click(); return false;"></a>';

                if (prospect.mobile_phone_number) {
                    html +=
                        '<a href="#" class="gmap-prospect-infowindow-sms fa fa-comment" onclick="document.getElementById(\'user-direction-send-sms-event-prospect\').click(); return false;"></a>';
                }

                if (prospect.phone_number || prospect.mobile_phone_number) {
                    html +=
                        '<a href="#" class="gmap-prospect-infowindow-call fa fa-phone" onclick="document.getElementById(\'user-direction-call-event-prospect\').click(); return false;"></a>';
                }

                html +=
                    '<a href="#" class="gmap-prospect-infowindow-call fa fa-search" onclick="document.getElementById(\'user-direction-search-prospect\').click(); return false;"></a>';

                html += "</div>";

                html += "</div>";
            }

            // body

            html += '<div class="gmap-prospect-infowindow-body">';

            // event prospect

            if (prospect) {
                html +=
                    '<div class="gmap-prospect-infowindow-name" style="margin: 8px 0;">';
                html +=
                    '<span class="fa fa-user" style="width: 26px;text-align: center;"></span>';
                html +=
                    '<b><a href="' +
                    this.getProspectUrl(prospect) +
                    '" target="_blank" style="color: inherit">' +
                    this.getProspectName(prospect) +
                    "</a></b>";
                html += "</div>";
            }

            // event calendar

            if (calendar) {
                html +=
                    '<div class="gmap-prospect-infowindow-location" style="margin: 8px 0;">';
                html +=
                    '<span class="fa fa-calendar" style="width: 26px;text-align: center;"></span>';
                html += calendar.name;
                html += "</div>";
            }

            // event location

            html +=
                '<div class="gmap-prospect-infowindow-location" style="margin: 8px 0;">';
            html +=
                '<span class="fa fa-map-marker" style="width: 26px;text-align: center;"></span>';
            html += event.location;
            html += "</div>";

            // event hour

            html +=
                '<div class="gmap-prospect-infowindow-location" style="margin: 8px 0;">';
            html +=
                '<span class="fa fa-calendar" style="width: 26px;text-align: center;"></span>';
            html +=
                event.started_at.substring(0, 10) +
                " " +
                event.started_at.substring(11, 16) +
                " - " +
                event.ended_at.substring(11, 16);
            html += "</div>";

            // route distance and duration

            if (event.distance && event.duration) {
                html += '<div style="font-weight: bold; margin: 8px 0;">';
                html +=
                    '<span class="fa fa-car" style="width: 26px;text-align: center;"></span>';
                html +=
                    '<span class="gmap-prospect-infowindow-distance">' +
                    event.distance +
                    "</span>";
                html += " - ";
                html +=
                    '<span class="gmap-prospect-infowindow-duration">' +
                    event.duration +
                    "</span>";
                html += "</div>";
            }

            html += "</div>";

            html += "</div>";

            return html;
        },

        // Get Prospect Url

        getProspectUrl(prospect) {
            return "/project/" + this.project.slug + "/prospect/" + prospect.id;
        },

        // Get Prospect Location

        getProspectAddress(prospect) {
            return [
                prospect.street,
                prospect.street_bis,
                prospect.postal_code,
                prospect.city,
                prospect.state,
                prospect.county,
                prospect.country,
            ]
                .filter((item) => item)
                .join(" ");
        },

        // Get Prospect Name
        // by concatening prospect:
        // - first_name
        // - last_name
        // - company_name

        getProspectName(prospect) {
            return [prospect.first_name, prospect.last_name]
                .filter((item) => item)
                .join(" ");
        },

        // Create a map marker icon
        // index: index in route
        // color: map marker color
        // eventColor:

        getIcon(index, color, eventColor) {
            return (
                "data:image/svg+xml;charset=UTF-8;base64," +
                btoa(
                    this.mapIconTemplate
                        .replace("{{ index }}", index)
                        .replace("{{ color }}", color)
                        .replace("{{ event-color }}", eventColor)
                )
            );
        },

        // Create custom marker for prospect
        // latlng: position
        // html: mapInfoWindow content

        createEventProspectMarker(
            direction,
            event,
            index,
            color,
            eventColor,
            latlng,
            html
        ) {
            // marker
            var marker = new google.maps.Marker({
                position: latlng,
                map: this.map,
                zIndex: Math.round(latlng.lat * -100000) << 5,
                icon: {
                    url: this.getIcon(index, color, eventColor),
                },
            });

            // mapInfoWindow

            google.maps.event.addListener(marker, "click", () => {
                this.initSelectedMarker();
                hcMapUserDailyDirectionsInfoWindow.setContent(html);
                hcMapUserDailyDirectionsInfoWindow.open(this.map, marker);
                this.event = event;
                this.selectedDirection = direction;
                this.marker = marker;
            });

            return marker;
        },

        // search prospect

        openProspectSearch() {
            this.prospectSearchOpen = true;
            this.addressSearchOpen = false;
            this.$refs.prospectSearchInput.focus();
        },

        closeProspectSearch() {
            this.prospectSearchOpen = false;
        },

        openAddressSearch() {
            this.addressSearchOpen = true;
            this.prospectSearchOpen = false;
            this.$refs.addressSearchInput.focus();
        },

        closeAddressSearch() {
            this.addressSearchOpen = false;
        },

        openSearch() {
            this.searchOpen = true;
        },

        closeSearch() {
            setTimeout(() => {
                this.searchOpen = false;
            }, 200);
        },

        initSelectedMarker() {
            this.selectedProspect = null;
            this.event = null;
            this.selectedDirection = null;
            this.marker = null;
        },

        placeProspectMarker(prospect) {
            let latlng = {
                lat: prospect.latitude,
                lng: prospect.longitude,
            };

            var marker = new google.maps.Marker({
                position: latlng,
                map: this.map,
                zIndex: Math.round(latlng.lat * -100000) << 5,
            });

            this.map.setCenter(latlng);

            google.maps.event.addListener(marker, "click", () => {
                this.initSelectedMarker();
                hcMapUserDailyDirectionsInfoWindow.setContent(
                    this.getProspectMapInfoWindowContent(prospect)
                );
                hcMapUserDailyDirectionsInfoWindow.open(this.map, marker);
                this.selectedProspect = prospect;
                this.marker = marker;
                hcCurrentUserDailyDirectionMarker = marker;
            });
        },

        placeLocationMarker(e) {
            let latlng = e.place.geometry.location;

            var marker = new google.maps.Marker({
                position: latlng,
                map: this.map,
                zIndex: Math.round(latlng.lat() * -100000) << 5,
            });

            this.map.setCenter(latlng);

            google.maps.event.addListener(marker, "click", () => {
                this.initSelectedMarker();
                hcMapUserDailyDirectionsInfoWindow.setContent(
                    this.getLocationMapInfoWindowContent(
                        e.place.formatted_address
                    )
                );
                hcMapUserDailyDirectionsInfoWindow.open(this.map, marker);
                this.marker = marker;
                hcCurrentUserDailyDirectionMarker = marker;
            });
        },

        getLocationMapInfoWindowContent(location) {
            var html =
                '<div class="gmap-prospect-infowindow" style="background-color: white">';

            html += '<div class="gmap-prospect-infowindow-header">';

            html += '<div class="gmap-prospect-infowindow-header-btn">';

            html +=
                '<a href="#" class="gmap-prospect-infowindow-remove fa fa-trash" style="color: #ED5E3E" onclick="document.getElementById(\'user-direction-remove-selected-marker\').click(); return false;"></a>';

            html += "</div>";

            html += "</div>";

            // body

            html += '<div class="gmap-prospect-infowindow-body">';

            html += "<div>";

            html += location;

            html += "</div>";

            html += "</div>";

            return html;
        },

        getProspectMapInfoWindowContent(prospect) {
            var html =
                '<div class="gmap-prospect-infowindow" style="background-color: white">';

            html += '<div class="gmap-prospect-infowindow-header">';

            html += '<div class="gmap-prospect-infowindow-header-btn">';

            html +=
                '<a href="#" class="gmap-prospect-infowindow-remove fa fa-trash icon-red" onclick="document.getElementById(\'user-direction-remove-selected-marker\').click(); return false;"></a>';

            html +=
                '<a href="#" class="gmap-prospect-infowindow-call fa fa-calendar icon-purple" onclick="document.getElementById(\'user-direction-event-prospect\').click(); return false;"></a>';

            html +=
                '<a href="#" class="gmap-prospect-infowindow-call fa fa-folder icon-blue" onclick="document.getElementById(\'user-direction-file-prospect\').click(); return false;"></a>';

            html +=
                '<a href="#" class="gmap-prospect-infowindow-call fa fa-envelope icon-green" onclick="document.getElementById(\'user-direction-message-prospect\').click(); return false;"></a>';

            if (prospect.mobile_phone_number) {
                html +=
                    '<a href="#" class="gmap-prospect-infowindow-sms fa fa-comment icon-garnet" onclick="document.getElementById(\'user-direction-send-sms-event-prospect\').click(); return false;"></a>';
            }

            if (prospect.phone_number || prospect.mobile_phone_number) {
                html +=
                    '<a href="#" class="gmap-prospect-infowindow-call fa fa-phone icon-green" onclick="document.getElementById(\'user-direction-call-event-prospect\').click(); return false;"></a>';
            }

            html +=
                '<a href="#" class="gmap-prospect-infowindow-call fa fa-search icon-blue" onclick="document.getElementById(\'user-direction-search-prospect\').click(); return false;"></a>';

            html += "</div>";

            html += "</div>";

            // body

            html += '<div class="gmap-prospect-infowindow-body">';

            html += "<div>";

            html +=
                '<div class="gmap-prospect-infowindow-name" style="margin: 8px 0;">';
            html +=
                '<span class="fa fa-user" style="width: 26px;text-align: center;"></span>';
            html +=
                '<b><a href="' +
                this.getProspectUrl(prospect) +
                '" target="_blank" style="color: inherit">' +
                this.getProspectName(prospect) +
                "</a></b>";
            html += "</div>";

            html += "</div>";

            // prospect location

            html +=
                '<div class="gmap-prospect-infowindow-location" style="margin: 8px 0;">';
            html +=
                '<span class="fa fa-map-marker" style="width: 26px;text-align: center;"></span>';
            html += this.getProspectAddress(prospect);
            html += "</div>";

            html += "</div>";

            return html;
        },

        removeSelectedMarker() {
            if (hcCurrentUserDailyDirectionMarker) {
                hcCurrentUserDailyDirectionMarker.setMap(null);
                hcCurrentUserDailyDirectionMarker = null;
                this.marker = null;
            }
        },

        // emit send prospect sms

        prospectSms() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-sms");
        },

        // emit call prospect

        prospectInteractions() {
            store.commit(SET_INTERACTION_PROSPECT, this.prospect);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
        },

        // emit prospect files

        prospectFiles() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-files");
        },

        // emit prospect messages

        prospectMessages() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-messages");
        },

        // emit prospect events

        prospectEvents() {
            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(90);

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, {
                prospect: this.prospect,
                user: this.$store.state.auth.user,
                location: this.getProspectAddress(this.prospect),
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-events");
        },

        /**
         * Retrieve prospect first label
         * in a category
         * @param {*} prospect
         * @param {*} categoryId
         */
        getProspectFirstLabel(prospect, categoryId) {
            if (!categoryId) {
                return null;
            }

            const label = prospect.labels
                ? prospect.labels.find(
                      (label) => label.category_id == categoryId
                  )
                : undefined;

            if (label === undefined) {
                return null;
            }

            return label;
        },

        /**
         * Update list of prospects map markers
         */
        updateProspectsMapMarkers() {
            hcMapUserDailyDirectionsProspectsMapMarkers.forEach((marker) => {
                marker.setMap(null);
            });

            hcMapUserDailyDirectionsProspectsMapMarkers = this.mapProspects.map(
                (prospect) => {
                    const label = this.getProspectFirstLabel(
                        prospect,
                        this.mapColorByLabel
                    );

                    return this.createProspectMarker(
                        prospect,
                        label ? label.bgcolor : null,
                        label ? label.color : null
                    );
                }
            );
        },

        /**
         * Create custom marker for prospect
         *
         * @param {*} prospect
         * @param {*} bgcolor
         * @param {*} color
         */
        createProspectMarker(prospect, bgcolor, color) {
            // marker
            const marker = new google.maps.Marker({
                // position
                position: {
                    lat: prospect.latitude,
                    lng: prospect.longitude,
                },
                // Give a higher z-index
                // for southest markers
                zIndex: Math.round(prospect.latitude * -100000) << 5,
                // Generate a colored icon
                icon: {
                    url: this.getProspectColoredMarker(
                        bgcolor ? bgcolor : "#EA5D5D",
                        color ? color : "#00000033"
                    ),
                },
                // Map
                map: this.map,
            });

            google.maps.event.addListener(marker, "click", () => {
                this.initSelectedMarker();
                hcMapUserDailyDirectionsInfoWindow.setContent(
                    this.getProspectMapInfoWindowContent(prospect)
                );
                hcMapUserDailyDirectionsInfoWindow.open(this.map, marker);
                this.selectedProspect = prospect;
                this.selectedDirection = null;
                this.marker = marker;
            });

            return marker;
        },

        /**
         * get map icon corresponding
         * to some color
         * @param {*} color
         */
        getProspectColoredMarker(bgcolor, color) {
            // Use cache
            // so we don't need
            // to regenerate
            // generated icon for some
            // bgcolor and color
            const key = bgcolor + "-" + color;

            if (this.generatedMarkerTemplates[key] == undefined) {
                this.generatedMarkerTemplates[key] =
                    "data:image/svg+xml;charset=UTF-8;base64," +
                    // Convert svg
                    // to base64 image
                    btoa(
                        this.prospectIconTemplate
                            .replace("{bgcolor}", bgcolor)
                            .replace("{color}", color)
                    );
            }

            return this.generatedMarkerTemplates[key];
        },

        /**
         *
         */
        async checkDefaultMapColorByCategory() {
            try {
                const category = await store.dispatch(GET_SETTING, "map.color");
                if (category) {
                    store.commit(SET_MAP_COLOR_BY_LABEL, category);
                }
            } finally {
            }
        },

        /**
         * Set map bound
         */
        updateMapBound() {
            // Using the circle bound
            if (
                hcMapUserDailyDirectionsProspectsMapCircle &&
                hcMapUserDailyDirectionsProspectsMapCircle.getMap()
            ) {
                const bounds = new google.maps.LatLngBounds();
                bounds.extend(
                    hcMapUserDailyDirectionsProspectsMapCircle
                        .getBounds()
                        .getNorthEast()
                );
                bounds.extend(
                    hcMapUserDailyDirectionsProspectsMapCircle
                        .getBounds()
                        .getSouthWest()
                );
                this.map.fitBounds(bounds, 10);
            }
        },
    },

    watch: {
        mapProspects() {
            this.updateProspectsMapMarkers();
            this.updateMapBound();
        },

        dailyDirections(newValue, oldValue) {
            const added = newValue.filter(
                (d) =>
                    !oldValue.find(
                        (d2) =>
                            d.user_id == d2.user_id &&
                            d.direction_at == d2.direction_at
                    )
            );

            added.forEach((d) => {
                this.addDirection(d);
            });

            const removed = oldValue.filter(
                (d) =>
                    !newValue.find(
                        (d2) =>
                            d.user_id == d2.user_id &&
                            d.direction_at == d2.direction_at
                    )
            );

            removed.forEach((d) => {
                this.removeDirection(d.user_id, d.direction_at);
            });
        },

        async prospectKeyword(newValue, oldValue) {
            if (this.prospectKeyword.length < 2) {
                this.prospects = [];
                return;
            }

            if (this.prospectCancelTokenSource) {
                this.prospectCancelTokenSource.cancel();
            }

            this.prospectCancelTokenSource = axios.CancelToken.source();

            this.fetchingProspects = true;
            const filters = {
                query: this.prospectKeyword,
                validAddress: 1,
            };

            try {
                const { data } = await ProspectService.get(this.project.slug, {
                    params: {
                        filters: JSON.stringify(filters),
                        fields: "id,first_name,last_name,phone_number,mobile_phone_number,latitude,longitude,street,street_bis,postal_code,city,state,county,country",
                        page: 1,
                        count: 30,
                    },
                    cancelToken: this.prospectCancelTokenSource.token,
                });
                this.prospects = data.data;
            } finally {
                this.prospectCancelTokenSource = null;
                this.fetchingProspects = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "dailyDirections", "mapColorByLabel"]),

        prospect: function () {
            if (this.selectedProspect) {
                return this.selectedProspect;
            }

            if (this.event && this.event.prospect) {
                return this.event.prospect;
            }

            return null;
        },

        hcMapUserDailyDirections() {
            return hcMapUserDailyDirections;
        },
    },
};
</script>
