<template>
    <item class="hc-campaign-rule-user-row">
        <icon class="fa fa-user" icon-size="11" :size="30" />
        <div class="hc-item-main-content" v-text="user.name"></div>
    </item>
</template>

<style>
.hc-campaign-rule-user-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
export default {
    props: {
        user: {
            type: Object,
        },
    },
};
</script>
