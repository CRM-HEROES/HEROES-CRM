<template>
    <tag
        class="hc-map-prospect-label"
        :text="label.name"
        :color="label.color"
        :bgcolor="label.bgcolor"
    />
</template>

<style>
.hc-map-prospect-label {
    margin: 1px;
}
</style>

<script>
export default {
    props: {
        /**
         * Label
         */
        label: {
            type: Object,
        },

        /**
         */
        prospect: {
            type: Object,
        },
    },
};
</script>
