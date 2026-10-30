<template>
    <div class="hc-wfe">
        <div class="hc-wfe-head">
            <strong v-text="execution.workflow.name || $t('workflow.monitor.deleted_workflow')"></strong>
            <span class="hc-workflow-badge" :class="'exec-' + execution.status" v-text="$t('workflow.exec_status.' + execution.status)"></span>
            <router-link
                v-if="prospectLink"
                class="hc-wfe-subject"
                :to="prospectLink"
                v-text="subjectLabel"
            ></router-link>
            <span v-else class="hc-wfe-subject" v-text="subjectLabel"></span>
        </div>

        <p v-if="waiting" class="hc-wfe-waiting">
            {{ $t("workflow.monitor." + waiting, { event: execution.waiting_event ? $t("workflow.event." + execution.waiting_event) : "", date: dateTime(execution.resume_at) }) }}
            <template v-if="execution.waiting_for && execution.waiting_for.name"> ({{ execution.waiting_for.name }})</template>
        </p>

        <p v-if="execution.error" class="hc-wfe-error" role="alert" v-text="execution.error"></p>

        <ol class="hc-wfe-steps">
            <li v-for="step in execution.steps" :key="step.node_id" :class="step.status">
                <span class="hc-wfe-icon" :title="$t('workflow.step_status.' + step.status)" v-text="stepIcon(step.status)"></span>
                <div class="hc-wfe-step">
                    <div class="hc-wfe-title">
                        {{ title(step) }}
                        <span v-if="step.branch" class="hc-wfe-branch" :class="step.branch" v-text="$t('workflow.' + step.branch)"></span>
                    </div>
                    <div v-for="line in details(step)" :key="line" class="hc-wfe-detail" v-text="line"></div>
                    <div v-if="step.error" class="hc-wfe-error" v-text="step.error"></div>
                    <div class="hc-wfe-time" v-text="dateTime(step.finished_at || step.started_at)"></div>
                </div>
            </li>
            <li v-if="!execution.steps.length" class="hc-wfe-none" v-text="$t('workflow.monitor.no_step')"></li>
        </ol>
    </div>
</template>

<style>
/* This component is also used outside of the workflow page (prospect page) */
.hc-wfe .hc-workflow-badge {
    padding: 1px 8px;
    border-radius: 10px;
    background: #e5e7eb;
    color: #444;
    font-size: 11px;
}

.hc-wfe-branch {
    margin-left: 6px;
    font-size: 11px;
    font-weight: 600;
}

.hc-wfe-branch.yes {
    color: #2e9d5c;
}

.hc-wfe-branch.no {
    color: #d05a45;
}

.hc-wfe-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px 10px;
}

.hc-wfe-subject {
    color: #555;
    font-size: 13px;
}

.hc-workflow-badge.exec-completed {
    background: #d6f5df;
    color: #14633a;
}

.hc-workflow-badge.exec-failed {
    background: #fdecea;
    color: #8a1f11;
}

.hc-workflow-badge.exec-waiting,
.hc-workflow-badge.exec-pending,
.hc-workflow-badge.exec-running {
    background: #e3ecff;
    color: #1b4aa8;
}

.hc-workflow-badge.exec-cancelled,
.hc-workflow-badge.exec-skipped {
    background: #ececec;
    color: #888;
}

.hc-wfe-waiting {
    margin: 8px 0 0;
    color: #1b4aa8;
    font-size: 13px;
}

.hc-wfe-error {
    margin: 4px 0 0;
    color: #c0341d;
    font-size: 13px;
}

.hc-wfe-steps {
    margin: 10px 0 0;
    padding: 0;
    list-style: none;
}

.hc-wfe-steps li {
    display: flex;
    gap: 10px;
    padding: 6px 0;
    border-top: 1px solid #eee;
}

.hc-wfe-icon {
    flex: none;
    width: 20px;
    color: #14633a;
    text-align: center;
}

.hc-wfe-steps li.failed .hc-wfe-icon {
    color: #c0341d;
}

.hc-wfe-steps li.skipped .hc-wfe-icon,
.hc-wfe-steps li.waiting .hc-wfe-icon {
    color: #888;
}

.hc-wfe-step {
    flex: 1;
    min-width: 0;
}

.hc-wfe-title {
    font-size: 13px;
    font-weight: 600;
}

.hc-wfe-detail,
.hc-wfe-time {
    color: #777;
    font-size: 12px;
}

.hc-wfe-none {
    color: #888;
    font-size: 13px;
}
</style>

<script>
import { mapGetters } from "vuex";
import { slug } from "@/utils/workflow-i18n";
import { dateTime, stepIcon, waitingKind } from "@/utils/workflow-format";

export default {
    props: {
        // An execution of the follow-up API, with its steps
        execution: { type: Object, required: true },
    },

    computed: {
        ...mapGetters(["project"]),

        waiting() {
            return waitingKind(this.execution);
        },

        subjectLabel() {
            const subject = this.execution.subject;

            return subject.name || `${this.$t("workflow.monitor.subject." + subject.type)} #${subject.id}`;
        },

        prospectLink() {
            const subject = this.execution.subject;

            return subject.type === "prospect"
                ? { name: "prospect.show", params: { project: this.project.slug, prospect: subject.id } }
                : null;
        },
    },

    methods: {
        dateTime,
        stepIcon,

        /**
         * What the step is: the name given in the builder, else what it does
         */
        title(step) {
            if (step.name) {
                return step.name;
            }

            if (step.action) {
                const key = "workflow.action_type." + slug(step.action);

                return this.$te(key) ? this.$t(key) : step.action;
            }

            if (step.trigger) {
                return this.$t("workflow.trigger." + slug(step.trigger));
            }

            return this.$t("workflow.node." + step.type);
        },

        /**
         * What the step left behind (references only: never a credential)
         */
        details(step) {
            const result = step.result || {};
            const lines = [];

            if (result.provider) {
                lines.push(`${this.$t("workflow.monitor.operator")} : ${result.provider}`);
            }

            if (result.sms_id) {
                lines.push(`${this.$t("workflow.monitor.sms")} #${result.sms_id}`);
            }

            if (result.message_id) {
                lines.push(`${this.$t("workflow.monitor.message")} #${result.message_id}`);
            }

            if (result.call_uuid || result.call_id) {
                lines.push(`${this.$t("workflow.monitor.call")} ${result.call_uuid || result.call_id}`);
            }

            if (result.outcome) {
                lines.push(this.$t("workflow.monitor.outcome." + result.outcome));
            }

            if (step.status === "skipped" && step.error) {
                lines.push(this.$t("workflow.monitor.skipped"));
            }

            return lines;
        },
    },
};
</script>
