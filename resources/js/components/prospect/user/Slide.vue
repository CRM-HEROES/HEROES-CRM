<template>
    <slide
        name="prospect-manage-users"
        :title="
            $t('prospect.affected_user.title', { prospect: prospectFullName })
        "
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
        style="width: 260px"
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
