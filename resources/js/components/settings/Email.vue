<template>
    <modal
        :name="name"
        title="Email via Brevo"
        class="hc-email-settings-modal"
        @open="showSetting"
    >
        <form
            class="hc-flex-column"
            style="height: 100%; min-height: 0"
            @submit.prevent="update"
        >
            <item-list
                gap="5px"
                class="hc-flex-1"
                padding="10px 0"
                style="min-height: 0; overflow-y: auto"
            >
                <v-field label="Clé API Brevo" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.api_key"
                        type="password"
                        required
                /></v-field>
                <v-field label="Nom expéditeur" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.from.name"
                        required
                /></v-field>
                <v-field label="Email expéditeur" required v-slot="{ label }"
                    ><input
                        :placeholder="label + ' ...'"
                        v-model="setting.from.address"
                        type="email"
                        required
                /></v-field>

                <hr style="width: 100%; border: 0; border-top: 1px solid #eee; margin: 5px 0" />

                <v-field label="Emails entrants (IMAP)">
                    <label class="hc-flex-row" style="align-items: center; gap: 8px; cursor: pointer">
                        <input type="checkbox" v-model="setting.imap.enabled" />
                        <span>Récupérer les emails reçus dans la boîte du projet</span>
                    </label>
                </v-field>

                <template v-if="setting.imap.enabled">
                    <v-field label="Fournisseur" v-slot
                        ><select v-model="setting.imap.provider" @change="applyProvider">
                            <option
                                v-for="(provider, key) in providers"
                                :key="key"
                                :value="key"
                                v-text="provider.name"
                            ></option>
                            <option value="custom">Personnalisé (serveur manuel)</option>
                        </select></v-field
                    >

                    <template v-if="isCustomProvider">
                        <v-field label="Serveur IMAP" required v-slot="{ label }"
                            ><input
                                :placeholder="label + ' ...'"
                                v-model="setting.imap.host"
                                required
                        /></v-field>
                        <v-field label="Port" required v-slot="{ label }"
                            ><input
                                :placeholder="label + ' ...'"
                                v-model.number="setting.imap.port"
                                type="number"
                                required
                        /></v-field>
                        <v-field label="Chiffrement" v-slot
                            ><select v-model="setting.imap.encryption">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="starttls">STARTTLS</option>
                                <option value="none">Aucun</option>
                            </select></v-field
                        >
                    </template>
                    <p
                        v-else
                        style="font-size: 11px; opacity: 0.7; padding: 0 10px"
                        v-text="serverSummary"
                    ></p>

                    <v-field label="Identifiant (adresse email)" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="setting.imap.username"
                            type="email"
                            required
                    /></v-field>
                    <v-field
                        label="Mot de passe"
                        :required="!setting.imap.has_password"
                        v-slot="{ label }"
                        ><input
                            :placeholder="
                                setting.imap.has_password
                                    ? 'Mot de passe enregistré (laisser vide pour le conserver)'
                                    : label + ' ...'
                            "
                            v-model="setting.imap.password"
                            type="password"
                            autocomplete="new-password"
                            :required="!setting.imap.has_password"
                    /></v-field>
                    <button
                        type="button"
                        class="hc-email-imap-advanced"
                        :aria-expanded="showAdvanced ? 'true' : 'false'"
                        @click.prevent="showAdvanced = !showAdvanced"
                    >
                        <i
                            :class="[
                                'fa',
                                showAdvanced ? 'fa-caret-down' : 'fa-caret-right',
                            ]"
                        ></i>
                        Réglages avancés
                    </button>

                    <template v-if="showAdvanced">
                        <v-field label="Dossier (optionnel)" v-slot="{ label }"
                            ><input
                                :placeholder="label + ' ...'"
                                v-model="setting.imap.folder"
                                placeholder="INBOX"
                        /></v-field>
                        <p class="hc-email-imap-help">
                            Dossier IMAP lu sur le serveur. Laisser vide pour
                            INBOX : seuls les emails de ce dossier sont
                            récupérés, rien n'est déplacé ni marqué comme lu.
                        </p>
                    </template>

                    <div class="hc-email-imap-actions">
                        <button
                            type="button"
                            class="hc-button"
                            :disabled="imapBusy"
                            @click.prevent="testImap"
                        >
                            <i class="fa fa-plug"></i>
                            <span
                                v-text="
                                    imapAction === 'test'
                                        ? 'Test en cours…'
                                        : 'Tester la connexion'
                                "
                            ></span>
                        </button>
                        <button
                            type="button"
                            class="hc-button hc-email-imap-sync"
                            :disabled="imapBusy"
                            @click.prevent="syncImap"
                        >
                            <i class="fa fa-download"></i>
                            <span
                                v-text="
                                    imapAction === 'sync'
                                        ? 'Récupération…'
                                        : 'Récupérer les emails'
                                "
                            ></span>
                        </button>
                    </div>

                    <p
                        v-if="imapMessage"
                        :class="[
                            'hc-email-imap-message',
                            imapError ? 'hc-email-imap-message-error' : '',
                        ]"
                        role="status"
                    >
                        <i
                            :class="[
                                'fa',
                                imapError
                                    ? 'fa-exclamation-triangle'
                                    : 'fa-check',
                            ]"
                        ></i>
                        <span v-text="imapMessage"></span>
                    </p>
                    <p
                        v-if="lastSync"
                        style="font-size: 11px; opacity: 0.6; padding: 0 10px"
                        v-text="lastSync"
                    ></p>
                    <p
                        v-if="setting.imap.last_error"
                        style="font-size: 11px; color: #c0392b; padding: 0 10px"
                        v-text="setting.imap.last_error"
                    ></p>
                </template>
            </item-list>
            <buttons>
                <button v-text="$t('update')"></button>
                <button
                    class="hc-button-danger"
                    v-text="$t('delete')"
                    @click.prevent="remove"
                ></button>
            </buttons>
            <loading :loading="updatingSetting || showingSetting || imapBusy" />
        </form>
    </modal>
</template>

<style>
.hc-email-settings-modal .hc-modal-body {
    min-height: 0;
}

.hc-email-imap-advanced {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 2px 10px;
    background: none;
    border: none;
    color: #12a0f3;
    font-size: 11px;
    cursor: pointer;
}

.hc-email-imap-help {
    padding: 0 10px;
    font-size: 11px;
    line-height: 1.4;
    opacity: 0.7;
}

.hc-email-imap-actions {
    display: flex;
    flex-direction: row;
    gap: 8px;
    padding: 0 10px;
}

.hc-email-imap-actions > button {
    flex: 1;
    min-width: 0;
    font-size: 11px;
}

.hc-email-imap-actions > button > i {
    margin-right: 6px;
}

.hc-email-imap-actions > button > span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.hc-email-imap-sync {
    background-color: #c36d34;
}

.hc-email-imap-sync:hover:not(:disabled) {
    background-color: #a95b28;
}

.hc-email-imap-message {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 0 10px;
    font-size: 11px;
    color: #1e7e34;
}

.hc-email-imap-message-error {
    color: #c0392b;
}
</style>

<script>
import store from "@/store";
import SettingService from "@/apis/project/setting";

// Actions
import { CLOSE_MODAL } from "@/actions/modal";
import {
    UPDATE_SETTING,
    TEST_SETTING,
    REMOVE_SETTING,
    GET_SETTING,
} from "@/actions/project/setting";

/**
 * Known IMAP providers: the server settings are filled from the provider.
 * Keep in sync with App\Services\ImapInboxSync::PROVIDERS.
 */
const PROVIDERS = {
    ovh: {
        name: "OVH",
        host: "imap.mail.ovh.net",
        port: 993,
        encryption: "ssl",
    },
    gmail: {
        name: "Gmail",
        host: "imap.gmail.com",
        port: 993,
        encryption: "ssl",
    },
    outlook: {
        name: "Outlook / Microsoft 365",
        host: "outlook.office365.com",
        port: 993,
        encryption: "ssl",
    },
    yahoo: {
        name: "Yahoo",
        host: "imap.mail.yahoo.com",
        port: 993,
        encryption: "ssl",
    },
    icloud: {
        name: "iCloud",
        host: "imap.mail.me.com",
        port: 993,
        encryption: "ssl",
    },
    zoho: {
        name: "Zoho",
        host: "imap.zoho.com",
        port: 993,
        encryption: "ssl",
    },
    infomaniak: {
        name: "Infomaniak",
        host: "mail.infomaniak.com",
        port: 993,
        encryption: "ssl",
    },
};

export default {
    data() {
        return {
            name: "setting-email",
            updatingSetting: false,
            showingSetting: false,
            imapBusy: false,
            imapAction: null,
            imapMessage: null,
            imapError: false,
            // The IMAP folder is an advanced setting: hidden unless needed.
            showAdvanced: false,
            key: "email",
            providers: PROVIDERS,
            setting: this.newSetting(),
        };
    },

    computed: {
        /**
         * The server fields are only editable for a custom provider
         */
        isCustomProvider() {
            return (
                this.setting.imap.provider === "custom" ||
                !this.providers[this.setting.imap.provider]
            );
        },

        /**
         * Server used by the selected provider
         */
        serverSummary() {
            const { host, port, encryption } = this.setting.imap;
            const cipher =
                encryption && encryption !== "none"
                    ? encryption.toUpperCase()
                    : "";

            return [host, port, cipher].filter(Boolean).join(" · ");
        },

        lastSync() {
            return this.setting.imap.last_sync_at
                ? "Dernière synchronisation : " + this.setting.imap.last_sync_at
                : null;
        },
    },

    methods: {
        newSetting() {
            return {
                api_key: "",
                from: { name: "", address: "" },
                imap: {
                    enabled: false,
                    provider: "ovh",
                    host: "imap.mail.ovh.net",
                    port: 993,
                    encryption: "ssl",
                    username: "",
                    password: "",
                    // The stored password is never sent to the browser: it is
                    // kept unless a new one is typed.
                    has_password: false,
                    folder: "INBOX",
                    last_sync_at: null,
                    last_error: null,
                },
            };
        },

        /**
         * Stored setting completed with the defaults (an older setting has no
         * IMAP part at all).
         */
        normalize(data) {
            const defaults = this.newSetting();

            return {
                ...defaults,
                ...data,
                from: { ...defaults.from, ...(data.from || {}) },
                imap: { ...defaults.imap, ...(data.imap || {}) },
            };
        },

        /**
         * Fill the server settings from the selected provider
         */
        applyProvider() {
            const provider = this.providers[this.setting.imap.provider];

            if (!provider) {
                return;
            }

            this.setting.imap.host = provider.host;
            this.setting.imap.port = provider.port;
            this.setting.imap.encryption = provider.encryption;
        },

        async save() {
            const value = JSON.parse(JSON.stringify(this.setting));

            await store.dispatch(UPDATE_SETTING, {
                key: this.key,
                value: value,
            });

            // Do not keep the typed password in the form nor in the store: the
            // server only stored its encrypted value.
            if (value.imap && value.imap.password) {
                this.setting.imap.password = "";
                this.setting.imap.has_password = true;

                store.commit(UPDATE_SETTING, {
                    key: this.key,
                    value: this.setting,
                });
            }
        },

        /**
         *
         */
        async update() {
            this.updatingSetting = true;

            try {
                await this.save();
                store.dispatch(TEST_SETTING, this.key).catch(() => {});
            } finally {
                this.updatingSetting = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        async remove() {
            this.updatingSetting = true;

            try {
                await store.dispatch(REMOVE_SETTING, this.key);
            } finally {
                this.updatingSetting = false;
                this.setting = this.newSetting();
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        async showSetting() {
            this.showingSetting = true;

            try {
                const data = await store.dispatch(GET_SETTING, this.key);

                if (data) {
                    this.setting = this.normalize(data);
                }

                // Show the advanced part when a custom folder is already used.
                const folder = this.setting.imap.folder;
                this.showAdvanced = Boolean(folder && folder !== "INBOX");
            } finally {
                this.showingSetting = false;
            }
        },

        async testImap() {
            if (this.imapBusy) return;
            this.imapBusy = true;
            this.imapAction = "test";
            this.imapMessage = null;
            this.imapError = false;

            try {
                await this.save();

                const { data } = await SettingService.testImap(
                    store.getters.project.slug
                );
                this.imapMessage = `Connexion réussie à ${data.host} (dossier ${data.folder}).`;
            } catch (error) {
                this.imapError = true;
                this.imapMessage = this.errorMessage(error);
            } finally {
                this.imapBusy = false;
                this.imapAction = null;
            }
        },

        async syncImap() {
            if (this.imapBusy) return;
            this.imapBusy = true;
            this.imapAction = "sync";
            this.imapMessage = null;
            this.imapError = false;

            try {
                await this.save();

                const { data } = await SettingService.syncImap(
                    store.getters.project.slug
                );
                this.imapMessage = `${data.imported} email(s) importé(s), ${data.ignored} ignoré(s), ${data.duplicates} doublon(s).`;

                const setting = await store.dispatch(GET_SETTING, this.key);
                if (setting) {
                    this.setting = this.normalize(setting);
                }
            } catch (error) {
                this.imapError = true;
                this.imapMessage = this.errorMessage(error);
            } finally {
                this.imapBusy = false;
                this.imapAction = null;
            }
        },

        errorMessage(error) {
            return (
                (error &&
                    error.response &&
                    error.response.data &&
                    error.response.data.message) ||
                (error && error.message) ||
                "Une erreur est survenue."
            );
        },
    },
};
</script>
