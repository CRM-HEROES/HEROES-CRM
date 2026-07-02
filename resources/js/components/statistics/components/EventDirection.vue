<template>
    <stat-chart
        :title="chart.name"
        :stat-chart="chart"
        class="stat-chart-bloc-events-direction"
    >
        <div ref="map" style="width: 100%; height: 100%"></div>
        <loading :loading="showingChart || fetchingDirection" />
    </stat-chart>
</template>

<style>
.stat-chart-bloc-events-direction .stat-chart-bloc-body {
    padding-bottom: 0 !important;
}

.gm-style .gm-style-iw-c {
    border-radius: 3px !important;
    box-shadow: 0 3px 4px -3px #a59d8a !important;
    padding: 0 !important;
    padding-top: 0 !important;
}

.gm-style-iw-d {
    overflow: auto !important;
}

.gm-style-iw-chr {
    position: absolute;
    z-index: 1;
    width: 100%;
    height: 30px;
}

.gm-style-iw-chr > button {
    width: 30px !important;
    height: 30px !important;
}

.gm-style-iw-chr > button > span {
    margin: 3px !important;
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
    padding-top: 30px;
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
</style>

<script>
import GoogleMapInit from "@/utils/google-map";
import EventService from "@/apis/event";
import { mapGetters } from "vuex";
import store from "@/store";

import StatChart from "./StatChart.vue";

import { FETCH_EVENT_USER_DAILY_DIRECTION } from "@/actions/project/event";
import { SHOW_CHART } from "@/actions/project/stat/chart";

export default {
    components: {
        StatChart,
    },

    props: {
        /**
         * Chart option
         */
        chart: {
            type: Object,
        },
    },

    created() {
        this.mapUserDailyDirections = {};
        this.mapUserDailyDirectionsInfoWindow = null;
        this.mapIconTemplate =
            '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="40px" viewBox="0 0 24 40" style="enable-background:new 0 0 24 40;" xml:space="preserve"><path style="fill:{{ color }};stroke:#FFFFFFAA;stroke-width:1;" d="M22.2,17.9L22.2,17.9c0.8-1.6,1.2-3.3,1.2-5.2c0-6.3-5.1-11.5-11.5-11.5S0.6,6.4,0.6,12.7 c0,1.9,0.5,3.6,1.2,5.2l0,0c2.1,4.3,5.6,8,7.6,12.3c1.5,3.3,2.2,7.2,2.5,9l0,0c0,0.1,0.1,0.1,0.1,0.1c0.1,0,0.1-0.1,0.1-0.1l0,0 c0.3-1.7,1-5.7,2.5-9C16.6,26,20.1,22.2,22.2,17.9z"/><text transform="matrix(1 0 0 1 8.8906 17.9366)" style="fill:#FFFFFF;font-size:12px;font-weight:bold;text-align:center;">{{ index }}</text><circle style="fill:{{ event-color }};stroke:#FFFFFF;stroke-width:1;" cx="19.1" cy="4.9" r="4.6"/></svg>';

        this.prospectIconTemplate =
            '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 26 37" width="26px" height="37px" style="enable-background:new 0 0 26 37;" xml:space="preserve"><path style="fill:{bgcolor}" d="M26,13c0-7.2-5.8-13-13-13S0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0c2.8,3,5.8,6.1,7.3,9.2s1,5.9,2.4,5.9 s0.9-2.8,2.4-5.9s4.5-6.2,7.3-9.2l0,0C24.7,19.4,26,16.3,26,13z"/><path style="fill:#00000015" d="M13,1c6.6,0,12,5.4,12,12c0,2.9-1.1,5.8-3.1,8l-0.4,0.4c-0.2,0.3-0.5,0.5-0.7,0.8c-2.5,2.6-5,5.4-6.3,8.2 c-0.7,1.5-1,2.9-1.2,3.9c-0.1,0.3-0.2,0.8-0.3,1.1c-0.1-0.3-0.2-0.7-0.3-1.1c-0.2-1-0.5-2.4-1.2-3.9c-1.3-2.8-3.9-5.6-6.3-8.2 C5,22,4.9,21.9,4.7,21.7L4.1,21c-2-2.2-3.1-5.1-3.1-8C1,6.4,6.4,1,13,1 M13,0C5.8,0,0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0 c2.8,3,5.8,6.1,7.3,9.2c1.5,3.1,1,5.9,2.4,5.9s0.9-2.8,2.4-5.9c1.5-3.1,4.5-6.2,7.3-9.2l0,0c2.1-2.3,3.3-5.3,3.3-8.7 C26,5.8,20.2,0,13,0L13,0z"/><circle style="opacity:0.3" cx="13" cy="13" r="5"/></svg>';
    },

    mounted() {
        this.initMap().then(() => this.fetchDirection());
    },

    data() {
        return {
            fetchingDirection: false,
            showingChart: false,
            date: dateToString(new Date()).substring(0, 10),
        };
    },

    methods: {
        async showChart() {
            this.showingChart = true;

            const date = new Date(this.date);

            // Get the first day of the month
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);

            // Get the last day of the month
            const lastDay = new Date(
                date.getFullYear(),
                date.getMonth() + 1,
                0
            );

            try {
                await store.dispatch(SHOW_CHART, {
                    slug: this.chart.id,
                    project: this.chart.project.slug,
                    params: {
                        params: {
                            from: dateToString(firstDay).substring(0, 10),
                            to: dateToString(lastDay).substring(0, 10),
                        },
                    },
                });
            } finally {
                this.showingChart = false;
            }
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
                zoom: 18,
                disableDefaultUI: true,
            });

            this.mapUserDailyDirectionsInfoWindow = new google.maps.InfoWindow({
                minWidth: 300,
            });
        },

        /**
         *
         */
        async fetchDirection() {
            this.fetchingDirection = true;

            try {
                const data = await store.dispatch(
                    FETCH_EVENT_USER_DAILY_DIRECTION,
                    {
                        user: this.user,
                        direction_at: this.date,
                    }
                );

                if (data) {
                    data.color = "#ea5d5d";
                    this.addDirection(data);
                }
            } finally {
                this.fetchingDirection = false;
            }
        },

        /**
         * Fetch events
         */
        async addDirection(dir) {
            // await this.initMap();

            if (!dir.direction) {
                return;
            }

            const direction = dir.direction;
            if (direction.status != "OK") {
                return;
            }

            let key = dir.user_id + "-" + dir.direction_at;

            if (!this.mapUserDailyDirections[key]) {
                let markerColor = dir.color;

                this.mapUserDailyDirections[key] = {
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

                this.mapUserDailyDirections[key].renderer.setOptions({
                    polylineOptions: {
                        strokeColor: markerColor + "77",
                    },
                });
            }

            const events = dir.events;
            let eventResponse = await EventService.get({
                params: {
                    sort_by: "started_at",
                    filters: JSON.stringify({
                        ids: events,
                    }),
                },
            });

            this.mapUserDailyDirections[key].events = eventResponse.data;

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

                this.mapUserDailyDirections[key].direction = direction;

                var legs = direction.routes[0].legs;

                this.mapUserDailyDirections[key].events[0].distance = "";
                this.mapUserDailyDirections[key].events[0].duration = "";

                for (var i = 0; i < legs.length; i++) {
                    var leg = legs[i];
                    var distance = leg.distance.text;
                    var duration = leg.duration.text
                        .replace(/\s+heures?/g, "h")
                        .replace(/\s+minutes?/g, "m");

                    this.mapUserDailyDirections[key].events[i + 1].distance =
                        distance;
                    this.mapUserDailyDirections[key].events[i + 1].duration =
                        duration;
                }

                this.mapUserDailyDirections[key].markers = this.getMarkers(
                    this.mapUserDailyDirections[key]
                );

                this.mapUserDailyDirections[key].renderer.setDirections(
                    this.mapUserDailyDirections[key].direction
                );
            } else {
                this.mapUserDailyDirections[key].direction = direction;
                let event = this.mapUserDailyDirections[key].events[0];

                let marker = this.createEventProspectMarker(
                    this.mapUserDailyDirections[key],
                    event,
                    1,
                    this.mapUserDailyDirections[key].markerColor,
                    event.calendar.bgcolor,
                    direction.candidates[0].geometry.location,
                    this.getInfoWindowContent(event)
                );

                this.mapUserDailyDirections[key].markers = [marker];
                this.map.setCenter(direction.candidates[0].geometry.location);
            }
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
            return (
                "/project/" + prospect.project.slug + "/prospect/" + prospect.id
            );
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
                this.mapUserDailyDirectionsInfoWindow.setContent(html);
                this.mapUserDailyDirectionsInfoWindow.open(this.map, marker);
            });

            return marker;
        },
    },

    watch: {
        date() {
            this.showChart();
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
    },
};
</script>
