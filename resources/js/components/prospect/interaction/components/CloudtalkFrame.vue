<template>
    <div class="hc-flex-column" style="height: 100%">
        <item @click="$emit('back')" class="bordered">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content"
                v-text="$t('prospect.interaction.call_by_cloudtalk')"
            ></div>
        </item>
        <div
            style="
                flex: 1;
                width: 100%;
                height: 100%;
                overflow: auto;
                position: relative;
            "
        ></div>
    </div>
</template>

<script>
export default {
    emits: ["back"],
};
</script>
