<script>
export default {
    inject: ["google"],

    props: {
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
        const { DirectionsService } = this.google.maps;

        this.item = new DirectionsService();

        this.calculateDirections();
    },

    methods: {
        calculateDirections() {
            if (!this.options.origin || !this.options.destination) {
                return;
            }

            this.item.route(this.options, (response, status) => {
                if (status === "OK") {
                    this.$emit("directions-loaded", response);
                } else {
                    window.alert("Directions request failed due to " + status);
                }
            });
        },
    },

    watch: {
        /**
         * Update direction options
         * @param newValue
         */
        options() {
            this.calculateDirections();
        },
    },
};
</script>
