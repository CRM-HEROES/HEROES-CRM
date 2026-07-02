<template>
    <relation
        :width="width"
        :height="height"
        :class="['hc-campaign-operator-operator-relation-arrow']"
    ></relation>
</template>

<style>
.hc-campaign-operator-operator-relation-arrow:before {
    border-top-style: dashed;
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
    },

    computed: {
        width() {
            return (
                parseInt(this.parent.style.left) -
                parseInt(this.child.style.left) -
                15
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
                15;
            const deltaY =
                parseInt(this.parent.style.top) -
                parseInt(this.child.style.top);
            const alpha = Math.atan2(deltaY, deltaX);
            const width = Math.sqrt(deltaX * deltaX + deltaY * deltaY) - 17;

            return {
                width: `${width}px`,
                transform: `rotate(${alpha}rad)`,
            };
        },
    },
};
</script>
