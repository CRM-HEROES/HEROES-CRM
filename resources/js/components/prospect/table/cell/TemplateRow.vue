<template>
    <item>
        <icon class="fa fa-file-alt" />
        <div class="hc-item-main-content" v-text="template.name"></div>
        <icon
            class="fa fa-paper-plane icon-blue"
            @click.prevent.stop="$emit('send')"
        />
    </item>
</template>

<script>
export default {
    props: {
        template: {
            type: Object,
        },
    },
};
</script>
