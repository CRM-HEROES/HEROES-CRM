<template>
    <item class="hc-search-item" @click="action.action">
        <icon v-if="action.icon" :class="action.icon" :size="28" />
        <icon v-else-if="action.logo" :size="28" v-html="action.logo"></icon>
        <div class="hc-item-main-content hc-flex-column">
            <span class="hc-search-title" v-text="action.name"></span>
            <span
                v-if="action.description"
                class="hc-search-description"
                v-html="action.description"
            ></span>
        </div>
    </item>
</template>

<script>
export default {
    props: {
        action: {
            type: Object,
        },
    },
};
</script>
