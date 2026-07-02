<template>
    <item>
        <icon class="fa fa-shopping-cart" />
        <div class="hc-item-main-content" v-text="order.name"></div>
        <!--icon class="fa fa-cog" @click.stop.prevent="edit" /-->
    </item>
</template>

<script>
export default {
    props: {
        order: {
            type: Object,
        },
    },
};
</script>
