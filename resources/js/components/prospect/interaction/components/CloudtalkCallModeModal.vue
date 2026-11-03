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
                    {{ $t(step === 'mode' ? 'prospect.interaction.mode_picker.title' : 'prospect.interaction.mode_picker.campaign_name_title') }}
                </div>
                <div class="hc-modal-body">
                    <template v-if="step === 'mode'">
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
                            ref="parallelButton"
                            type="button"
                            class="hc-cloudtalk-mode-option hc-cloudtalk-mode-parallel"
                            :disabled="prospectCount > parallelLimit"
                            @click="openCampaignNameStep"
                        >
                            <span class="hc-cloudtalk-mode-option-title">
                                <i class="fa fa-bolt" aria-hidden="true"></i>
                                {{ $t('prospect.interaction.mode_picker.parallel_title') }}
                            </span>
                            <span>{{ $t('prospect.interaction.mode_picker.parallel_description') }}</span>
                            <span class="hc-cloudtalk-mode-limit-note">
                                {{ $t('prospect.interaction.mode_picker.parallel_selection_cap', { limit: parallelLimit }) }}
                            </span>
                            <span v-if="prospectCount > parallelLimit" class="hc-cloudtalk-mode-limit-warning">
                                {{ $t('prospect.interaction.mode_picker.parallel_selection_limit', { limit: parallelLimit }) }}
                            </span>
                        </button>
                        <button
                            type="button"
                            class="hc-cloudtalk-mode-cancel"
                            @click="$emit('cancel')"
                        >
                            {{ $t('prospect.interaction.mode_picker.cancel') }}
                        </button>
                    </template>
                    <template v-else>
                        <label class="hc-cloudtalk-campaign-name">
                            <span>{{ $t('prospect.interaction.mode_picker.campaign_name_label') }}</span>
                            <input
                                ref="campaignNameInput"
                                v-model="campaignNameDraft"
                                type="text"
                                maxlength="40"
                                autocomplete="off"
                                @keydown.enter.prevent="confirmParallel"
                            />
                            <small>{{ $t('prospect.interaction.mode_picker.campaign_name_hint') }}</small>
                        </label>
                        <div class="hc-cloudtalk-campaign-name-actions">
                            <button
                                type="button"
                                class="hc-cloudtalk-campaign-name-back"
                                @click="backToModes"
                            >
                                {{ $t('prospect.interaction.mode_picker.campaign_name_back') }}
                            </button>
                            <button
                                type="button"
                                class="hc-cloudtalk-campaign-name-continue"
                                @click="confirmParallel"
                            >
                                {{ $t('prospect.interaction.mode_picker.campaign_name_continue') }}
                            </button>
                        </div>
                    </template>
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
.hc-cloudtalk-campaign-name { display: flex; flex-direction: column; gap: 6px; color: #344054; font-size: 12px; font-weight: 600; }
.hc-cloudtalk-campaign-name input { width: 100%; min-height: 36px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #344054; font: inherit; font-weight: 400; }
.hc-cloudtalk-campaign-name small { color: #667085; font-size: 11px; font-weight: 400; }
.hc-cloudtalk-campaign-name-actions { display: flex; justify-content: space-between; gap: 10px; }
.hc-cloudtalk-campaign-name-back,
.hc-cloudtalk-campaign-name-continue { min-height: 34px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: 600; }
.hc-cloudtalk-campaign-name-back { background: #fff; color: #475467; }
.hc-cloudtalk-campaign-name-continue { border-color: #0f77d4; background: #0f77d4; color: #fff; }
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
.hc-cloudtalk-mode-option:disabled { border-color: #cbd5e1; background: #f8fafc; color: #667085; cursor: not-allowed; }
.hc-cloudtalk-mode-option:disabled:hover { border-color: #cbd5e1; background: #f8fafc; }
.hc-cloudtalk-mode-limit-note { color: #667085; }
.hc-cloudtalk-mode-limit-warning { color: #b42318; font-weight: 600; }
.hc-cloudtalk-mode-option:focus-visible,
.hc-cloudtalk-mode-cancel:focus-visible { outline: 2px solid #0f77d4; outline-offset: 2px; }
.hc-cloudtalk-mode-option-title { display: flex; align-items: center; gap: 9px; color: #24364b; font-size: 14px; font-weight: 700; }
.hc-cloudtalk-mode-option-title i { width: 16px; color: #0f77d4; text-align: center; }
.hc-cloudtalk-mode-cancel { align-self: flex-end; padding: 7px 10px; border: 0; background: transparent; color: #667085; font-size: 13px; cursor: pointer; }
.dark .hc-cloudtalk-mode-option { border-color: #566172; background: #263344; color: #b0bac8; }
.dark .hc-cloudtalk-mode-option:hover { border-color: #57a6ec; background: #2a3a4d; }
.dark .hc-cloudtalk-mode-option:disabled,
.dark .hc-cloudtalk-mode-option:disabled:hover { border-color: #566172; background: #263344; color: #b0bac8; }
.dark .hc-cloudtalk-mode-limit-note { color: #b0bac8; }
.dark .hc-cloudtalk-mode-limit-warning { color: #fda29b; }
.dark .hc-cloudtalk-mode-option-title { color: #e2e8f0; }
.dark .hc-cloudtalk-mode-cancel { color: #b0bac8; }
.dark .hc-cloudtalk-campaign-name { color: #e2e8f0; }
.dark .hc-cloudtalk-campaign-name input { border-color: #566172; background: #263344; color: #e2e8f0; }
.dark .hc-cloudtalk-campaign-name small { color: #b0bac8; }
.dark .hc-cloudtalk-campaign-name-back { border-color: #566172; background: #263344; color: #e2e8f0; }
</style>

<script>
export default {
    emits: ["sequential", "parallel", "cancel"],
    props: {
        visible: { type: Boolean, default: false },
        prospectCount: { type: Number, default: 0 },
        parallelLimit: { type: Number, default: 10 },
        campaignName: { type: String, default: "" },
    },
    data() {
        return { previousFocus: null, step: "mode", campaignNameDraft: this.campaignName };
    },
    watch: {
        campaignName(value) {
            if (this.step !== "campaign-name") this.campaignNameDraft = value;
        },
        async visible(isVisible, wasVisible) {
            if (isVisible) {
                this.step = "mode";
                this.campaignNameDraft = this.campaignName;
                if (!wasVisible) this.previousFocus = document.activeElement;
                await this.$nextTick();
                if (this.visible) this.$refs.dialog?.querySelector("button")?.focus();
            } else {
                this.step = "mode";
                if (this.previousFocus?.isConnected) this.previousFocus.focus();
                this.previousFocus = null;
            }
        },
    },
    methods: {
        openCampaignNameStep() {
            this.campaignNameDraft = this.campaignName;
            this.step = "campaign-name";
            this.$nextTick(() => this.$refs.campaignNameInput?.focus());
        },
        backToModes() {
            this.step = "mode";
            this.$nextTick(() => this.$refs.parallelButton?.focus());
        },
        confirmParallel() {
            this.$emit("parallel", this.campaignNameDraft);
        },
        trapFocus(event) {
            const targets = [...this.$refs.dialog.querySelectorAll("button:not([disabled]), input:not([disabled])")];
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
