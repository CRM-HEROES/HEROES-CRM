<template>
    <div>
        <button
            type="button"
            class="hc-button-secondary"
            :disabled="testingConnection || !config.api_token || !config.domain_uuid"
            @click.prevent="testConnection"
        >
            {{ testingConnection ? "Diagnostic en cours..." : "Diagnostic complet" }}
        </button>

        <div v-if="testMessage" :class="['hc-kavkom-test-message', testMessageType]">
            {{ testMessage }}
        </div>

        <div v-if="testResults" class="hc-kavkom-test-results">
            <div class="hc-kavkom-results-header">Résultats du diagnostic:</div>
            <div v-for="(test, idx) in testResults" :key="idx" class="hc-kavkom-result-item">
                <div :class="['hc-kavkom-result-status', test.status]">
                    {{ test.name }} -
                    <span class="hc-status-badge">{{ test.status === 'ok' ? '✓ OK' : test.status === 'error' ? '✗ ERREUR' : '⚠ ALERTE' }}</span>
                </div>
                <div class="hc-kavkom-result-message">{{ test.message }}</div>
                <div v-if="test.extension" class="hc-kavkom-result-detail">
                    Extension: {{ test.extension }}
                </div>
                <div v-if="test.effective_caller_id_number" class="hc-kavkom-result-detail">
                    DID sortant de l'extension: {{ test.effective_caller_id_number }}
                </div>
                <div v-if="test.wss_url" class="hc-kavkom-result-detail">
                    WebSocket URL: <code>{{ test.wss_url }}</code>
                </div>
                <div v-if="test.details" class="hc-kavkom-result-detail">
                    {{ test.details.extensionsCount }} extension(s) trouvée(s)
                </div>
            </div>
        </div>

        <div v-if="testRecommendations" class="hc-kavkom-recommendations">
            <div class="hc-kavkom-recommendations-header">Recommandations:</div>
            <div v-for="(rec, idx) in testRecommendations" :key="idx" :class="['hc-kavkom-recommendation', rec.severity]">
                <div class="hc-kavkom-rec-title">{{ rec.title }}</div>
                <ul class="hc-kavkom-rec-steps">
                    <li v-for="(step, stepIdx) in rec.steps" :key="stepIdx">{{ step }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hc-kavkom-test-message {
    font-size: 12px;
    padding: 8px 10px;
    margin-top: 8px;
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

.hc-kavkom-test-results {
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    background: #fafafa;
}

.hc-kavkom-results-header {
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 12px;
    color: #333;
}

.hc-kavkom-result-item {
    padding: 8px;
    margin-bottom: 8px;
    background: white;
    border-radius: 4px;
    border-left: 3px solid #ccc;
}

.hc-kavkom-result-item:last-child {
    margin-bottom: 0;
}

.hc-kavkom-result-status {
    font-weight: bold;
    font-size: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.hc-kavkom-result-status.ok {
    color: #2e7d32;
    border-left-color: #2e7d32;
}

.hc-kavkom-result-status.error {
    color: #c62828;
    border-left-color: #c62828;
}

.hc-kavkom-result-status.warning {
    color: #f57f17;
    border-left-color: #f57f17;
}

.hc-status-badge {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
}

.hc-kavkom-result-message {
    font-size: 11px;
    color: #666;
    margin-top: 4px;
}

.hc-kavkom-result-detail {
    font-size: 11px;
    color: #888;
    margin-top: 3px;
    font-style: italic;
}

.hc-kavkom-recommendations {
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    background: #fafafa;
}

.hc-kavkom-recommendations-header {
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 12px;
    color: #333;
}

.hc-kavkom-recommendation {
    padding: 8px;
    margin-bottom: 8px;
    background: white;
    border-radius: 4px;
    border-left: 3px solid #ccc;
}

.hc-kavkom-recommendation.critical {
    border-left-color: #c62828;
    background: #ffebee;
}

.hc-kavkom-recommendation.warning {
    border-left-color: #f57f17;
    background: #fff3e0;
}

.hc-kavkom-recommendation.info {
    border-left-color: #1976d2;
    background: #e3f2fd;
}

.hc-kavkom-rec-title {
    font-weight: bold;
    font-size: 12px;
    margin-bottom: 6px;
}

.hc-kavkom-rec-steps {
    margin: 0;
    padding-left: 20px;
    font-size: 11px;
    color: #555;
}

.hc-kavkom-rec-steps li {
    margin-bottom: 4px;
}

.hc-kavkom-result-detail code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-family: 'Courier New', monospace;
    color: #c62828;
}
</style>

<script>
import ApiService from "@/apis/api.service";

export default {
    props: {
        config: {
            type: Object,
            default: () => ({}),
        },
    },

    data() {
        return {
            testingConnection: false,
            testMessage: "",
            testMessageType: "",
            testResults: null,
            testRecommendations: null,
        };
    },

    methods: {
        async testConnection() {
            if (!this.config.api_token || !this.config.domain_uuid) {
                this.testMessage = "Veuillez saisir le jeton API et le domain_uuid avant de tester.";
                this.testMessageType = "error";
                this.testResults = null;
                this.testRecommendations = null;
                return;
            }

            this.testingConnection = true;
            this.testMessage = "";
            this.testMessageType = "";
            this.testResults = null;
            this.testRecommendations = null;

            try {
                const { data } = await ApiService.post("settings/kavkom/test-full", {
                    api_token: this.config.api_token,
                    domain_uuid: this.config.domain_uuid,
                    extension: this.config.extension,
                    phone_number: this.config.phone_number,
                });

                if (data.success) {
                    this.testMessage = "Diagnostic réussi.";
                    this.testMessageType = "success";
                } else {
                    this.testMessage = data.message || "Le diagnostic a détecté des problèmes. Consultez les recommandations ci-dessous.";
                    this.testMessageType = "error";
                }

                this.testResults = data.tests || [];
                this.testRecommendations = data.recommendations || [];
            } catch (error) {
                this.testMessage =
                    error.response?.data?.message ||
                    "Erreur inattendue lors du diagnostic.";
                this.testMessageType = "error";
                this.testResults = null;
                this.testRecommendations = null;
            } finally {
                this.testingConnection = false;
            }
        },
    },
};
</script>
