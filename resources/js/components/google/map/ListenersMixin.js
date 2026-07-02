export const ListenersMixin = {
  methods: {
    addListeners() {
      this.events.forEach((e) => {
        this.google.maps.event.addListener(this.item, e, (event) => {
          this.$emit(e, event, this.item);
        });
      });
    },
    removeListeners() {
      this.events.forEach((e) => {
        this.google.maps.event.clearListeners(this.item, e);
      });
    },
  },

  computed: {
    events() {
      return Object.keys(this.$attrs)
        .filter((attrName) => attrName.startsWith("on"))
        .map((attrName) => attrName.slice(2).toLowerCase());
    },
  },

  beforeUnmount() {
    if (this.item) {
      this.removeListeners();
    }
  },
};
