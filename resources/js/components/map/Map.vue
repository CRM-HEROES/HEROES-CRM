<template>
    <div id="hc-map">
        <form id="hc-map-options-wrapper" @submit.prevent="search">
            <div id="hc-map-options">
                <div class="hc-map-options-position">
                    <icon class="fa fa-map-marker" :size="30" />
                    <google-map-input
                        @changed="
                            (addresses) => {
                                latLng =
                                    addresses.latitude +
                                    ',' +
                                    addresses.longitude;
                                getProspects();
                            }
                        "
                    />
                </div>
                <div class="hc-map-options-position">
                    <icon class="fa fa-circle-notch" :size="30" />
                    <input
                        v-model.lazy="ray"
                        @change="getProspects"
                        :disabled="!latLng"
                        :placeholder="'R km'"
                        style="width: 40px"
                    />
                </div>
                <div id="hc-map-options-filters">
                    <icon
                        tag="a"
                        class="fa fa-filter icon-orange"
                        :size="40"
                        style="min-width: 40px"
                        v-tuto="{
                            key: 'project.map.header.filter',
                            name: $t('tutorial.project_map_header_filter.name'),
                            body: $t(
                                'tutorial.project_map_header_filter.body.0'
                            ),
                        }"
                        @click.prevent="filterAll"
                    />
                </div>
                <div id="hc-map-options-filters">
                    <icon
                        tag="a"
                        class="fa fa-calendar icon-purple"
                        :size="40"
                        style="min-width: 40px"
                        @click.prevent="filterEvents"
                    />
                </div>
                <icon
                    tag="a"
                    class="fa fa-palette icon-garnet"
                    :size="40"
                    style="min-width: 40px"
                    v-tuto="{
                        key: 'project.map.header.color',
                        name: $t('tutorial.project_map_header_color.name'),
                        body:
                            $t('tutorial.project_map_header_color.body.0') +
                            '<br><img style=&quot;width: 100%;margin-top: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/map.color.gif&quot; />',
                    }"
                    @click.prevent.stop="colorByLabel"
                />
                <icon
                    tag="a"
                    class="fa fa-car icon-blue"
                    :size="40"
                    style="min-width: 40px"
                    v-tuto="{
                        key: 'project.map.header.user-position',
                        name: $t(
                            'tutorial.project_map_header_user_position.name'
                        ),
                        body: $t(
                            'tutorial.project_map_header_user_position.body.0'
                        ),
                    }"
                    @click.prevent.stop="addUserPosition"
                />
                <icon
                    tag="a"
                    class="fa fa-upload icon-green"
                    :size="40"
                    style="min-width: 40px"
                    v-tuto="{
                        key: 'project.map.header.export',
                        name: $t('tutorial.project_map_header_export.name'),
                        body:
                            '<div style=&quot;display: flex; flex-direction: row;align-items: start;gap: 5px;&quot;><img style=&quot;border-radius: 5px;width: 60px;&quot; src=&quot;/images/tutorial/prospects.table.imports.png&quot; /><div style=&quot;flex: 1&quot;>' +
                            $t('tutorial.project_map_header_export.body.0') +
                            '</div></div>',
                    }"
                    @click.prevent="exportProspects"
                />
                <icon
                    tag="a"
                    class="fa fa-star icon-orange"
                    :size="40"
                    style="min-width: 40px"
                    @click.prevent.stop="showMenus"
                />
                <icon
                    tag="a"
                    class="fa fa-comment icon-purple"
                    :size="40"
                    style="min-width: 40px"
                    @click.prevent.stop="bulkSms"
                />
                <icon
                    tag="a"
                    class="fa fa-envelope icon-garnet"
                    :size="40"
                    style="min-width: 40px"
                    @click.prevent.stop="bulkMessage"
                />
                <icon
                    tag="a"
                    class="fa fa-cog"
                    :size="40"
                    style="min-width: 40px"
                    v-tuto="{
                        key: 'project.map.header.prospect-without-lat-lng',
                        name: $t(
                            'tutorial.project_map_header_prospect_without_lat_lng.name'
                        ),
                        body: $t(
                            'tutorial.project_map_header_prospect_without_lat_lng.body.0'
                        ),
                    }"
                    @click.prevent="searchProspectsWithoutLatLng"
                />
            </div>
        </form>
        <map-prospect @search="searchFromProspect" />
        <div
            id="hc-map-view"
            v-tuto="{
                key: 'project.map',
                name: $t('tutorial.project_map.name'),
                body:
                    $t('tutorial.project_map.body.0') +
                    '<br>' +
                    $t('tutorial.project_map.body.1') +
                    '<br><img style=&quot;width: 100%;margin: 10px 0;border-radius: 5px;&quot; src=&quot;/images/tutorial/header.map.gif&quot; /><br>' +
                    $t('tutorial.project_map.body.2') +
                    '<br>' +
                    $t('tutorial.project_map.body.3'),
            }"
            ref="map"
        ></div>
    </div>

    <color-slide />
    <prospects-without-lat-lng-slide />
    <vehicles-slide />
    <menu-modal />
    <menu-slide />
    <event-filters-slide />
</template>

<style>
#hc-map {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: row;
}

#hc-map-view {
    width: 100%;
    height: 100%;
}

#hc-map-view .gmnoprint {
    bottom: 0;
    top: unset !important;
}

#hc-map-options-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    padding: 10px;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    z-index: 1;
    pointer-events: none;
}

#hc-map-options {
    background-color: white;
    border-radius: 3px;
    overflow: auto;
    display: flex;
    flex-direction: row;
    height: 40px;
    box-shadow: 0 2px 3px #0003;
    pointer-events: all;
}

#hc-map-options > * {
    border-right: 1px solid #eeeeee;
}

#hc-map-options > *:last-child {
    border-right: none;
}

.hc-map-options-position {
    display: flex;
    flex-direction: row;
    align-items: center;
}

.hc-map-options-position > input {
    border: none;
    max-width: 100px;
    font-size: 12px;
}

#hc-map-options-filters {
    display: flex;
    flex-direction: row;
    align-items: center;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import GoogleMapInit from "@/utils/google-map";
// import EventService from "@/apis/project/event";
import { API_URL } from "@/apis/common";

import {
    FETCH_PROSPECTS,
    SET_PROSPECT,
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    SET_PROSPECTS_PAGE,
    SET_PROSPECTS_COUNT,
    SET_PROSPECTS_FIELDS,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";

import {
    SET_EVENT,
    SET_EVENTS_COUNT,
    SET_EVENT_PARAMS,
    SET_EVENTS_SORT_ORDER,
    SET_EVENTS_SORT_BY,
    FETCH_EVENTS,
} from "@/actions/project/event";

import {
    SET_MAP_PROSPECT,
    SET_MAP_COLOR_BY_LABEL,
} from "@/actions/project/map";
import { OPEN_SLIDE, OPEN_SUB_SLIDE } from "@/actions/slide";

import { GET_SETTING } from "@/actions/project/setting";

import { FETCH_EVENT_USER_DAILY_DIRECTION } from "@/actions/project/event";

import ProspectsWithoutLatLngSlide from "./slides/prospects-without-lat-lng/Slide.vue";
import VehiclesSlide from "./slides/vehicles/Slide.vue";
import ColorSlide from "./slides/color/Slide.vue";
import MapProspect from "./prospect/Prospect.vue";
import MenuModal from "./modals/menu/Modal.vue";
import MenuSlide from "./slides/menu/Slide.vue";
import EventFiltersSlide from "@/components/map/filters/Slide.vue";

/**
 * List of prospect markers
 */
let prospectsMapMarkers = [];

/**
 * List of event markers
 */
let eventsMapMarkers = [];

/**
 * Circle defining the search radius
 * with respect to the position (latitude, longitude) indicated
 */
let prospectsMapCircle = null;

/**
 * Polygon search
 */
let prospectsMapPolygon = null;
let prospectsMapPolygonPoints = [];

let vehiclesMapMarkers = [];

let hcMapUserDailyDirections = {};
let hcMapUserDailyDirectionsInfoWindow = {};

export default {
    components: {
        ProspectsWithoutLatLngSlide,
        VehiclesSlide,
        ColorSlide,
        MapProspect,
        MenuSlide,
        MenuModal,
        EventFiltersSlide,
    },

    data() {
        return {
            map: null,

            /**
             * Marker template
             */
            markerTemplate:
                '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 26 37" width="26px" height="37px" style="enable-background:new 0 0 26 37;" xml:space="preserve"><path style="fill:{bgcolor}" d="M26,13c0-7.2-5.8-13-13-13S0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0c2.8,3,5.8,6.1,7.3,9.2s1,5.9,2.4,5.9 s0.9-2.8,2.4-5.9s4.5-6.2,7.3-9.2l0,0C24.7,19.4,26,16.3,26,13z"/><path style="fill:#00000015" d="M13,1c6.6,0,12,5.4,12,12c0,2.9-1.1,5.8-3.1,8l-0.4,0.4c-0.2,0.3-0.5,0.5-0.7,0.8c-2.5,2.6-5,5.4-6.3,8.2 c-0.7,1.5-1,2.9-1.2,3.9c-0.1,0.3-0.2,0.8-0.3,1.1c-0.1-0.3-0.2-0.7-0.3-1.1c-0.2-1-0.5-2.4-1.2-3.9c-1.3-2.8-3.9-5.6-6.3-8.2 C5,22,4.9,21.9,4.7,21.7L4.1,21c-2-2.2-3.1-5.1-3.1-8C1,6.4,6.4,1,13,1 M13,0C5.8,0,0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0 c2.8,3,5.8,6.1,7.3,9.2c1.5,3.1,1,5.9,2.4,5.9s0.9-2.8,2.4-5.9c1.5-3.1,4.5-6.2,7.3-9.2l0,0c2.1-2.3,3.3-5.3,3.3-8.7 C26,5.8,20.2,0,13,0L13,0z"/><circle style="opacity:0.3" cx="13" cy="13" r="5"/></svg>',

            /**
             * Event marker template
             */
            eventMarkerTemplate:
                '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 26 37" width="26px" height="37px" style="enable-background:new 0 0 26 37;" xml:space="preserve"><path style="fill:{bgcolor}" d="M26,13c0-7.2-5.8-13-13-13S0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0c2.8,3,5.8,6.1,7.3,9.2s1,5.9,2.4,5.9 s0.9-2.8,2.4-5.9s4.5-6.2,7.3-9.2l0,0C24.7,19.4,26,16.3,26,13z"/><path style="fill:#00000015" d="M13,1c6.6,0,12,5.4,12,12c0,2.9-1.1,5.8-3.1,8l-0.4,0.4c-0.2,0.3-0.5,0.5-0.7,0.8c-2.5,2.6-5,5.4-6.3,8.2 c-0.7,1.5-1,2.9-1.2,3.9c-0.1,0.3-0.2,0.8-0.3,1.1c-0.1-0.3-0.2-0.7-0.3-1.1c-0.2-1-0.5-2.4-1.2-3.9c-1.3-2.8-3.9-5.6-6.3-8.2 C5,22,4.9,21.9,4.7,21.7L4.1,21c-2-2.2-3.1-5.1-3.1-8C1,6.4,6.4,1,13,1 M13,0C5.8,0,0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0 c2.8,3,5.8,6.1,7.3,9.2c1.5,3.1,1,5.9,2.4,5.9s0.9-2.8,2.4-5.9c1.5-3.1,4.5-6.2,7.3-9.2l0,0c2.1-2.3,3.3-5.3,3.3-8.7 C26,5.8,20.2,0,13,0L13,0z"/><path style="fill:{color}" d="M16.8,16.8H9.2c-0.7,0-1.2-0.5-1.2-1.2v-4.8h10v4.8C18,16.2,17.5,16.8,16.8,16.8z"/><rect style="fill:{color}" x="8" y="8" width="10" height="1"/></svg>',

            /**
             * Event marker template
             */
            todayEventMarkerTemplate:
                '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 39 53" width="39px" height="53px" style="enable-background:new 0 0 39 53;" xml:space="preserve"><path style="fill:{bgcolor}" d="M32.5,29.2c0-7.2-5.8-13-13-13s-13,5.8-13,13c0,3.3,1.3,6.4,3.3,8.7l0,0c2.8,3,5.8,6.1,7.3,9.2s1,5.9,2.4,5.9 s0.9-2.8,2.4-5.9c1.5-3.1,4.5-6.2,7.3-9.2l0,0C31.2,35.6,32.5,32.5,32.5,29.2z"/><path style="fill:#00000015" d="M19.5,17.2c6.6,0,12,5.4,12,12c0,2.9-1.1,5.8-3.1,8L28,37.6c-0.2,0.3-0.5,0.5-0.7,0.8c-2.5,2.6-5,5.4-6.3,8.2 c-0.7,1.5-1,2.9-1.2,3.9c-0.1,0.3-0.2,0.8-0.3,1.1c-0.1-0.3-0.2-0.7-0.3-1.1c-0.2-1-0.5-2.4-1.2-3.9c-1.3-2.8-3.9-5.6-6.3-8.2 c-0.2-0.2-0.3-0.3-0.5-0.5l-0.6-0.7c-2-2.2-3.1-5.1-3.1-8C7.5,22.6,12.9,17.2,19.5,17.2 M19.5,16.2c-7.2,0-13,5.8-13,13 c0,3.3,1.3,6.4,3.3,8.7l0,0c2.8,3,5.8,6.1,7.3,9.2s1,5.9,2.4,5.9s0.9-2.8,2.4-5.9c1.5-3.1,4.5-6.2,7.3-9.2l0,0 c2.1-2.3,3.3-5.3,3.3-8.7C32.5,22,26.7,16.2,19.5,16.2L19.5,16.2z"/><path style="fill:{color}" d="M23.3,33h-7.6c-0.7,0-1.2-0.5-1.2-1.2V27h10v4.8C24.5,32.4,24,33,23.3,33z"/><rect style="fill:{color}" x="14.5" y="24.2" width="10" height="1"/><rect style="fill:{daycolor}" width="39" height="16" rx="5"/><text x="50%" y="9px" dominant-baseline="middle" text-anchor="middle" style="fill:{color};font-family:monospace;font-size:12px;">{text}</text></svg>',

            /**
             * Marker template
             */
            circleMarker:
                "data:image/svg+xml;charset=UTF-8;base64," +
                // Convert svg
                // to base64 image
                btoa(
                    '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 6 6" width="6px" height="6px" style="enable-background:new 0 0 6 6;" xml:space="preserve"><circle style="fill:#73499D" cx="3" cy="3" r="3"/></svg>'
                ),

            vehicleMarker:
                "data:image/svg+xml;charset=UTF-8;base64," +
                // Convert svg
                // to base64 image
                btoa(
                    '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20px" height="17px" viewBox="0 0 20 17"><path fill="{{ color }}" d="M5.3,3.2l-1,2.8h11.5l-1-2.8c-0.2-0.5-0.6-0.8-1.2-0.8H6.5C5.9,2.4,5.5,2.8,5.3,3.2z M1.5,6.3l1.4-3.8C3.4,1,4.9,0,6.5,0 h7.1c1.6,0,3,1,3.5,2.4l1.4,3.8C19.4,6.6,20,7.5,20,8.5V14v1.8c0,0.7-0.6,1.2-1.3,1.2h-1.3c-0.7,0-1.3-0.5-1.3-1.2V14H3.8v1.8 c0,0.7-0.6,1.2-1.3,1.2H1.3C0.6,17,0,16.5,0,15.8V14V8.5C0,7.5,0.6,6.6,1.5,6.3z M5,9.7C5,9,4.4,8.5,3.8,8.5S2.5,9,2.5,9.7 c0,0.7,0.6,1.2,1.3,1.2S5,10.4,5,9.7z M16.3,10.9c0.7,0,1.3-0.5,1.3-1.2c0-0.7-0.6-1.2-1.3-1.2S15,9,15,9.7 C15,10.4,15.6,10.9,16.3,10.9z"/></svg>'
                ),

            /**
             * For optimization,
             * Cache already generated marker templates for each color
             */
            generatedMarkerTemplates: {},
            generatedEventMarkerTemplates: {},
            generatedTodayEventMarkerTemplates: {},

            /**
             * Fetching prospects
             */
            loading: false,

            /**
             * Draw circle using mouse
             */
            circleSearch: {
                from: null,
                to: null,
            },

            /**
             * Draw polygon using mouse
             */
            polygonSearch: {
                polygon: [],
                drawing: false,
            },
        };
    },

    created() {
        store.commit(INIT_PROSPECT_PARAMS, {
            key: "validAddress",
            value: 1,
        });

        this.updateParamsFromUrl();

        // Only prospects with valid address
        store.commit(ADD_PROSPECT_PARAMS, {
            key: "validAddress",
            value: 1,
        });
    },

    mounted() {
        this.initMap();
        this.checkDefaultMapColorByCategory();
    },

    methods: {
        /**
         * Update params from URL
         */
        updateParamsFromUrl() {
            store.commit(INIT_PROSPECT_PARAMS);
            // store.commit(SET_PROSPECTS_SORT_BY, "null");

            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);

            searchParams.forEach(function (value, param) {
                // Pagination
                if (param == "page") {
                    store.commit(SET_PROSPECTS_PAGE, parseInt(value));
                    // Max markers count
                } else if (param == "count") {
                    store.commit(SET_PROSPECTS_COUNT, parseInt(value));
                    // Filters
                } else if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            store.commit(ADD_PROSPECT_PARAMS, {
                                key: key,
                                value: value[key],
                            });
                        }
                    } catch (e) {}
                }
            });
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
         * Get project prospects
         */
        async getProspects(count) {
            // Update browser URL
            this.updateUrl();

            this.loading = true;

            // Fields that we need to get
            let fields = ["id", "latitude", "longitude"];
            if (this.mapColorByLabel) {
                fields.push("category->" + this.mapColorByLabel);
            }

            // Select only latitude, longitude fields
            store.commit(SET_PROSPECTS_FIELDS, fields.join(","));

            store.commit(SET_PROSPECTS_COUNT, count ? count : 1000);

            try {
                // Update prospects markers
                await store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Get project events
         */
        getEvents(count) {
            store.commit(SET_EVENTS_SORT_ORDER, "desc");
            store.commit(SET_EVENTS_SORT_BY, "started_at");
            store.commit(SET_EVENTS_COUNT, count ? count : 1000);

            store.dispatch(FETCH_EVENTS);
        },

        /**
         * Show google map
         */
        async initMap() {
            await GoogleMapInit();

            // MAP
            this.map = new google.maps.Map(this.$refs.map, {
                // Cambrai city as center of the map
                center: new google.maps.LatLng(50.1773063, 3.2337914),
                zoom: 2,
                fullscreenControl: false,
            });

            // Map circle
            prospectsMapCircle = new google.maps.Circle({
                strokeColor: "#7939B8",
                strokeOpacity: 0,
                strokeWeight: 2,
                fillColor: "#7939B8",
                fillOpacity: 0.1,
                clickable: false,
            });

            // Polygon
            prospectsMapPolygon = new google.maps.Polygon({
                strokeColor: "#7939B8",
                strokeOpacity: 0.3,
                strokeWeight: 2,
                fillColor: "#7939B8",
                fillOpacity: 0.1,
                clickable: false,
            });

            // Add a mousedown event listener to the map
            this.map.addListener("mousedown", (event) => {
                if (event.domEvent.ctrlKey) {
                    this.map.setOptions({ draggable: false });
                    this.circleSearch.from = this.circleSearch.to =
                        event.latLng;
                }
            });

            // Add a mousemove event listener to the map
            this.map.addListener("mousemove", (event) => {
                if (!this.circleSearch.from) {
                    return;
                }

                this.circleSearch.to = event.latLng;
                // Calculate the radius of the circle (half the distance between LatLng A and LatLng B)
                const radius =
                    google.maps.geometry.spherical.computeDistanceBetween(
                        this.circleSearch.from,
                        this.circleSearch.to
                    );

                prospectsMapCircle.setMap(this.map);
                prospectsMapCircle.setCenter(this.circleSearch.from);
                prospectsMapCircle.setRadius(radius);
            });

            // Add a mouseup event listener to the map
            this.map.addListener("mouseup", (event) => {
                if (!this.circleSearch.from) {
                    return;
                }

                this.map.setOptions({ draggable: true });

                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: "polygon",
                });

                store.commit(ADD_PROSPECT_PARAMS, {
                    key: "position",
                    value:
                        this.circleSearch.from.lat() +
                        "," +
                        this.circleSearch.from.lng() +
                        "," +
                        google.maps.geometry.spherical.computeDistanceBetween(
                            this.circleSearch.from,
                            this.circleSearch.to
                        ) /
                            1000,
                });

                this.getProspects();

                this.circleSearch.from = this.circleSearch.to = null;
            });

            // Add a click event listener to the map
            this.map.addListener("click", (event) => {
                if (event.domEvent.shiftKey) {
                    if (!this.polygonSearch.drawing) {
                        this.map.setOptions({ draggable: false });
                        this.polygonSearch.polygon = [];
                        prospectsMapPolygon.setMap(null);
                        this.polygonSearch.drawing = true;

                        prospectsMapPolygonPoints.forEach((point) => {
                            point.setMap(null);
                        });

                        prospectsMapPolygonPoints = [];
                    }

                    this.polygonSearch.polygon.push(event.latLng);
                    if (this.polygonSearch.polygon.length > 1) {
                        prospectsMapPolygon.setPaths(
                            this.polygonSearch.polygon
                        );
                        prospectsMapPolygon.setMap(this.map);
                    }

                    prospectsMapPolygonPoints.push(
                        new google.maps.Marker({
                            position: event.latLng,
                            icon: {
                                url: this.circleMarker,
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(3, 3),
                            },
                            map: this.map,
                        })
                    );
                }
            });

            document.addEventListener("keyup", (e) => {
                if (e.key == "Shift") {
                    this.map.setOptions({ draggable: true });
                    this.polygonSearch.drawing = false;

                    if (this.polygonSearch.polygon.length > 2) {
                        // ["lat1 lng1", "lat2 lng2", "lat3 lng3"]
                        const polygons = this.polygonSearch.polygon.map(
                            (latLng) => latLng.lat() + " " + latLng.lng()
                        );

                        // "lat1 lng1, lat2 lng2, lat3 lng3"
                        let params = polygons.join(", ");
                        // "lat1 lng1, lat2 lng2, lat3 lng3, lat1 lng1"
                        if (polygons[0] != polygons[polygons.length - 1]) {
                            params += ", " + polygons[0];
                        }

                        store.commit(REMOVE_PROSPECT_PARAMS, {
                            key: "position",
                        });

                        store.commit(ADD_PROSPECT_PARAMS, {
                            key: "polygon",
                            value: params,
                        });

                        this.getProspects();
                    }
                }
            });

            hcMapUserDailyDirectionsInfoWindow = new google.maps.InfoWindow({
                minWidth: 300,
            });

            if (this.paramPosition) {
                const [latitude, longitude, ray] =
                    this.paramPosition.split(",");
                this.drawMapCircle(latitude, longitude, ray);
                prospectsMapPolygon.setMap(null);
            } else if (this.paramPolygon) {
                this.polygonSearch.polygon = this.paramPolygon
                    .split(", ")
                    .map((latLng) => {
                        const [lat, lng] = latLng.split(" ");

                        return new google.maps.LatLng(lat, lng);
                    });

                prospectsMapPolygon.setPaths(this.polygonSearch.polygon);
                prospectsMapPolygon.setMap(this.map);
                prospectsMapCircle.setMap(null);
            }

            this.getProspects();

            const date = dateToString(new Date()).substring(0, 10);
            store.commit(SET_EVENT_PARAMS, {
                startedAt: date,
                endedAt: date,
                validAddress: 1,
            });
            this.getEvents();

            setTimeout(() => {
                this.userEventsDailyDirection(this.$store.state.auth.user);
            }, 5000);
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
                        this.markerTemplate
                            .replace("{bgcolor}", bgcolor)
                            .replace("{color}", color)
                    );
            }

            return this.generatedMarkerTemplates[key];
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
                store.commit(SET_MAP_PROSPECT, prospect);
            });

            return marker;
        },

        /**
         * get map icon corresponding
         * to some color
         * @param {*} color
         */
        getEventColoredMarker(bgcolor, color) {
            // Use cache
            // so we don't need
            // to regenerate
            // generated icon for some
            // bgcolor and color
            const key = bgcolor + "-" + color;

            if (this.generatedEventMarkerTemplates[key] == undefined) {
                this.generatedEventMarkerTemplates[key] =
                    "data:image/svg+xml;charset=UTF-8;base64," +
                    // Convert svg
                    // to base64 image
                    btoa(
                        this.eventMarkerTemplate
                            .replace("{bgcolor}", bgcolor)
                            .replace(/\{color\}/g, color)
                    );
            }

            return this.generatedEventMarkerTemplates[key];
        },

        /**
         * get map icon corresponding
         * to some color
         * @param {*} color
         */
        getTodayEventColoredMarker(text, daycolor, bgcolor, color) {
            // Use cache
            // so we don't need
            // to regenerate
            // generated icon for some
            // bgcolor and color
            const key = bgcolor + "-" + color + "-" + daycolor + "-" + text;

            if (this.generatedTodayEventMarkerTemplates[key] == undefined) {
                this.generatedTodayEventMarkerTemplates[key] =
                    "data:image/svg+xml;charset=UTF-8;base64," +
                    // Convert svg
                    // to base64 image
                    btoa(
                        this.todayEventMarkerTemplate
                            .replace(/\{bgcolor\}/g, bgcolor)
                            .replace(/\{color\}/g, color)
                            .replace(/\{daycolor\}/g, daycolor)
                            .replace(/\{text\}/g, text)
                    );
            }

            return this.generatedTodayEventMarkerTemplates[key];
        },

        /**
         * Create custom marker for event
         *
         * @param {*} event
         * @param {*} bgcolor
         * @param {*} color
         */
        createEventMarker(event, bgcolor, color) {
            // marker
            const marker = new google.maps.Marker({
                // position
                position: {
                    lat: event.latitude,
                    lng: event.longitude,
                },
                // Give a higher z-index
                // for southest markers
                zIndex: Math.round(event.latitude * -100000) << 5,
                // Generate a colored icon
                icon: {
                    url: this.getTodayEventColoredMarker(
                        this.isTodayEvent(event)
                            ? event.started_at.substring(11, 16)
                            : this.daysDiff(
                                  new Date(),
                                  new Date(event.started_at)
                              ) + "j",

                        this.isTodayEvent(event)
                            ? bgcolor
                                ? bgcolor
                                : "#EA5D5D"
                            : this.isPastEvent(event)
                            ? "#EA5D5D"
                            : "#489f1f",
                        bgcolor ? bgcolor : "#EA5D5D",
                        color ? color : "#00000033"
                    ),
                },
                // Map
                map: this.map,
            });

            google.maps.event.addListener(marker, "click", () => {
                store.commit(SET_PROSPECT, null);
                store.commit(SET_EVENT, event);
                store.commit(OPEN_SUB_SLIDE, "prospect-manage-events");
            });

            return marker;
        },

        /**
         *
         */
        isPastEvent(event) {
            return (
                event.started_at.substring(0, 10) <
                dateToString(new Date()).substring(0, 10)
            );
        },

        /**
         *
         */
        isTodayEvent(event) {
            return (
                event.started_at.substring(0, 10) ==
                dateToString(new Date()).substring(0, 10)
            );
        },

        daysDiff(first, second) {
            return Math.round((second - first) / (1000 * 60 * 60 * 24));
        },

        /**
         * Create custom marker for vehicle
         *
         * @param {*} vehicle
         */
        createVehicleMarker(vehicle) {
            // marker
            const marker = new google.maps.Marker({
                // position
                position: {
                    lat: vehicle.latitude,
                    lng: vehicle.longitude,
                },
                // Give a higher z-index
                // for southest markers
                zIndex: Math.round(vehicle.latitude * -100000) << 5,
                // Generate an icon
                icon: {
                    url: this.vehicleMarker,
                },
                // Map
                map: this.map,
            });

            return marker;
        },

        /**
         * Draw the circle in which to search
         */
        drawMapCircle(latitude, longitude, ray) {
            prospectsMapCircle.setMap(this.map);
            prospectsMapCircle.setCenter({
                lat: parseFloat(latitude),
                lng: parseFloat(longitude),
            });
            prospectsMapCircle.setRadius(ray * 1000);
        },

        /**
         * Draw the polygon in which to search
         */
        drawPolygon(polygon) {},

        /**
         * Set map bound
         */
        updateMapBound() {
            // Using the circle bound
            if (prospectsMapCircle && prospectsMapCircle.getMap()) {
                const bounds = new google.maps.LatLngBounds();
                bounds.extend(prospectsMapCircle.getBounds().getNorthEast());
                bounds.extend(prospectsMapCircle.getBounds().getSouthWest());
                this.map.fitBounds(bounds, 10);

                // Using min max prospects lat lng bound
            } else if (prospectsMapPolygon && prospectsMapPolygon.getMap()) {
                const bounds = new google.maps.LatLngBounds();
                prospectsMapPolygon.getPath().forEach((element) => {
                    bounds.extend(element);
                });
                this.map.fitBounds(bounds, 10);

                // Using min max prospects lat lng bound
            } else {
                const lats = this.prospects.map(
                    (prospect) => prospect.latitude
                );
                const lngs = this.prospects.map(
                    (prospect) => prospect.longitude
                );

                this.map.fitBounds(
                    {
                        west: Math.min.apply(null, lngs),
                        east: Math.max.apply(null, lngs),
                        north: Math.min.apply(null, lats),
                        south: Math.max.apply(null, lats),
                    },
                    10
                );
            }
        },

        /**
         * Update URL
         */
        updateUrl() {
            let url =
                "?page=" + this.prospectsPage + "&count=" + this.prospectsCount;

            if (this.prospectsParamsUrl) {
                url += "&filters=" + this.prospectsParamsUrl;
            }

            history.pushState(null, null, url);
        },

        /**
         * Update list of prospects map markers
         */
        updateProspectsMapMarkers() {
            prospectsMapMarkers.forEach((marker) => {
                marker.setMap(null);
            });

            prospectsMapMarkers = this.prospects.map((prospect) => {
                const label = this.getProspectFirstLabel(
                    prospect,
                    this.mapColorByLabel
                );

                return this.createProspectMarker(
                    prospect,
                    label ? label.bgcolor : null,
                    label ? label.color : null
                );
            });
        },

        /**
         * Update list of events map markers
         */
        updateEventsMapMarkers() {
            eventsMapMarkers.forEach((marker) => {
                marker.setMap(null);
            });

            if (this.prospectsParamExists("withoutEvents")) {
                eventsMapMarkers = [];
                return;
            }

            eventsMapMarkers = this.events.map((event) => {
                return this.createEventMarker(
                    event,
                    event.calendar ? event.calendar.bgcolor : null,
                    event.calendar ? event.calendar.color : null
                );
            });
        },

        /**
         * Update list of vehicles map markers
         */
        updateVehiclesMapMarkers() {
            vehiclesMapMarkers.forEach((marker) => {
                marker.setMap(null);
            });

            vehiclesMapMarkers = this.mapVehicles.map((vehicle) => {
                return this.createVehicleMarker(vehicle);
            });
        },

        /**
         * Filter
         * See: @/components/prospect/filters/all/Slide.vue
         */
        filterAll() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-all");
        },

        /**
         * Filter
         * See: @/components/event/filters/Slide.vue
         */
        filterEvents() {
            store.commit(OPEN_SLIDE, "events-table-filter-event");
        },

        /**
         * Color map icon by label
         * See: @/components/map/slides/color/Slide.vue"
         */
        colorByLabel() {
            store.commit(OPEN_SLIDE, "map-color-by-label");
        },

        /**
         * Add user position to the map
         */
        addUserPosition() {
            store.commit(OPEN_SLIDE, "map-vehicles");
        },

        /**
         * Filter by labels
         * See: @/components/map/slides/prospects-without-lat-lng/Slide.vue"
         */
        searchProspectsWithoutLatLng() {
            store.commit(OPEN_SLIDE, "map-prospects-without-lat-lng");
        },

        /**
         *
         */
        searchFromProspect(prospect) {
            store.commit(SET_PROSPECT_PARAMS, {
                position: [prospect.latitude, prospect.longitude, 30].join(","),
            });
            this.getProspects();
        },

        /**
         * Download prospects list
         * in a XLSX file
         */
        async exportProspects() {
            let params = this.prospectsParams;
            // We also add selected prospects
            // as filter for the list of prospects
            // do download
            params.ids = this.prospects.map((p) => p.id);

            // Build query URL
            params = JSON.stringify(params);

            // Download prospects list
            location.href =
                `${API_URL}/project/${this.project.slug}/export/create` +
                (params ? "?filters=" + params : "");
        },

        /**
         * Show menus
         */
        async showMenus() {
            store.commit(OPEN_SUB_SLIDE, "map-menus");
        },

        /**
         * Bulk sms
         */
        async bulkSms() {
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.prospects.map((p) => p.id)
            );
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },

        /**
         * Bulk messages
         */
        async bulkMessage() {
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.prospects.map((p) => p.id)
            );
            store.commit(SET_PROSPECT, null);
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         *
         * @param {*} user
         */
        async userEventsDailyDirection(user) {
            const direction_at = dateToString(new Date()).substring(0, 10);

            const data = await store.dispatch(
                FETCH_EVENT_USER_DAILY_DIRECTION,
                {
                    user,
                    direction_at,
                }
            );

            if (data && data.direction) {
                this.addDirection({ ...data, color: "#55AAFF" });
            }
        },

        /**
         * Fetch events
         */
        async addDirection(dir) {
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
                        strokeColor: markerColor,
                    },
                });
            }
            /*
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
*/
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

                /*hcMapUserDailyDirections[key].events[0].distance = "";
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
                );*/

                hcMapUserDailyDirections[key].renderer.setDirections(
                    hcMapUserDailyDirections[key].direction
                );
            } /* else {
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
            }*/
        },

        /**
         * Remove direction
         */
        removeDirection(userId, date) {
            let key = userId + "-" + date;

            if (hcMapUserDailyDirections[key]) {
                hcMapUserDailyDirections[key].renderer.setMap(null);
                /*hcMapUserDailyDirections[key].markers.forEach((marker) => {
                    marker.setMap(null);
                });*/
                delete hcMapUserDailyDirections[key];
                // this.marker = null;

                return true;
            }

            return false;
        },
    },

    watch: {
        /**
         * Update prospects markers in the map
         */
        async prospects(newValue, oldValue) {
            if (this.$route.name == "map") {
                await GoogleMapInit();
                this.updateProspectsMapMarkers();
                this.updateEventsMapMarkers();

                if (
                    newValue &&
                    oldValue &&
                    newValue.map((p) => p.id).join(" ") !=
                        oldValue.map((p) => p.id).join(" ")
                ) {
                    this.updateMapBound();
                }
            }
        },

        /**
         * Update events markers in the map
         */
        async events() {
            if (this.$route.name == "map") {
                await GoogleMapInit();
                this.updateEventsMapMarkers();
            }
        },

        /**
         * Update vehicles markers in the map
         */
        mapVehicles() {
            this.updateVehiclesMapMarkers();
        },

        /**
         * Update map circle
         * when position param is updated
         */
        paramPosition(newValue) {
            // Remove circle
            // from the map
            // if position param is not given
            if (!newValue) {
                if (prospectsMapCircle) {
                    // prospectsMapCircle.setMap(null);
                }
                return;
            }

            // Draw circle map
            const [latitude, longitude] = this.latLng.split(",");
            this.drawMapCircle(latitude, longitude, this.ray);
            prospectsMapPolygon.setMap(null);
            this.updateMapBound();
        },

        /**
         * Update polygon
         * when polygon param is updated
         */
        paramPolygon(newValue) {
            // Remove polygon
            // from the map
            // if polygon param is not given
            if (!newValue) {
                if (prospectsMapPolygon) {
                    prospectsMapPolygon.setMap(null);
                    prospectsMapPolygonPoints.forEach((point) => {
                        point.setMap(null);
                    });
                    prospectsMapPolygonPoints = [];
                }
                return;
            }

            this.polygonSearch.polygon = newValue.split(", ").map((latLng) => {
                const [lat, lng] = latLng.split(" ");

                return new google.maps.LatLng(lat, lng);
            });

            prospectsMapPolygon.setPaths(this.polygonSearch.polygon);
            prospectsMapPolygon.setMap(this.map);
            prospectsMapCircle.setMap(null);
            this.updateMapBound();
        },

        /**
         *
         */
        prospectsParamsUrl() {
            this.updateUrl();
        },

        /**
         *
         */
        mapColorByLabel() {
            this.getProspects();
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "prospects",
            "prospectsParamValue",
            "prospectsParams",
            "prospectsParamsUrl",
            "prospectsParamExists",
            "prospectsPage",
            "prospectsCount",
            "events",
            "mapColorByLabel",
            "mapVehicles",
        ]),

        /**
         * Position filter param
         */
        paramPosition() {
            return this.prospectsParamValue("position");
        },

        /**
         * Polygon filter param
         */
        paramPolygon() {
            return this.prospectsParamValue("polygon");
        },

        /**
         * Get filter
         * Latitude and Longitude
         */
        latLng: {
            get() {
                if (!this.paramPosition) {
                    return null;
                }

                // Extract latitude and longitude
                // from position filter param
                const [latitude, longitude] = this.paramPosition.split(",");
                return latitude + "," + longitude;
            },
            set(value) {
                // Update position param
                store.commit(ADD_PROSPECT_PARAMS, {
                    key: "position",
                    value: value + "," + (this.ray ? this.ray : 30),
                });
            },
        },

        /**
         * Get filter
         * search ray
         */
        ray: {
            get() {
                if (!this.paramPosition) {
                    return null;
                }

                // Extract ray
                // from position filter param
                const [, , ray] = this.paramPosition.split(",");
                return ray;
            },
            set(value) {
                // Remove position param
                if (!value) {
                    store.commit(REMOVE_PROSPECT_PARAMS, {
                        key: "position",
                    });
                    return;
                }

                // Update position param
                store.commit(ADD_PROSPECT_PARAMS, {
                    key: "position",
                    value: this.latLng + "," + value,
                });
            },
        },
    },
};
</script>
