<template>
    <Teleport to="body">
        <div
            v-if="visible && campaigns.length"
            ref="dialog"
            class="hc-modal active hc-cloudtalk-active-campaign-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="hc-cloudtalk-active-campaign-title"
            @keydown.esc.stop.prevent="$emit('cancel')"
            @keydown.tab="trapFocus"
        >
            <div class="hc-modal-overlay" @click="$emit('cancel')"></div>
            <div class="hc-modal-content">
                <div id="hc-cloudtalk-active-campaign-title" class="hc-modal-header">
                    {{ $t(step === 'actions' ? 'prospect.interaction.mode_picker.active_campaign_title' : 'prospect.interaction.mode_picker.choose_campaign_title') }}
                </div>
                <div class="hc-modal-body">
                    <template v-if="step === 'actions'">
                        <p class="hc-cloudtalk-active-campaign-description">
                            {{ $t(sessionOpen
                                ? 'prospect.interaction.mode_picker.active_campaign_session_description'
                                : 'prospect.interaction.mode_picker.active_campaign_description') }}
                        </p>
                        <button
                            ref="continueActionButton"
                            type="button"
                            class="hc-cloudtalk-active-campaign-option"
                            :disabled="busy"
                            @click="showCampaignList"
                        >
                            <i class="fa fa-list" aria-hidden="true"></i>
                            <span>{{ $t('prospect.interaction.mode_picker.continue_active_campaigns') }}</span>
                        </button>
                        <button
                            type="button"
                            class="hc-cloudtalk-active-campaign-option replace"
                            :disabled="busy"
                            @click="$emit('replace')"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i>
                            <span>{{ $t(sessionOpen
                                ? 'prospect.interaction.mode_picker.replace_active_campaigns_session'
                                : 'prospect.interaction.mode_picker.replace_active_campaigns') }}</span>
                        </button>
                    </template>
                    <template v-else>
                        <p class="hc-cloudtalk-active-campaign-description">
                            {{ $t('prospect.interaction.mode_picker.choose_campaign_description') }}
                        </p>
                        <div class="hc-cloudtalk-active-campaign-list">
                            <article
                                v-for="campaign in campaigns"
                                :key="campaign.id"
                                class="hc-cloudtalk-active-campaign-card"
                            >
                                <div class="hc-cloudtalk-active-campaign-name">
                                    {{ campaign.name || ('Campagne #' + campaign.id) }}
                                </div>
                                <button
                                    type="button"
                                    class="hc-cloudtalk-active-campaign-option"
                                    :disabled="busy"
                                    @click="$emit('continue', campaign)"
                                >
                                    <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                    <span>{{ $t('prospect.interaction.mode_picker.continue_active_campaign') }}</span>
                                </button>
                            </article>
                        </div>
                        <button
                            ref="backButton"
                            type="button"
                            class="hc-cloudtalk-active-campaign-back"
                            :disabled="busy"
                            @click="backToActions"
                        >
                            <i class="fa fa-arrow-left" aria-hidden="true"></i>
                            {{ $t('prospect.interaction.mode_picker.back_to_campaign_actions') }}
                        </button>
                    </template>
                    <button
                        type="button"
                        class="hc-cloudtalk-active-campaign-cancel"
                        :disabled="busy"
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
.hc-cloudtalk-active-campaign-modal { z-index: 1101; }
.hc-cloudtalk-active-campaign-modal .hc-modal-content { width: 520px; max-width: calc(100vw - 32px); }
.hc-cloudtalk-active-campaign-modal .hc-modal-header { height: auto; min-height: 50px; padding: 14px 20px; line-height: 1.5; white-space: normal; }
.hc-cloudtalk-active-campaign-modal .hc-modal-body { gap: 10px; padding: 12px 20px 20px; }
.hc-cloudtalk-active-campaign-description { margin: 0 0 2px; color: #475467; font-size: 13px; line-height: 1.5; }
.hc-cloudtalk-active-campaign-list { display: flex; max-height: min(52vh, 440px); flex-direction: column; gap: 9px; overflow-y: auto; padding: 1px 4px 1px 1px; }
.hc-cloudtalk-active-campaign-card { display: flex; flex-direction: column; gap: 8px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; }
.hc-cloudtalk-active-campaign-name { color: #344054; font-size: 13px; font-weight: 700; line-height: 1.45; overflow-wrap: anywhere; }
.hc-cloudtalk-active-campaign-option { display: flex; width: 100%; align-items: center; justify-content: center; gap: 8px; padding: 11px 14px; border: 1px solid #0f77d4; border-radius: 7px; background: #0f77d4; color: #fff; font-size: 12px; font-weight: 600; line-height: 1.4; cursor: pointer; }
.hc-cloudtalk-active-campaign-option:hover:not(:disabled) { background: #0964b8; }
.hc-cloudtalk-active-campaign-option.replace { border-color: #cbd5e1; background: #fff; color: #334155; }
.hc-cloudtalk-active-campaign-option.replace:hover:not(:disabled) { border-color: #94a3b8; background: #f8fafc; }
.hc-cloudtalk-active-campaign-back { align-self: flex-start; display: inline-flex; align-items: center; gap: 7px; padding: 8px 10px; border: 0; background: transparent; color: #475467; font-size: 12px; cursor: pointer; }
.hc-cloudtalk-active-campaign-option:disabled,
.hc-cloudtalk-active-campaign-back:disabled,
.hc-cloudtalk-active-campaign-cancel:disabled { opacity: .55; cursor: wait; }
.hc-cloudtalk-active-campaign-option:focus-visible,
.hc-cloudtalk-active-campaign-back:focus-visible,
.hc-cloudtalk-active-campaign-cancel:focus-visible { outline: 2px solid #0f77d4; outline-offset: 2px; }
.hc-cloudtalk-active-campaign-cancel { align-self: flex-end; padding: 7px 10px; border: 0; background: transparent; color: #667085; font-size: 13px; cursor: pointer; }
.dark .hc-cloudtalk-active-campaign-description { color: #b0bac8; }
.dark .hc-cloudtalk-active-campaign-card { border-color: #566172; background: #263344; }
.dark .hc-cloudtalk-active-campaign-name { color: #e2e8f0; }
.dark .hc-cloudtalk-active-campaign-option.replace { border-color: #566172; background: #263344; color: #dbe4f0; }
.dark .hc-cloudtalk-active-campaign-option.replace:hover:not(:disabled) { background: #343b46; }
.dark .hc-cloudtalk-active-campaign-back { color: #b0bac8; }
.dark .hc-cloudtalk-active-campaign-cancel { color: #b0bac8; }
</style>

<script>
export default {
    emits: ["continue", "replace", "cancel"],
    props: {
        visible: { type: Boolean, default: false },
        campaigns: { type: Array, default: () => [] },
        busy: { type: Boolean, default: false },
        sessionOpen: { type: Boolean, default: false },
    },
    data() {
        return { previousFocus: null, step: "actions" };
    },
    watch: {
        async visible(isVisible, wasVisible) {
            if (isVisible) {
                this.step = "actions";
                if (!wasVisible) this.previousFocus = document.activeElement;
                await this.$nextTick();
                if (this.visible) this.$refs.dialog?.querySelector("button:not(:disabled)")?.focus();
            } else {
                this.step = "actions";
                if (this.previousFocus?.isConnected) this.previousFocus.focus();
                this.previousFocus = null;
            }
        },
    },
    methods: {
        showCampaignList() {
            this.step = "list";
            this.$nextTick(() => this.$refs.dialog?.querySelector(".hc-cloudtalk-active-campaign-list button:not(:disabled)")?.focus());
        },
        backToActions() {
            this.step = "actions";
            this.$nextTick(() => this.$refs.continueActionButton?.focus());
        },
        trapFocus(event) {
            const targets = [...this.$refs.dialog.querySelectorAll("button:not(:disabled)")];
            if (!targets.length) return;
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
