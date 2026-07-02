<template>
    <item>
        <icon class="fa fa-file-alt" />
        <div class="hc-item-main-content" v-text="user.name"></div>
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
