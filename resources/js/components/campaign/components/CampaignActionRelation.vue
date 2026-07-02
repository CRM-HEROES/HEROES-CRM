<template>
    <relation
        :width="width"
        :height="height"
        :class="[
            'hc-campaign-action-relation-arrow',
            processing ? 'processing' : '',
            error ? 'error' : '',
        ]"
    ></relation>
</template>

<style>
@keyframes arrowmove {
    from {
        right: 100%;
    }
    to {
        right: 0;
    }
}

@keyframes arrowopacity {
    0% {
        opacity: 0;
        transform: scale(0.5);
    }
    70% {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.7);
    }
}

.hc-campaign-action-relation-arrow:before {
    border-top-color: #7939b8;
}

.hc-campaign-action-relation-arrow:after {
    border-left-color: #7939b8;
}

.hc-campaign-action-relation-arrow.processing:before {
    border-top-color: #49a563;
}

.hc-campaign-action-relation-arrow.processing:after {
    border-left-color: #49a563;
    animation: arrowmove 1s infinite, arrowopacity 1s infinite;
}

.hc-campaign-action-relation-arrow.error:before {
    border-top-color: #cc0000;
}

.hc-campaign-action-relation-arrow.error:after {
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
        processing: {
            type: Boolean,
            default: false,
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
    },
};
</script>
