<template>
    <div class="hc-global-user-profile-bloc">
        <item class="hc-global-user-profile-bloc-header">
            <icon :class="icon" />
            <div class="hc-item-main-content" v-text="name"></div>
            <slot name="options"></slot>
        </item>
        <div class="hc-global-user-profile-bloc-body">
            <slot name="body"></slot>
        </div>
    </div>
</template>

<style>
.hc-global-user-profile-bloc {
    width: 100%;
    background-color: white;
    border-radius: 10px;
}

.hc-global-user-profile-bloc-body {
    position: relative;
    font-size: 12px;
}

.hc-global-user-profile-bloc-header > .hc-item-main-content {
    font-weight: bold;
}

.hc-global-user-profile-item-project {
    color: #7939b8;
    font-size: 11px;
    font-weight: 500;
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
    },
};
</script>
