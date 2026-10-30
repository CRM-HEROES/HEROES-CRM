<template>
    <div id="hc-workflow">
        <div v-if="mobile" id="hc-workflow-mobile" v-text="$t('workflow.mobile')"></div>
        <template v-else>
            <aside id="hc-workflow-list">
                <div class="hc-workflow-list-title">
                    <span v-text="$t('workflow.title')"></span>
                    <span v-if="workflows.length" class="hc-workflow-count" v-text="workflows.length"></span>
                </div>

                <form v-if="can('all.project.workflow.add')" class="hc-workflow-new" @submit.prevent="create()">
                    <input v-model="newName" type="text" maxlength="150" :placeholder="$t('workflow.new_placeholder')" />
                    <button type="submit" :disabled="!newName.trim() || creating">
                        <span>+</span> {{ $t("workflow.new") }}
                    </button>
                </form>

                <div class="hc-workflow-items">
                    <p v-if="!loading && !workflows.length" class="hc-workflow-empty" v-text="$t('workflow.empty')"></p>

                    <router-link
                        v-for="workflow in workflows"
                        :key="workflow.id"
                        class="hc-workflow-list-item"
                        :class="[workflow.status, { current: current && current.id === workflow.id }]"
                        :to="{ name: 'workflow.show', params: { project: project.slug, workflow: workflow.id } }"
                    >
                        <span class="hc-workflow-list-top">
                            <span class="hc-workflow-list-name" v-text="workflow.name"></span>
                            <span class="hc-workflow-badge" :class="workflow.status" v-text="$t('workflow.status.' + workflow.status)"></span>
                        </span>
                        <span v-if="workflow.legacy_campaign_id" class="hc-workflow-origin" v-text="$t('workflow.from_campaign')"></span>
                        <span v-if="Object.keys(visibleCounts(workflow)).length" class="hc-workflow-list-counts">
                            <span
                                v-for="(total, status) in visibleCounts(workflow)"
                                :key="status"
                                class="hc-workflow-pill"
                                :class="'exec-' + status"
                                :title="$t('workflow.executions.' + status)"
                                >{{ total }} {{ $t("workflow.executions." + status).toLowerCase() }}</span
                            >
                        </span>
                    </router-link>
                </div>

                <!-- Starting points: open while there is nothing yet, tucked away afterwards -->
                <div v-if="can('all.project.workflow.add')" class="hc-workflow-templates">
                    <button type="button" class="hc-workflow-templates-toggle" @click="showTemplates = !showTemplates">
                        <span v-text="$t('workflow.templates.title')"></span>
                        <span class="hc-workflow-chevron" :class="{ open: templatesOpen }">›</span>
                    </button>
                    <template v-if="templatesOpen">
                        <button
                            v-for="id in templateIds"
                            :key="id"
                            type="button"
                            class="hc-workflow-template"
                            :disabled="creating"
                            @click="createFromTemplate(id)"
                        >
                            <span class="hc-workflow-template-icon" v-text="templateIcon(id)"></span>
                            <span class="hc-workflow-template-text">
                                <strong v-text="$t('workflow.template.' + id + '.name')"></strong>
                                <small v-text="$t('workflow.template.' + id + '.description')"></small>
                            </span>
                        </button>
                    </template>
                </div>
                <loading :loading="loading" />
            </aside>

            <section id="hc-workflow-main">
                <nav v-if="canMonitor" class="hc-workflow-tabs">
                    <button type="button" :class="{ active: tab === 'schema' }" @click="tab = 'schema'" v-text="$t('workflow.tab.schema')"></button>
                    <button type="button" :class="{ active: tab === 'monitor' }" @click="tab = 'monitor'" v-text="$t('workflow.tab.monitor')"></button>
                </nav>

                <!-- Follow-up of the executions (of the workflow selected, else of all of them) -->
                <workflow-monitor
                    v-if="tab === 'monitor' && canMonitor"
                    :key="'monitor-' + (current ? current.id : 0)"
                    :workflow-id="current ? current.id : ''"
                />

                <template v-else>
                    <p v-if="!current && !loadingCurrent" class="hc-workflow-hint" v-text="$t('workflow.select')"></p>
                    <workflow-builder
                        v-if="current && catalog"
                        :key="current.id"
                        :workflow="current"
                        :catalog="catalog"
                        @changed="refreshList"
                        @deleted="deleted"
                    />
                    <loading :loading="loadingCurrent" />
                </template>
            </section>
        </template>
    </div>
</template>

<style>
#hc-workflow {
    display: flex;
    width: 100%;
    height: 100%;
    background-color: #f7f7fb;
}

#hc-workflow-mobile {
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: center;
    padding: 30px;
    font-size: 20px;
    text-align: center;
}

#hc-workflow-list {
    position: relative;
    display: flex;
    flex-direction: column;
    flex: none;
    width: 290px;
    overflow: auto;
    background: #fff;
    border-right: 1px solid #e3e6ef;
}

.hc-workflow-list-title {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 18px 16px 10px;
    font-size: 17px;
    font-weight: 700;
}

.hc-workflow-count {
    padding: 1px 8px;
    color: #3a49c9;
    background: #eceefd;
    border-radius: 10px;
    font-size: 12px;
}

.hc-workflow-new {
    align-items: center;
    display: flex;
    gap: 6px;
    padding: 4px 14px 14px;
}

.hc-workflow-new input {
    flex: 1;
    min-width: 0;
    padding: 8px 10px;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
    font-size: 13px;
}

.hc-workflow-new input:focus {
    border-color: #5b6cf0;
    box-shadow: 0 0 0 3px rgba(91, 108, 240, 0.15);
    outline: 0;
}

.hc-workflow-new button {
    flex: none;
    padding: 8px 12px;
    color: #fff;
    background: #4b5ce8;
    border: 0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.hc-workflow-new button:hover:not(:disabled) {
    background: #3a49c9;
}

.hc-workflow-new button:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.hc-workflow-items {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 0 14px;
}

.hc-workflow-empty {
    margin: 0;
    padding: 14px;
    color: #7a7f90;
    background: #f6f7fb;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.4;
    text-align: center;
}

.hc-workflow-hint {
    padding: 16px;
    color: #7a7f90;
    font-size: 13px;
}

.hc-workflow-list-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px 12px;
    color: inherit;
    text-decoration: none;
    background: #fff;
    border: 1px solid #e3e6ef;
    border-left: 4px solid #c3c7d4;
    border-radius: 10px;
    transition: box-shadow 0.15s, border-color 0.15s;
}

.hc-workflow-list-item.active {
    border-left-color: #2e9d5c;
}

.hc-workflow-list-item.paused {
    border-left-color: #e8a317;
}

.hc-workflow-list-item:hover {
    box-shadow: 0 3px 10px rgba(30, 35, 60, 0.1);
}

.hc-workflow-list-item.current {
    background: #f1f2fd;
    border-color: #aab2f5;
    box-shadow: 0 0 0 2px rgba(91, 108, 240, 0.18);
}

.hc-workflow-list-top {
    display: flex;
    align-items: center;
    gap: 8px;
}

.hc-workflow-list-name {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    color: #2b2f3a;
    font-size: 14px;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-workflow-list-counts {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.hc-workflow-pill {
    padding: 1px 8px;
    color: #5d6272;
    background: #f0f1f6;
    border-radius: 10px;
    font-size: 11px;
}

.hc-workflow-pill.exec-completed {
    color: #14633a;
    background: #d6f5df;
}

.hc-workflow-pill.exec-failed {
    color: #8a1f11;
    background: #fdecea;
}

.hc-workflow-pill.exec-waiting {
    color: #1b4aa8;
    background: #e3ecff;
}

.hc-workflow-badge {
    flex: none;
    padding: 2px 9px;
    border-radius: 10px;
    background: #e5e7eb;
    color: #444;
    font-size: 11px;
    font-weight: 600;
}

.hc-workflow-origin {
    color: #8e5bd6;
    font-size: 11px;
}

.hc-workflow-badge.active {
    background: #d6f5df;
    color: #14633a;
}

.hc-workflow-badge.paused {
    background: #fff0cc;
    color: #8a5a00;
}

.hc-workflow-badge.archived {
    background: #ececec;
    color: #888;
}

.hc-workflow-templates {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: auto;
    padding: 14px;
}

.hc-workflow-templates-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 2px;
    color: #7a7f90;
    background: transparent;
    border: 0;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    cursor: pointer;
}

.hc-workflow-chevron {
    font-size: 16px;
    transition: transform 0.15s;
}

.hc-workflow-chevron.open {
    transform: rotate(90deg);
}

.hc-workflow-template {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 9px 10px;
    text-align: left;
    background: #fafbfd;
    border: 1px solid #e3e6ef;
    border-radius: 10px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}

.hc-workflow-template:hover:not(:disabled) {
    background: #f1f2fd;
    border-color: #aab2f5;
}

.hc-workflow-template:disabled {
    opacity: 0.5;
}

.hc-workflow-template-icon {
    display: flex;
    flex: none;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background: #eceefd;
    border-radius: 8px;
    font-size: 15px;
}

.hc-workflow-template-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.hc-workflow-template strong {
    color: #2b2f3a;
    font-size: 13px;
}

.hc-workflow-template small {
    color: #7a7f90;
    font-size: 11px;
    line-height: 1.35;
}

#hc-workflow-main {
    position: relative;
    display: flex;
    flex: 1;
    min-width: 0;
    flex-direction: column;
}

.hc-workflow-tabs {
    display: flex;
    gap: 4px;
    padding: 10px 16px 0;
    background: #fff;
    border-bottom: 1px solid #e3e6ef;
}

.hc-workflow-tabs button {
    padding: 8px 16px;
    color: #7a7f90;
    background: transparent;
    border: 0;
    border-bottom: 3px solid transparent;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.hc-workflow-tabs button:hover {
    color: #2b2f3a;
}

.hc-workflow-tabs button.active {
    color: #3a49c9;
    border-bottom-color: #4b5ce8;
}
</style>

<script>
import { mapGetters } from "vuex";
import WorkflowService from "@/apis/project/workflow";
import WorkflowBuilder from "./WorkflowBuilder.vue";
import { TEMPLATE_IDS, templateGraph } from "@/utils/workflow-templates";
import WorkflowMonitor from "./WorkflowMonitor.vue";

export default {
    components: { WorkflowBuilder, WorkflowMonitor },

    data() {
        return {
            tab: "schema",
            workflows: [],
            current: null,
            catalog: null,
            loading: false,
            loadingCurrent: false,
            creating: false,
            newName: "",
            showTemplates: null,
        };
    },

    computed: {
        ...mapGetters(["project", "can"]),

        /**
         * Open by default while there is no automation yet
         */
        templatesOpen() {
            return this.showTemplates === null ? !this.loading && !this.workflows.length : this.showTemplates;
        },

        templateIds() {
            return TEMPLATE_IDS;
        },

        mobile() {
            return deviceType() != "desktop";
        },

        canMonitor() {
            return this.can("all.project.workflow.execution.view");
        },
    },

    async created() {
        if (this.mobile) {
            return;
        }

        await Promise.all([this.refreshList(), this.loadCatalog()]);
        await this.load();
    },

    methods: {
        /**
         * Executions worth showing in the list
         */
        visibleCounts(workflow) {
            const counts = workflow.executions || {};

            return Object.fromEntries(
                ["waiting", "running", "failed"]
                    .filter((status) => counts[status] > 0)
                    .map((status) => [status, counts[status]])
            );
        },

        async refreshList() {
            this.loading = true;

            try {
                const { data } = await WorkflowService.get(this.project.slug);
                this.workflows = data;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                this.loading = false;
            }
        },

        async loadCatalog() {
            try {
                const { data } = await WorkflowService.catalog(this.project.slug);
                this.catalog = data;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            }
        },

        /**
         * The workflow of the route
         */
        async load() {
            const id = this.$route.params.workflow;

            if (!id) {
                this.current = null;
                return;
            }

            this.loadingCurrent = true;

            try {
                const { data } = await WorkflowService.show(this.project.slug, id);
                this.current = data;
            } catch (e) {
                this.current = null;
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                this.loadingCurrent = false;
            }
        },

        templateIcon(id) {
            return { welcome_email: "✉", sms_follow_up: "↻", appointment_reminder: "⏰", work_hours_contact: "☀" }[id] || "⚡";
        },

        async createFromTemplate(id) {
            await this.create(this.$t(`workflow.template.${id}.name`), templateGraph(id));
        },

        async create(name = this.newName.trim(), graph = null) {
            if (typeof name !== "string" || !name || this.creating) {
                return;
            }

            this.creating = true;

            try {
                const { data } = await WorkflowService.create(this.project.slug, { name });

                // A template starts as the draft of the new workflow
                if (graph) {
                    await WorkflowService.update(this.project.slug, data.id, { draft_graph: graph });
                }

                this.newName = "";
                await this.refreshList();
                this.$router.push({
                    name: "workflow.show",
                    params: { project: this.project.slug, workflow: data.id },
                });
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.create") });
            } finally {
                this.creating = false;
            }
        },

        async deleted() {
            this.current = null;
            await this.refreshList();
            this.$router.push({ name: "workflow", params: { project: this.project.slug } });
        },
    },

    watch: {
        "$route.params.workflow"() {
            this.load();
        },
    },
};
</script>
