<template>
    <label class="hc-h-field">
        <span v-text="label + ' : '"></span>
        <div><slot :label="label"></slot><span></span></div>
    </label>
</template>

<style>
.hc-h-field {
    width: 100%;
    min-height: 30px;
    display: flex;
    flex-direction: row;
    /* border: 1px solid #dddddd; */
    font-size: 12px;
    border-radius: 3px;
    position: relative;
    align-items: stretch;
}

.hc-h-field > span {
    height: 100%;
    line-height: 20px;
    display: inline-block;
    padding: 5px 10px;
    font-weight: 600;
    font-size: 11px;
    max-width: 70%;
}

.hc-h-field > div {
    flex: 1;
    padding: 5px;
    border-bottom: 1px solid #ddd;
}

.hc-h-field > div > * {
    border: none;
    outline: none;
    height: 100%;
    width: 100%;
    padding: 0;
    border-radius: 5px;
    overflow: hidden;
}

.hc-h-field > div > span {
    top: 0;
    left: 0;
    pointer-events: none;
    position: absolute;
}

.hc-h-field > div > *:focus {
    outline: none;
}

.hc-h-field > div > *:focus + span {
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    border-radius: 3px;
    border: 2px solid #12a0f3;
}
</style>

<script>
export default {
    props: {
        label: {
            type: String,
        },
    },
};
</script>
