<template>
    <item
        :class="[
            'hc-prospect-import-column',
            mapping ? 'hc-prospect-import-column-mapped' : '',
        ]"
    >
        <icon v-if="mapping" class="fa fa-link" />
        <icon v-else class="fa fa-columns" />
        <div class="hc-item-main-content" v-text="column"></div>
        <div
            v-if="mapping"
            class="hc-prospect-import-column-mapping"
            v-text="mapping"
        ></div>
        <icon class="fa fa-copy" />
    </item>
</template>

<style>
.hc-prospect-import-column > .hc-item-main-content {
    color: #333333;
}
.hc-prospect-import-column > .hc-icon {
    color: #bbbbbb;
}
.hc-prospect-import-column-mapped > .hc-icon {
    color: #333333;
}
.hc-prospect-import-column-mapped > .hc-item-main-content {
    color: #111111;
}
.hc-prospect-import-column-mapping {
    color: #2891d2;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
export default {
    props: {
        column: {
            type: String,
        },
        mapping: {
            type: String,
        },
    },
};
</script>
