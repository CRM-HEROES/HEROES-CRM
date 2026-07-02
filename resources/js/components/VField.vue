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
