<template>
    <slide
        name="prospect-manage-documents"
        :title="$t('prospect.document.title', { prospect: prospectFullName })"
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-users"
        style="width: 260px"
        @open="fetchOrders()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_PROSPECT_ORDERS } from "@/actions/project/prospect/order";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    methods: {
        /**
         *
         */
        async fetchOrders() {
            this.fetchingOrders = true;

            try {
                await store.dispatch(FETCH_PROSPECT_ORDERS);
            } finally {
                this.fetchingOrders = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectFullName"]),
    },
};
</script>
