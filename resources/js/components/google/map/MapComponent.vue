<script>
import { ListenersMixin } from "./ListenersMixin";

export default {
  inject: ["google", "map"],
  mixins: [ListenersMixin],

  props: {
    options: {
      type: Object,
      default: () => ({}),
    },
    component: {
      type: String,
      required: true,
    },
  },

  /**
   * Do not render anything
   */
  render() {
    return null;
  },

  created() {
    const component = this.google.maps[this.component];

    this.item = new component({
      map: this.map,
      ...this.options,
    });

    this.addListeners();
  },

  beforeUnmount() {
    if (this.item) {
      this.item.setMap(null);
    }
  },

  watch: {
    /**
     * Update component options
     * @param newValue
     */
    options(newValue) {
      this.item.setOptions(newValue);
    },
  },
};
</script>
