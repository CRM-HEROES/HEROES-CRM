<template>
    <modal name="line-update" :title="lineTitle">
        <layout />
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    computed: {
        ...mapGetters(["line", "users"]),

        assignedUser() {
            return this.line
                ? this.users.find((user) => user.id == this.line.user_id)
                : null;
        },

        lineTitle() {
            if (!this.line) {
                return "";
            }

            return this.assignedUser ? this.assignedUser.name : this.line.name;
        },
    },
};
</script>
