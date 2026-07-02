<template>
    <div
        :class="[
            'hc-login-email-autocompletion-row',
            selected ? 'selected' : '',
        ]"
        v-text="email"
    ></div>
</template>

<style>
.hc-login-email-autocompletion-row {
    width: 100%;
    height: 36px;
    line-height: 36px;
    padding: 0 15px;
    cursor: pointer;
}
.hc-login-email-autocompletion-row.selected,
.hc-login-email-autocompletion-row:hover {
    background-color: #eee;
}
</style>

<script>
export default {
    props: {
        email: {
            type: String,
        },
        selected: {
            type: Boolean,
        },
    },
};
</script>
