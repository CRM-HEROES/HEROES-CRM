<template>
    <div v-if="pendingRecords.length" class="hc-process-communications">
        <div v-if="error" class="hc-process-communications-error" role="alert">
            {{ $t('prospect.communications.process_error') }}
        </div>
        <buttons>
            <button
                type="button"
                :disabled="disabled || processing"
                @click="process"
            >
                {{ $t('prospect.communications.process') }}
            </button>
        </buttons>
    </div>
</template>

<script>
import { markCommunicationsRead } from "@/utils/incoming-communications";

export default {
    props: {
        projectSlug: { type: String, required: true },
        prospect: { type: Object, required: true },
        type: { type: String, required: true, validator: value => ["sms", "voicemail"].includes(value) },
        records: { type: Array, default: () => [] },
        disabled: { type: Boolean, default: false },
    },

    data() {
        return { processing: false, error: false, processedIds: [] };
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

            // Snapshot the displayed messages; later arrivals still need another click.
            const records = [...this.pendingRecords];
            this.processing = true;
            this.error = false;
            try {
                if (await markCommunicationsRead(this.projectSlug, this.prospect.id, this.type, records)) {
                    this.processedIds.push(...records.map(record => Number(record.id)));
                } else {
                    this.error = true;
                }
            } finally {
                this.processing = false;
            }
        },
    },
};
</script>

<style scoped>
.hc-process-communications {
    flex: 0 0 auto;
}
.hc-process-communications-error {
    padding: 7px;
    color: #d32f2f;
    font-size: 12px;
}
</style>
