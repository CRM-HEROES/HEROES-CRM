<template>
    <div class="calls-settings-backdrop" @click.self="$emit('close')">
        <section
            class="calls-settings-panel"
            role="dialog"
            aria-modal="true"
            :aria-label="$t('project.calls_settings.title')"
        >
            <header class="calls-settings-header">
                <h2>{{ $t("project.calls_settings.title") }}</h2>
                <button type="button" class="calls-settings-close" @click="$emit('close')">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">{{ $t("project.calls_settings.close") }}</span>
                </button>
            </header>

            <div v-if="loading" class="calls-settings-loading">{{ $t("common.loading") }}</div>
            <form v-else @submit.prevent="save">
                <p class="calls-settings-description">{{ $t("project.calls_settings.description") }}</p>

                <div class="calls-settings-global">
                    <div>
                        <strong>
                            {{ $t("project.calls_settings.global_status", {
                                status: form.global_enabled
                                    ? $t("project.calls_settings.authorized")
                                    : $t("project.calls_settings.suspended")
                            }) }}
                        </strong>
                        <p>{{ $t("project.calls_settings.global_description") }}</p>
                    </div>
                    <label class="calls-settings-toggle">
                        <input
                            v-model="form.global_enabled"
                            type="checkbox"
                            :disabled="!canToggleGlobal"
                        />
                        <span>{{ $t("project.calls_settings.global_toggle") }}</span>
                    </label>
                </div>

                <label class="calls-settings-toggle calls-settings-project-toggle">
                    <input v-model="form.calls_enabled" type="checkbox" />
                    <span>{{ $t("project.calls_settings.project_enabled") }}</span>
                </label>

                <label class="calls-settings-field">
                    <span>{{ $t("project.calls_settings.provider") }}</span>
                    <select v-model="form.provider">
                        <option value="fake">{{ $t("project.calls_settings.providers.fake") }}</option>
                        <option value="cloudtalk">{{ $t("project.calls_settings.providers.cloudtalk") }}</option>
                        <option value="ai_voice">{{ $t("project.calls_settings.providers.ai_voice") }}</option>
                    </select>
                </label>
                <p class="calls-settings-description">
                    {{ $t("project.calls_settings.provider_priority") }}
                </p>

                <label class="calls-settings-field">
                    <span>{{ $t("project.calls_settings.timezone") }}</span>
                    <input v-model="form.timezone" type="text" required />
                </label>

                <fieldset class="calls-settings-field">
                    <legend>{{ $t("project.calls_settings.working_hours") }}</legend>
                    <div class="calls-settings-hours">
                        <label>
                            <span>{{ $t("project.calls_settings.start") }}</span>
                            <input v-model="form.work_hours.start" type="time" required />
                        </label>
                        <label>
                            <span>{{ $t("project.calls_settings.end") }}</span>
                            <input v-model="form.work_hours.end" type="time" required />
                        </label>
                    </div>
                    <div class="calls-settings-days">
                        <label v-for="day in days" :key="day.value">
                            <input v-model="form.work_days" type="checkbox" :value="day.value" />
                            <span>{{ $t(`project.calls_settings.days.${day.key}`) }}</span>
                        </label>
                    </div>
                </fieldset>

                <label class="calls-settings-field">
                    <span>{{ $t("project.calls_settings.greeting") }}</span>
                    <textarea v-model="form.greeting" rows="3" maxlength="4000"></textarea>
                </label>

                <label class="calls-settings-field">
                    <span>{{ $t("project.calls_settings.welcome_agent") }}</span>
                    <select v-model="form.call_agent_id">
                        <option :value="null">{{ $t("project.calls_settings.none") }}</option>
                        <option v-for="agent in options.agents" :key="agent.id" :value="agent.id">
                            {{ agent.name }}
                        </option>
                    </select>
                </label>

                <label class="calls-settings-field">
                    <span>{{ $t("project.calls_settings.transfer_adviser") }}</span>
                    <select v-model="form.call_transfer_user_id">
                        <option :value="null">{{ $t("project.calls_settings.none") }}</option>
                        <option v-for="user in options.users" :key="user.id" :value="user.id">
                            {{ [user.name, user.last_name].filter(Boolean).join(" ") }}
                        </option>
                    </select>
                </label>

                <label class="calls-settings-field">
                    <span>{{ $t("project.calls_settings.callback_label") }}</span>
                    <select v-model="form.call_back_label_id">
                        <option :value="null">{{ $t("project.calls_settings.none") }}</option>
                        <option v-for="label in options.labels" :key="label.id" :value="label.id">
                            {{ label.name }}
                        </option>
                    </select>
                </label>

                <label class="calls-settings-toggle">
                    <input v-model="form.transfer_available" type="checkbox" />
                    <span>{{ $t("project.calls_settings.manual_availability") }}</span>
                </label>

                <section class="calls-settings-diagnostic">
                    <h3>{{ $t("project.calls_settings.diagnostic_title") }}</h3>
                    <label class="calls-settings-field">
                        <span>{{ $t("project.calls_settings.prospect_id") }}</span>
                        <input v-model.number="diagnosticProspectId" type="number" min="1" />
                    </label>
                    <button
                        type="button"
                        class="btn btn-default"
                        :disabled="diagnosing || !diagnosticProspectId"
                        @click="diagnose"
                    >
                        {{ diagnosing ? $t("project.calls_settings.diagnosing") : $t("project.calls_settings.diagnose") }}
                    </button>
                    <p v-if="diagnosticError" class="calls-settings-error" role="alert">
                        {{ diagnosticError }}
                    </p>
                    <ul v-if="diagnostic" class="calls-settings-checks">
                        <li v-for="check in diagnostic.checks" :key="check.key">
                            <strong :class="check.passed ? 'check-passed' : 'check-failed'">
                                {{ check.passed ? "✓" : "✗" }}
                                {{ $t(`project.calls_settings.checks.${check.key}`) }}
                            </strong>
                            <span>{{ check.message }}</span>
                        </li>
                        <li>{{ $t("project.calls_settings.timezone_used", { timezone: diagnostic.timezone }) }}</li>
                    </ul>
                </section>

                <p v-if="error" class="calls-settings-error" role="alert">{{ error }}</p>
                <p v-if="saved" class="calls-settings-saved" role="status">{{ $t("project.calls_settings.saved") }}</p>

                <footer class="calls-settings-footer">
                    <button type="button" class="btn btn-default" @click="$emit('close')">
                        {{ $t("project.calls_settings.cancel") }}
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        {{ saving ? $t("project.calls_settings.saving") : $t("project.calls_settings.save") }}
                    </button>
                </footer>
            </form>
        </section>
    </div>
</template>

<script>
import SettingService from "@/apis/project/setting";

export default {
    data() {
        return {
            loading: true,
            saving: false,
            saved: false,
            error: "",
            diagnosticProspectId: "",
            diagnostic: null,
            diagnosticError: "",
            diagnosing: false,
            canToggleGlobal: false,
            options: {
                agents: [],
                users: [],
                labels: [],
            },
            days: [
                { key: "monday", value: 1 },
                { key: "tuesday", value: 2 },
                { key: "wednesday", value: 3 },
                { key: "thursday", value: 4 },
                { key: "friday", value: 5 },
                { key: "saturday", value: 6 },
                { key: "sunday", value: 7 },
            ],
            form: {
                global_enabled: false,
                calls_enabled: false,
                provider: "fake",
                timezone: "UTC",
                work_hours: { start: "08:00", end: "17:00" },
                work_days: [1, 2, 3, 4, 5],
                greeting: "",
                transfer_available: false,
                call_agent_id: null,
                call_transfer_user_id: null,
                call_back_label_id: null,
            },
        };
    },

    async created() {
        try {
            const { data } = await SettingService.show(this.$store.state.project.project.slug, "calls");
            this.form = {
                ...this.form,
                ...data,
                work_hours: { ...this.form.work_hours, ...data.work_hours },
                work_days: [...data.work_days],
                timezone: data.timezone || this.form.timezone,
            };
            this.options = data.options;
            this.canToggleGlobal = data.can_toggle_global;
        } catch (error) {
            this.error = error.response?.data?.message || this.$t("project.calls_settings.load_error");
        } finally {
            this.loading = false;
        }
    },

    methods: {
        async save() {
            this.saving = true;
            this.saved = false;
            this.error = "";

            try {
                const value = { ...this.form };
                if (!this.canToggleGlobal) {
                    delete value.global_enabled;
                }
                await SettingService.update(this.$store.state.project.project.slug, "calls", value);
                this.saved = true;
            } catch (error) {
                this.error = error.response?.data?.message || this.$t("project.calls_settings.save_error");
            } finally {
                this.saving = false;
            }
        },
        async diagnose() {
            this.diagnosing = true;
            this.diagnostic = null;
            this.diagnosticError = "";
            try {
                const { data } = await SettingService.diagnoseCalls(
                    this.$store.state.project.project.slug,
                    this.diagnosticProspectId
                );
                this.diagnostic = data;
            } catch (error) {
                this.diagnosticError =
                    error.response?.data?.message ||
                    this.$t("project.calls_settings.diagnostic_error");
            } finally {
                this.diagnosing = false;
            }
        },
    },
};
</script>

<style scoped>
.calls-settings-backdrop {
    position: fixed;
    z-index: 1200;
    inset: 0;
    display: flex;
    justify-content: flex-end;
    background: rgba(0, 0, 0, 0.35);
}

.calls-settings-panel {
    width: min(520px, 100%);
    height: 100%;
    overflow-y: auto;
    padding: 20px;
    background: white;
    box-shadow: -2px 0 12px rgba(0, 0, 0, 0.18);
}

.calls-settings-header,
.calls-settings-footer,
.calls-settings-global,
.calls-settings-hours,
.calls-settings-days {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.calls-settings-header {
    margin-bottom: 16px;
}

.calls-settings-header h2 {
    margin: 0;
    font-size: 20px;
}

.calls-settings-close {
    border: 0;
    background: transparent;
    font-size: 26px;
}

.calls-settings-description,
.calls-settings-global p {
    color: #666;
}

.calls-settings-global {
    padding: 12px;
    border: 1px solid #e4c879;
    border-radius: 5px;
    background: #fff9e8;
}

.calls-settings-global p {
    margin: 4px 0 0;
}

.calls-settings-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin: 16px 0;
    border: 0;
}

.calls-settings-field select,
.calls-settings-field input,
.calls-settings-field textarea {
    width: 100%;
    padding: 7px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.calls-settings-hours > label,
.calls-settings-days > label,
.calls-settings-toggle {
    display: flex;
    align-items: center;
    gap: 7px;
}

.calls-settings-days {
    flex-wrap: wrap;
    justify-content: flex-start;
    margin-top: 12px;
}

.calls-settings-days input,
.calls-settings-toggle input {
    width: auto;
}

.calls-settings-project-toggle {
    margin: 16px 0;
}

.calls-settings-error {
    color: #b42318;
}

.calls-settings-saved {
    color: #27833b;
}

.calls-settings-diagnostic {
    margin-top: 20px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.calls-settings-diagnostic h3 {
    margin-top: 0;
}

.calls-settings-checks {
    padding-left: 0;
    list-style: none;
}

.calls-settings-checks li {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-top: 8px;
}

.check-passed {
    color: #27833b;
}

.check-failed {
    color: #b42318;
}

.calls-settings-footer {
    justify-content: flex-end;
    margin-top: 20px;
}
</style>
