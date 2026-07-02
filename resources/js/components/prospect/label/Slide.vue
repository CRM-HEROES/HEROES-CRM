<template>
    <slide
        name="prospect-manage-labels"
        :title="$t('prospect.label.title', { prospect: prospectFullName })"
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
        icon="fa fa-tags"
        style="width: 360px"
    >
        <layout />
    </slide>
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
        ...mapGetters(["project", "prospect", "prospectFullName"]),
    },
};
</script>
