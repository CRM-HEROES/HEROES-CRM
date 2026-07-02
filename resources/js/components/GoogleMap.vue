<template>
    <google-map style="height: 300px" :api-key="apiKey" :options="options">
        <slot />
    </google-map>
</template>

<script>
import GoogleMap from "@/components/google/map/Map.vue";

export default {
    components: {
        GoogleMap,
    },

    props: {
        options: {
            type: Object,
        },
    },

    computed: {
        apiKey() {
            return process.env.MIX_GOOGLE_MAP_API_KEY;
        },
    },
};
</script>
