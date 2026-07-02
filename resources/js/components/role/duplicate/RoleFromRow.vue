<template>
    <item tag="a">
        <icon class="fa fa-user-tie" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
export default {
    props: {
        role: {
            type: Object,
        },
    },
};
</script>
