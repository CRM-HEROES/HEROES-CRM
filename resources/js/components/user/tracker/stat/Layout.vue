<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <item>
                    <div class="hc-item-main-content">
                        <input
                            type="date"
                            v-model="currentDateValue"
                            id="user-log-chart-current-date"
                        />
                    </div>
                </item>

                <div class="hc-flex-1" style="position: relative">
                    <div id="user-logs-chart-container">
                        <div
                            id="user-logs-chart"
                            :class="disableScroll ? 'disable-scroll' : ''"
                            @touchstart="touchdown"
                            @touchend="touchup"
                            @touchmove="touchmove"
                        >
                            <div id="user-logs-chart-content">
                                <div
                                    v-for="date in dates()"
                                    class="user-logs-chart-row"
                                >
                                    <div class="user-logs-chart-row-chart">
                                        <div
                                            v-for="(hit, index) in logs[date]"
                                            :class="
                                                'user-logs-chart-row-chart-row' +
                                                (dateIntervalStart &&
                                                dateIntervalEnd &&
                                                ((getDate(date, index) >=
                                                    dateIntervalStart &&
                                                    getDate(date, index) <=
                                                        dateIntervalEnd) ||
                                                    (getDate(date, index) <=
                                                        dateIntervalStart &&
                                                        getDate(date, index) >=
                                                            dateIntervalEnd))
                                                    ? ' selected'
                                                    : '')
                                            "
                                            :key="date + '-' + index"
                                            @mousedown="
                                                beginDateIntervalSelect(
                                                    date,
                                                    index
                                                )
                                            "
                                            @mouseover="
                                                setDateIntervalSelectEnd(
                                                    date,
                                                    index
                                                )
                                            "
                                            @touchstart="
                                                beginDateIntervalSelect(
                                                    date,
                                                    index
                                                )
                                            "
                                            @touchend="
                                                setDateIntervalSelectEnd(
                                                    date,
                                                    index
                                                )
                                            "
                                            @mouseup="endDateIntervalSelect"
                                        >
                                            <div
                                                :style="
                                                    'height: ' +
                                                    (hit * 100) / maxValue +
                                                    '%;'
                                                "
                                            >
                                                <div
                                                    class="user-logs-chart-row-chart-row-tooltip"
                                                    v-text="
                                                        getHour(index) +
                                                        ' > ' +
                                                        hit
                                                    "
                                                ></div>
                                            </div>
                                        </div>

                                        <div class="user-logs-chart-row-times">
                                            <div
                                                v-for="i in 24"
                                                class="user-logs-chart-row-time-row"
                                            >
                                                <span
                                                    v-if="i % 4 == 1"
                                                    v-text="
                                                        i <= 10
                                                            ? '0' + (i - 1)
                                                            : i - 1
                                                    "
                                                ></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="user-logs-chart-row-date"
                                        v-text="date"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div class="user-logs-chart-values">
                            <div
                                v-for="i in parseInt(maxValue / 25)"
                                :class="
                                    'user-logs-chart-value-row' +
                                    (i % 4 == 0
                                        ? ' user-logs-chart-value-100'
                                        : '')
                                "
                                :style="
                                    'height: ' + (i * 2500) / maxValue + '%;'
                                "
                                :key="i"
                            >
                                <span v-text="i * 25"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <buttons>
                    <button
                        :disabled="!dateIntervalStart || !dateIntervalEnd"
                        @click.prevent="viewConnexions"
                        v-text="$t('auth.session.connections')"
                    ></button>
                </buttons>
            </div>
        </template>
        <template #2>
            <div
                class="hc-flex-column"
                style="height: 100%"
                v-if="dateIntervalStart && dateIntervalEnd"
            >
                <item tag="a" @click.prevent="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            selectedLogs.length +
                            ' connexions entre ' +
                            dateIntervalStart +
                            ' et ' +
                            dateIntervalEnd
                        "
                    ></div>
                </item>

                <div class="hc-flex-1" id="user-logs">
                    <user-log-row
                        v-for="log in selectedLogs"
                        :key="log.id"
                        :log="log"
                    />

                    <loading :loading="fetchingSelectedLogs" />
                </div>
            </div>
        </template>
    </tab-layout>
</template>

<style>
#user-log-chart-panel {
    width: 500px;
}

#user-logs-chart-container {
    width: 100%;
    height: 100%;
    position: absolute;
    left: 0;
    bottom: 0;
    user-select: none;
    border-bottom: 1px solid #eee;
}

#user-logs-chart {
    width: 100%;
    height: 100%;
    overflow: auto;
    position: absolute;
    left: 0;
    bottom: 0px;

    &.disable-scroll {
        overflow: hidden;
    }
}

@media (min-width: 768px) {
    #user-logs-chart {
        bottom: -12px;
    }
}

#user-logs-chart-content {
    white-space: nowrap;
    height: 100%;
    width: auto;
    display: inline-block;
    font-size: 0px;
}

.user-logs-chart-row {
    width: auto;
    height: 100%;
    display: inline-block;
    padding-bottom: 40px;
    border-right: 1px solid #4e13ad22;
}

.user-logs-chart-row-chart {
    width: 480px;
    height: 100%;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid #159eec;
}

.user-logs-chart-row-chart-row {
    width: 5px;
    height: 100%;
    margin-left: 0;
    position: relative;
    display: inline-block;
}

.user-logs-chart-row-chart-row > div {
    width: 3px;
    background: #159eec;
    position: absolute;
    left: 1px;
    bottom: 0;
    pointer-events: none;
    border-radius: 2px 2px 0 0;
    transition: height 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.user-logs-chart-row-chart-row > div > .user-logs-chart-row-chart-row-tooltip {
    position: absolute;
    border-radius: 3px;
    background-color: #000e;
    border: 1px solid #fff;
    color: white;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    visibility: hidden;
    font-size: 12px;
    font-size: 11px;
    padding: 2px 4px;
    z-index: 1;
    font-family: monospace;
}

.user-logs-chart-row-chart-row:hover {
    background: #f5f5f5;
}

.user-logs-chart-row-chart-row:hover > div {
    background: #489f1f;
}

.user-logs-chart-row-chart-row:hover
    > div
    > .user-logs-chart-row-chart-row-tooltip {
    visibility: visible;
}

.user-logs-chart-row-chart-row.selected:before {
    content: "";
    background: #ffdf0055;
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.user-logs-chart-row-times {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
}

.user-logs-chart-row-time-row {
    position: relative;
    width: 20px;
    height: 100%;
    display: inline-block;
    border-right: 1px solid #0001;

    > span {
        position: absolute;
        bottom: 0px;
        left: 2px;
        font-size: 11px;
    }
}

.user-logs-chart-row-date {
    width: 100%;
    height: 40px;
    line-height: 40px;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
}

.user-logs-chart-values {
    position: absolute;
    width: 100%;
    height: calc(100% - 40px);
    top: 0;
    left: 0;
    pointer-events: none;
}

.user-logs-chart-value-row {
    position: absolute;
    width: 100%;
    bottom: 0;
    left: 0;
    border-top: 1px solid #4e13ad11;
    transition: height 0.3s cubic-bezier(0.23, 1, 0.32, 1);

    > span {
        position: absolute;
        top: -20px;
        left: 5px;
        font-size: 11px;
        color: #555;
    }

    &.user-logs-chart-value-100 {
        border-top: 1px solid #4e13ad22;

        > span {
            font-weight: bold;
            font-size: 12px;
            color: #000;
        }
    }
}

#user-logs {
    overflow: auto;
}

#user-log-chart-footer {
    overflow: hidden;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

// Components
import UserLogRow from "./UserLogRow.vue";

export default {
    components: {
        UserLogRow,
    },

    data() {
        return {
            tab: 0,

            /**
             * logs charts list
             */
            logs: {},

            selectedLogs: [],

            currentDate: null,

            refreshMaxValue: false,

            fetchingLogs: false,
            fetchingSelectedLogs: false,

            dateIntervalStart: null,
            dateIntervalEnd: null,
            dateIntervalSelect: false,

            touchX: null,
            touchY: null,
            disableScroll: false,
        };
    },

    mounted() {
        this.init();
    },

    methods: {
        /**
         * Fetch logs
         */
        async fetchLogs(date) {
            this.logs[date] = new Array(96).fill(0);
            this.$forceUpdate();

            this.fetchingLogs = true;

            try {
                let { data } = await ApiService.get(
                    (this.project ? "project/" + this.project.slug + "/" : "") +
                        "user/" +
                        this.currentUser.id +
                        "/tracker-stat",
                    {
                        params: {
                            date: date,
                        },
                    }
                );

                this.logs[date] = data;
                this.refreshMaxValue = !this.refreshMaxValue;
                this.$forceUpdate();
            } finally {
                this.fetchingLogs = false;
            }
        },

        /**
         *
         */
        dates: function () {
            return Object.keys(this.logs).sort((a, b) => (a > b ? 1 : -1));
        },

        getHour(index) {
            let hour = parseInt(index / 4);
            let minute = (index % 4) * 15;

            return (
                (hour < 10 ? "0" + hour : hour) +
                ":" +
                (minute < 10 ? "0" + minute : minute)
            );
        },

        getDate(date, index) {
            return date + " " + this.getHour(index) + ":00";
        },

        beginDateIntervalSelect(date, index) {
            this.dateIntervalSelect = true;
            this.dateIntervalStart = this.getDate(date, index);
            this.dateIntervalEnd = this.getDate(date, index);
        },

        setDateIntervalSelectEnd(date, index) {
            if (this.dateIntervalSelect) {
                this.dateIntervalEnd = this.getDate(date, index);
            }

            if (this.disableScroll) {
                this.endDateIntervalSelect();
            }
        },

        endDateIntervalSelect() {
            this.dateIntervalSelect = false;

            if (this.dateIntervalStart > this.dateIntervalEnd) {
                let date = this.dateIntervalStart;
                this.dateIntervalStart = this.dateIntervalEnd;
                this.dateIntervalEnd = date;
            }

            let date = new Date(this.dateIntervalEnd);
            date.setMinutes(date.getMinutes() + 15);
            date.setSeconds(date.getSeconds() - 1);

            this.dateIntervalEnd = dateToString(date);
        },

        async viewConnexions() {
            this.tab = 1;
            this.fetchingSelectedLogs = true;

            try {
                let { data } = await ApiService.get(
                    (this.project ? "project/" + this.project.slug + "/" : "") +
                        "user/" +
                        this.currentUser.id +
                        "/tracker",
                    {
                        params: {
                            start: this.dateIntervalStart,
                            end: this.dateIntervalEnd,
                        },
                    }
                );

                this.selectedLogs = data;
            } catch (e) {
                this.selectedLogs = [];
            } finally {
                this.fetchingSelectedLogs = false;
            }
        },

        touchdown(event) {
            this.touchX = event.touches[0].clientX;
            this.touchY = event.touches[0].clientY;

            setTimeout(() => {
                if (this.touchX != null) {
                    this.disableScroll = true;
                }
            }, 500);
        },

        touchup() {
            this.touchX = this.touchY = null;
            this.disableScroll = false;
        },

        touchmove(event) {
            if (
                !this.disableScroll &&
                Math.abs(event.touches[0].clientX - this.touchX) +
                    Math.abs(event.touches[0].clientY - this.touchY) >
                    6
            ) {
                this.touchX = this.touchY = null;
            }
        },

        init() {
            let chart = document.getElementById("user-logs-chart");
            let chartContent = document.getElementById(
                "user-logs-chart-content"
            );

            chart.addEventListener("scroll", (event) => {
                if (
                    chart.scrollLeft ==
                    chartContent.getBoundingClientRect().width -
                        chart.getBoundingClientRect().width
                ) {
                    let dates = this.dates();
                    let date = new Date(dates[dates.length - 1]);
                    date.setDate(date.getDate() + 1);
                    this.fetchLogs(dateToString(date).substring(0, 10));

                    if (dates.length >= 5) {
                        delete this.logs[dates[0]];
                        chart.scrollLeft -= 480;
                        this.$forceUpdate();
                    }
                } else if (chart.scrollLeft == 0) {
                    let dates = this.dates();
                    let date = new Date(dates[0]);
                    date.setDate(date.getDate() - 1);
                    this.fetchLogs(dateToString(date).substring(0, 10));
                    chart.scrollLeft = 480;

                    if (dates.length >= 5) {
                        delete this.logs[dates[dates.length - 1]];
                        this.$forceUpdate();
                    }
                }
            });

            let date = new Date();
            date.setDate(date.getDate() - 1);
            this.currentDate = date;
        },
    },

    watch: {
        /**
         * On user selected
         * load user folders
         */
        currentDate: function (newValue, oldValue) {
            this.logs = {};

            this.fetchLogs(dateToString(this.currentDate).substring(0, 10));

            let yesterday = new Date(this.currentDate);
            yesterday.setDate(yesterday.getDate() - 1);

            this.fetchLogs(dateToString(yesterday).substring(0, 10));

            let tomorrow = new Date(this.currentDate);
            tomorrow.setDate(tomorrow.getDate() + 1);

            this.fetchLogs(dateToString(tomorrow).substring(0, 10));

            this.$nextTick(() => {
                let chart = document.getElementById("user-logs-chart");
                chart.scrollLeft = 480;
            });
        },
    },

    computed: {
        ...mapGetters(["project", "user", "globalUser"]),

        currentUser() {
            return this.globalUser ? this.globalUser : this.user;
        },

        /**
         *
         */
        maxValue: function () {
            this.refreshMaxValue;

            let maxValue = 0;

            for (let date in this.logs) {
                let log = this.logs[date];

                log.forEach((hit) => {
                    if (hit > maxValue) {
                        maxValue = hit;
                    }
                });
            }

            return maxValue;
        },

        /**
         *
         */
        currentDateValue: {
            get: function () {
                return this.currentDate
                    ? dateToString(this.currentDate).substring(0, 10)
                    : "";
            },
            set: function (value) {
                this.currentDate = new Date(value);
            },
        },
    },
};
</script>
