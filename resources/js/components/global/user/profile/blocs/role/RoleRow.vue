<template>
    <item>
        <icon class="fa fa-user-md" :size="30" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <div
            v-if="role.project"
            class="hc-global-user-profile-item-project"
            v-text="role.project.name"
        ></div>
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

export default {
    props: {
        role: {
            type: Object,
        },
    },
};
</script>
