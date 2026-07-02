<template>
  <map-component component="DirectionsRenderer" :options="options" />
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
      default: () => ({}),
    },
  },
};
</script>
