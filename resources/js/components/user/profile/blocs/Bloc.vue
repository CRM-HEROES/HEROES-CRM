<template>
    <tree-layout class="hc-user-profile-bloc" :tabulation="0" :open="open">
        <template #header>
            <item class="hc-user-profile-bloc-header bloc-handle">
                <icon :class="icon" />
                <div class="hc-item-main-content" v-text="name"></div>
                <slot name="options"></slot>
                <icon class="fa fa-arrows" color="#CCCCCC" />
            </item>
        </template>
        <template #body>
            <div class="hc-user-profile-bloc-body">
                <slot name="body"></slot>
            </div>
        </template>
    </tree-layout>
</template>

<style>
.hc-user-profile-bloc {
    width: 100%;
    background-color: white;
    border-radius: 10px;
}

.hc-user-profile-bloc-body {
    position: relative;
    font-size: 12px;
}

.hc-user-profile-bloc-header > .hc-item-main-content {
    font-weight: bold;
}

@media (max-width: 767px) {
    .hc-user-profile-bloc-header .fa-arrows {
        display: none;
    }
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
