<template>
    <div class="hc-wff">
        <!-- Yes / no -->
        <label v-if="type === 'boolean'" class="hc-wff-check">
            <input type="checkbox" :checked="!!modelValue" :disabled="readOnly" @change="$emit('update:modelValue', $event.target.checked)" />
            <span v-text="$t('workflow.yes')"></span>
        </label>

        <!-- Numbers -->
        <input
            v-else-if="type === 'number' || type === 'minutes'"
            type="number"
            min="0"
            :value="modelValue"
            :disabled="readOnly"
            @input="$emit('update:modelValue', $event.target.value === '' ? null : Number($event.target.value))"
        />

        <!-- The email: written as in a word processor -->
        <email-editor
            v-else-if="type === 'html'"
            :model-value="modelValue || ''"
            :variables="variables"
            :subject="subject"
            :design="design"
            :project-name="project ? project.name : ''"
            :read-only="readOnly"
            @update:model-value="$emit('update:modelValue', $event)"
            @update:design="$emit('update:design', $event)"
        />

        <!-- One line (the subject of an email) -->
        <input
            v-else-if="type === 'line'"
            type="text"
            maxlength="255"
            :value="modelValue"
            :placeholder="$t('workflow.line_placeholder.' + field.key)"
            :disabled="readOnly"
            @input="$emit('update:modelValue', $event.target.value)"
        />

        <!-- Texts -->
        <template v-else-if="type === 'text'">
            <textarea
                ref="text"
                rows="5"
                maxlength="5000"
                :value="modelValue"
                :disabled="readOnly"
                @input="$emit('update:modelValue', $event.target.value)"
            ></textarea>
            <div v-if="!readOnly" class="hc-wff-variables">
                <span v-text="$t('workflow.variables')"></span>
                <button
                    v-for="variable in variableList"
                    :key="variable"
                    type="button"
                    @click="insertVariable(variable)"
                    v-text="variable"
                ></button>
            </div>
        </template>

        <!-- One or several items of the project -->
        <select
            v-else-if="isSingle"
            :value="modelValue"
            :disabled="readOnly"
            @change="$emit('update:modelValue', $event.target.value === '' ? null : Number($event.target.value))"
        >
            <option value="" v-text="emptyLabel"></option>
            <option v-for="option in options" :key="option.id" :value="option.id" v-text="option.label"></option>
        </select>

        <!-- Several items: a click on a line ticks it, no key to hold -->
        <div v-else-if="isMultiple" class="hc-wff-list">
            <label v-for="option in options" :key="option.id" class="hc-wff-item" :class="{ on: isChosen(option.id) }">
                <input type="checkbox" :checked="isChosen(option.id)" :disabled="readOnly" @change="toggleId(option.id)" />
                <span v-text="option.label"></span>
            </label>
            <p v-if="!emptyList" class="hc-wff-count" v-text="$t('workflow.chosen_count', { count: (modelValue || []).length })"></p>
        </div>

        <!-- An operator of the project -->
        <template v-else-if="type === 'provider'">
            <select ref="provider" :value="modelValue" :disabled="readOnly" @change="chooseProvider($event.target.value)">
                <option value="" v-text="$t('workflow.choose')"></option>
                <option
                    v-if="modelValue && !providers.some((p) => p.ref === modelValue)"
                    :value="modelValue"
                    v-text="$t('workflow.provider_missing')"
                ></option>
                <option v-for="provider in providers" :key="provider.ref" :value="provider.ref" v-text="providerLabel(provider)"></option>
            </select>
            <p v-if="unconfiguredChosen" class="hc-wff-count" v-text="$t('workflow.provider_setup.waiting')"></p>
            <p v-if="!providersLoading && !providers.length" class="hc-wff-warn" v-text="$t('workflow.no_provider.' + field.capability)"></p>
        </template>

        <!-- What happens when the step fails -->
        <select v-else-if="type === 'on_error'" :value="modelValue || 'fail'" :disabled="readOnly" @change="$emit('update:modelValue', $event.target.value)">
            <option value="fail" v-text="$t('workflow.on_error.fail')"></option>
            <option value="continue" v-text="$t('workflow.on_error.continue')"></option>
        </select>

        <input v-else-if="type === 'time'" type="time" :value="modelValue" :disabled="readOnly" @input="$emit('update:modelValue', $event.target.value)" />
        <input v-else-if="type === 'date'" type="date" :value="modelValue" :disabled="readOnly" @input="$emit('update:modelValue', $event.target.value || null)" />

        <div v-else-if="type === 'weekdays'" class="hc-wff-days">
            <label v-for="day in 7" :key="day" class="hc-wff-check">
                <input type="checkbox" :checked="(modelValue || allDays).includes(day)" :disabled="readOnly" @change="toggleDay(day)" />
                <span v-text="$t('workflow.weekday.' + day)"></span>
            </label>
        </div>

        <input v-else type="text" :value="modelValue" :disabled="readOnly" @input="$emit('update:modelValue', $event.target.value)" />

        <!-- Not part of the chain above: it would take the place of its last "else" -->
        <p v-if="isLabels && !labelsLoaded" class="hc-wff-count" v-text="$t('workflow.loading')"></p>
        <!-- A channel is needed to send emails: offer to create it right here -->
        <div v-if="emptyList && type === 'thread'" class="hc-wff-create">
            <p class="hc-wff-warn" v-text="$t('workflow.thread_create.why')"></p>
            <template v-if="canAddThread && !readOnly">
                <input v-model="newThreadName" type="text" maxlength="50" :placeholder="$t('workflow.thread_create.placeholder')" @keydown.enter.prevent="createThread" />
                <button type="button" :disabled="!newThreadName.trim() || creatingThread" @click="createThread" v-text="$t('workflow.thread_create.button')"></button>
            </template>
            <p v-else class="hc-wff-count" v-text="$t('workflow.thread_create.ask_admin')"></p>
        </div>
        <p v-else-if="emptyList" class="hc-wff-warn" v-text="$t('workflow.empty_list.' + type)"></p>
    </div>
</template>

<style>
.hc-wff select,
.hc-wff input[type="text"],
.hc-wff input[type="number"],
.hc-wff input[type="time"],
.hc-wff input[type="date"],
.hc-wff textarea {
    width: 100%;
    box-sizing: border-box;
}

.hc-wff select,
.hc-wff input[type="text"],
.hc-wff input[type="number"],
.hc-wff input[type="time"],
.hc-wff input[type="date"],
.hc-wff textarea {
    padding: 7px 10px;
    color: #2b2f3a;
    background: #fff;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
    font-size: 13px;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.hc-wff select:focus,
.hc-wff input:focus,
.hc-wff textarea:focus {
    border-color: #5b6cf0;
    box-shadow: 0 0 0 3px rgba(91, 108, 240, 0.15);
    outline: 0;
}

.hc-wff-check {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-right: 8px;
    font-size: 13px;
    cursor: pointer;
}

.hc-wff-list {
    max-height: 190px;
    overflow: auto;
    background: #fff;
    border: 1px solid #d5d9e4;
    border-radius: 8px;
}

.hc-wff-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding: 7px 10px;
    border-bottom: 1px solid #f0f1f6;
    font-size: 13px;
    cursor: pointer;
}

.hc-wff-item:hover {
    background: #f5f6fe;
}

.hc-wff-item.on {
    color: #3a49c9;
    background: #eceefd;
    font-weight: 600;
}

.hc-wff-count {
    position: sticky;
    bottom: 0;
    margin: 0;
    padding: 4px 10px;
    color: #7a7f90;
    background: #fafbfd;
    font-size: 11px;
}

.hc-wff-variables {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    margin-top: 6px;
    color: #7a7f90;
    font-size: 11px;
}

.hc-wff-variables button {
    padding: 2px 8px;
    color: #3a49c9;
    background: #eceefd;
    border: 0;
    border-radius: 10px;
    font-size: 11px;
    cursor: pointer;
}

.hc-wff-variables button:hover {
    background: #dde1fb;
}

.hc-wff-create {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 6px;
}

.hc-wff-create button {
    align-self: flex-start;
    padding: 6px 14px;
    color: #fff;
    background: #4b5ce8;
    border: 0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.hc-wff-create button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.hc-wff-create .hc-wff-warn {
    margin: 0;
}

.hc-wff-warn {
    margin: 4px 0 0;
    padding: 6px 10px;
    color: #8a5a00;
    background: #fff7dd;
    border-radius: 6px;
    font-size: 12px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import WorkflowService from "@/apis/project/workflow";
import EmailEditor from "./EmailEditor.vue";
import LabelService from "@/apis/project/label";
import { FETCH_USERS } from "@/actions/project/user";
import { FETCH_GROUPS } from "@/actions/project/group";
import { ADD_THREAD, FETCH_THREADS } from "@/actions/project/thread";
import { OPEN_MODAL } from "@/actions/modal";
import { FETCH_CATEGORIES } from "@/actions/project/category";

const ALL_DAYS = [1, 2, 3, 4, 5, 6, 7];

export default {
    components: { EmailEditor },

    props: {
        modelValue: { default: null },
        // boolean, number, minutes, text, html, user(s), group(s), label(s), thread, provider, on_error, time, date, weekdays
        type: { type: String, required: true },
        // The field of the catalog (its capability, for an operator)
        field: { type: Object, default: () => ({}) },
        // { prospect: [...], user: [...], project: [...] }
        variables: { type: Object, default: () => ({}) },
        // The subject of the email, shown in its preview
        subject: { type: String, default: "" },
        // The look of the email (null: the usual look of the CRM)
        design: { type: Object, default: null },
        readOnly: { type: Boolean, default: false },
    },

    emits: ["update:modelValue", "update:design", "provider-label"],

    data() {
        return {
            providers: [],
            providersLoading: false,
            // The operator being configured in its window: chosen when it is done
            pendingProvider: null,
            labels: [],
            labelsLoaded: false,
            loadingLists: false,
            newThreadName: this.$t("workflow.thread_create.default_name"),
            creatingThread: false,
            allDays: ALL_DAYS,
        };
    },

    computed: {
        ...mapGetters(["project", "users", "groups", "threads", "categories", "can", "modals"]),

        isSingle() {
            return ["user", "group", "label", "thread"].includes(this.type);
        },

        canAddThread() {
            return this.can("all.project.thread.add");
        },

        /**
         * First option of the list: for a channel, what happens when none is chosen
         */
        emptyLabel() {
            if (this.type !== "thread") {
                return this.$t("workflow.choose");
            }

            return this.options.length ? this.$t("workflow.thread_create.automatic", { name: this.options[0].label }) : this.$t("workflow.choose");
        },

        /**
         * The operator in the list is one that still has to be configured: say what is awaited
         */
        unconfiguredChosen() {
            return !!this.pendingProvider;
        },

        isLabels() {
            return this.type === "label" || this.type === "labels";
        },

        isMultiple() {
            return ["users", "groups", "labels"].includes(this.type);
        },

        /**
         * Items of the project to choose from
         */
        options() {
            switch (this.type) {
                case "user":
                case "users":
                    return this.users.map((u) => ({ id: u.id, label: [u.name, u.last_name].filter(Boolean).join(" ") }));
                case "group":
                case "groups":
                    return this.groups.map((g) => ({ id: g.id, label: g.name }));
                case "thread":
                    // An email is not sent through a private channel
                    return this.threads.filter((t) => !t.private).map((t) => ({ id: t.id, label: t.name }));
                case "label":
                case "labels":
                    return this.labels;
            }

            return [];
        },

        /**
         * Nothing to choose from (once loaded): said, instead of an empty list
         */
        emptyList() {
            if (!this.isSingle && !this.isMultiple) {
                return false;
            }

            const loaded = this.type === "label" || this.type === "labels" ? this.labelsLoaded : !this.loadingLists;

            return loaded && !this.options.length;
        },

        variableList() {
            return Object.entries(this.variables).flatMap(([group, names]) => names.map((name) => `{{${group}.${name}}}`));
        },
    },

    watch: {
        // The window of the operator was closed: is it configured now?
        modals(list) {
            if (this.pendingProvider && !list.includes(this.pendingProvider.setup)) {
                this.afterSetup();
            }
        },

        // The same field can change of type (another criterion) or get its categories late
        type() {
            this.refresh();
        },

        // Another action (SMS instead of email): the operators are not the same
        "field.capability"() {
            if (this.type === "provider") {
                this.providers = [];
                this.loadProviders();
            }
        },

        categories() {
            if (this.isLabels) {
                this.loadLabels();
            }
        },
    },

    async created() {
        if (this.type === "provider") {
            this.loadProviders();
        }

        // The lists of the project are not loaded for this page: load what is needed
        await this.refresh();
    },

    methods: {
        async refresh() {
            this.labelsLoaded = false;

            await this.ensureLoaded();

            if (this.isLabels) {
                this.loadLabels();
            }
        },

        async ensureLoaded() {
            const needs = [];

            if (["user", "users"].includes(this.type) && !this.users.length) needs.push(FETCH_USERS);
            if (["group", "groups"].includes(this.type) && !this.groups.length) needs.push(FETCH_GROUPS);
            if (this.type === "thread" && !this.threads.length) needs.push(FETCH_THREADS);
            if (["label", "labels"].includes(this.type) && !this.categories.length) needs.push(FETCH_CATEGORIES);

            if (!needs.length) {
                return;
            }

            this.loadingLists = true;

            try {
                await Promise.all(needs.map((action) => store.dispatch(action)));
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                this.loadingLists = false;
            }
        },

        /**
         * A public channel made on the spot, and chosen
         */
        async createThread() {
            const name = this.newThreadName.trim();

            if (!name || this.creatingThread) {
                return;
            }

            this.creatingThread = true;

            try {
                const thread = await store.dispatch(ADD_THREAD, { name, private: false });
                this.$emit("update:modelValue", thread.id);
                flash({ title: this.$t("workflow.title"), body: this.$t("workflow.thread_create.done", { name }) });
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.thread_create.error") });
            } finally {
                this.creatingThread = false;
            }
        },

        isChosen(id) {
            return (this.modelValue || []).includes(id);
        },

        toggleId(id) {
            const ids = new Set(this.modelValue || []);

            ids.has(id) ? ids.delete(id) : ids.add(id);

            this.$emit("update:modelValue", [...ids]);
        },

        toggleDay(day) {
            const days = new Set(this.modelValue || ALL_DAYS);

            days.has(day) ? days.delete(day) : days.add(day);

            this.$emit("update:modelValue", [...days].sort());
        },

        /**
         * Chosen operator: the node keeps its reference, and its name for the messages
         */
        chooseProvider(ref) {
            const provider = this.providers.find((p) => p.ref === ref);

            // Not configured yet: its window opens to configure it, the choice is made once it is
            if (provider && provider.configured === false) {
                this.setupProvider(provider);
                return;
            }

            this.$emit("update:modelValue", ref || null);
            this.$emit("provider-label", provider?.label || null);
        },

        providerLabel(provider) {
            return provider.configured === false ? `${provider.label} (${this.$t("workflow.provider_setup.to_configure")})` : provider.label;
        },

        /**
         * Opens the window of the CRM that configures the operator (the same as in the settings)
         */
        setupProvider(provider) {
            // The list shows what is really chosen, not the operator clicked
            if (this.$refs.provider) {
                this.$refs.provider.value = this.modelValue || "";
            }

            if (!provider.setup) {
                flashWarning({ title: this.$t("workflow.title"), body: this.$t("workflow.provider_setup.no_window", { name: provider.label }) });
                return;
            }

            this.pendingProvider = provider;
            store.commit(OPEN_MODAL, provider.setup);
        },

        /**
         * Back from the window: the operator is chosen if it is configured now
         */
        async afterSetup() {
            const pending = this.pendingProvider;
            this.pendingProvider = null;

            await this.loadProviders();

            const provider = this.providers.find((p) => p.ref === pending.ref);

            if (provider && provider.configured) {
                this.chooseProvider(provider.ref);
                flash({ title: this.$t("workflow.title"), body: this.$t("workflow.provider_setup.done", { name: provider.label }) });
            } else {
                flashWarning({ title: this.$t("workflow.title"), body: this.$t("workflow.provider_setup.not_done", { name: pending.label }) });
            }
        },

        insertVariable(variable) {
            const area = this.$refs.text;
            const text = this.modelValue || "";
            const start = area ? area.selectionStart : text.length;
            const end = area ? area.selectionEnd : text.length;

            this.$emit("update:modelValue", text.slice(0, start) + variable + text.slice(end));
        },

        async loadProviders() {
            this.providersLoading = true;
            const capability = this.field.capability;

            try {
                const { data } = await WorkflowService.providers(this.project.slug, capability, true);

                // The answer of a previous choice that comes late is not the one wanted
                if (capability !== this.field.capability) {
                    return;
                }

                this.providers = data;
            } catch (e) {
                flashError({ title: this.$t("workflow.title"), body: this.$t("workflow.error.load") });
            } finally {
                if (capability === this.field.capability) {
                    this.providersLoading = false;
                }
            }
        },

        /**
         * Labels belong to categories: the ones of the prospects, all of them
         */
        async loadLabels() {
            const categories = this.categories.filter((c) => c.for === "prospect");

            const lists = await Promise.all(
                categories.map(async (category) => {
                    try {
                        const { data } = await LabelService.get(this.project.slug, category.id);
                        return data.map((label) => ({ id: label.id, label: `${category.name} / ${label.name}` }));
                    } catch (e) {
                        return [];
                    }
                })
            );

            this.labels = lists.flat();
            this.labelsLoaded = true;
        },
    },
};
</script>
