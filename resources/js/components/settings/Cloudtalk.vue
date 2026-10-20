<template>
    <modal :name="name" title="CloudTalk" :width="520">
        <div class="hc-flex-column" style="height: 100%">
            <item-list
                gap="5px"
                class="hc-flex-1"
                padding="10px 0"
            >
                <div class="hc-cloudtalk-webhook">

                    <!-- En-tête -->
                    <div class="hc-cloudtalk-webhook-header">
                        <icon class="fa fa-link" />
                        <span>Recevoir les SMS, appels et messages vocaux</span>
                    </div>

                    <!-- Description -->
                    <p class="hc-cloudtalk-webhook-help">
                        Configurez un webhook CloudTalk pour
                        recevoir automatiquement les nouveaux
                        SMS, les messages vocaux et les enregistrements des appels dans votre CRM.
                    </p>

                    <!-- URL du webhook -->
                    <div class="hc-cloudtalk-webhook-url">
                        <input
                            type="text"
                            readonly
                            :value="cloudtalkWebhookUrl"
                            @focus="$event.target.select()"
                        />

                        <button
                            type="button"
                            class="hc-button-secondary"
                            @click.prevent="copyCloudtalkWebhookUrl"
                        >
                            <icon class="fa fa-copy" />
                            <span>Copier</span>
                        </button>
                    </div>

                    <!-- Instructions détaillées -->
                    <div
                        class="hc-cloudtalk-webhook-instructions"
                    >
                        <div class="hc-cloudtalk-webhook-instructions-title">
                            Comment configurer les événements CloudTalk ?
                        </div>

                        <ol>
                            <li>
                                Accédez aux
                                <a
                                    href="https://dashboard.cloudtalk.io/menu/account/webhooks"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    https://dashboard.cloudtalk.io/menu/account/webhooks
                                </a>.
                            </li>

                            <li>
                                Ajoutez un nouveau webhook
                                dans votre compte CloudTalk.
                            </li>

                            <li>
                                Copiez l'URL ci-dessus et
                                collez-la dans le champ URL
                                du webhook.
                            </li>

                            <li>
                                Sélectionnez les événements
                                <strong>message.received</strong>,
                                <strong>call.ended</strong> et
                                <strong>call.recording_ready</strong>.
                            </li>

                            <li>
                                Enregistrez votre configuration.
                            </li>
                        </ol>

                        <p class="hc-cloudtalk-webhook-note">
                            <icon class="fa fa-info-circle" />
                            Les SMS reçus seront transmis au CRM et les
                            enregistrements seront rattachés aux interactions existantes.
                            Les appels et messages vocaux créeront une interaction si le
                            numéro correspond à un prospect du projet.
                        </p>
                    </div>

                </div>
            </item-list>
        </div>
    </modal>
</template>

<style>
.hc-cloudtalk-webhook {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #d6e4f0;
    border-radius: 6px;
    background: #f4f9ff;
}

.hc-cloudtalk-webhook-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    color: #12a0f3;
}

.hc-cloudtalk-webhook-help {
    margin: 0;
    font-size: 12px;
    line-height: 1.4;
    color: #495057;
}

.hc-cloudtalk-webhook-url {
    display: flex;
    align-items: stretch;
    gap: 6px;
}

.hc-cloudtalk-webhook-url input {
    flex: 1;
    min-width: 0;
    padding: 5px 10px;
    border: 1px solid #dddddd;
    border-radius: 6px;
    background: #ffffff;
    color: #333333;
    font-size: 12px;
}

/* Instructions */
.hc-cloudtalk-webhook-instructions {
    padding: 10px 0 0;
    border-top: 1px solid #d6e4f0;
    font-size: 12px;
    line-height: 1.6;
    color: #495057;
}

.hc-cloudtalk-webhook-instructions-title {
    font-weight: 600;
    margin-bottom: 8px;
}

.hc-cloudtalk-webhook-instructions ol {
    margin: 0;
    padding-left: 20px;
}

.hc-cloudtalk-webhook-instructions li {
    margin-bottom: 7px;
}

.hc-cloudtalk-webhook-instructions strong {
    color: #12a0f3;
    font-weight: 600;
}

.hc-cloudtalk-webhook-instructions a {
    color: #12a0f3;
    text-decoration: none;
    font-weight: 600;
}

.hc-cloudtalk-webhook-instructions a:hover {
    text-decoration: underline;
}

.hc-cloudtalk-webhook-instructions a .fa {
    font-size: 10px;
    margin-left: 3px;
}

/* Note d'information */
.hc-cloudtalk-webhook-note {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    margin: 8px 0 0;
    padding: 8px;
    border-radius: 4px;
    background: #e6f3ff;
    color: #495057;
    font-size: 11px;
    line-height: 1.5;
}

.hc-cloudtalk-webhook-note .fa {
    color: #12a0f3;
    margin-top: 2px;
}

/* Mode sombre */
.dark .hc-cloudtalk-webhook {
    border-color: #334155;
    background: #1e293b;
}

.dark .hc-cloudtalk-webhook-help {
    color: #bbbbbb;
}

.dark .hc-cloudtalk-webhook-url input {
    border-color: #444444;
    background: #2a2a2a;
    color: #dddddd;
}

.dark .hc-cloudtalk-webhook-instructions {
    border-color: #334155;
    color: #bbbbbb;
}

.dark .hc-cloudtalk-webhook-note {
    background: #263b53;
    color: #cccccc;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    data() {
        return {
            name: "setting-cloudtalk",
        };
    },

    methods: {
        copyCloudtalkWebhookUrl() {
            if (!this.cloudtalkWebhookUrl) {
                return;
            }

            navigator.clipboard
                .writeText(this.cloudtalkWebhookUrl)
                .then(() => {
                    flashInfo({
                        title: "CloudTalk",
                        body: "URL du webhook CloudTalk copiée",
                        duration: 5000,
                    });
                })
                .catch(() => {
                    flashInfo({
                        title: "CloudTalk",
                        body: "Impossible de copier l'URL du webhook",
                        duration: 5000,
                    });
                });
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         * URL du webhook CloudTalk pour
         * recevoir les SMS entrants et les références des enregistrements.
         */
        cloudtalkWebhookUrl() {
            if (!this.project || !this.project.slug) {
                return "";
            }

            return (
                window.location.origin +
                "/webhook/project/" +
                this.project.slug +
                "/cloudtalk"
            );
        },
    },
};
</script>
