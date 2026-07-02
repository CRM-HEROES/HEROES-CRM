<template>
    <relation :width="width" :height="height"></relation>
</template>

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
                30
            );
        },

        height() {
            return (
                parseInt(this.parent.style.top) - parseInt(this.child.style.top)
            );
        },
    },
};
</script>
