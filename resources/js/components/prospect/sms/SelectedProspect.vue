<template>
    <div
        class="hc-prospect-sms-selected-prospect"
        v-tooltip="prospect.mobile_phone_number"
        @click.prevent="$emit('remove', prospect)"
    >
        <div
            class="hc-prospect-sms-selected-prospect-name"
            v-text="
                prospect.first_name ? prospect.first_name : prospect.last_name
            "
        ></div>
        <icon class="fa fa-times icon-red" size="30" />
    </div>
</template>

<style>
.hc-prospect-sms-selected-prospect {
    float: left;
    margin-right: 5px;
    margin-bottom: 5px;
    display: flex;
    flex-direction: row;
    padding: 0 0 0 10px;
    background-color: #eee;
    align-items: center;
    border-radius: 3px;
    cursor: pointer;
}
</style>

<script>
export default {
    props: {
        prospect: {
            type: Object,
        },
    },
};
</script>
