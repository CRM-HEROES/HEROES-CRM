<template>
    <modal
        name="prospect-link-add"
        :title="
            $t('prospect.external_link.add.title', {
                prospect: prospectFullName,
            })
        "
    >
        <layout />
    </modal>
</template>

<script>
import { mapGetters } from "vuex";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    computed: {
        ...mapGetters(["prospectFullName"]),
    },
};
</script>
