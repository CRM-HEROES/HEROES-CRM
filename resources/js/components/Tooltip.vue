<template>
    <div
        id="hc-tooltip"
        ref="tooltip"
        :class="element ? 'shown' : ''"
        v-text="text"
        :style="style"
    ></div>
</template>

<style>
#hc-tooltip {
    visibility: hidden;
    left: 0;
    top: 0;
    opacity: 0;
    position: fixed;
    font-size: 11px;
    color: white;
    line-height: 15px;
    background-color: #333333;
    padding: 5px 10px;
    border-radius: 4px;
    transition: transform 100ms ease-out, opacity 100ms ease-out;
    z-index: 1000;
    max-width: 200px;
    pointer-events: none;
}

#hc-tooltip.shown {
    opacity: 1;
    visibility: visible;
    transform: scale(1);
}
</style>

<script>
import EventBus from "@/utils/event-bus";

export default {
    props: [],

    data() {
        return {
            element: null,
            givenPosition: null,
            text: null,
            style: {},
        };
    },

    modelModifiers: {
        default: () => ({}),
    },

    created() {
        EventBus.on("tooltip", (element, text, position) => {
            this.tooltip(element, text, position);
        });
    },

    methods: {
        async tooltip(element, text, position) {
            this.element = element;
            this.text = text;
            this.givenPosition = position;

            await this.$nextTick();
            this.style = this.getStyle();
        },

        getStyle() {
            if (!this.element) {
                return {};
            }

            if (!this.$refs.tooltip) {
                return {};
            }

            let elementRect = this.element.getBoundingClientRect();
            const margin = 2;
            const tooltipRect = this.$refs.tooltip.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;

            if (
                elementRect.left < 0 ||
                elementRect.left + elementRect.width > windowWidth ||
                elementRect.top < 0 ||
                elementRect.top + elementRect.height > windowHeight
            ) {
                this.element.scrollIntoView();
                elementRect = this.element.getBoundingClientRect();
                this.update++;
            }

            if (this.position == "left") {
                let left = elementRect.left + elementRect.width + margin;
                let top =
                    elementRect.top +
                    (elementRect.height - tooltipRect.height) / 2;
                let right = "unset";
                let bottom = "unset";

                if (top < 0) {
                    top = 0;
                } else if (top > windowHeight - tooltipRect.height) {
                    top = windowHeight - tooltipRect.height;
                }

                if (left > windowWidth - elementRect.width) {
                    left = Math.min(
                        elementRect.left + (windowWidth - elementRect.left) / 2,
                        left - elementRect.width / 2
                    );
                }

                return {
                    left: left + "px",
                    top: top + "px",
                    right,
                    bottom,
                };
            } else if (this.position == "top") {
                let left =
                    elementRect.left +
                    (elementRect.width - tooltipRect.width) / 2;
                let top = elementRect.top + elementRect.height + margin;
                let right = "unset";
                let bottom = "unset";

                if (left < 0) {
                    left = 0;
                } else if (left > windowWidth - tooltipRect.width) {
                    left = windowWidth - tooltipRect.width;
                }

                if (top > windowHeight - elementRect.height) {
                    top = Math.min(
                        elementRect.top + (windowHeight - elementRect.top) / 2,
                        top - elementRect.height / 2
                    );
                }

                return {
                    left: left + "px",
                    top: top + "px",
                    right,
                    bottom,
                };
            } else if (this.position == "right") {
                let left = "unset";
                let top =
                    elementRect.top +
                    (elementRect.height - tooltipRect.height) / 2;
                let right = windowWidth - elementRect.left + margin;
                let bottom = "unset";

                if (top < 0) {
                    top = 0;
                } else if (top > windowHeight - tooltipRect.height) {
                    top = windowHeight - tooltipRect.height;
                }

                if (right > windowWidth - elementRect.width) {
                    right = Math.min(
                        (windowWidth - elementRect.left) / 2,
                        right - elementRect.width / 2
                    );
                }

                return {
                    left,
                    top: top + "px",
                    right: right + "px",
                    bottom,
                };
            } else {
                let left =
                    elementRect.left +
                    (elementRect.width - tooltipRect.width) / 2;

                let top = "unset";
                let right = "unset";
                let bottom = windowHeight - elementRect.top + margin;

                if (left < 0) {
                    left = 0;
                } else if (left > windowWidth - tooltipRect.width) {
                    left = windowWidth - tooltipRect.width;
                }

                if (bottom > windowHeight - elementRect.height) {
                    bottom = Math.min(
                        (windowHeight - elementRect.top) / 2,
                        bottom - elementRect.height / 2
                    );
                }

                return {
                    left: left + "px",
                    top,
                    right,
                    bottom: bottom + "px",
                };
            }
        },
    },

    computed: {
        position() {
            if (this.givenPosition) {
                return this.givenPosition;
            }

            const elementRect = this.element.getBoundingClientRect();
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            const elementRectLeft = elementRect.left + elementRect.width / 2;
            const elementRectTop = elementRect.top + elementRect.height / 2;
            const A = (windowHeight / windowWidth) * elementRectLeft;
            const B =
                windowHeight - (windowHeight / windowWidth) * elementRectLeft;

            if (deviceType() == "desktop") {
                if (elementRectTop <= A && elementRectTop <= B) {
                    return "top";
                } else if (elementRectTop >= A && elementRectTop >= B) {
                    return "bottom";
                } else if (elementRectTop >= A && elementRectTop <= B) {
                    return "left";
                } else {
                    return "right";
                }
            } else {
                if (elementRectTop <= windowHeight / 2) {
                    return "top";
                } else {
                    return "bottom";
                }
            }
        },
    },
};
</script>
