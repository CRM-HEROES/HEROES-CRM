<template>
    <slot v-if="permission"></slot>
</template>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        /**
         *
         */
        permission: {
            type: String,
            default: "all",
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
