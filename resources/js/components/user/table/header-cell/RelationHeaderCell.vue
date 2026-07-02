<template>
    <label class="hc-relation-header-cell-label">
        <div class="hc-relation-header-cell-options">
            <span class="fa fa-filter"></span>
        </div>

        <span v-text="column.name"></span>
    </label>
</template>

<script>
export default {
    props: {
        /**
         * Column
         */
        column: {
            type: Object,
        },
    },
};
</script>
