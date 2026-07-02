<template>
    <item tag="label">
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox v-model="selected" :value="user.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SET_USER } from "@/actions/project/user";
import { UPDATE_PROSPECT_BULK_USERS } from "@/actions/project/prospect";

export default {
    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(SET_USER, this.user);
            this.$router.push({
                name: "user.show",
                params: {
                    project: this.project.slug,
                    user: this.user.id,
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project", "prospectBulkUsers"]),

        selected: {
            get() {
                return this.prospectBulkUsers;
            },
            set(value) {
                store.commit(UPDATE_PROSPECT_BULK_USERS, value);
            },
        },
    },
};
</script>
