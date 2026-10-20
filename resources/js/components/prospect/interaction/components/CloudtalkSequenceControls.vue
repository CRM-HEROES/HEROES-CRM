<template>
    <Teleport to="body">
        <div
            v-if="dialogMode"
            ref="dialog"
            class="hc-modal active hc-cloudtalk-sequence-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="hc-cloudtalk-sequence-title"
            @keydown.esc.stop.prevent="dismissDialog"
            @keydown.tab="trapFocus"
        >
            <div class="hc-modal-overlay" @click="dismissDialog"></div>
            <div class="hc-modal-content">
                <div id="hc-cloudtalk-sequence-title" class="hc-modal-header">
                    {{ $t(dialogMode === 'confirm' ? 'prospect.interaction.batch.confirm_title' : 'prospect.interaction.batch.title') }}
                </div>
                <div class="hc-modal-body">
                    <template v-if="dialogMode === 'confirm'">
                        <p>{{ $t('prospect.interaction.batch.confirm_body') }}</p>
                        <p v-if="!sequence.awaitingChoice && !sequence.loading" class="hc-cloudtalk-sequence-muted">
                            {{ $t('prospect.interaction.batch.ongoing_call') }}
                        </p>
                        <div class="hc-cloudtalk-sequence-dialog-actions">
                            <button type="button" class="hc-cloudtalk-sequence-button hc-cloudtalk-sequence-next-button" @click="$emit('cancel-stop')">
                                {{ $t('prospect.interaction.batch.continue') }}
                            </button>
                            <button type="button" class="hc-cloudtalk-sequence-button hc-cloudtalk-sequence-stop" @click="$emit('confirm-stop')">
                                {{ $t('prospect.interaction.batch.stop_sequence') }}
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <span class="hc-cloudtalk-sequence-muted">
                            {{ $t('prospect.interaction.batch.progress', { index: sequence.index + 1, total: sequence.prospects.length }) }}
                        </span>
                        <div v-if="nextNumber" class="hc-cloudtalk-sequence-next">
                            <span class="hc-cloudtalk-sequence-muted">{{ $t('prospect.interaction.batch.next_contact') }}</span>
                            <strong v-if="nextName" v-text="nextName"></strong>
                            <span class="hc-cloudtalk-sequence-next-number" v-text="nextNumber"></span>
                        </div>
                        <p v-else class="hc-cloudtalk-sequence-muted">{{ $t('prospect.interaction.batch.no_next') }}</p>
                        <p v-if="sequence.error" class="hc-cloudtalk-sequence-error" role="alert" v-text="sequence.error"></p>
                        <a v-if="loginRequired" href="https://phone.cloudtalk.io/" target="_blank" rel="noopener noreferrer">
                            {{ $t('prospect.interaction.batch.connect') }}
                        </a>
                        <div class="hc-cloudtalk-sequence-dialog-actions">
                            <button type="button" class="hc-cloudtalk-sequence-button hc-cloudtalk-sequence-retry" :disabled="busy" @click="$emit('retry')">
                                <i class="fa fa-redo" aria-hidden="true"></i>
                                {{ $t('prospect.interaction.batch.retry') }}
                            </button>
                            <button type="button" class="hc-cloudtalk-sequence-button hc-cloudtalk-sequence-next-button" :disabled="busy || !nextNumber" @click="$emit('next')">
                                <i class="fa fa-step-forward" aria-hidden="true"></i>
                                {{ $t('prospect.interaction.batch.next') }}
                            </button>
                            <button type="button" class="hc-cloudtalk-sequence-button hc-cloudtalk-sequence-stop" @click="$emit('stop')">
                                <i class="fa fa-stop" aria-hidden="true"></i>
                                {{ $t('prospect.interaction.batch.stop') }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style>
.hc-cloudtalk-sequence-next { display: flex; flex-direction: column; gap: 6px; padding: 16px; margin: 4px 0; border: 1px solid #dce8f5; border-radius: 10px; background: #f4f8fd; overflow-wrap: anywhere; }
.hc-cloudtalk-sequence-next strong { font-size: 14px; color: #24364b; }
.hc-cloudtalk-sequence-next-number { font-size: 18px; font-weight: 600; color: #1269b0; font-variant-numeric: tabular-nums; }
.hc-cloudtalk-sequence-muted { color: #667085; font-size: 12px; }
.hc-cloudtalk-sequence-dialog-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 12px; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-button { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; min-height: 44px; padding: 12px 16px; border: 1px solid transparent; border-radius: 8px; font-size: 13px; font-weight: 600; line-height: 1.4; cursor: pointer; transition: background-color 150ms, border-color 150ms, box-shadow 150ms; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-retry { background: #f8fafc; color: #334155; border-color: #cbd5e1; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-retry:hover:not(:disabled) { background: #edf2f7; border-color: #94a3b8; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-next-button { background: #0f77d4; color: #fff; border-color: #0f77d4; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-next-button:hover:not(:disabled) { background: #0964b8; border-color: #0964b8; box-shadow: 0 3px 10px #0f77d426; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-stop { background: #fff1f2; color: #b91c1c; border-color: #fecdd3; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-stop:hover:not(:disabled) { background: #ffe4e6; border-color: #fda4af; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-button:focus-visible { outline: 2px solid #0f77d4; outline-offset: 2px; }
.hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-button:disabled { opacity: 0.45; cursor: not-allowed; box-shadow: none; }
.hc-cloudtalk-sequence-modal { z-index: 1100; }
.hc-cloudtalk-sequence-modal .hc-modal-content { width: 440px; }
.hc-cloudtalk-sequence-modal .hc-modal-header { height: auto; min-height: 50px; padding: 14px 20px; line-height: 1.5; white-space: normal; }
.hc-cloudtalk-sequence-modal .hc-modal-body { gap: 8px; padding: 8px 20px 20px; overflow-y: auto; }
.hc-cloudtalk-sequence-error { color: #bc3434; }
.dark .hc-cloudtalk-sequence-next { background: #263344; border-color: #405777; }
.dark .hc-cloudtalk-sequence-next strong { color: #e2e8f0; }
.dark .hc-cloudtalk-sequence-next-number { color: #8ecaff; }
.dark .hc-cloudtalk-sequence-muted { color: #b0bac8; }
.dark .hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-retry { background: #343b46; color: #dbe4f0; border-color: #566172; }
.dark .hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-retry:hover:not(:disabled) { background: #414b59; }
.dark .hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-stop { background: #452d32; color: #ffb4bb; border-color: #75414a; }
.dark .hc-cloudtalk-sequence-modal .hc-cloudtalk-sequence-stop:hover:not(:disabled) { background: #5a343b; }
</style>

<script>
export default {
    emits: ['retry', 'next', 'stop', 'request-stop', 'cancel-stop', 'confirm-stop'],
    props: {
        sequence: Object,
        nextName: String,
        nextNumber: String,
        busy: Boolean,
        loginRequired: Boolean,
    },
    data() {
        return { previousFocus: null };
    },
    computed: {
        dialogMode() {
            if (this.sequence?.stopAction) return 'confirm';
            return this.sequence?.awaitingChoice ? 'choice' : null;
        },
    },
    watch: {
        async dialogMode(mode, previousMode) {
            if (mode) {
                if (!previousMode) this.previousFocus = document.activeElement;
                await this.$nextTick();
                if (this.dialogMode === mode) this.$refs.dialog?.querySelector('button:not(:disabled)')?.focus();
            } else {
                if (this.previousFocus?.isConnected) this.previousFocus.focus();
                this.previousFocus = null;
            }
        },
    },
    methods: {
        dismissDialog() {
            if (this.dialogMode === 'confirm') this.$emit('cancel-stop');
            else this.$emit('request-stop', 'stay');
        },
        trapFocus(event) {
            const targets = [...this.$refs.dialog.querySelectorAll('button:not(:disabled), a[href]')];
            const first = targets[0], last = targets[targets.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    },
};
</script>
