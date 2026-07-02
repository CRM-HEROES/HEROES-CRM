<template>
    <item tag="a" target="_blank" :href="url">
        <icon class="fa fa-file-download" :size="30" />
        <div class="hc-item-main-content" v-text="date"></div>
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import { API_URL } from "@/apis/common";

export default {
    props: {
        userExport: {
            type: Object,
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         * Formatted date
         * that will be shown
         * in the event item
         * from the event.started_at field
         */
        date() {
            const date = dayjs(new Date(this.userExport.created_at)).format(
                "DD MMM YYYY HH:mm"
            );

            if (date == "Invalid date") {
                return this.userExport.created_at;
            }

            return date;
        },

        url() {
            return `${API_URL}/project/${this.project.slug}/export/${this.userExport.id}`;
        },
    },
};
</script>
