<template>
    <div class="hc-wfm">
        <!-- Figures -->
        <section class="hc-wfm-block">
            <header class="hc-wfm-title">
                <strong v-text="$t('workflow.monitor.overview')"></strong>
                <select v-model.number="days" @change="loadStats">
                    <option v-for="d in [1, 7, 30]" :key="d" :value="d">{{ $t("workflow.monitor.last_days", { n: d }) }}</option>
                </select>
            </header>

            <div v-if="stats" class="hc-wfm-cards">
                <div class="hc-wfm-card"><b>{{ totals.waiting }}</b><span v-text="$t('workflow.monitor.card.waiting')"></span></div>
                <div class="hc-wfm-card"><b>{{ totals.completed }}</b><span v-text="$t('workflow.monitor.card.completed')"></span></div>
                <div class="hc-wfm-card" :class="{ bad: totals.failed }"><b>{{ totals.failed }}</b><span v-text="$t('workflow.monitor.card.failed')"></span></div>
                <div class="hc-wfm-card"><b>{{ stepTotal }}</b><span v-text="$t('workflow.monitor.card.steps')"></span></div>
            </div>

            <table v-if="stats && stats.workflows.length" class="hc-wfm-table">
                <thead>
                    <tr>
                        <th v-text="$t('workflow.monitor.col.workflow')"></th>
                        <th v-text="$t('workflow.monitor.col.waiting')"></th>
                        <th v-text="$t('workflow.monitor.col.completed')"></th>
                        <th v-text="$t('workflow.monitor.col.failed')"></th>
                        <th v-text="$t('workflow.monitor.col.average')"></th>
                        <th v-text="$t('workflow.monitor.col.last')"></th>
                        <th v-text="$t('workflow.monitor.col.next')"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="workflow in stats.workflows" :key="workflow.id" @click="filters.workflow = workflow.id; loadList(true)">
                        <td>
                            {{ workflow.name }}
                            <span class="hc-workflow-badge" :class="workflow.status" v-text="$t('workflow.status.' + workflow.status)"></span>
                        </td>
                        <td>{{ workflow.current.waiting + workflow.current.pending + workflow.current.running }}</td>
                        <td>{{ workflow.period.completed }}</td>
                        <td :class="{ bad: workflow.period.failed }">{{ workflow.period.failed }}</td>
                        <td>{{ duration(workflow.average_seconds) }}</td>
                        <td>{{ dateTime(workflow.last_started_at) }}</td>
                        <td>{{ dateTime(workflow.next_resume_at) }}</td>
                    </tr>
                </tbody>
            </table>

            <div v-if="stats && (stats.errors.length || stats.providers.length)" class="hc-wfm-columns">
                <div v-if="stats.errors.length">
                    <h4 v-text="$t('workflow.monitor.errors')"></h4>
                    <ul class="hc-wfm-list">
                        <li v-for="error in stats.errors" :key="error.message">
                            <b>{{ error.count }}×</b> {{ error.message }}
                        </li>
                    </ul>
                </div>
                <div v-if="stats.providers.length">
                    <h4 v-text="$t('workflow.monitor.operators')"></h4>
                    <ul class="hc-wfm-list">
                        <li v-for="provider in stats.providers" :key="provider.provider">
                            {{ provider.provider }}: {{ provider.sent }} {{ $t("workflow.monitor.sent") }}<template v-if="provider.failed">, <span class="bad">{{ provider.failed }} {{ $t("workflow.monitor.failed") }}</span></template>
                        </li>
                    </ul>
                </div>
            </div>
            <loading :loading="loadingStats" />
        </section>

        <!-- Executions -->
        <section class="hc-wfm-block">
            <header class="hc-wfm-title">
                <strong v-text="$t('workflow.monitor.executions')"></strong>
                <select v-model="filters.workflow" @change="loadList(true)">
                    <option value="" v-text="$t('workflow.monitor.all_workflows')"></option>
                    <option v-for="workflow in workflowOptions" :key="workflow.id" :value="workflow.id" v-text="workflow.name"></option>
                </select>
                <select v-model="filters.status" @change="loadList(true)">
                    <option value="" v-text="$t('workflow.monitor.all_statuses')"></option>
                    <option v-for="status in ['waiting', 'failed', 'completed', 'cancelled']" :key="status" :value="status" v-text="$t('workflow.exec_status.' + status)"></option>
                </select>
                <button type="button" @click="refresh" v-text="$t('workflow.monitor.refresh')"></button>
            </header>

            <div class="hc-wfm-split">
                <div class="hc-wfm-rows">
                    <p v-if="!loadingList && !rows.length" class="hc-workflow-hint" v-text="$t('workflow.monitor.none')"></p>

                    <div
                        v-for="row in rows"
                        :key="row.id"
                        class="hc-wfm-row"
                        :class="{ current: detail && detail.id === row.id }"
                        @click="open(row)"
                    >
                        <span class="hc-workflow-badge" :class="'exec-' + row.status" v-text="$t('workflow.exec_status.' + row.status)"></span>
                        <span class="hc-wfm-row-main">
                            <b>{{ row.workflow.name || $t("workflow.monitor.deleted_workflow") }}</b>
                            <span>{{ row.subject.name || $t("workflow.monitor.subject." + row.subject.type) + " #" + row.subject.id }}</span>
                        </span>
                        <span class="hc-wfm-row-date">{{ dateTime(row.started_at) }}</span>
                        <span v-if="row.error" class="hc-wfm-row-error" :title="row.error" v-text="row.error"></span>
                    </div>

                    <button v-if="hasMore" type="button" class="hc-wfm-more" :disabled="loadingList" @click="loadList(false)" v-text="$t('workflow.monitor.more')"></button>
                    <loading :loading="loadingList" />
                </div>

                <div v-if="detail" class="hc-wfm-detail">
                    <execution-steps :execution="detail" />
                    <button
                        v-if="canCancel && cancellable(detail)"
                        type="button"
                        class="hc-wfb-danger"
                        :disabled="cancelling"
                        @click="cancel"
                        v-text="$t('workflow.monitor.cancel')"
                    ></button>
                </div>
            </div>
        </section>
    </div>
</template>

<style>
.hc-wfm {
    flex: 1;
    min-height: 0;
    padding: 18px;
    overflow: auto;
}

.hc-wfm-block {
    position: relative;
    margin-bottom: 18px;
    padding: 16px;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(30, 35, 60, 0.05);
}

.hc-wfm-title {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
}

.hc-wfm-title strong {
    margin-right: auto;
    font-size: 15px;
}

.hc-wfm select,
.hc-wfm-title button {
    padding: 6px 10px;
    color: #2b2f3a;
    background: #fff;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
    font-size: 13px;
}

.hc-wfm-title button,
.hc-wfm-more {
    font-weight: 600;
    cursor: pointer;
}

.hc-wfm-title button:hover,
.hc-wfm-more:hover:not(:disabled) {
    background: #f3f4f9;
}

.hc-wfm-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.hc-wfm-card {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 14px 16px;
    background: #f6f7fb;
    border-top: 4px solid #4b5ce8;
    border-radius: 10px;
}

.hc-wfm-card:nth-child(2) {
    border-top-color: #2e9d5c;
}

.hc-wfm-card:nth-child(3) {
    border-top-color: #c3c7d4;
}

.hc-wfm-card:nth-child(4) {
    border-top-color: #8e5bd6;
}

.hc-wfm-card.bad {
    background: #fdecea;
    border-top-color: #d05a45;
}

.hc-wfm-card b {
    font-size: 26px;
    line-height: 1.1;
}

.hc-wfm-card span {
    color: #7a7f90;
    font-size: 12px;
}

.hc-wfm .bad {
    color: #c0341d;
}

.hc-wfm-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.hc-wfm-table th {
    padding: 8px 10px;
    color: #7a7f90;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-align: left;
    text-transform: uppercase;
}

.hc-wfm-table td {
    padding: 10px;
    text-align: left;
    border-top: 1px solid #eef0f5;
}

.hc-wfm-table tbody tr {
    cursor: pointer;
}

.hc-wfm-table tbody tr:hover {
    background: #f4f5fe;
}

.hc-wfm-columns {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 16px;
    font-size: 13px;
}

.hc-wfm-columns > div {
    flex: 1;
    min-width: 220px;
    padding: 12px 14px;
    background: #f6f7fb;
    border-radius: 10px;
}

.hc-wfm-columns h4 {
    margin: 0 0 6px;
    font-size: 13px;
}

.hc-wfm-list {
    margin: 0;
    padding-left: 16px;
    line-height: 1.6;
}

.hc-wfm-split {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.hc-wfm-rows {
    position: relative;
    flex: 1;
    min-width: 0;
}

.hc-wfm-detail {
    flex: none;
    width: 380px;
    padding: 14px;
    background: #fafbfe;
    border: 1px solid #e3e6ef;
    border-radius: 10px;
}

.hc-wfm-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px 12px;
    margin-bottom: 6px;
    padding: 10px 12px;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-radius: 10px;
    cursor: pointer;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.hc-wfm-row:hover {
    box-shadow: 0 2px 8px rgba(30, 35, 60, 0.08);
}

.hc-wfm-row.current {
    background: #f1f2fd;
    border-color: #aab2f5;
}

.hc-wfm-row-main {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    font-size: 13px;
}

.hc-wfm-row-main span {
    color: #7a7f90;
    font-size: 12px;
}

.hc-wfm-row-date {
    color: #7a7f90;
    font-size: 12px;
}

.hc-wfm-row-error {
    flex-basis: 100%;
    overflow: hidden;
    padding: 4px 8px;
    color: #8a1f11;
    background: #fdecea;
    border-radius: 6px;
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-wfm-more {
    margin-top: 8px;
    padding: 7px 16px;
    background: #fff;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
}
</style>

<script>
import { mapGetters } from "vuex";
import WorkflowService from "@/apis/project/workflow";
import ExecutionSteps from "./ExecutionSteps.vue";
import { cancellable, dateTime, duration } from "@/utils/workflow-format";

export default {
    components: { ExecutionSteps },

    props: {
        // Start on the executions of this workflow
        workflowId: { type: [Number, String], default: "" },
    },

    data() {
        return {
            days: 7,
            stats: null,
            loadingStats: false,
            filters: { workflow: this.workflowId || "", status: "" },
            rows: [],
            page: 1,
            hasMore: false,
            loadingList: false,
            detail: null,
            cancelling: false,
        };
    },

    computed: {
        ...mapGetters(["project", "can"]),

        canCancel() {
            return this.can("all.project.workflow.execute");
        },

        workflowOptions() {
            return this.stats ? this.stats.workflows : [];
        },

        /**
         * Sums over the workflows of the figures
         */
        totals() {
            const sum = (path) => (this.stats ? this.stats.workflows.reduce((total, w) => total + path(w), 0) : 0);

            return {
                waiting: sum((w) => w.current.waiting + w.current.pending + w.current.running),
                completed: sum((w) => w.period.completed),
                failed: sum((w) => w.period.failed),
            };
        },

        stepTotal() {
            return this.stats
                ? Object.values(this.stats.steps).reduce((total, byStatus) => total + Object.values(byStatus).reduce((a, b) => a + b, 0), 0)
                : 0;
        },
    },

    created() {
        this.loadStats();
        this.loadList(true);
    },

    methods: {
        cancellable,
        dateTime,
        duration,

        async loadStats() {
            this.loadingStats = true;

            try {
                const { data } = await WorkflowService.stats(this.project.slug, this.days);
                this.stats = data;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                this.loadingStats = false;
            }
        },

        /**
         * @param {boolean} reset start again from the first page (filters changed)
         */
        async loadList(reset) {
            if (reset) {
                this.page = 1;
                this.rows = [];
                this.detail = null;
            }

            this.loadingList = true;

            try {
                const { data } = await WorkflowService.executions(this.project.slug, {
                    workflow: this.filters.workflow || undefined,
                    status: this.filters.status || undefined,
                    page: this.page,
                });

                this.rows = [...this.rows, ...data.data];
                this.hasMore = data.has_more;
                this.page = data.current_page + 1;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                this.loadingList = false;
            }
        },

        refresh() {
            this.loadStats();
            this.loadList(true);
        },

        async open(row) {
            try {
                const { data } = await WorkflowService.execution(this.project.slug, row.id);
                this.detail = data;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            }
        },

        async cancel() {
            if (!window.confirm(this.$t("workflow.monitor.confirm_cancel"))) {
                return;
            }

            this.cancelling = true;

            try {
                const { data } = await WorkflowService.cancelExecution(this.project.slug, this.detail.id);
                this.detail = data;
                this.rows = this.rows.map((row) => (row.id === data.id ? { ...row, status: data.status, error: data.error } : row));
                this.loadStats();
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.monitor.cannot_cancel") });
            } finally {
                this.cancelling = false;
            }
        },
    },
};
</script>
