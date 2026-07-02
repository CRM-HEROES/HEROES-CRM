<script>
import { ListenersMixin } from "./ListenersMixin";

export default {
  inject: ["google", "map"],
  mixins: [ListenersMixin],

  props: {
    marker: {
      type: Object,
    },
    options: {
      type: Object,
      default: () => ({}),
    },
  },

  /**
   * Do not render anything
   */
  render() {
    return null;
  },

  created() {
    const { InfoWindow } = this.google.maps;
    this.item = new InfoWindow(this.options);

    this.addListeners();
  },

  watch: {
    /**
     * When a marker is associated to the infowindow
     * Open the infowindow for this marker
     * @param newValue
     */
    marker(newValue) {
      if (!newValue) {
        return;
      }

      this.item.open(this.map, newValue);
    },

    /**
     * Update infowindow options
     * @param newValue
     */
    options(newValue) {
      if (!newValue) {
        return;
      }

      this.item.setOptions(this.options);
    },
  },
};
</script>
