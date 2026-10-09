<template>
    <modal :name="name" title="CloudTalk" :width="520">
        <div class="hc-flex-column" style="height: 100%">
            <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                <div class="hc-cloudtalk-webhook">
                    <div class="hc-cloudtalk-webhook-header">
                        <icon class="fa fa-link" />
                        <span v-text="'Recevoir les nouveaux SMS'" />
                    </div>
                    <p class="hc-cloudtalk-webhook-help">
                        Pour que les nouveaux SMS arrivent automatiquement dans
                        le CRM, configurez cette URL comme webhook SMS entrant
                        dans votre compte CloudTalk :
                    </p>
                    <div class="hc-cloudtalk-webhook-url">
                        <input
                            type="text"
                            readonly
                            :value="cloudtalkSmsWebhookUrl"
                            @focus="$event.target.select()"
                        />
                        <button
                            type="button"
                            class="hc-button-secondary"
                            @click.prevent="copyCloudtalkSmsWebhookUrl"
                        >
                            <icon class="fa fa-copy" />
                            <span v-text="'Copier'" />
                        </button>
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
        copyCloudtalkSmsWebhookUrl() {
            if (!this.cloudtalkSmsWebhookUrl) {
                return;
            }

            navigator.clipboard
                .writeText(this.cloudtalkSmsWebhookUrl)
                .then(() => {
                    flashInfo({
                        title: "CloudTalk",
                        body: "URL Webhook SMS CloudTalk copié",
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
         * CloudTalk SMS webhook URL used to receive incoming messages.
         */
        cloudtalkSmsWebhookUrl() {
            if (!this.project || !this.project.slug) {
                return "";
            }

            return (
                window.location.origin +
                "/webhook/project/" +
                this.project.slug +
                "/cloudtalk/sms"
            );
        },
    },
};
</script>
