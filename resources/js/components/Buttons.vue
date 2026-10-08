<template>
    <div class="hc-buttons">
        <slot></slot>
    </div>
</template>

<style>
.hc-buttons {
    border-top: 1px solid #eeeeee;
    padding: 7px;
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: center;
    gap: 5px;
    position: sticky;
    bottom: 0;
    background-color: white;
    position: sticky;
    bottom: 0;
}

.hc-buttons:empty {
    display: none;
}

.hc-button,
.hc-button-secondary,
.hc-buttons > button,
.hc-buttons > a,
.hc-buttons > label {
    background-color: #12a0f3;
    border-radius: 5px;
    color: white !important;
    border: none;
    font-size: 12px;
    height: 32px;
    padding: 0 12px;
    line-height: 34px;
    cursor: pointer;
    text-decoration: none;
}

.hc-button,
.hc-button-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.hc-buttons > button > i,
.hc-buttons > a > i,
.hc-buttons > label > i {
    margin-right: 10px;
}

.hc-button-secondary,
.hc-buttons > *.hc-button-secondary {
    background-color: #f1f5f9;
    border: 1px solid #d7e0ea;
    color: #2c3e50 !important;
}

.hc-button-secondary:hover:not(:disabled),
.hc-buttons > *.hc-button-secondary:hover:not(:disabled) {
    background-color: #e6eef7;
    border-color: #bfd0df;
}

.hc-buttons > *.hc-button-danger {
    background-color: #c33434;
}

.hc-buttons > *.hc-button-warning {
    background-color: #c36d34;
}

.hc-buttons > *.hc-button-grey {
    background-color: #888888;
}

.hc-buttons > *.hc-button-grey:hover {
    background-color: #12a0f3;
}

.hc-button:disabled,
.hc-button-secondary:disabled,
.hc-buttons > button:disabled {
    background-color: #aaaaaa;
    border-color: #aaaaaa;
    color: white !important;
    cursor: not-allowed;
}

.hc-buttons > label > input[type="file"] {
    display: none;
}

.dark .hc-buttons {
    border-top: 1px solid #444444;
    background-color: #2e302e;
}

.dark .hc-button-secondary,
.dark .hc-buttons > *.hc-button-secondary {
    background-color: #3a3d40;
    border-color: #55595d;
    color: #eeeeee !important;
}

.dark .hc-button-secondary:hover:not(:disabled),
.dark .hc-buttons > *.hc-button-secondary:hover:not(:disabled) {
    background-color: #45494d;
    border-color: #666b70;
}

@media (max-width: 767px) {
    .hc-buttons {
        border-top: 1px solid #eeeeee;
        padding: 10px 7px;
    }

    .hc-buttons > button,
    .hc-buttons > a,
    .hc-buttons > label,
    .hc-button,
    .hc-button-secondary {
        font-size: 14px;
        height: 40px;
        line-height: 40px;
    }
}
</style>

<script>
export default {};
</script>
