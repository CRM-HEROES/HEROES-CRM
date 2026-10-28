<template>
    <modal :name="name" title="claude.ai" @open="showSetting" @closed="closeSetting">
        <form class="hc-flex-column" @submit.prevent="update">
            <item-list gap="10px" padding="10px 0">
                <section class="hc-claude-help" aria-labelledby="claude-api-help-title">
                    <h4 id="claude-api-help-title">{{ $t('setting.claude.help_title') }}</h4>
                    <a href="https://platform.claude.com/settings/keys" target="_blank" rel="noopener noreferrer">
                        {{ $t('setting.claude.help_link') }}
                    </a>
                    <ol>
                        <li>{{ $t('setting.claude.help_sign_in') }}</li>
                        <li>{{ $t('setting.claude.help_create') }}</li>
                        <li>{{ $t('setting.claude.help_details') }}</li>
                        <li>{{ $t('setting.claude.help_paste') }}</li>
                    </ol>
                    <p>{{ $t('setting.claude.help_once') }}</p>
                </section>
                <p v-if="showingSetting" class="hc-claude-hint">{{ $t('setting.claude.checking') }}</p>
                <p class="hc-claude-status" v-if="loaded && !showingSetting"
                    :class="{ 'is-valid': keyValid === true, 'is-invalid': keyValid === false, 'is-unknown': configured && keyValid === null }">
                    {{ keyStatusMessage }}
                </p>
                <v-field :label="$t('setting.claude.api_key')" required>
                    <input
                        v-model="apiKey"
                        type="password"
                        name="claude-api-key"
                        autocomplete="off"
                        maxlength="4096"
                        :disabled="showingSetting || updatingSetting"
                        :placeholder="$t('setting.claude.api_key')"
                        required
                    />
                </v-field>
                <p v-if="configured" class="hc-claude-hint">
                    {{ $t('setting.claude.replace_hint') }}
                </p>
                <p v-if="error" class="hc-claude-error" role="alert">{{ error }}</p>
            </item-list>
            <buttons>
                <button type="submit" :disabled="!canSave">
                    {{ $t(updatingSetting ? 'setting.claude.saving' : 'setting.claude.save') }}
                </button>
            </buttons>
            <loading :loading="showingSetting || updatingSetting" />
        </form>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { CLOSE_MODAL } from "@/actions/modal";
import ClaudeService from "@/apis/user/claude";

export default {
    data() {
        return {
            name: "setting-claude",
            apiKey: "",
            configured: false,
            keyValid: null,
            validationStatus: null,
            loaded: false,
            showingSetting: false,
            updatingSetting: false,
            error: null,
            active: false,
            operationId: 0,
        };
    },

    computed: {
        ...mapGetters("auth", ["user"]),

        canSave() {
            const key = this.apiKey.trim();
            return this.active && !!this.user?.id && !this.showingSetting && !this.updatingSetting
                && key.length > 0 && key.length <= 4096;
        },

        keyStatusMessage() {
            if (!this.configured) return this.$t('setting.claude.not_configured');
            return this.validationMessage(this.validationStatus);
        },
    },

    watch: {
        "user.id"(newId, oldId) {
            if (newId === oldId) return;
            if (this.active && newId) {
                this.showSetting();
            } else {
                this.closeSetting();
            }
        },
    },

    beforeUnmount() {
        this.closeSetting();
    },

    methods: {
        reset() {
            this.operationId++;
            this.apiKey = "";
            this.configured = false;
            this.keyValid = null;
            this.validationStatus = null;
            this.loaded = false;
            this.showingSetting = false;
            this.updatingSetting = false;
            this.error = null;
        },

        closeSetting() {
            this.active = false;
            this.reset();
        },

        isCurrent(operationId, userId) {
            return this.active && operationId === this.operationId
                && String(this.user?.id) === String(userId);
        },

        async showSetting() {
            this.reset();
            this.active = true;
            const userId = this.user?.id;
            if (!userId) {
                this.closeSetting();
                return;
            }

            const operationId = this.operationId;
            this.showingSetting = true;
            try {
                const { data } = await ClaudeService.show();
                if (this.isCurrent(operationId, userId)) {
                    this.configured = !!data.configured;
                    this.keyValid = typeof data.valid === 'boolean' ? data.valid : null;
                    this.validationStatus = data.status;
                    this.loaded = true;
                }
            } catch (error) {
                if (this.isCurrent(operationId, userId)) this.error = this.$t("setting.claude.load_error");
            } finally {
                if (this.isCurrent(operationId, userId)) this.showingSetting = false;
            }
        },

        async update() {
            if (!this.canSave) return;
            const userId = this.user.id;
            const operationId = ++this.operationId;
            const apiKey = this.apiKey.trim();
            this.updatingSetting = true;
            this.error = null;

            try {
                await ClaudeService.update(apiKey);
                if (this.isCurrent(operationId, userId)) {
                    this.closeSetting();
                    store.commit(CLOSE_MODAL);
                }
            } catch (error) {
                if (this.isCurrent(operationId, userId)) {
                    const status = error.response?.data?.status;
                    this.error = status ? this.validationMessage(status) : this.$t("setting.claude.save_error");
                }
            } finally {
                if (this.isCurrent(operationId, userId)) this.updatingSetting = false;
            }
        },

        validationMessage(status) {
            const known = ['valid', 'invalid', 'forbidden', 'no_models', 'rate_limited', 'verification_unavailable', 'configuration_error'];
            return this.$t('setting.claude.' + (known.includes(status) ? status : 'verification_unavailable'));
        },
    },
};
</script>

<style scoped>
.hc-claude-help {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 12px;
    line-height: 1.5;
}

.hc-claude-help h4 {
    margin: 0 0 5px;
    font-size: inherit;
}

.hc-claude-help a {
    color: #1e88e5;
    text-decoration: underline;
}

.hc-claude-help ol {
    margin: 8px 0;
    padding-inline-start: 20px;
}

.hc-claude-help li + li {
    margin-top: 4px;
}

.hc-claude-help p {
    margin: 0;
}

.hc-claude-status,
.hc-claude-hint,
.hc-claude-error {
    margin: 0;
    font-size: 12px;
}

.hc-claude-hint {
    color: #888;
}

.hc-claude-error {
    color: #c0392b;
}

.hc-claude-status.is-valid {
    color: #218838;
}

.hc-claude-status.is-invalid {
    color: #c0392b;
}

.hc-claude-status.is-unknown {
    color: #9a6700;
}
</style>
