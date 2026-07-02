<template>
    <tree-layout class="hc-prospect-profile-bloc" :tabulation="0" :open="open">
        <template #header>
            <item class="hc-prospect-profile-bloc-header bloc-handle">
                <icon :class="icon" />
                <div class="hc-item-main-content" v-text="name"></div>
                <slot name="options"></slot>
                <icon class="fa fa-arrows" color="#CCCCCC" />
                <icon class="fa fa-caret-down" />
            </item>
        </template>
        <template #body>
            <div class="hc-prospect-profile-bloc-body">
                <slot name="body"></slot>
            </div>
        </template>
    </tree-layout>
</template>

<style>
.hc-prospect-profile-bloc {
    width: 100%;
    background-color: white;
    border-radius: 10px;
}

.hc-prospect-profile-bloc-body {
    position: relative;
    font-size: 12px;
    min-height: 50px;
}

.hc-prospect-profile-bloc-header > .hc-item-main-content {
    font-weight: bold;
}

@media (max-width: 767px) {
    #hc-prospect-profile-body-columns {
        flex-direction: column;
    }

    #hc-prospect-profile-body-columns > div {
        width: 100%;
    }

    .hc-prospect-profile-bloc-header .fa-arrows {
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
