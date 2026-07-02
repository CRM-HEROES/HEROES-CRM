<template>
    <map-component component="Circle" :options="options" />
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
