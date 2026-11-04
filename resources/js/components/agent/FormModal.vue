<template>
    <modal
        name="agent-form"
        :title="$t(editing ? 'agent.edit_title' : 'agent.new')"
        @open="load"
    >
        <form class="hc-flex-column" @submit.prevent="submit">
            <ol class="hc-agent-steps" role="list">
                <li
                    v-for="(key, i) in steps"
                    :key="key"
                    :class="{ 'is-current': i === step, 'is-done': i < step }"
                    :aria-current="i === step ? 'step' : null"
                >
                    <button
                        type="button"
                        :disabled="!canGoTo(i)"
                        @click="goTo(i)"
                    >
                        <span class="hc-agent-step-number">{{ i + 1 }}</span>
                        {{ $t(`agent.step.${key}`) }}
                    </button>
                </li>
            </ol>

            <item-list gap="8px" padding="10px 0">
                <p v-if="error" class="hc-agent-form-error" role="alert">
                    {{ error }}
                </p>

                <!-- 1. Who is it -->
                <template v-if="current === 'identity'">
                    <p class="hc-agent-form-intro">
                        {{ $t("agent.step_intro.identity") }}
                    </p>
                    <div class="hc-agent-photo">
                        <img
                            v-if="photoPreview"
                            :src="photoPreview"
                            :alt="$t('agent.photo')"
                        />
                        <icon v-else class="fa fa-robot" :size="40" />
                        <div class="hc-agent-photo-actions">
                            <label class="hc-agent-photo-choose">
                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                    :disabled="saving"
                                    @change="pickPhoto"
                                />
                                {{ $t(photoPreview ? "agent.photo_change" : "agent.photo_add") }}
                            </label>
                            <ag-button
                                v-if="photoPreview"
                                variant="ghost"
                                size="sm"
                                :disabled="saving"
                                @click="removePhoto"
                            >
                                {{ $t("agent.photo_remove") }}
                            </ag-button>
                            <span class="hc-agent-photo-hint">
                                {{ $t("agent.photo_hint") }}
                            </span>
                        </div>
                    </div>
                    <v-field :label="$t('agent.name')" required>
                        <input
                            v-model="form.name"
                            required
                            maxlength="100"
                            :placeholder="$t('agent.name_placeholder')"
                        />
                    </v-field>
                    <v-field :label="$t('agent.specialty')">
                        <input
                            v-model="form.specialty"
                            maxlength="100"
                            :placeholder="$t('agent.specialty_placeholder')"
                        />
                    </v-field>
                    <v-field :label="$t('agent.field_description')">
                        <textarea
                            v-model="form.description"
                            rows="3"
                            maxlength="1000"
                        ></textarea>
                    </v-field>
                </template>

                <!-- 2. What it must do -->
                <template v-else-if="current === 'mission'">
                    <p class="hc-agent-form-intro">
                        {{ $t("agent.step_intro.mission") }}
                    </p>
                    <v-field :label="$t('agent.instructions')">
                        <textarea
                            v-model="form.instructions"
                            rows="7"
                            maxlength="20000"
                            :placeholder="$t('agent.instructions_placeholder')"
                        ></textarea>
                    </v-field>
                    <v-field :label="$t('agent.objectives')">
                        <textarea
                            v-model="form.objectives"
                            rows="2"
                            maxlength="2000"
                        ></textarea>
                    </v-field>
                    <v-field :label="$t('agent.persona')">
                        <textarea
                            v-model="form.persona"
                            rows="2"
                            maxlength="2000"
                            :placeholder="$t('agent.persona_placeholder')"
                        ></textarea>
                    </v-field>
                </template>

                <!-- 3. How free it is, and what will be created -->
                <template v-else>
                    <p class="hc-agent-form-intro">
                        {{ $t("agent.step_intro.autonomy") }}
                    </p>
                    <label
                        v-for="level in autonomyLevels"
                        :key="level.value"
                        class="hc-agent-autonomy"
                        :class="{ 'is-selected': form.autonomy === level.value }"
                    >
                        <input
                            type="radio"
                            name="agent-autonomy"
                            :value="level.value"
                            v-model="form.autonomy"
                        />
                        <span>
                            <strong v-text="$t(`agent.autonomy_short.${level.value}`)"></strong>
                            <span class="hc-agent-autonomy-detail" v-text="level.label"></span>
                        </span>
                    </label>

                    <section class="hc-agent-recap" aria-live="polite">
                        <h4 v-text="$t('agent.recap')"></h4>
                        <dl>
                            <dt v-text="$t('agent.name')"></dt>
                            <dd v-text="form.name"></dd>
                            <template v-if="form.specialty">
                                <dt v-text="$t('agent.specialty')"></dt>
                                <dd v-text="form.specialty"></dd>
                            </template>
                            <dt v-text="$t('agent.scope')"></dt>
                            <dd v-text="scopeText"></dd>
                        </dl>
                        <p>{{ $t("agent.knows_all") }}</p>
                    </section>
                </template>
            </item-list>

            <div class="hc-agent-form-footer">
                <ag-button
                    v-if="editing"
                    variant="ghost-danger"
                    icon="fa fa-trash"
                    :disabled="saving"
                    @click="remove"
                >
                    {{ $t("agent.delete") }}
                </ag-button>
                <span class="hc-agent-form-spacer"></span>
                <ag-button
                    v-if="step > 0"
                    variant="ghost"
                    icon="fa fa-arrow-left"
                    :disabled="saving"
                    @click="back"
                >
                    {{ $t("agent.back") }}
                </ag-button>
                <ag-button v-if="!last" type="submit" variant="primary" :disabled="!canNext">
                    {{ $t("agent.next") }}
                </ag-button>
                <ag-button
                    v-else
                    type="submit"
                    variant="primary"
                    icon="fa fa-check"
                    :loading="saving"
                    :disabled="!canSave"
                >
                    {{ $t(saving ? "agent.saving" : editing ? "agent.save" : "agent.create") }}
                </ag-button>
            </div>
            <loading :loading="loading || saving" />
        </form>
    </modal>
</template>

<style>
.hc-agent-steps {
    display: flex;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0 0 6px;
}

.hc-agent-steps li {
    flex: 1;
}

.hc-agent-steps button {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 4px 10px;
    border: 0;
    border-bottom: 3px solid var(--ag-line);
    background: transparent;
    color: var(--ag-muted);
    cursor: pointer;
    font: inherit;
    font-size: 13px;
    transition: color 0.15s ease, border-color 0.15s ease;
}

.hc-agent-steps button:disabled {
    cursor: default;
}

.hc-agent-steps button:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
    border-radius: 6px;
}

.hc-agent-steps li.is-done button {
    border-bottom-color: var(--ag-success);
    color: var(--ag-ink-soft);
}

.hc-agent-steps li.is-current button {
    border-bottom-color: var(--ag-accent);
    color: var(--ag-ink);
    font-weight: 600;
}

.hc-agent-step-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--ag-line);
    color: var(--ag-ink-soft);
    font-size: 12px;
    font-weight: 700;
}

.hc-agent-steps li.is-done .hc-agent-step-number {
    background: var(--ag-success);
    color: #fff;
}

.hc-agent-steps li.is-current .hc-agent-step-number {
    background: var(--ag-accent);
    color: #fff;
}

.hc-agent-form-error {
    margin: 0;
    padding: 9px 12px;
    border-radius: 8px;
    background: var(--ag-danger-soft);
    color: var(--ag-danger-strong);
    font-size: 13px;
}

.hc-agent-form-intro {
    color: var(--ag-muted);
    font-size: 13px;
    line-height: 1.45;
    margin: 0;
}

.hc-agent-photo {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 14px;
    border: 1px solid var(--ag-line);
    border-radius: var(--ag-radius);
    background: var(--ag-surface);
}

.hc-agent-photo > img,
.hc-agent-photo > .fa {
    width: 76px;
    height: 76px;
    flex: 0 0 76px;
    border-radius: 50%;
    object-fit: cover;
    background: #e2e8f0;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.15);
}

.hc-agent-photo-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

.hc-agent-photo-choose {
    display: inline-flex;
    align-items: center;
    padding: 7px 14px;
    border: 1px solid var(--ag-line);
    border-radius: 8px;
    background: #fff;
    color: var(--ag-ink-soft);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: border-color 0.15s ease, background-color 0.15s ease;
}

.hc-agent-photo-choose:hover {
    border-color: #cbd5e1;
    background: var(--ag-surface);
}

.hc-agent-photo-choose:focus-within {
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
}

.hc-agent-photo-choose input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    overflow: hidden;
}

.hc-agent-photo-hint {
    flex-basis: 100%;
    color: var(--ag-muted);
    font-size: 12px;
}

.hc-agent-autonomy {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 12px 14px;
    border: 1px solid var(--ag-line);
    border-radius: var(--ag-radius);
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.hc-agent-autonomy:hover {
    border-color: #cbd5e1;
}

.hc-agent-autonomy.is-selected {
    border-color: var(--ag-accent);
    background: var(--ag-accent-soft);
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.12);
}

.hc-agent-autonomy input {
    margin-top: 3px;
    accent-color: var(--ag-accent);
}

.hc-agent-autonomy-detail {
    display: block;
    margin-top: 2px;
    color: var(--ag-muted);
    font-size: 13px;
    line-height: 1.4;
}

.hc-agent-recap {
    border: 1px solid var(--ag-line);
    border-radius: var(--ag-radius);
    background: var(--ag-surface);
    padding: 12px 14px;
    font-size: 13px;
}

.hc-agent-recap h4 {
    margin: 0 0 8px;
    font-size: 11px;
    font-weight: 700;
    color: var(--ag-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.hc-agent-recap dl {
    display: grid;
    grid-template-columns: max-content 1fr;
    gap: 4px 14px;
    margin: 0 0 10px;
}

.hc-agent-recap dt {
    color: var(--ag-muted);
}

.hc-agent-recap dd {
    margin: 0;
    color: var(--ag-ink);
    font-weight: 600;
}

.hc-agent-recap p {
    margin: 0;
    color: var(--ag-ink-soft);
    line-height: 1.45;
}

.hc-agent-form-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-top: 12px;
    margin-top: 4px;
    border-top: 1px solid var(--ag-line);
}

.hc-agent-form-spacer {
    flex: 1;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import AgentService from "@/apis/agent";
import AgButton from "./ui/AgentButton.vue";

// Actions
import { CLOSE_MODAL } from "@/actions/modal";

const PHOTO_TYPES = ["image/jpeg", "image/png", "image/gif", "image/webp"];
const PHOTO_MAX_BYTES = 2 * 1024 * 1024;

const FIELDS = [
    "name",
    "specialty",
    "description",
    "persona",
    "instructions",
    "objectives",
    "autonomy",
];

/**
 * Creation of an AI agent in three steps: who it is, what it must do, how free
 * it is.
 *
 * Nothing technical is asked. The model is the default one, the agent knows
 * every function of the CRM (the instructions guide what it does with them),
 * and it works in the project where it is created, with the rights its owner
 * has in that project: all of this is decided by the server, not by this form.
 */
export default {
    components: { AgButton },

    data() {
        return {
            steps: ["identity", "mission", "autonomy"],
            step: 0,
            catalog: {},
            form: this.blank(),
            loading: false,
            saving: false,
            error: "",
            // The picture: the file chosen (sent when saving), the address shown, and a removal asked
            photo: null,
            photoPreview: null,
            photoRemoved: false,
        };
    },

    computed: {
        ...mapGetters(["project", "agentEditing"]),

        editing() {
            return !!this.agentEditing;
        },

        current() {
            return this.steps[this.step];
        },

        last() {
            return this.step === this.steps.length - 1;
        },

        // The first step cannot be left without a name
        canNext() {
            return !this.loading && !!this.form.name.trim();
        },

        canSave() {
            return !!this.form.name.trim() && !this.saving && !this.loading;
        },

        autonomyLevels() {
            return Object.entries(this.catalog.autonomies || {}).map(
                ([value, label]) => ({ value, label })
            );
        },

        // An existing agent keeps the project it was created in
        scopeText() {
            const project = this.agentEditing?.project || this.project;

            return this.$t("agent.this_project", { name: project.name });
        },
    },

    methods: {
        blank() {
            return {
                name: "",
                specialty: "",
                description: "",
                persona: "",
                instructions: "",
                objectives: "",
                // Nothing is changed in the CRM without the user's validation
                autonomy: "propose",
            };
        },

        message(e) {
            const errors = e?.response?.data?.errors;
            const first = errors ? Object.values(errors).flat()[0] : null;

            return first || e?.response?.data?.message || this.$t("agent.error");
        },

        // Picture

        pickPhoto(event) {
            const file = event.target.files && event.target.files[0];
            // The same file can be chosen again afterwards
            event.target.value = "";

            if (!file) return;

            if (!PHOTO_TYPES.includes(file.type)) {
                this.error = this.$t("agent.photo_type");
                return;
            }

            if (file.size > PHOTO_MAX_BYTES) {
                this.error = this.$t("agent.photo_size");
                return;
            }

            this.error = "";
            this.releasePreview();
            this.photo = file;
            this.photoRemoved = false;
            this.photoPreview = URL.createObjectURL(file);
        },

        removePhoto() {
            this.releasePreview();
            this.photo = null;
            this.photoPreview = null;
            // Only an existing picture has to be removed on the server
            this.photoRemoved = !!this.agentEditing?.avatar_url;
        },

        releasePreview() {
            if (this.photo && this.photoPreview) URL.revokeObjectURL(this.photoPreview);
        },

        // Send the picture chosen, or remove the one that was taken off
        async savePhoto(id) {
            if (this.photo) {
                await AgentService.uploadAvatar(id, this.photo);
            } else if (this.photoRemoved) {
                await AgentService.removeAvatar(id);
            }
        },

        // Steps

        canGoTo(i) {
            // Back is always possible; forward only once the name is there
            return !this.saving && (i <= this.step || !!this.form.name.trim());
        },

        goTo(i) {
            if (this.canGoTo(i)) this.step = i;
        },

        back() {
            if (this.step > 0) this.step--;
        },

        next() {
            if (this.canNext && !this.last) this.step++;
        },

        // Enter moves forward; only the last step creates the agent
        submit() {
            return this.last ? this.save() : this.next();
        },

        // Load

        async load() {
            this.error = "";
            this.step = 0;
            this.loading = true;
            this.form = this.blank();
            this.releasePreview();
            this.photo = null;
            this.photoPreview = null;
            this.photoRemoved = false;

            try {
                if (!this.catalog.autonomies) {
                    this.catalog = (await AgentService.catalog()).data;
                }

                if (this.agentEditing) {
                    // The list only has the summary: read the whole agent
                    const { data } = await AgentService.get(this.agentEditing.id);
                    this.form = {
                        ...this.form,
                        ...Object.fromEntries(
                            FIELDS.map((key) => [key, data[key] ?? this.form[key]])
                        ),
                    };
                    this.photoPreview = data.avatar_url || null;
                }
            } catch (e) {
                this.error = this.message(e);
            } finally {
                this.loading = false;
            }
        },

        // Save

        async save() {
            if (!this.canSave || !this.last) return;

            this.error = "";
            this.saving = true;

            const body = Object.fromEntries(FIELDS.map((key) => [key, this.form[key]]));

            let saved = this.agentEditing;

            try {
                if (this.agentEditing) {
                    // The model, the project and the skills are not asked: they stay as they are
                    await AgentService.update(this.agentEditing.id, body);
                } else {
                    // The agent belongs to the project where it is created
                    saved = (await AgentService.create({ ...body, project: this.project.slug })).data;
                }

                try {
                    await this.savePhoto(saved.id);
                } catch (e) {
                    if (!this.agentEditing) {
                        // The agent exists, only its picture failed: from now on this window
                        // edits it, so a retry updates it and never creates a second one
                        store.commit("SET_AGENT_EDITING", saved);
                        await store.dispatch("FETCH_AGENTS").catch(() => {});
                    }

                    throw e;
                }

                await store.dispatch("FETCH_AGENTS");
                store.commit(CLOSE_MODAL);
            } catch (e) {
                this.error = this.message(e);
            } finally {
                this.saving = false;
            }
        },

        async remove() {
            if (!confirm(this.$t("agent.confirm_delete", { name: this.form.name }))) {
                return;
            }

            this.saving = true;

            try {
                await AgentService.remove(this.agentEditing.id);
                await store.dispatch("FETCH_AGENTS");
                store.commit(CLOSE_MODAL);
            } catch (e) {
                this.error = this.message(e);
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>
