<template>
    <div
        :class="['hc-movable', positionDragging ? 'dragging' : '', ...classes]"
        :style="containerStyle"
        ref="movable"
    >
        <slot></slot>
        <!--
            Field width resize
        -->
        <div
            v-if="wResizable"
            class="hc-movable-w-resize"
            ref="wResize"
            @dblclick="autowidth"
        ></div>

        <!--
            Field height resize
        -->
        <div
            v-if="hResizable"
            class="hc-movable-h-resize"
            ref="hResize"
            @dblclick="autoHeight"
        ></div>

        <!--
            Field width/height resize
        -->
        <div
            v-if="whResizable"
            class="hc-movable-wh-resize"
            ref="whResize"
            @dblclick="autoWidthHeight"
        ></div>

        <!--
            Field width/height resize
        -->
        <div
            v-if="rotatable"
            class="hc-movable-rotate"
            ref="rotate"
            @dblclick="cancelRotate"
        ></div>
    </div>
</template>

<style>
.hc-movable {
    border-radius: 2px;
    cursor: grab;
    font-size: 12px;
    height: auto;
    line-height: normal;
    position: absolute;
    transform-origin: top left;
    transition: box-shadow ease-out 100ms outline ease-out 100ms;
    width: auto;
}

.hc-movable.dragging {
    box-shadow: 0 4px 8px #0005;
    cursor: grabbing;
    z-index: 1000;
}

.hc-movable-h-resize,
.hc-movable-w-resize,
.hc-movable-wh-resize,
.hc-movable-rotate {
    position: absolute;
}

.hc-movable-w-resize {
    bottom: 0;
    cursor: ew-resize;
    height: 100%;
    right: -3px;
    width: 5px;
}

.hc-movable-h-resize {
    bottom: -3px;
    cursor: ns-resize;
    height: 5px;
    right: 0;
    width: 100%;
}

.hc-movable-wh-resize {
    bottom: -4px;
    cursor: nwse-resize;
    height: 8px;
    right: -4px;
    width: 8px;
}

.hc-movable-rotate {
    bottom: -12px;
    cursor: crosshair;
    height: 8px;
    right: -12px;
    transform: rotate(-60deg);
    transition: transform ease-out 0ms;
    width: 8px;
}

.hc-movable-rotate:hover {
    transform: rotate(0deg);
    transition: transform ease-out 150ms;
}

.hc-movable-rotate:before {
    border-bottom: 1px solid #7939b8;
    border-left: 1px solid transparent;
    border-radius: 50%;
    border-right: 0px solid transparent;
    border-top: 1px solid transparent;
    content: "";
    display: inline-block;
    height: 18px;
    left: -6px;
    opacity: 0;
    position: absolute;
    top: -4px;
    transform: rotate(-45deg);
    width: 18px;
}

.hc-movable-rotate:after {
    border-bottom: 6px solid #7939b8;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 0px solid transparent;
    content: "";
    display: inline-block;
    height: 0;
    left: -4px;
    opacity: 0;
    position: absolute;
    top: 11px;
    transform: rotate(-98deg);
    width: 0;
}

.hc-movable-rotate:hover:before,
.hc-movable-rotate:hover:after {
    opacity: 1;
    transition: opacity ease-out 100ms;
}

.hc-movable:hover > .hc-movable-wh-resize {
    background-color: white;
    border: 1px solid #7939b8;
}

.hc-movable-wh-resize:hover {
    box-shadow: 0 0 4px #0007;
}
</style>

<script>
import { useDrag } from "../composables/mouse";

export default {
    props: {
        /**
         * Is movable dragging
         */
        isNew: {
            type: Boolean,
            default: false,
        },

        zoom: {
            type: Number,
            default: 1,
        },

        style: {
            type: Object,
        },

        classes: {
            type: Array,
            default: [],
        },

        movable: {
            type: Boolean,
            default: true,
        },

        wResizable: {
            type: Boolean,
            default: true,
        },

        hResizable: {
            type: Boolean,
            default: true,
        },

        whResizable: {
            type: Boolean,
            default: true,
        },

        rotatable: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            drag: null,
            wResize: null,
            hResize: null,
            whResize: null,
            rotate: null,

            currentX: null,
            currentY: null,
            currentWidth: null,
            currentHeight: null,
            currentRotation: null,
            currentCenterX: null,
            currentCenterY: null,

            currentStyle: this.style,
        };
    },

    mounted() {
        const movable = this.$refs.movable;

        if (this.movable) {
            this.drag = useDrag(movable);
        }

        if (this.wResizable) {
            this.wResize = useDrag(this.$refs.wResize, movable);
        }

        if (this.hResizable) {
            this.hResize = useDrag(this.$refs.hResize, movable);
        }

        if (this.whResizable) {
            this.whResize = useDrag(this.$refs.whResize, movable);
        }

        if (this.rotatable) {
            this.rotate = useDrag(this.$refs.rotate, movable);
        }
    },

    methods: {
        /**
         *
         */
        autowidth() {
            // To change
            this.currentStyle = {
                ...this.currentStyle,
                width: "auto",
            };

            this.change();
        },

        /**
         *
         */
        autoHeight() {
            // To change
            this.currentStyle = {
                ...this.currentStyle,
                height: "auto",
            };

            this.change();
        },

        /**
         *
         */
        autoWidthHeight() {
            // To change
            this.currentStyle = {
                ...this.currentStyle,
                width: "auto",
                height: "auto",
            };

            this.change();
        },

        /**
         *
         */
        cancelRotate() {
            this.setCurrentPosition();

            let [x, y] = this.rotateCoordinatesAroundCenter(
                this.currentX + this.currentWidth / 2,
                this.currentY + this.currentHeight / 2,
                this.currentX,
                this.currentY,
                this.currentRotation
            );

            [x, y] = this.rotateCoordinatesAroundCenter(
                this.currentX,
                this.currentY,
                x,
                y,
                -this.currentRotation
            );

            // To change
            this.currentStyle = {
                ...this.currentStyle,
                transform: "unset",
                left: `${x}px`,
                top: `${y}px`,
            };

            this.unsetCurrentPosition();
            this.change();
        },

        /**
         *
         */
        getCurrentX() {
            return this.currentStyle.left
                ? parseFloat(this.currentStyle.left)
                : this.$refs.movable.offsetLeft;
        },

        /**
         *
         */
        getCurrentY() {
            return this.currentStyle.top
                ? parseFloat(this.currentStyle.top)
                : this.$refs.movable.offsetTop;
        },

        /**
         *
         */
        getCurrentWidth() {
            return this.currentStyle.width && this.currentStyle.width != "auto"
                ? parseFloat(this.currentStyle.width)
                : this.$refs.movable.offsetWidth;
        },

        /**
         *
         */
        getCurrentHeight() {
            return this.currentStyle.height &&
                this.currentStyle.height != "auto"
                ? parseFloat(this.currentStyle.height)
                : this.$refs.movable.offsetHeight;
        },

        /**
         *
         */
        getCurrentRotation() {
            const matches = this.currentStyle.transform
                ? this.currentStyle.transform.match(/rotate\(([^)]+)\)/)
                : null;
            return matches ? parseFloat(matches[1]) : 0;
        },

        /**
         *
         */
        setCurrentPosition() {
            this.currentX = this.getCurrentX();
            this.currentY = this.getCurrentY();
            this.currentWidth = this.getCurrentWidth();
            this.currentHeight = this.getCurrentHeight();

            this.currentRotation = this.getCurrentRotation();
        },

        /**
         *
         */
        unsetCurrentPosition() {
            this.currentX =
                this.currentY =
                this.currentWidth =
                this.currentHeight =
                this.currentRotation =
                    null;
        },

        /**
         *
         * @param {*} xp x position to rotate
         * @param {*} yp y position to rotate
         * @param {*} theta rotation angle
         */
        rotateCoordinates(xp, yp, theta) {
            const cos = Math.cos(theta);
            const sin = Math.sin(theta);

            return [xp * cos - yp * sin, xp * sin + yp * cos];
        },

        /**
         *
         * @param {*} xp x position to rotate
         * @param {*} yp y position to rotate
         * @param {*} xc x rotation center
         * @param {*} yc y rotation center
         * @param {*} theta rotation angle
         */
        rotateCoordinatesAroundCenter(xp, yp, xc, yc, theta) {
            let [x, y] = this.rotateCoordinates(xp - xc, yp - yc, theta);
            return [x + xc, y + yc];
        },

        /**
         *
         */
        change() {
            this.$emit("change", this.currentStyle);
        },
    },

    computed: {
        /**
         * Container style
         */
        containerStyle() {
            return Object.fromEntries(
                Object.entries(this.currentStyle).filter(
                    ([key]) =>
                        key == "left" ||
                        key == "top" ||
                        key == "width" ||
                        key == "height" ||
                        key == "transform"
                )
            );
        },

        /**
         */
        positionDragging() {
            return this.drag && this.drag.dragging;
        },

        /**
         */
        wResizeDragging() {
            return this.wResize && this.wResize.dragging;
        },

        /**
         */
        hResizeDragging() {
            return this.hResize && this.hResize.dragging;
        },

        /**
         */
        whResizeDragging() {
            return this.whResize && this.whResize.dragging;
        },

        /**
         */
        rotateDragging() {
            return this.rotate && this.rotate.dragging;
        },

        /**
         */
        wResizePosition() {
            if (!this.wResize || !this.wResize.position) {
                return null;
            }

            return this.wResize.position;
        },

        /**
         */
        dragPosition() {
            if (!this.drag || !this.drag.position) {
                return null;
            }

            return this.drag.position;
        },

        /**
         */
        hResizePosition() {
            if (!this.hResize || !this.hResize.position) {
                return null;
            }

            return this.hResize.position;
        },

        /**
         */
        whResizePosition() {
            if (!this.whResize || !this.whResize.position) {
                return null;
            }

            return this.whResize.position;
        },

        /**
         */
        rotatePosition() {
            if (!this.rotate || !this.rotate.position) {
                return null;
            }

            return this.rotate.position;
        },
    },

    watch: {
        /**
         *
         */
        positionDragging(newValue) {
            if (newValue) {
                this.setCurrentPosition();
                this.$emit("dragging", this.drag.event);
            } else {
                if (
                    (this.currentX !== null &&
                        this.currentX != this.getCurrentX()) ||
                    (this.currentY !== null &&
                        this.currentY != this.getCurrentY())
                ) {
                    this.change();
                }
                this.unsetCurrentPosition();
            }
        },

        /**
         *
         */
        wResizeDragging(newValue) {
            if (newValue) {
                this.setCurrentPosition();
                this.$emit("w-resizing", this.wResize.event);
            } else {
                if (
                    this.currentWidth !== null &&
                    this.currentWidth != this.getCurrentWidth()
                ) {
                    this.change();
                }
                this.unsetCurrentPosition();
            }
        },

        /**
         *
         */
        hResizeDragging(newValue) {
            if (newValue) {
                this.setCurrentPosition();
                this.$emit("h-resizing", this.hResize.event);
            } else {
                if (
                    this.currentHeight !== null &&
                    this.currentHeight != this.getCurrentHeight()
                ) {
                    this.change();
                }
                this.unsetCurrentPosition();
            }
        },

        /**
         *
         */
        whResizeDragging(newValue) {
            if (newValue) {
                this.setCurrentPosition();
                this.$emit("wh-resizing", this.whResize.event);
            } else {
                if (
                    (this.currentWidth !== null &&
                        this.currentWidth != this.getCurrentWidth()) ||
                    (this.currentHeight !== null &&
                        this.currentHeight != this.getCurrentHeight())
                ) {
                    this.change();
                }
                this.unsetCurrentPosition();
            }
        },

        /**
         *
         */
        rotateDragging(newValue) {
            if (newValue) {
                this.setCurrentPosition();
                const rect = this.$refs.movable.getBoundingClientRect();
                this.currentCenterX = rect.left + rect.width / 2;
                this.currentCenterY = rect.top + rect.height / 2;
                this.$emit("rotating", this.rotate.event);
            } else {
                if (
                    this.currentRotation !== null &&
                    this.currentRotation != this.getCurrentRotation()
                ) {
                    this.change();
                }
                this.currentCenterX = null;
                this.currentCenterY = null;
                this.unsetCurrentPosition();
            }
        },

        /**
         */
        dragPosition(newValue) {
            if (!newValue || this.currentX === null || this.currentY === null) {
                return;
            }

            let deltaX = this.drag.position.x - this.drag.origin.x;
            let deltaY = this.drag.position.y - this.drag.origin.y;

            // + SHIFT
            if (this.drag.event.shiftKey) {
                if (Math.abs(deltaX) > Math.abs(deltaY)) {
                    deltaY = 0;
                } else {
                    deltaX = 0;
                }
            }

            // + ALT
            if (this.drag.event.altKey) {
                if (Math.abs(deltaX) > Math.abs(deltaY)) {
                    deltaY = deltaY < 0 ? -Math.abs(deltaX) : Math.abs(deltaX);
                } else {
                    deltaX = deltaX < 0 ? -Math.abs(deltaY) : Math.abs(deltaY);
                }
            }

            deltaX /= this.zoom;
            deltaY /= this.zoom;

            let left = this.currentX + deltaX;
            let top = this.currentY + deltaY;

            // CTRL
            if (this.drag.event.ctrlKey) {
                left -= left % 10;
                top -= top % 10;
            }

            // To change
            this.currentStyle.left = `${left}px`;
            this.currentStyle.top = `${top}px`;

            this.$emit("changing", this.currentStyle);
        },

        /**
         */
        wResizePosition(newValue) {
            if (!newValue || this.currentWidth === null) {
                return;
            }

            let deltaX = this.wResize.position.x - this.wResize.origin.x;
            let deltaY = this.wResize.position.y - this.wResize.origin.y;

            if (this.currentRotation) {
                [deltaX, deltaY] = this.rotateCoordinates(
                    deltaX,
                    deltaY,
                    -this.currentRotation
                );
            }

            deltaX /= this.zoom;
            deltaY /= this.zoom;

            let width = Math.max(0, deltaX + this.currentWidth);

            // + CTRL
            if (this.wResize.event.ctrlKey) {
                width -= width % 10;
            }

            // + CTRL
            if (this.wResize.event.shiftKey) {
                let height = this.currentHeight;
                height *= width / this.currentWidth;
                this.currentStyle.height = `${height}px`;
            }

            // To change
            this.currentStyle.width = `${width}px`;

            this.$emit("changing", this.currentStyle);
        },

        /**
         */
        hResizePosition(newValue) {
            if (!newValue || this.currentHeight === null) {
                return;
            }

            let deltaX = this.hResize.position.x - this.hResize.origin.x;
            let deltaY = this.hResize.position.y - this.hResize.origin.y;

            if (this.currentRotation) {
                [deltaX, deltaY] = this.rotateCoordinates(
                    deltaX,
                    deltaY,
                    -this.currentRotation
                );
            }

            deltaX /= this.zoom;
            deltaY /= this.zoom;

            let height = Math.max(0, deltaY + this.currentHeight);

            // + CTRL
            if (this.hResize.event.ctrlKey) {
                height -= height % 10;
            }

            // + CTRL
            if (this.hResize.event.shiftKey) {
                let width = this.currentWidth;
                width *= height / this.currentHeight;
                this.currentStyle.width = `${width}px`;
            }

            // To change
            this.currentStyle.height = `${height}px`;

            this.$emit("changing", this.currentStyle);
        },

        /**
         */
        whResizePosition(newValue) {
            if (
                !newValue ||
                this.currentWidth === null ||
                this.currentHeight === null
            ) {
                return;
            }

            let deltaX = this.whResize.position.x - this.whResize.origin.x;
            let deltaY = this.whResize.position.y - this.whResize.origin.y;

            if (this.currentRotation) {
                [deltaX, deltaY] = this.rotateCoordinates(
                    deltaX,
                    deltaY,
                    -this.currentRotation
                );
            }

            deltaX /= this.zoom;
            deltaY /= this.zoom;

            let width = Math.max(0, this.currentWidth + deltaX);
            let height = Math.max(0, this.currentHeight + deltaY);

            // + CTRL
            if (this.whResize.event.ctrlKey) {
                width -= width % 10;
                height -= height % 10;
            }

            // + ALT
            if (this.whResize.event.altKey) {
                width = height = Math.max(width, height);
            }

            // + SHIFT
            if (this.whResize.event.shiftKey) {
                if (width > height) {
                    height = width;
                } else {
                    width = height;
                }
            }

            // To change
            this.currentStyle.width = `${width}px`;
            this.currentStyle.height = `${height}px`;

            this.$emit("changing", this.currentStyle);
        },

        /**
         */
        rotatePosition(newValue) {
            if (!newValue && !this.currentRotation) {
                return;
            }

            // Delta between angle
            // from current cursor position <-> center
            // to origin cursor position <-> center
            let delta =
                Math.atan2(
                    this.rotate.position.y - this.currentCenterY,
                    this.rotate.position.x - this.currentCenterX
                ) -
                Math.atan2(
                    this.rotate.origin.y - this.currentCenterY,
                    this.rotate.origin.x - this.currentCenterX
                );

            // Append current rotation to delta
            delta += this.currentRotation;

            // + CTRL
            if (this.rotate.event.ctrlKey) {
                delta = (delta * 180) / Math.PI;
                delta = ((delta - (delta % 10)) * Math.PI) / 180;
                // + SHIFT
            } else if (this.rotate.event.shiftKey) {
                delta = (delta * 180) / Math.PI;
                delta = ((delta - (delta % 45)) * Math.PI) / 180;
            }

            // Update movable position
            // (Note that transform origin is top left)

            // Field center position rotated (by current rotation)
            // from current movable position
            let [x, y] = this.rotateCoordinatesAroundCenter(
                this.currentX + this.currentWidth / 2,
                this.currentY + this.currentHeight / 2,
                this.currentX,
                this.currentY,
                this.currentRotation
            );

            // Current movable position rotated (by initial delta)
            // from [x, y]
            [x, y] = this.rotateCoordinatesAroundCenter(
                this.currentX,
                this.currentY,
                x,
                y,
                delta - this.currentRotation
            );

            // To change
            this.currentStyle.transform = `rotate(${delta}rad)`;
            this.currentStyle.left = `${x}px`;
            this.currentStyle.top = `${y}px`;

            this.$emit("changing", this.currentStyle);
        },

        style(newValue) {
            this.currentStyle = newValue;
        },
    },
};
</script>
