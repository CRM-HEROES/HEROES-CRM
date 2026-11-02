<template>
    <div
        v-if="showHistory || pendingRecords.length"
        class="hc-process-communications"
    >
        <div v-if="error" class="hc-process-communications-error" role="alert">
            {{ $t("prospect.communications.process_error") }}
        </div>
        <buttons>
            <button
                type="button"
                class="hc-button-warning"
                :disabled="disabled || processing || !pendingRecords.length"
                @click="process"
            >
                {{
                    $t(
                        showHistory
                            ? "prospect.communications.request_process"
                            : "prospect.communications.process"
                    )
                }}
            </button>
            <button
                v-if="showHistory"
                type="button"
                class="hc-button-secondary"
                :disabled="processing"
                @click="$emit('close')"
            >
                {{ $t("prospect.communications.not_processed") }}
            </button>
        </buttons>
        <section v-if="showHistory" class="hc-process-communications-history">
            <strong>{{ $t("prospect.communications.history") }}</strong>
            <div
                v-if="historyError"
                class="hc-process-communications-error"
                role="alert"
            >
                {{ $t("prospect.communications.history_error") }}
            </div>
            <loading :loading="loadingHistory" />
            <p
                v-if="!loadingHistory && !historyError && !history.length"
                class="hc-process-communications-empty"
            >
                {{ $t("prospect.communications.empty_history") }}
            </p>
            <ul v-if="history.length">
                <li
                    v-for="entry in history"
                    :key="entry.id + ':' + entry.user_id"
                >
                    <span v-text="entry.message"></span>
                    {{ $t("prospect.communications.processed_by", { user: entry.user_name || $t("prospect.communications.unknown_user") }) }}
                    <time :datetime="entry.read_at" v-text="formatDate(entry.read_at)"></time>
                </li>
            </ul>
        </section>
    </div>
</template>

<script>
import { markCommunicationsRead } from "@/utils/incoming-communications";
import IncomingCommunicationService from "@/apis/project/incoming-communication";

export default {
    props: {
        projectSlug: { type: String, required: true },
        prospect: { type: Object, required: true },
        type: { type: String, required: true, validator: value => ["sms", "voicemail"].includes(value) },
        records: { type: Array, default: () => [] },
        disabled: { type: Boolean, default: false },
        showHistory: { type: Boolean, default: false },
    },

    data() {
        return {
            processing: false,
            error: false,
            processedIds: [],
            history: [],
            loadingHistory: false,
            historyError: false,
        };
    },

    created() {
        if (this.showHistory) {
            this.loadHistory();
        }
    },

    computed: {
        pendingRecords() {
            const unreadIds = this.prospect.unread_communications?.[this.type];
            return this.records.filter(record => {
                if (record.prospect_id != this.prospect.id || this.processedIds.includes(Number(record.id))) {
                    return false;
                }
                if (Array.isArray(unreadIds)) {
                    return unreadIds.some(id => Number(id) === Number(record.id));
                }
                // Profiles opened directly may not have table unread metadata.
                if (this.type === "sms") {
                    return record.is_incoming || record.from_user === false;
                }
                return record.from_user === false && (record.call_outcome === "voicemail"
                    || ["voicemail", "voice_mail", "voice mail", "messagerie"].includes(record.status));
            });
        },
    },

    methods: {
        async process() {
            if (this.disabled || this.processing || !this.pendingRecords.length) return;

            const latestRecord = this.pendingRecords.reduce((latest, record) => {
                if (!latest) return record;

                const latestDate = Date.parse(latest.created_at || "");
                const recordDate = Date.parse(record.created_at || "");
                if (Number.isFinite(latestDate) && Number.isFinite(recordDate)) {
                    return recordDate > latestDate ? record : latest;
                }

                return Number(record.id) > Number(latest.id) ? record : latest;
            }, null);
            const records = [latestRecord];
            this.processing = true;
            this.error = false;
            try {
                if (await markCommunicationsRead(this.projectSlug, this.prospect.id, this.type, records)) {
                    this.processedIds.push(...records.map(record => Number(record.id)));
                    if (this.showHistory) {
                        await this.loadHistory();
                    }
                } else {
                    this.error = true;
                }
            } finally {
                this.processing = false;
            }
        },

        async loadHistory() {
            this.loadingHistory = true;
            this.historyError = false;
            try {
                const { data } = await IncomingCommunicationService.smsHistory(
                    this.projectSlug,
                    this.prospect.id
                );
                this.history = data;
            } catch (error) {
                this.historyError = true;
            } finally {
                this.loadingHistory = false;
            }
        },

        formatDate(value) {
            return new Intl.DateTimeFormat(undefined, {
                dateStyle: "short",
                timeStyle: "short",
            }).format(new Date(value));
        },
    },
};
</script>

<style scoped>
.hc-process-communications {
    flex: 0 0 auto;
}
.hc-process-communications-history {
    max-height: 180px;
    overflow-y: auto;
    padding: 8px 10px;
    font-size: 12px;
}
.hc-process-communications-history ul {
    list-style: none;
    margin: 6px 0 0;
    padding: 0;
}
.hc-process-communications-history li {
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #eeeeee;
    padding: 5px 0;
    overflow-wrap: anywhere;
}
.hc-process-communications-history time,
.hc-process-communications-empty {
    color: #888888;
    font-size: 11px;
}
.hc-process-communications-error {
    padding: 7px;
    color: #d32f2f;
    font-size: 12px;
}
</style>
