<template>
    <aside class="hc-wfn">
        <header class="hc-wfn-head">
            <strong v-text="$t('workflow.node.' + node.type)"></strong>
            <button type="button" class="hc-wfn-close" :title="$t('workflow.close')" @click="$emit('close')">×</button>
        </header>

        <label class="hc-wfn-label"><span v-text="$t('workflow.step_name')"></span><help-tip k="workflow.tip.step_name" /></label>
        <input
            type="text"
            maxlength="120"
            :value="node.name"
            :placeholder="$t('workflow.step_name_placeholder')"
            :disabled="readOnly"
            @input="$emit('update', { name: $event.target.value })"
        />

        <!-- When -->
        <template v-if="node.type === 'trigger'">
            <label class="hc-wfn-label"><span v-text="$t('workflow.node.trigger')"></span><help-tip k="workflow.tip.trigger" /></label>
            <select :value="config.trigger" :disabled="readOnly" @change="changeTrigger($event.target.value)">
                <option v-for="trigger in catalog.triggers" :key="trigger.type" :value="trigger.type" v-text="$t('workflow.trigger.' + slug(trigger.type))"></option>
            </select>
            <p class="hc-wfn-help" v-text="$t('workflow.trigger_help.' + slug(config.trigger))"></p>

            <template v-for="field in triggerFields" :key="field.key">
                <template v-if="field.type === 'condition'">
                    <label class="hc-wfn-label"><span v-text="$t('workflow.trigger_filter')"></span><help-tip k="workflow.trigger_filter_help" /></label>
                    <condition-group :model-value="tree" :subject="subject" :catalog="catalog" :read-only="readOnly" @update:model-value="setTree" />
                </template>
                <template v-else>
                    <label class="hc-wfn-label"><span v-text="$t('workflow.field.' + field.key)"></span><help-tip :text="help(field) || ''" /></label>
                    <field-input
                        :model-value="config[field.key]"
                        :type="field.type"
                        :field="field"
                        :read-only="readOnly"
                        @update:model-value="setConfig({ [field.key]: $event })"
                    />
                </template>
            </template>
        </template>

        <!-- If -->
        <template v-else-if="node.type === 'condition'">
            <label class="hc-wfn-label"><span v-text="$t('workflow.node_help.condition')"></span></label>
            <condition-group :model-value="tree" :subject="subject" :catalog="catalog" :read-only="readOnly" @update:model-value="setTree" />
        </template>

        <!-- Do -->
        <template v-else-if="node.type === 'action'">
            <label class="hc-wfn-label"><span v-text="$t('workflow.field.action')"></span><help-tip k="workflow.tip.action" /></label>
            <select :value="config.action || ''" :disabled="readOnly" @change="chooseAction($event.target.value)">
                <option value="" v-text="$t('workflow.choose')"></option>
                <option v-if="config.action && !actionDef" :value="config.action" v-text="actionLabel(config.action)"></option>
                <optgroup v-for="group in actionGroups" :key="group.category" :label="$t('workflow.category.' + group.category)">
                    <option v-for="action in group.actions" :key="action.type" :value="action.type" v-text="$t('workflow.action_type.' + slug(action.type))"></option>
                </optgroup>
            </select>

            <!-- An action taken over from a campaign: kept as it is, its settings are shown -->
            <template v-if="config.action && !actionDef">
                <p class="hc-wfn-help" v-text="$t('workflow.legacy_action')"></p>
                <pre v-if="hasValue" class="hc-wfn-legacy" v-text="JSON.stringify(config.value, null, 2)"></pre>
            </template>

            <template v-if="actionDef">
                <p v-if="actionDef.danger" class="hc-wfn-danger" v-text="$t('workflow.danger')"></p>
                <template v-for="field in actionDef.fields" :key="field.key">
                    <label class="hc-wfn-label">
                        {{ $t("workflow.field." + fieldName(field)) }}
                        <span v-if="field.required" class="hc-wfn-required">*</span>
                        <help-tip :text="help(field) || ''" />
                    </label>
                    <field-input
                        :model-value="getPath(field.key)"
                        :type="field.type"
                        :field="field"
                        :variables="catalog.variables"
                        :read-only="readOnly"
                        @update:model-value="setPath(field.key, $event)"
                        @provider-label="setConfig({ provider_label: $event })"
                    />
                </template>
            </template>
        </template>

        <!-- Wait -->
        <template v-else-if="node.type === 'wait'">
            <label class="hc-wfn-label"><span v-text="$t('workflow.wait.mode')"></span><help-tip k="workflow.tip.wait_mode" /></label>
            <select :value="waitMode" :disabled="readOnly" @change="setWaitMode($event.target.value)">
                <option value="duration" v-text="$t('workflow.wait.duration')"></option>
                <option value="time" v-text="$t('workflow.wait.until_time')"></option>
                <option value="date" v-text="$t('workflow.wait.until_date')"></option>
            </select>

            <div v-if="waitMode === 'duration'" class="hc-wfn-duration">
                <label v-for="unit in ['days', 'hours', 'minutes']" :key="unit">
                    <span v-text="$t('workflow.wait.' + unit)"></span>
                    <field-input :model-value="config[unit]" type="number" :read-only="readOnly" @update:model-value="setConfig({ [unit]: $event })" />
                </label>
            </div>

            <template v-if="waitMode === 'time'">
                <label class="hc-wfn-label" v-text="$t('workflow.wait.time')"></label>
                <field-input :model-value="config.until_time" type="time" :read-only="readOnly" @update:model-value="setConfig({ until_time: $event })" />
                <label class="hc-wfn-label" v-text="$t('workflow.wait.only_days')"></label>
                <field-input :model-value="config.days" type="weekdays" :read-only="readOnly" @update:model-value="setConfig({ days: $event })" />
            </template>

            <template v-if="waitMode === 'date'">
                <label class="hc-wfn-label" v-text="$t('workflow.wait.date')"></label>
                <input
                    type="datetime-local"
                    :value="(config.until_date || '').replace(' ', 'T')"
                    :disabled="readOnly"
                    @input="setConfig({ until_date: $event.target.value.replace('T', ' ') })"
                />
            </template>

            <p v-if="waitMode !== 'duration'" class="hc-wfn-help" v-text="$t('workflow.condition.timezone_help')"></p>
        </template>

        <!-- Wait for an event -->
        <template v-else-if="node.type === 'wait_event'">
            <label class="hc-wfn-label"><span v-text="$t('workflow.field.event')"></span><help-tip k="workflow.tip.wait_event" /></label>
            <select :value="config.event" :disabled="readOnly" @change="setConfig({ event: $event.target.value })">
                <option v-for="event in catalog.events" :key="event" :value="event" v-text="$t('workflow.event.' + event)"></option>
            </select>

            <label class="hc-wfn-label"><span v-text="$t('workflow.wait.max')"></span><help-tip k="workflow.wait.event_help" /></label>
            <div class="hc-wfn-duration">
                <label v-for="unit in ['days', 'hours', 'minutes']" :key="unit">
                    <span v-text="$t('workflow.wait.' + unit)"></span>
                    <field-input :model-value="config[unit]" type="number" :read-only="readOnly" @update:model-value="setConfig({ [unit]: $event })" />
                </label>
            </div>
        </template>

        <p v-else-if="node.type === 'end'" class="hc-wfn-help" v-text="$t('workflow.node_help.end')"></p>

        <!-- What follows -->
        <template v-if="outputList.length">
            <label class="hc-wfn-label"><span v-text="$t('workflow.next')"></span><help-tip k="workflow.tip.next" /></label>
            <div v-for="output in outputList" :key="String(output.branch)" class="hc-wfn-output">
                <span v-if="output.branch" class="hc-wfn-branch" :class="output.branch" v-text="$t('workflow.' + output.branch)"></span>
                <select :value="output.to || ''" :disabled="readOnly" @change="$emit('link', { branch: output.branch, to: $event.target.value || null })">
                    <option value="" v-text="$t('workflow.nothing')"></option>
                    <option
                        v-for="candidate in candidates"
                        :key="candidate.id"
                        :value="candidate.id"
                        :disabled="cycle(candidate.id)"
                        v-text="label(candidate)"
                    ></option>
                </select>
            </div>
        </template>

        <button v-if="!readOnly && node.type !== 'trigger'" type="button" class="hc-wfn-delete" :title="$t('workflow.tip.remove_node')" @click="$emit('remove')" v-text="$t('workflow.remove_node')"></button>
    </aside>
</template>

<style>
.hc-wfn {
    display: flex;
    flex: none;
    flex-direction: column;
    gap: 4px;
    width: 320px;
    padding: 12px;
    overflow: auto;
    background: #fff;
    border-left: 1px solid #ddd;
}

.hc-wfn select,
.hc-wfn input[type="text"],
.hc-wfn input[type="datetime-local"] {
    width: 100%;
    box-sizing: border-box;
    padding: 7px 10px;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
    font-size: 13px;
}

.hc-wfn-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 15px;
}

.hc-wfn-close { width: 28px; height: 28px; padding: 0; color: #6b7080; background: #f0f1f6; border: 0; border-radius: 50%; font-size: 18px; line-height: 1; cursor: pointer; }
.hc-wfn-close:hover { background: #e1e3ec; }

.hc-wfn-label {
    margin-top: 8px;
    color: #555;
    font-size: 12px;
    font-weight: 600;
}

.hc-wfn-required {
    color: #c0341d;
}

.hc-wfn-help {
    margin: 4px 0 0;
    color: #888;
    font-size: 12px;
}

.hc-wfn-danger {
    margin: 4px 0 0;
    color: #c0341d;
    font-size: 12px;
}

.hc-wfn-legacy {
    max-height: 160px;
    margin: 4px 0 0;
    padding: 6px;
    overflow: auto;
    background: #f4f5f9;
    border-radius: 4px;
    font-size: 11px;
}

.hc-wfn-duration {
    display: flex;
    gap: 6px;
}

.hc-wfn-duration label {
    flex: 1;
    min-width: 0;
    font-size: 11px;
    color: #777;
}

.hc-wfn-output {
    display: flex;
    align-items: center;
    gap: 6px;
}

.hc-wfn-branch {
    flex: none;
    width: 34px;
    font-size: 12px;
    font-weight: 600;
}

.hc-wfn-branch.yes {
    color: #2e9d5c;
}

.hc-wfn-branch.no {
    color: #d05a45;
}

.hc-wfn-delete { margin-top: 16px; padding: 8px 12px; color: #c0341d; background: #fff; border: 1px solid #f1c3bb; border-radius: 8px; font-weight: 600; cursor: pointer; }
.hc-wfn-delete:hover { background: #fdecea; }
</style>

<script>
import ConditionGroup from "./ConditionGroup.vue";
import HelpTip from "./HelpTip.vue";
import FieldInput from "./FieldInput.vue";
import { outputs, triggerNode, wouldCreateCycle } from "@/utils/workflow-graph";
import { slug } from "@/utils/workflow-i18n";
import { triggerConfig } from "@/utils/workflow-conditions";

export default {
    components: { HelpTip, ConditionGroup, FieldInput },

    props: {
        node: { type: Object, required: true },
        graph: { type: Object, required: true },
        catalog: { type: Object, required: true },
        readOnly: { type: Boolean, default: false },
    },

    // update: { name?, config? } - link: { branch, to } (to null: no next step)
    emits: ["update", "link", "remove", "close"],

    data() {
        return { pending: null };
    },

    watch: {
        // The parent has applied the change: the prop is up to date again
        "node.config"() {
            this.pending = null;
        },
    },

    computed: {
        /**
         * The configuration being edited. A change is only seen in the "node" prop
         * after the parent has rendered again; "pending" holds the latest one in the meantime, so
         * that two changes in a row (an operator and its name, for instance) are
         * both kept instead of the second starting from the old configuration.
         */
        config() {
            return this.pending || this.node.config || {};
        },

        /**
         * What the workflow works on (prospect, order, appointment): the criteria depend on it
         */
        subject() {
            const trigger = triggerNode(this.graph);

            return trigger && trigger.config.trigger ? trigger.config.trigger.split(".")[0] : "prospect";
        },

        triggerFields() {
            return (this.catalog.triggers.find((t) => t.type === this.config.trigger) || {}).fields || [];
        },

        actionDef() {
            return this.catalog.actions.find((a) => a.type === this.config.action) || null;
        },

        actionGroups() {
            const groups = {};

            this.catalog.actions.forEach((action) => {
                (groups[action.category] = groups[action.category] || { category: action.category, actions: [] }).actions.push(action);
            });

            return Object.values(groups);
        },

        /**
         * The condition of a "If" step or of a filter trigger, as a tree.
         * (It sits at the top of the configuration: { op, items }.)
         */
        tree() {
            if (Array.isArray(this.config.items)) {
                return { op: this.config.op || "and", items: this.config.items };
            }

            // The first format: { mode: "all" | "any", filters: { name: value } }
            const filters = this.config.filters || {};

            return {
                op: this.config.mode === "any" ? "or" : "and",
                items: Object.entries(filters).map(([name, value]) => ({ filters: { [name]: value } })),
            };
        },

        hasValue() {
            const value = this.config.value;

            return value && typeof value === "object" && Object.keys(value).length > 0;
        },

        waitMode() {
            return this.config.until_date !== undefined ? "date" : this.config.until_time !== undefined ? "time" : "duration";
        },

        outputList() {
            return outputs(this.node).map((branch) => {
                const edge = this.graph.edges.find((e) => e.from === this.node.id && (e.branch || null) === branch);

                return { branch, to: edge ? edge.to : null };
            });
        },

        /**
         * Steps that can follow this one
         */
        candidates() {
            return this.graph.nodes.filter((n) => n.id !== this.node.id && n.type !== "trigger");
        },
    },

    methods: {
        slug,

        /**
         * Name of an action; the identifier itself when it has no translation yet
         */
        actionLabel(type) {
            const key = "workflow.action_type." + slug(type);

            return this.$te(key) ? this.$t(key) : type;
        },

        setConfig(patch) {
            this.replaceConfig({ ...this.config, ...patch });
        },

        replaceConfig(config) {
            this.pending = config;
            this.$emit("update", { config });
        },

        changeTrigger(type) {
            this.replaceConfig(triggerConfig(this.catalog, type));
        },

        /**
         * The explanation of a field, if there is one
         */
        help(field) {
            const key = "workflow.help." + this.fieldName(field);

            return this.$te(key) ? this.$t(key) : null;
        },

        /**
         * "value.user" => config.value.user
         */
        getPath(path) {
            return path.split(".").reduce((value, key) => (value == null ? undefined : value[key]), this.config);
        },

        setPath(path, value) {
            const [first, ...rest] = path.split(".");

            if (!rest.length) {
                this.setConfig({ [first]: value });
                return;
            }

            this.setConfig({ [first]: { ...(this.config[first] || {}), [rest.join(".")]: value } });
        },

        /**
         * "value.user" => "user": the name of the field in the translations
         */
        fieldName(field) {
            return field.key.split(".").pop();
        },

        chooseAction(type) {
            const def = this.catalog.actions.find((a) => a.type === type);

            // The legacy campaign actions keep their data in "value"
            this.replaceConfig(def && def.fields.some((f) => f.key.startsWith("value.")) ? { action: type, value: {} } : { action: type });
        },

        setTree(tree) {
            // Only the tree: the first format ("mode", "filters") is dropped
            const { mode, filters, ...rest } = this.config;

            this.replaceConfig({ ...rest, op: tree.op, items: tree.items });
        },

        setWaitMode(mode) {
            const config = {
                duration: { minutes: 30 },
                time: { until_time: "09:00", days: [1, 2, 3, 4, 5, 6, 7] },
                date: { until_date: "" },
            }[mode];

            this.replaceConfig(config);
        },

        cycle(id) {
            return wouldCreateCycle(this.graph, this.node.id, id);
        },

        label(node) {
            return node.name || `${this.$t("workflow.node." + node.type)} (${node.id})`;
        },
    },
};
</script>
