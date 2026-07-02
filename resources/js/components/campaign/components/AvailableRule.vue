<template>
    <item>
        <icon
            :class="rule.icon ? rule.icon : 'fa fa-filter'"
            :size="26"
            :style="{ color: rule.style['background-color'] }"
        />
        <div class="hc-item-main-content" v-text="rule.name"></div>
    </item>
</template>

<script>
export default {
    props: {
        rule: {
            type: Object,
        },
    },
};
</script>
