<template>
    <div ref="field">
        <slot></slot>
    </div>
</template>

<script>
import { useDrag } from "../../../composables/mouse";

export default {
    props: {
        /**
         *
         */
        type: {
            type: String,
        },

        /**
         *
         */
        content: {
            type: String,
        },

        /**
         *
         */
        style: {
            type: Object,
            default: {},
        },
    },

    data() {
        return {
            field: {
                type: this.type,
                content: this.content,
                style: this.style,
            },
            drag: null,
        };
    },

    mounted() {
        this.drag = useDrag(this.$refs.field);
    },

    computed: {
        /**
         * Field is being dragged
         */
        newFieldDragging() {
            return this.drag && this.drag.dragging;
        },

        /**
         * Current field position
         */
        newFieldPosition() {
            if (!this.newFieldDragging) {
                return null;
            }

            return this.drag.position;
        },
    },

    watch: {
        /**
         *
         */
        newFieldDragging(newValue) {
            if (newValue) {
                this.$emit("dragging", this.field);
            } else if (this.drag && this.drag.position) {
                this.$emit(
                    "dragged",
                    this.drag.position.x,
                    this.drag.position.y
                );
            }
        },

        /**
         * When cursor position is changed
         */
        newFieldPosition(newValue) {
            if (!this.field) {
                return;
            }

            if (!newValue) {
                return;
            }

            // Move new field position
            this.field.style = {
                ...this.field.style,
                left: this.drag.position.x + "px",
                top: this.drag.position.y + "px",
            };
        },
    },
};
</script>
