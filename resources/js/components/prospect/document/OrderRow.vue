<template>
    <item class="hc-prospect-order" tag="a" :href="url" target="_blank">
        <icon class="fa fa-shopping-cart" />
        <div class="hc-item-main-content" v-text="order.name"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import { API_URL } from "@/apis/common";

export default {
    props: {
        order: {
            type: Object,
        },
        document: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["project", "prospect"]),

        url() {
            return `${API_URL}/project/${this.project.slug}/prospect/${
                this.prospect.id
            }/order/${this.order.id}/${
                this.document.for == "order" ? "document" : "invoice"
            }/${this.document.id}`;
        },
    },
};
</script>
