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

.hc-buttons > button > i,
.hc-buttons > a > i,
.hc-buttons > label > i {
    margin-right: 10px;
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

.hc-buttons > button:disabled {
    background-color: #aaaaaa;
    cursor: not-allowed;
}

.hc-buttons > label > input[type="file"] {
    display: none;
}

.dark .hc-buttons {
    border-top: 1px solid #444444;
    background-color: #2e302e;
}

@media (max-width: 767px) {
    .hc-buttons {
        border-top: 1px solid #eeeeee;
        padding: 10px 7px;
    }

    .hc-buttons > button,
    .hc-buttons > a,
    .hc-buttons > label {
        font-size: 14px;
        height: 40px;
        line-height: 40px;
    }
}
</style>

<script>
export default {};
</script>
