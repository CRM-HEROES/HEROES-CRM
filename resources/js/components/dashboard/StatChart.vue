<template>
    <prospects-labels
        v-if="statChart.type == 'total-prospects-per-label'"
        :chart="statChart"
        @label-clicked="labelClicked"
    />
    <prospects-progression
        v-else-if="
            statChart.type == 'new-prospects-per-label-per-day' ||
            statChart.type == 'total-prospects-per-label-per-day' ||
            statChart.type == 'new-commissions-per-user-per-day' ||
            statChart.type == 'total-commissions-per-user-per-day' ||
            statChart.type == 'new-sms-per-day' ||
            statChart.type == 'total-sms-per-day' ||
            statChart.type == 'new-events-per-day' ||
            statChart.type == 'total-events-per-day' ||
            statChart.type == 'new-orders-per-day' ||
            statChart.type == 'new-orders-price-per-day' ||
            statChart.type == 'total-orders-per-day' ||
            statChart.type == 'total-orders-price-per-day' ||
            statChart.type == 'new-messages-per-day' ||
            statChart.type == 'total-messages-per-day' ||
            statChart.type == 'new-files-per-day' ||
            statChart.type == 'total-files-per-day' ||
            statChart.type == 'new-interactions-per-day' ||
            statChart.type == 'total-interactions-per-day'
        "
        :chart="statChart"
        @label-clicked="labelClicked"
    />
    <prospects-chropleth
        v-else-if="statChart.type == 'total-prospects-per-country'"
        :chart="statChart"
        :url="
            statChart.options && statChart.options.geojson
                ? statChart.options.geojson
                : 'json/world.geojson'
        "
        :projection="
            statChart.options && statChart.options.projection
                ? statChart.options.projection
                : 'equalEarth'
        "
        :scale="
            statChart.options && statChart.options.scale
                ? statChart.options.scale
                : 1
        "
        :offset-x="
            statChart.options && statChart.options.offsetX
                ? statChart.options.offsetX
                : 0
        "
        :offset-y="
            statChart.options && statChart.options.offsetY
                ? statChart.options.offsetY
                : 0
        "
    />
    <prospects-events
        v-else-if="statChart.type == 'events-list'"
        :chart="statChart"
    />
    <event-direction
        v-else-if="statChart.type == 'events-direction'"
        :chart="statChart"
    />
    <prospects-events
        v-else-if="statChart.type == 'events-list'"
        :chart="statChart"
    />
</template>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { FETCH_CHARTS } from "@/actions/project/stat/chart";

import ProspectsLabels from "@/components/statistics/components/ProspectsLabels.vue";
import ProspectsChropleth from "@/components/statistics/components/ProspectsChropleth.vue";
import ProspectsProgression from "@/components/statistics/components/ProspectsProgression.vue";
import ProspectsEvents from "@/components/statistics/components/ProspectsEvents.vue";
import EventDirection from "@/components/statistics/components/EventDirection.vue";
import statChart from "../../store/stat-chart";

export default {
    components: {
        ProspectsLabels,
        ProspectsChropleth,
        ProspectsProgression,
        ProspectsEvents,
        EventDirection,
    },

    props: {
        statChart: Object,
    },

    methods: {
        /**
         *
         * @param {*} chart
         * @param {*} label
         */
        labelClicked(chart, label) {
            if (
                chart.type == "total-prospects-per-label" ||
                chart.type == "new-prospects-per-label-per-day" ||
                chart.type == "total-prospects-per-label-per-day"
            ) {
                return this.searchLabel(chart, label);
            }
        },

        /**
         *
         */
        searchLabel(chart, label) {
            this.$router.push({
                name: "prospect",
                params: {
                    project: chart.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        ["withCategory_" + label.category_id]: [label.id],
                    }),
                },
            });
        },
    },
};
</script>
