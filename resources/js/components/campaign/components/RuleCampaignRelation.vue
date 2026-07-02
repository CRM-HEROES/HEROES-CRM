<template>
    <relation
        :width="width"
        :height="height"
        :class="['hc-campaign-rule-relation-arrow', error ? 'error' : '']"
    ></relation>
</template>

<style>
.hc-campaign-rule-relation-arrow.error:before {
    border-top-color: #cc0000;
}

.hc-campaign-rule-relation-arrow.error:after {
    border-left-color: #cc0000;
}
</style>

<script>
import Relation from "./Relation.vue";

export default {
    components: {
        Relation,
    },

    props: {
        /**
         *
         */
        child: {
            type: Object,
        },

        /**
         *
         */
        parent: {
            type: Object,
            default: null,
        },

        /**
         *
         */
        error: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        width() {
            return (
                parseInt(this.parent.style.left) -
                parseInt(this.child.style.left) -
                parseInt(this.child.style.width)
            );
        },

        height() {
            return (
                parseInt(this.parent.style.top) - parseInt(this.child.style.top)
            );
        },

        style() {
            const deltaX =
                parseInt(this.parent.style.left) -
                parseInt(this.child.style.left) -
                parseInt(this.child.style.width);
            const deltaY =
                parseInt(this.parent.style.top) -
                parseInt(this.child.style.top);
            const alpha = Math.atan2(deltaY, deltaX);
            const width = Math.sqrt(deltaX * deltaX + deltaY * deltaY) - 2;

            return {
                width: `${width}px`,
                transform: `rotate(${alpha}rad)`,
            };
        },
    },
};
</script>
