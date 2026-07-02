<template>
    <slide
        name="prospect-manage-logs"
        :title="
            $t('prospect.log_history.title', {
                prospect: prospectFullName,
            })
        "
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-eye"
        style="width: 260px"
        @open="fetchLogs"
    >
        <div id="hc-prospect-logs" style="height: 100%; overflow: auto">
            <item-list id="hc-prospect-logs-content" padding="12px">
                <log-row v-for="(log, i) in logs" :key="log.id" :log="log" />
            </item-list>
        </div>
    </slide>
</template>

<style>
.hc-log-date {
    width: 100%;
    text-align: right;
    height: 35px;
    line-height: 35px;
    font-weight: 500;
    opacity: 0.7;
    border-bottom: 1px solid #0002;
    margin-bottom: 5px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { FETCH_PROSPECT_LOGS } from "@/actions/project/prospect/log";

// Components
import LogRow from "./LogRow.vue";

export default {
    components: {
        LogRow,
    },

    data() {
        return {
            fetchingLogs: false,
            logs: [],
            page: 1,
        };
    },

    methods: {
        async fetchLogs() {
            if (this.page == 0) {
                return;
            }

            this.fetchingLogs = true;

            try {
                const data = await store.dispatch(FETCH_PROSPECT_LOGS);

                this.logs = [...this.logs, ...data.data];

                if (this.page < data.last_page) {
                    this.page++;
                } else {
                    this.page = 0;
                }
            } finally {
                this.fetchingLogs = false;
            }
        },

        logDate(log) {
            return dayjs(log.created_at).format("DD/MM/YYYY");
        },

        scrollListener() {
            let prospectLogs = document.getElementById("hc-prospect-logs");
            let prospectLogsContent = document.getElementById(
                "hc-prospect-logs-content"
            );

            prospectLogs.addEventListener("scroll", (event) => {
                if (
                    prospectLogs.scrollTop >=
                        prospectLogsContent.getBoundingClientRect().height -
                            prospectLogs.getBoundingClientRect().height -
                            100 &&
                    !this.fetchingLogs
                ) {
                    this.fetchLogs();
                }
            });
        },
    },

    watch: {},

    computed: {
        ...mapGetters(["project", "prospect", "prospectFullName"]),
    },
};
</script>
