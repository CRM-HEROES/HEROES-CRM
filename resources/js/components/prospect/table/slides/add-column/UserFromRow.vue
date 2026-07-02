<template>
    <item tag="a">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
export default {
    props: {
        user: {
            type: Object,
        },
    },
};
</script>
