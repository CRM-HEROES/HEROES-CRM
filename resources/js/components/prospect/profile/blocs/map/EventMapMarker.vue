<template>
    <google-map-marker
        :options="{
            position: position,
            icon: {
                url: icon,
            },
        }"
    />
</template>

<script>
// Components
import GoogleMapMarker from "@/components/google/map/Marker.vue";

export default {
    components: {
        GoogleMapMarker,
    },

    props: {
        position: {
            type: Object,
        },
        icon: {
            type: String,
        },
    },
};
</script>
