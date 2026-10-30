<template>
    <slide
        name="prospect-manage-workflows"
        :title="$t('workflow.monitor.prospect_title', { prospect: prospectFullName })"
        icon="fa fa-diagram-project"
        style="width: 360px"
        @open="fetchExecutions"
    >
        <div class="hc-wfps">
            <p v-if="!loading && !executions.length" class="hc-workflow-hint" v-text="$t('workflow.monitor.prospect_none')"></p>

            <section v-for="execution in executions" :key="execution.id" class="hc-wfps-execution">
                <execution-steps :execution="execution" />
            </section>

            <loading :loading="loading" />
        </div>
    </slide>
</template>

<style>
.hc-wfps {
    position: relative;
    height: 100%;
    padding: 12px;
    overflow: auto;
}

.hc-wfps-execution {
    margin-bottom: 14px;
    padding: 10px;
    background: #fafbfe;
    border: 1px solid #e1e3ea;
    border-radius: 6px;
}
</style>

<script>
import { mapGetters } from "vuex";
import WorkflowService from "@/apis/project/workflow";
import ExecutionSteps from "./ExecutionSteps.vue";

/**
 * What the automations did (and still do) for the prospect of the page
 */
export default {
    components: { ExecutionSteps },

    data() {
        return {
            executions: [],
            loading: false,
        };
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectFullName"]),
    },

    methods: {
        async fetchExecutions() {
            if (!this.prospect) {
                return;
            }

            this.loading = true;
            this.executions = [];

            try {
                const { data } = await WorkflowService.prospectExecutions(this.project.slug, this.prospect.id);
                this.executions = data;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
