<template>
    <label class="hc-v-field">
        <span v-text="label + ' : ' + (required !== false ? '*' : '')"></span>
        <div><slot :label="label"></slot></div>
    </label>
</template>

<style>
.hc-v-field {
    width: 100%;
    display: flex;
    flex-direction: column;
    font-size: 12px;
    border-radius: 3px;
    position: relative;
    color: #333333;
}

.hc-v-field > span {
    width: 100%;
    line-height: 20px;
    display: inline-block;
    padding: 0 5px;
    font-weight: 600;
}

.hc-v-field > div {
    flex: 1;
    padding: 5px;
}

.hc-v-field > div > * {
    border: 1px solid #dddddd;
    min-height: 34px;
    width: 100%;
    padding: 5px 10px;
    border-radius: 3px;
    overflow: hidden;
    float: left;
    background: none;
    color: #333333;
}

.hc-v-field > div > div {
    padding: 0;
}

.hc-v-field > div > div > input {
    padding: 5px 10px;
    border: none;
}

.hc-v-field > div > *:focus {
    outline: 2px solid #12a0f3;
    border-color: transparent;
}

.hc-v-field > div > input[type="color"] {
    padding: 0;
    border: none;
}

.hc-v-field > div > textarea {
    height: 60px;
}

.dark .hc-v-field {
    color: #bbbbbb;
}

.dark .hc-v-field > div > * {
    border-color: #444444;
}

.hc-line-operator-config-fields {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.hc-line-secret-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 34px;
}

.hc-line-secret-preview-value {
    flex: 1;
    min-width: 0;
    padding: 5px 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #333333;
}

.hc-line-secret-preview-edit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 34px;
    width: 34px;
    min-height: 34px;
    border: none;
    border-left: 1px solid #dddddd;
    background: transparent;
    color: #12a0f3;
    cursor: pointer;
}

.hc-line-secret-preview-edit:focus {
    outline: 2px solid #12a0f3;
    outline-offset: -2px;
}

.dark .hc-line-secret-preview-value {
    color: #bbbbbb;
}

.dark .hc-line-secret-preview-edit {
    border-left-color: #444444;
}

.hc-cloudtalk-agent-loader {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.hc-cloudtalk-agent-message {
    font-size: 12px;
    padding: 8px 10px;
    border-radius: 6px;
    background: #f8f9fa;
    color: #495057;
}

.hc-cloudtalk-agent-message.success {
    background: #e8f5e9;
    color: #2e7d32;
}

.hc-cloudtalk-agent-message.error {
    background: #ffebee;
    color: #c62828;
}
</style>

<script>
export default {
    props: {
        label: {
            type: String,
        },
        required: {
            type: Boolean,
            default: false,
        },
    },
};
</script>
