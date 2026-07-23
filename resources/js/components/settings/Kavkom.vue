<template>
    <modal :name="name" title="Kavkom" @open="showSetting">
        <form
            class="hc-flex-column"
            style="height: 100%"
            @submit.prevent="update"
        >
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <v-field label="Jeton API (X-API-TOKEN)" required v-slot="{ label }">
                    <input
                        :placeholder="label + ' ...'"
                        v-model="setting.api_token"
                        type="password"
                        autocomplete="off"
                        required
                    />
                </v-field>

                <v-field label="Domain UUID" required v-slot="{ label }">
                    <input
                        :placeholder="label + ' ...'"
                        v-model="setting.domain_uuid"
                        type="text"
                        autocomplete="off"
                        required
                    />
                </v-field>

                <div class="hc-kavkom-help">
                    Créez un token dans l’interface Kavkom : réglages avancés → Paramètres API → Ajouter un Token.
                    Le domain_uuid correspond au domaine associé à ce token. Le test est réalisé côté serveur,
                    sans exposer le jeton au navigateur.
                </div>

                <div v-if="testMessage" :class="['hc-kavkom-test-message', testMessageType]">
                    {{ testMessage }}
                </div>
            </item-list>
            <buttons>
                <button
                    v-if="setting && setting.id"
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
                <button
                    type="button"
                    class="hc-button-secondary"
                    :disabled="testingConnection"
                    @click.prevent="testConnection"
                >
                    {{ testingConnection ? "Validation en cours..." : "Valider" }}
                </button>
                <button v-text="$t('update')"></button>
            </buttons>
            <loading
                :loading="updatingSetting || showingSetting || removingSetting"
                :title="loadingTitle"
            />
        </form>
    </modal>
</template>

<style scoped>
.hc-kavkom-help {
    font-size: 12px;
    color: #6c757d;
    line-height: 1.5;
}

.hc-kavkom-test-message {
    font-size: 12px;
    padding: 8px 10px;
    border-radius: 6px;
    background: #f8f9fa;
    color: #495057;
}

.hc-kavkom-test-message.success {
    background: #e8f5e9;
    color: #2e7d32;
}

.hc-kavkom-test-message.error {
    background: #ffebee;
    color: #c62828;
}
</style>

<script>
import store from "@/store";
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

// Actions
import { CLOSE_MODAL } from "@/actions/modal";
import {
    UPDATE_USER_SETTING,
    GET_USER_SETTING,
    REMOVE_USER_SETTING,
} from "@/actions/user/setting";

export default {
    data() {
        return {
            name: "setting-kavkom",
            updatingSetting: false,
            showingSetting: false,
            removingSetting: false,
            testingConnection: false,
            loadingTitle: null,
            key: "kavkom",
            testMessage: "",
            testMessageType: "",
            setting: {
                api_token: "",
                domain_uuid: "",
            },
        };
    },

    methods: {
        async testConnection() {
            if (!this.setting.api_token || !this.setting.domain_uuid) {
                this.testMessage = "Veuillez saisir le jeton API et le domain_uuid avant de tester.";
                this.testMessageType = "error";
                return;
            }

            this.testingConnection = true;
            this.testMessage = "";
            this.testMessageType = "";

            try {
                const { data } = await ApiService.post("settings/kavkom/test", {
                    api_token: this.setting.api_token,
                    domain_uuid: this.setting.domain_uuid,
                });

                this.testMessage = data.message;
                this.testMessageType = data.success ? "success" : "error";
            } catch (error) {
                this.testMessage =
                    error.response?.data?.message ||
                    "Erreur inattendue lors du test de connexion.";
                this.testMessageType = "error";
            } finally {
                this.testingConnection = false;
            }
        },

        async update() {
            this.updatingSetting = true;

            try {
                await store.dispatch(UPDATE_USER_SETTING, {
                    key: this.key,
                    value: this.setting,
                });
                this.testMessage = "Paramètres enregistrés. L’interface Kavkom s’ouvre maintenant.";
                this.testMessageType = "success";
                window.dispatchEvent(new CustomEvent("hc:kavkom-settings-saved"));
            } finally {
                this.updatingSetting = false;
                store.commit(CLOSE_MODAL);
            }
        },

        async showSetting() {
            this.showingSetting = true;
            this.testMessage = "";
            this.testMessageType = "";

            try {
                const data = await store.dispatch(GET_USER_SETTING, this.key);

                if (data) {
                    this.setting = {
                        api_token: data.api_token || data.api_key || "",
                        domain_uuid: data.domain_uuid || "",
                        id: data.id,
                    };
                } else {
                    this.setting = {
                        api_token: "",
                        domain_uuid: "",
                    };
                }
            } finally {
                this.showingSetting = false;
            }
        },

        async remove() {
            this.removingSetting = true;

            try {
                await store.dispatch(REMOVE_USER_SETTING, this.key);
            } finally {
                this.removingSetting = false;
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
