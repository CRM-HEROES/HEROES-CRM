<template>
    <button
        class="hc-ag-btn"
        :class="[`is-${variant}`, `is-${size}`, { 'is-block': block, 'is-loading': loading, 'is-icon': iconOnly }]"
        :type="type"
        :disabled="disabled || loading"
        :aria-busy="loading ? 'true' : null"
    >
        <span v-if="loading" class="hc-ag-btn-spinner" aria-hidden="true"></span>
        <i v-else-if="icon" :class="icon" aria-hidden="true"></i>
        <span v-if="!iconOnly" class="hc-ag-btn-label"><slot></slot></span>
    </button>
</template>

<style>
/* Shared look of the AI agents screens */
:root {
    --ag-accent: #12a0f3;
    --ag-accent-strong: #0b86d1;
    --ag-accent-soft: rgba(18, 160, 243, 0.1);
    --ag-ink: #0f172a;
    --ag-ink-soft: #334155;
    --ag-muted: #64748b;
    --ag-line: #e2e8f0;
    --ag-surface: #f8fafc;
    --ag-danger: #dc2626;
    --ag-danger-strong: #b91c1c;
    --ag-danger-soft: #fef2f2;
    --ag-success: #16a34a;
    --ag-success-soft: #f0fdf4;
    --ag-warn: #d97706;
    --ag-warn-soft: #fffbeb;
    --ag-radius: 12px;
    --ag-shadow: 0 1px 2px rgba(15, 23, 42, 0.06), 0 6px 18px rgba(15, 23, 42, 0.06);
}

.hc-ag-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 1px solid transparent;
    border-radius: 10px;
    font: inherit;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease,
        color 0.15s ease, transform 0.05s ease;
}

.hc-ag-btn.is-md {
    padding: 10px 18px;
    font-size: 14px;
}

.hc-ag-btn.is-sm {
    padding: 7px 12px;
    font-size: 13px;
    border-radius: 8px;
}

.hc-ag-btn.is-icon {
    padding: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
}

.hc-ag-btn.is-icon.is-sm {
    width: 30px;
    height: 30px;
}

.hc-ag-btn.is-block {
    display: flex;
    width: 100%;
}

.hc-ag-btn:active:not(:disabled) {
    transform: translateY(1px);
}

.hc-ag-btn:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(18, 160, 243, 0.35);
}

.hc-ag-btn:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.hc-ag-btn.is-loading {
    cursor: progress;
    opacity: 0.85;
}

/* Primary: the main action of a screen */
.hc-ag-btn.is-primary {
    background: var(--ag-accent);
    color: #fff;
    box-shadow: 0 1px 2px rgba(11, 134, 209, 0.35);
}

.hc-ag-btn.is-primary:hover:not(:disabled) {
    background: var(--ag-accent-strong);
}

/* Success: authorising something */
.hc-ag-btn.is-success {
    background: var(--ag-success);
    color: #fff;
    box-shadow: 0 1px 2px rgba(22, 163, 74, 0.35);
}

.hc-ag-btn.is-success:hover:not(:disabled) {
    background: #15803d;
}

/* Danger: something that cannot be undone */
.hc-ag-btn.is-danger {
    background: var(--ag-danger);
    color: #fff;
    box-shadow: 0 1px 2px rgba(185, 28, 28, 0.35);
}

.hc-ag-btn.is-danger:hover:not(:disabled) {
    background: var(--ag-danger-strong);
}

/* Secondary: a visible but quieter action */
.hc-ag-btn.is-secondary {
    background: #fff;
    border-color: var(--ag-line);
    color: var(--ag-ink-soft);
}

.hc-ag-btn.is-secondary:hover:not(:disabled) {
    border-color: #cbd5e1;
    background: var(--ag-surface);
}

/* Ghost: no frame until hovered */
.hc-ag-btn.is-ghost {
    background: transparent;
    color: var(--ag-muted);
}

.hc-ag-btn.is-ghost:hover:not(:disabled) {
    background: rgba(15, 23, 42, 0.06);
    color: var(--ag-ink);
}

.hc-ag-btn.is-ghost-danger {
    background: transparent;
    color: var(--ag-danger);
}

.hc-ag-btn.is-ghost-danger:hover:not(:disabled) {
    background: var(--ag-danger-soft);
}

.hc-ag-btn-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: hc-ag-spin 0.7s linear infinite;
}

@keyframes hc-ag-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .hc-ag-btn,
    .hc-ag-btn-spinner {
        transition: none;
        animation-duration: 2s;
    }
}
</style>

<script>
/**
 * The button of the AI agents screens. One look for every action:
 *   primary    the main action          success   authorise
 *   secondary  a quieter action         danger    delete / cannot be undone
 *   ghost      no frame                 ghost-danger  a quiet destructive link
 */
export default {
    props: {
        variant: {
            type: String,
            default: "secondary",
            validator: (value) => ["primary", "secondary", "ghost", "danger", "success", "ghost-danger"].includes(value),
        },
        size: { type: String, default: "md", validator: (value) => ["sm", "md"].includes(value) },
        icon: { type: String, default: "" },
        // A round button with an icon only: the label then goes in aria-label
        iconOnly: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
        block: { type: Boolean, default: false },
        type: { type: String, default: "button" },
    },
};
</script>
