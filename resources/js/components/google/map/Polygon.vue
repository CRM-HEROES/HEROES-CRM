<template>
    <map-component component="Polygon" :options="options" />
</template>

<script>
import MapComponent from "./MapComponent.vue";

export default {
    components: {
        MapComponent,
    },
    props: {
        options: {
            type: Object,
            required: true,
        },
    },
};
</script>
