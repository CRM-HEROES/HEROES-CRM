<template>
    <label class="hc-default-cell-label">
        <router-link
            class="hc-order-prospect-cell"
            v-text="label"
            :to="{
                name: 'prospect.show',
                params: {
                    project: project.slug,
                    prospect: order.prospect_id,
                },
            }"
        ></router-link>
    </label>
</template>

<style>
.hc-order-prospect-cell {
    display: inline-block;
    width: 100%;
    height: 100%;
    line-height: 21px;
    padding: 2px 4px;
    padding: 0 4px;
    overflow: hidden;
    vertical-align: top;
    text-decoration: none;
    color: #12a0f3;
}
</style>

<script>
import { mapGetters } from "vuex";

export default {
    props: {
        /**
         * Order
         */
        order: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         * Label that will be shown
         * instead of raw value
         * for some type of field
         */
        label() {
            if (!this.order.prospect) {
                return "";
            }

            return [
                this.order.prospect.first_name,
                this.order.prospect.last_name,
            ]
                .filter((n) => n)
                .join(" ");
        },
    },
};
</script>
