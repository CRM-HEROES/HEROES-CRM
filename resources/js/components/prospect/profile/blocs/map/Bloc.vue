<template>
    <bloc icon="fa fa-map-marker" :name="$t('prospect.profile.bloc.map')">
        <template #options>
            <icon
                v-if="prospect.latitude && prospect.longitude"
                tag="a"
                class="fa fa-map-marker icon-garnet"
                v-tooltip="$t('prospect.profile.menus.map')"
                :icon-size="16"
                @click.prevent.stop="showMap"
            />
            <icon
                v-if="prospect.latitude && prospect.longitude"
                tag="a"
                v-tooltip="'Google MAP'"
                :icon-size="16"
                :href="googleMapURL"
                target="_blank"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 92.3 132.3"
                >
                    <path
                        fill="#1a73e8"
                        d="M60.2 2.2C55.8.8 51 0 46.1 0 32 0 19.3 6.4 10.8 16.5l21.8 18.3L60.2 2.2z"
                    />
                    <path
                        fill="#ea4335"
                        d="M10.8 16.5C4.1 24.5 0 34.9 0 46.1c0 8.7 1.7 15.7 4.6 22l28-33.3-21.8-18.3z"
                    />
                    <path
                        fill="#4285f4"
                        d="M46.2 28.5c9.8 0 17.7 7.9 17.7 17.7 0 4.3-1.6 8.3-4.2 11.4 0 0 13.9-16.6 27.5-32.7-5.6-10.8-15.3-19-27-22.7L32.6 34.8c3.3-3.8 8.1-6.3 13.6-6.3"
                    />
                    <path
                        fill="#fbbc04"
                        d="M46.2 63.8c-9.8 0-17.7-7.9-17.7-17.7 0-4.3 1.5-8.3 4.1-11.3l-28 33.3c4.8 10.6 12.8 19.2 21 29.9l34.1-40.5c-3.3 3.9-8.1 6.3-13.5 6.3"
                    />
                    <path
                        fill="#34a853"
                        d="M59.1 109.2c15.4-24.1 33.3-35 33.3-63 0-7.7-1.9-14.9-5.2-21.3L25.6 98c2.6 3.4 5.3 7.3 7.9 11.3 9.4 14.5 6.8 23.1 12.8 23.1s3.4-8.7 12.8-23.2"
                    />
                </svg>
            </icon>
            <icon
                v-if="address"
                :size="30"
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
        </template>
        <template #body>
            <div
                style="
                    padding: 10px 10px;
                    float: left;
                    width: 100%;
                    position: relative;
                "
            >
                <google-map
                    style="height: 300px"
                    :options="{
                        center: mapCenter,
                        zoom: 17,
                        mapTypeId: 'satellite',
                        fullscreenControl: false,
                        disableDefaultUI: true,
                    }"
                    @map-loaded="(m) => (map = m)"
                >
                    <google-map-marker
                        v-if="prospect.latitude && prospect.longitude"
                        :options="{
                            position: {
                                lat: prospect.latitude,
                                lng: prospect.longitude,
                            },
                        }"
                    />
                    <google-event-map-marker
                        v-for="e in locatedProspectEvents"
                        :key="e.id"
                        :position="{
                            lat: e.latitude,
                            lng: e.longitude,
                        }"
                        :icon="
                            getEventColoredMarker(
                                e.calendar.bgcolor,
                                e.calendar.color
                            )
                        "
                    />
                </google-map>
                <loading :loading="loading" />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import GoogleMap from "@/components/GoogleMap.vue";
import GoogleMapMarker from "@/components/google/map/Marker.vue";
import GoogleEventMapMarker from "./EventMapMarker.vue";

export default {
    components: {
        Bloc,
        GoogleMap,
        GoogleMapMarker,
        GoogleEventMapMarker,
    },

    created() {
        this.eventMarkerTemplate =
            '<?xml version="1.0" encoding="utf-8"?><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 26 37" width="26px" height="37px" style="enable-background:new 0 0 26 37;" xml:space="preserve"><path style="fill:{bgcolor}" d="M26,13c0-7.2-5.8-13-13-13S0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0c2.8,3,5.8,6.1,7.3,9.2s1,5.9,2.4,5.9 s0.9-2.8,2.4-5.9s4.5-6.2,7.3-9.2l0,0C24.7,19.4,26,16.3,26,13z"/><path style="fill:#00000015" d="M13,1c6.6,0,12,5.4,12,12c0,2.9-1.1,5.8-3.1,8l-0.4,0.4c-0.2,0.3-0.5,0.5-0.7,0.8c-2.5,2.6-5,5.4-6.3,8.2 c-0.7,1.5-1,2.9-1.2,3.9c-0.1,0.3-0.2,0.8-0.3,1.1c-0.1-0.3-0.2-0.7-0.3-1.1c-0.2-1-0.5-2.4-1.2-3.9c-1.3-2.8-3.9-5.6-6.3-8.2 C5,22,4.9,21.9,4.7,21.7L4.1,21c-2-2.2-3.1-5.1-3.1-8C1,6.4,6.4,1,13,1 M13,0C5.8,0,0,5.8,0,13c0,3.3,1.3,6.4,3.3,8.7l0,0 c2.8,3,5.8,6.1,7.3,9.2c1.5,3.1,1,5.9,2.4,5.9s0.9-2.8,2.4-5.9c1.5-3.1,4.5-6.2,7.3-9.2l0,0c2.1-2.3,3.3-5.3,3.3-8.7 C26,5.8,20.2,0,13,0L13,0z"/><path style="fill:{color}" d="M16.8,16.8H9.2c-0.7,0-1.2-0.5-1.2-1.2v-4.8h10v4.8C18,16.2,17.5,16.8,16.8,16.8z"/><rect style="fill:{color}" x="8" y="8" width="10" height="1"/></svg>';
        this.generatedEventMarkerTemplates = {};
    },

    data() {
        return {
            loading: false,
            map: null,
        };
    },

    methods: {
        /**
         *
         */
        showMap() {
            this.$router.push({
                name: "map",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        validAddress: 1,
                        position: [
                            this.prospect.latitude,
                            this.prospect.longitude,
                            30,
                        ].join(","),
                    }),
                },
            });
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
    },

    watch: {
        /*mapBounds(newValue) {
            if (this.map) {
                this.map.fitBound(newValue);
            }
        },

        map(newValue) {
            if (this.mapBounds) {
                this.map.fitBound(this.mapBounds);
            }
        },*/
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectEvents"]),

        mapCenter() {
            const event = this.prospectEvents.find(
                (e) => e.latitude && e.longitude
            );
            if (event) {
                return {
                    lat: event.latitude,
                    lng: event.longitude,
                };
            }

            if (this.prospect.latitude && this.prospect.longitude) {
                return {
                    lat: this.prospect.latitude,
                    lng: this.prospect.longitude,
                };
            }

            return {
                lat: 50.1773063,
                lng: 3.2337914,
            };
        },

        locatedProspectEvents() {
            return this.prospectEvents.filter((e) => e.latitude && e.longitude);
        },

        mapBounds() {
            const lats = [
                this.prospect.latitude,
                ...this.locatedProspectEvents.map((e) => e.latitude),
            ];
            const lngs = [
                this.prospect.longitude,
                ...this.locatedProspectEvents.map((e) => e.longitude),
            ];

            return {
                west: Math.min.apply(null, lngs),
                east: Math.max.apply(null, lngs),
                north: Math.min.apply(null, lats),
                south: Math.max.apply(null, lats),
            };
        },

        address() {
            return [
                this.prospect.street,
                this.prospect.postal_code,
                this.prospect.city,
                this.prospect.country,
            ]
                .filter((a) => a)
                .join(" ");
        },

        googleMapURL: function () {
            return (
                "https://www.google.com/maps/search/?api=1&query=" +
                encodeURI(this.address)
            );
        },

        wazeMapURL() {
            return "https://waze.com/ul?q=" + encodeURI(this.address);
        },
    },
};
</script>
