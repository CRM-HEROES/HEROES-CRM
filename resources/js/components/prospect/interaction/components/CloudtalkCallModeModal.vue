<template>
    <Teleport to="body">
        <div
            v-if="visible"
            ref="dialog"
            class="hc-modal active hc-cloudtalk-mode-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="hc-cloudtalk-mode-title"
            @keydown.esc.stop.prevent="$emit('cancel')"
            @keydown.tab="trapFocus"
        >
            <div class="hc-modal-overlay" @click="$emit('cancel')"></div>
            <div class="hc-modal-content">
                <div id="hc-cloudtalk-mode-title" class="hc-modal-header">
                    {{ $t('prospect.interaction.mode_picker.title') }}
                </div>
                <div class="hc-modal-body">
                    <button
                        type="button"
                        class="hc-cloudtalk-mode-option hc-cloudtalk-mode-sequential"
                        @click="$emit('sequential')"
                    >
                        <span class="hc-cloudtalk-mode-option-title">
                            <i class="fa fa-list-ol" aria-hidden="true"></i>
                            {{ $t('prospect.interaction.mode_picker.sequential_title') }}
                        </span>
                        <span>{{ $t('prospect.interaction.mode_picker.sequential_description') }}</span>
                    </button>
                    <button
                        type="button"
                        class="hc-cloudtalk-mode-option hc-cloudtalk-mode-parallel"
                        @click="$emit('parallel')"
                    >
                        <span class="hc-cloudtalk-mode-option-title">
                            <i class="fa fa-bolt" aria-hidden="true"></i>
                            {{ $t('prospect.interaction.mode_picker.parallel_title') }}
                        </span>
                        <span>{{ $t('prospect.interaction.mode_picker.parallel_description') }}</span>
                    </button>
                    <button
                        type="button"
                        class="hc-cloudtalk-mode-cancel"
                        @click="$emit('cancel')"
                    >
                        {{ $t('prospect.interaction.mode_picker.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style>
.hc-cloudtalk-mode-modal { z-index: 1100; }
.hc-cloudtalk-mode-modal .hc-modal-content { width: 460px; max-width: calc(100vw - 32px); }
.hc-cloudtalk-mode-modal .hc-modal-header { height: auto; min-height: 50px; padding: 14px 20px; line-height: 1.5; white-space: normal; }
.hc-cloudtalk-mode-modal .hc-modal-body { gap: 10px; padding: 12px 20px 20px; }
.hc-cloudtalk-mode-option {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #475467;
    text-align: left;
    font-size: 13px;
    line-height: 1.45;
    cursor: pointer;
}
.hc-cloudtalk-mode-option:hover { border-color: #0f77d4; background: #f5faff; }
.hc-cloudtalk-mode-option:focus-visible,
.hc-cloudtalk-mode-cancel:focus-visible { outline: 2px solid #0f77d4; outline-offset: 2px; }
.hc-cloudtalk-mode-option-title { display: flex; align-items: center; gap: 9px; color: #24364b; font-size: 14px; font-weight: 700; }
.hc-cloudtalk-mode-option-title i { width: 16px; color: #0f77d4; text-align: center; }
.hc-cloudtalk-mode-cancel { align-self: flex-end; padding: 7px 10px; border: 0; background: transparent; color: #667085; font-size: 13px; cursor: pointer; }
.dark .hc-cloudtalk-mode-option { border-color: #566172; background: #263344; color: #b0bac8; }
.dark .hc-cloudtalk-mode-option:hover { border-color: #57a6ec; background: #2a3a4d; }
.dark .hc-cloudtalk-mode-option-title { color: #e2e8f0; }
.dark .hc-cloudtalk-mode-cancel { color: #b0bac8; }
</style>

<script>
export default {
    emits: ["sequential", "parallel", "cancel"],
    props: {
        visible: { type: Boolean, default: false },
    },
    data() {
        return { previousFocus: null };
    },
    watch: {
        async visible(isVisible, wasVisible) {
            if (isVisible) {
                if (!wasVisible) this.previousFocus = document.activeElement;
                await this.$nextTick();
                if (this.visible) this.$refs.dialog?.querySelector("button")?.focus();
            } else {
                if (this.previousFocus?.isConnected) this.previousFocus.focus();
                this.previousFocus = null;
            }
        },
    },
    methods: {
        trapFocus(event) {
            const targets = [...this.$refs.dialog.querySelectorAll("button")];
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
