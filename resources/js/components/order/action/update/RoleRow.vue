<template>
    <item tag="a">
        <icon class="fa fa-user-tie" />
        <div class="hc-item-main-content" v-text="role.name"></div>
    </item>
</template>

<script>
import { mapGetters } from "vuex";

// Actions
import { UPDATE_ORDER_ACTION } from "@/actions/project/order/action";

export default {
    props: {
        role: {
            type: Object,
        },

        orderAction: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["project"]),
    },
};
</script>
