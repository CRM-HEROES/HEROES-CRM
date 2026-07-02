<template>
    <item
        :class="[
            'hc-prospect-import-duplicate',
            mapping ? 'hc-prospect-import-duplicate-mapped' : '',
        ]"
    >
        <icon class="fa fa-copy" />
        <div class="hc-item-main-content" v-text="duplicateFullName"></div>
        <div
            class="hc-prospect-import-duplicate-from"
            v-text="prospectFullName"
        ></div>
    </item>
</template>

<style>
.hc-prospect-import-duplicate > .hc-item-main-content {
    color: #333333;
}
.hc-prospect-import-duplicate > .hc-icon {
    color: #bbbbbb;
}
.hc-prospect-import-duplicate-mapped > .hc-icon {
    color: #333333;
}
.hc-prospect-import-duplicate-mapped > .hc-item-main-content {
    color: #111111;
}
.hc-prospect-import-duplicate-from {
    color: #2891d2;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 10px;
}
</style>

<script>
export default {
    props: {
        duplicate: {
            type: Object,
        },
        prospect: {
            type: Object,
        },
    },

    computed: {
        duplicateFullName() {
            return [this.duplicate.first_name, this.duplicate.last_name].join(
                " "
            );
        },
        prospectFullName() {
            return [this.prospect.first_name, this.prospect.last_name].join(
                " "
            );
        },
    },
};
</script>
