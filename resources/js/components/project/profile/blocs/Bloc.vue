<template>
    <tree-layout class="hc-project-profile-bloc" :tabulation="0" :open="open">
        <template #header>
            <item class="hc-project-profile-bloc-header">
                <icon :class="icon" />
                <div class="hc-item-main-content" v-text="name"></div>
                <slot name="options"></slot>
            </item>
        </template>
        <template #body>
            <div class="hc-project-profile-bloc-body">
                <slot name="body"></slot>
            </div>
        </template>
    </tree-layout>
</template>

<style>
.hc-project-profile-bloc {
    width: 100%;
    background-color: white;
    border-radius: 10px;
}

.hc-project-profile-bloc-body {
    position: relative;
    font-size: 12px;
}

.hc-project-profile-bloc-header > .hc-item-main-content {
    font-weight: bold;
}
</style>

<script>
export default {
    props: {
        icon: {
            type: String,
        },
        name: {
            type: String,
        },
        open: {
            type: Boolean,
            default: deviceType() == "desktop",
        },
    },
};
</script>
