<template>
    <div id="hc-stat">
        <div class="hc-stat-layout hc-stat-v-layout">
            <div class="hc-stat-layout hc-stat-h-layout hc-stat-layout-row">
                <div
                    class="hc-stat-bloc"
                    style="width: 33.333%"
                    v-tuto="{
                        key: 'project.stat.prospect.new',
                        name: 'Stat - Nouveaux prospects',
                        body: 'Nouveaux prospects ajoutés dans votre projet durant les 30 derniers jours',
                    }"
                >
                    <div>
                        <div class="hc-stat-bloc-header">
                            <i
                                class="hc-stat-prospect-bloc-icon fa fa-users"
                            ></i>
                            <span
                                class="hc-stat-prospect-bloc-name"
                                style="font-size: 20px"
                                v-text="$t('stat.blocs.prospect.title')"
                            >
                            </span>
                            <span
                                class="hc-stat-prospect-bloc-type"
                                style="font-size: 15px"
                                v-text="$t('new')"
                            >
                            </span>
                            <span
                                class="hc-stat-prospect-bloc-count"
                                style="font-size: 30px"
                            >
                            </span>
                        </div>
                        <div class="hc-stat-bloc-body">
                            <bar
                                stat-key="prospect.new"
                                :from="from"
                                :to="to"
                                border-color="#FF7A0000"
                                fill-color="#FF7A0099"
                            />
                        </div>
                    </div>
                </div>
                <div
                    v-tuto="{
                        key: 'project.stat.prospect.relation.new',
                        name: 'Stat - Nouveaux RDVs, devis, appels, ...',
                        body: 'Total des nouveaux SMS, RDV, appels, devis, fichiers, messages durant les 30 derniers jours',
                    }"
                    class="hc-stat-layout hc-stat-v-layout"
                    style="width: 44.444%"
                >
                    <div
                        class="hc-stat-layout hc-stat-h-layout"
                        style="height: 50%"
                    >
                        <prospect-relation-line
                            style="width: 33.333%"
                            icon="fa fa-comment"
                            :stats="smsStats"
                            border-color="#1DA2FFCC"
                            fill-color="#1DA2FF22"
                        />

                        <prospect-relation-line
                            style="width: 33.333%"
                            icon="fa fa-calendar"
                            :stats="eventStats"
                            border-color="#0CB500CC"
                            fill-color="#0CB50022"
                        />

                        <prospect-relation-line
                            style="width: 33.333%"
                            icon="fa fa-phone"
                            :stats="interactionStats"
                            border-color="#FF4400CC"
                            fill-color="#FF440022"
                        />
                    </div>

                    <div
                        class="hc-stat-layout hc-stat-h-layout"
                        style="height: 50%"
                    >
                        <prospect-relation-line
                            style="width: 33.333%"
                            icon="fa fa-shopping-cart"
                            :stats="orderStats"
                            border-color="#5900FFCC"
                            fill-color="#5900FF22"
                        />

                        <prospect-relation-line
                            style="width: 33.333%"
                            icon="fa fa-file"
                            :stats="fileStats"
                            border-color="#893D09CC"
                            fill-color="#893D0922"
                        />

                        <prospect-relation-line
                            style="width: 33.333%"
                            icon="fa fa-envelope"
                            :stats="messageStats"
                            border-color="#FF7A00CC"
                            fill-color="#FF7A0022"
                        />
                    </div>
                </div>

                <prospect-relation-pie
                    v-tuto="{
                        key: 'project.stat.prospect.relation.pie',
                        name: 'Stat - Répartition RDVs, devis, appels, ...',
                        body: 'Voir en detail la répartition des SMS, RDV, appels, devis, fichiers et messages',
                    }"
                    id="hc-stat-prospect-relations-pie"
                />
            </div>

            <div
                class="hc-stat-layout hc-stat-h-layout hc-stat-layout-row"
                style="height: 360px"
            >
                <div class="hc-stat-bloc" style="width: 25%">
                    <div>
                        <div
                            class="hc-stat-bloc-header"
                            style="padding-bottom: 20px"
                        >
                            <div class="hc-stat-bloc-selector">
                                <span v-text="'Durée des appels en s'"> </span>
                            </div>
                            <span class="hc-stat-prospect-bloc-type"> </span>
                            <input
                                type="date"
                                v-model="interactionsDurationsFromDate"
                                class="hc-stat-bloc-calendar"
                            />
                            <input
                                type="date"
                                v-model="interactionsDurationsToDate"
                                class="hc-stat-bloc-calendar"
                            />
                        </div>
                        <div class="hc-stat-bloc-body">
                            <chart
                                stat-key="prospect.interaction.duration.new"
                                :from="interactionsDurationsFromDate"
                                :to="interactionsDurationsToDate"
                                border-color="#ff6384CC"
                                fill-color="#ff638422"
                            />
                        </div>
                    </div>
                </div>
                <calendar
                    style="width: 25%"
                    v-tuto="{
                        key: 'project.stat.prospect.relation.new.calendar',
                        name: 'Stat - Calendrier - Répartition RDVs, devis, appels, ...',
                        body: 'Voir dans le calendrier la répartition des nouveaux SMS, RDV, appels, devis, fichiers et messages',
                    }"
                />
                <stat-map
                    style="width: 25%"
                    v-tuto="{
                        key: 'project.stat.prospect.relation.total.map',
                        name: 'Stat - MAP - Répartition RDVs, devis, appels, ...',
                        body: 'MAP de répartition des SMS, RDV, appels, devis, fichiers et messages pour une date donnée',
                    }"
                />
                <div
                    class="hc-stat-bloc"
                    style="width: 25%"
                    v-tuto="{
                        key: 'project.stat.prospect.relation.total.chart',
                        name: 'Stat - Chart - Total des prospects',
                        body: 'Evolution du total des prospects entre 2 dates données',
                    }"
                >
                    <div>
                        <div
                            class="hc-stat-bloc-header"
                            style="padding-bottom: 20px"
                        >
                            <div class="hc-stat-bloc-selector">
                                <select>
                                    <option
                                        v-text="
                                            $t('stat.blocs.total.prospects')
                                        "
                                    ></option>
                                </select>
                            </div>
                            <span class="hc-stat-prospect-bloc-type"> </span>
                            <input type="date" class="hc-stat-bloc-calendar" />
                            <input type="date" class="hc-stat-bloc-calendar" />
                        </div>
                        <div class="hc-stat-bloc-body">
                            <chart
                                stat-key="prospect.total"
                                :from="from"
                                :to="to"
                                border-color="#ff6384CC"
                                fill-color="#ff638422"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="hc-stat-layout hc-stat-h-layout hc-stat-layout-row"
                v-tuto="{
                    key: 'project.stat.prospect.metric',
                    name: 'Stat - Metric',
                    body: 'Suivre quotidiennement le nombre des prospects dans divers filtres dans votre projet',
                }"
            >
                <div
                    class="hc-stat-bloc"
                    style="width: 66.666%"
                    id="hc-stat-prospect-metric-chart"
                >
                    <div>
                        <div
                            class="hc-stat-bloc-header"
                            style="padding-bottom: 20px"
                        >
                            <span
                                class="hc-stat-prospect-bloc-name"
                                style="font-size: 20px"
                                v-text="
                                    $t('stat.blocs.follow_filter_daily.title')
                                "
                            ></span>
                            <span class="hc-stat-prospect-bloc-type"
                                >Nouveau</span
                            >
                            <input
                                type="date"
                                class="hc-stat-bloc-calendar"
                                v-model="from"
                            />
                            <input
                                type="date"
                                class="hc-stat-bloc-calendar"
                                v-model="to"
                            />
                            <icon
                                tag="a"
                                class="fa fa-plus-circle"
                                :size="30"
                                :icon-size="18"
                                @click.prevent="addMetric"
                            />
                        </div>
                        <div
                            class="hc-stat-bloc-body hc-stat-layout hc-stat-h-layout"
                        >
                            <div style="flex: 1; overflow: hidden">
                                <charts
                                    :stats="metricStats"
                                    :from="from"
                                    :to="to"
                                />
                            </div>
                            <div
                                id="hc-stat-choropleth-countries"
                                class="hc-stat-layout hc-stat-v-layout"
                                style="max-width: 150px"
                            >
                                <div
                                    class="hc-stat-choropleth-country"
                                    v-for="metric in metrics"
                                >
                                    <div
                                        :class="[
                                            'hc-stat-choropleth-country-icon',
                                            'fa',
                                            'fa-chart-bar',
                                        ]"
                                        :style="{ color: metric.color }"
                                    ></div>
                                    <div
                                        class="hc-stat-choropleth-country-name"
                                        v-text="metric.name"
                                    ></div>
                                    <div
                                        class="hc-stat-choropleth-country-count"
                                        v-text="13"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="hc-stat-bloc"
                    style="width: 33.333%"
                    id="hc-stat-prospect-metric-bar"
                >
                    <div>
                        <div
                            class="hc-stat-bloc-header"
                            style="padding-bottom: 20px"
                        >
                            <span
                                class="hc-stat-prospect-bloc-name"
                                v-text="
                                    $t(
                                        'stat.blocs.follow_filter_daily.distribution'
                                    )
                                "
                            ></span>
                            <span class="hc-stat-prospect-bloc-type"> </span>
                            <input type="date" class="hc-stat-bloc-calendar" />
                        </div>

                        <div
                            class="hc-stat-bloc-body hc-stat-layout hc-stat-h-layout"
                        >
                            <div style="flex: 1; overflow: hidden">
                                <h-bar />
                            </div>
                            <div
                                id="hc-stat-choropleth-countries"
                                class="hc-stat-layout hc-stat-v-layout"
                                style="max-width: 150px"
                            >
                                <div
                                    class="hc-stat-choropleth-country"
                                    v-for="calendar in calendars"
                                >
                                    <div
                                        :class="[
                                            'hc-stat-choropleth-country-icon',
                                            'fa',
                                            'fa-calendar',
                                        ]"
                                        :style="{ color: calendar.bgcolor }"
                                    ></div>
                                    <div
                                        class="hc-stat-choropleth-country-name"
                                        v-text="calendar.name"
                                    ></div>
                                    <div
                                        class="hc-stat-choropleth-country-count"
                                        v-text="13"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <country-modal />
        <metric-modal />
    </div>
</template>

<style>
#hc-stat {
    width: 100%;
    height: 100%;
    overflow-y: auto;
    padding: 20px;
    justify-content: center;
}

.hc-stat-layout-row {
    height: 280px !important;
}

.hc-stat-layout {
    display: flex;
    gap: 0;
}

.hc-stat-v-layout {
    flex-direction: column;
}

.hc-stat-h-layout {
    flex-direction: row;
}

.hc-stat-bloc {
    padding: 3px;
    position: relative;
}

.hc-stat-bloc > div {
    border: 1px solid #dedede;
    background-color: white;
    width: 100%;
    height: 100%;
    border-radius: 5px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.hc-stat-bloc-header {
    width: 100%;
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: baseline;
}

.hc-stat-prospect-bloc > div {
    padding: 10px 15px 15px 15px;
}

.hc-stat-prospect-bloc-icon {
    font-size: 12px;
}

.hc-stat-prospect-bloc-name {
    font-size: 13px;
    display: inline;
}

.hc-stat-prospect-bloc-type {
    font-size: 12px;
    color: #aaaaaa;
    flex: 1;
    display: inline;
    text-align: right;
    overflow: hidden;
}

.hc-stat-prospect-bloc-count {
    font-size: 20px;
    display: inline;
}

.hc-stat-bloc-body {
    flex: 1;
    height: 100%;
    overflow: hidden;
    position: relative;
}

.hc-stat-bloc-selector {
    position: relative;
}

.hc-stat-bloc-selector > select {
    border: 1px solid #dddddd;
    background: none;
    padding: 3px 20px 3px 5px;
    border-radius: 3px;
    font-size: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    text-indent: 1px;
    max-width: 80px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-stat-bloc-selector:after {
    content: "";
    position: absolute;
    right: 7px;
    top: 10px;
    width: 0;
    height: 0;
    border-radius: 2px;
    border-top: 5px solid #555555;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
}

.hc-stat-bloc-calendar {
    border: 1px solid #dddddd;
    background: none;
    padding: 3px 5px;
    border-radius: 3px;
    font-size: 12px;
}

.hc-stat-bloc-body > canvas {
}

.hc-stat-v-layout > .hc-stat-bloc {
    width: 100%;
}

.hc-stat-h-layout {
    height: 100%;
}

#hc-stat-choropleth-countries {
    padding: 0 10px;
    overflow: auto;
}

.hc-stat-choropleth-country {
    display: flex;
    flex-direction: row;
    gap: 7px;
    align-items: center;
    width: 100%;
    border-radius: 3px;
    cursor: pointer;
    padding: 1px 3px;
}

.hc-stat-choropleth-country:hover {
    background-color: #eeeeee;
}

.hc-stat-choropleth-country-icon {
    font-size: 12px;
    color: #cccccc;
}

.hc-stat-choropleth-country-icon.mapped {
    color: #2f7ebc;
}

.hc-stat-choropleth-country-name {
    font-size: 11px;
    flex: 1;
    padding-right: 20px;
}

.hc-stat-choropleth-country-count {
    font-size: 12px;
    color: #777777;
}

@media (max-width: 767px) {
    .hc-stat-bloc {
        width: 100% !important;
    }

    .hc-stat-v-layout {
        width: 100% !important;
    }

    .hc-stat-h-layout {
        flex-wrap: wrap;
        height: auto !important;
        flex-direction: column;
    }

    .hc-stat-h-layout canvas {
        width: 100% !important;
    }

    #hc-stat-prospect-relations-new {
        flex-direction: row !important;
        height: auto !important;
        width: 100% !important;
    }

    #hc-stat-prospect-relations-new > div {
        height: auto !important;
        width: 50% !important;
    }

    #hc-stat-prospect-relations-new > div > div {
        height: 160px !important;
    }

    .hc-stat-prospect-bloc > div {
        padding: 0px 10px 10px 10px;
    }

    #hc-stat-choropleth-countries {
        max-width: 100% !important;
        margin-top: 15px;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_COUNTRY } from "@/actions/project/stat/map";
import { FETCH_METRICS } from "@/actions/project/stat/metric";

import Chart from "./components/Chart.vue";
import Charts from "./components/Charts.vue";
import Choropleth from "./components/Choropleth.vue";
import Pie from "./components/Pie.vue";
import Calendar from "./components/Calendar.vue";
import Bar from "./components/Bar.vue";
import HBar from "./components/HBar.vue";

import StatMap from "./components/Map.vue";
import ProspectRelationPie from "./components/ProspectRelationPie.vue";
import ProspectRelationLine from "./components/ProspectRelationLine.vue";

import CountryModal from "./modals/country/Modal.vue";
import MetricModal from "./modals/metric/Modal.vue";

export default {
    components: {
        Chart,
        Charts,
        Choropleth,
        Pie,
        Calendar,
        Bar,
        HBar,

        StatMap,
        ProspectRelationPie,
        ProspectRelationLine,

        CountryModal,
        MetricModal,
    },

    data() {
        const from = new Date();
        const to = new Date();

        from.setMonth(from.getMonth() - 1);

        return {
            from: dateToString(from).substring(0, 10),
            to: dateToString(to).substring(0, 10),
            interactionsDurationsFromDate: dateToString(from).substring(0, 10),
            interactionsDurationsToDate: dateToString(to).substring(0, 10),
        };
    },

    created() {
        this.fetchMetrics();
    },

    methods: {
        addMetric() {
            store.commit(OPEN_MODAL, "stat-add-metric");
        },

        fetchMetrics() {
            store.dispatch(FETCH_METRICS);
        },

        mapCountry(country) {
            store.commit(SET_COUNTRY, country.name);
            store.commit(OPEN_MODAL, "stat-map-country");
        },
    },

    computed: {
        ...mapGetters([
            "metrics",
            "calendars",
            "orderStatuses",
            "folders",
            "threads",
        ]),

        smsSources() {
            return [
                {
                    key: "smsbox",
                    name: "SMSBOX",
                },
                {
                    key: "ultramsg",
                    name: "UltraMsg",
                },
                {
                    key: "mtarget",
                    name: "MTarget",
                },
                {
                    key: "telephone",
                    name: this.$t("stat.blocs.sms.phone"),
                },
                {
                    key: "whatsapp",
                    name: "Whatsapp",
                },
                {
                    key: "ringover",
                    name: "Ringover",
                },
            ];
        },

        interactionSources() {
            return [
                {
                    key: "ringover",
                    name: "Ringover",
                },
                {
                    key: "aircall",
                    name: "Aircall",
                },
                {
                    key: "telephone",
                    name: this.$t("stat.blocs.interaction.phone"),
                },
            ];
        },

        eventStats() {
            const key = "prospect.event.new";

            let stats = {
                [key]: this.$t("stat.blocs.event.title"),
            };

            this.calendars.forEach((item) => {
                stats[key + ".calendar." + item.id] = item.name;
            });

            return stats;
        },

        messageStats() {
            const key = "prospect.message.new";

            let stats = {
                [key]: this.$t("stat.blocs.message.title"),
            };

            this.threads.forEach((item) => {
                stats[key + ".thread." + item.id] = item.name;
            });

            return stats;
        },

        fileStats() {
            const key = "prospect.file.new";

            let stats = {
                [key]: this.$t("stat.blocs.file.title"),
            };

            this.folders.forEach((item) => {
                stats[key + ".folder." + item.id] = item.name;
            });

            return stats;
        },

        orderStats() {
            const key = "prospect.order.new";

            let stats = {
                [key]: this.$t("stat.blocs.order.title"),
            };

            this.orderStatuses.forEach((item) => {
                stats[key + ".status." + item.id] = item.name;
            });

            return stats;
        },

        smsStats() {
            const key = "prospect.sms.new";

            let stats = {
                [key]: this.$t("stat.blocs.sms.title"),
            };

            this.smsSources.forEach((item) => {
                stats[key + ".source." + item.key] = item.name;
            });

            return stats;
        },

        interactionStats() {
            const key = "prospect.interaction.new";

            let stats = {
                [key]: this.$t("stat.blocs.interaction.title"),
            };

            this.interactionSources.forEach((item) => {
                stats[key + ".source." + item.key] = item.name;
            });

            return stats;
        },

        metricStats() {
            return this.metrics.map((metric) => ({
                key: "prospect.total.metric." + metric.id,
                borderColor: metric.color + "CC",
                fillColor: metric.color + "22",
            }));
        },
    },
};
</script>
