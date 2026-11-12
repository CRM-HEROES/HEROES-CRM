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
                    <v-field label="Dossier" v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="setting.imap.folder"
                            placeholder="INBOX"
                    /></v-field>

                    <div class="hc-flex-row" style="gap: 8px">
                        <button
                            type="button"
                            style="flex: 1"
                            :disabled="imapBusy"
                            @click.prevent="testImap"
                        >
                            Tester la connexion
                        </button>
                        <button
                            type="button"
                            style="flex: 1"
                            :disabled="imapBusy"
                            @click.prevent="syncImap"
                        >
                            Récupérer les emails
                        </button>
                    </div>

                    <p
                        v-if="imapMessage"
                        style="font-size: 11px; padding: 0 10px"
                        v-text="imapMessage"
                    ></p>
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
            imapMessage: null,
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
            } finally {
                this.showingSetting = false;
            }
        },

        async testImap() {
            this.imapBusy = true;
            this.imapMessage = null;

            try {
                await this.save();

                const { data } = await SettingService.testImap(
                    store.getters.project.slug
                );
                this.imapMessage = `Connexion réussie à ${data.host} (dossier ${data.folder}).`;
            } catch (error) {
                this.imapMessage = this.errorMessage(error);
            } finally {
                this.imapBusy = false;
            }
        },

        async syncImap() {
            this.imapBusy = true;
            this.imapMessage = null;

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
                this.imapMessage = this.errorMessage(error);
            } finally {
                this.imapBusy = false;
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
