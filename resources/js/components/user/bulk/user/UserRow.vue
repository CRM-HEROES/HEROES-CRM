<template>
    <item tag="label">
        <icon class="fa fa-user" :style="style" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon
            v-if="can('all')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="user.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SET_USER } from "@/actions/project/user";
import { UPDATE_USER_BULK_USERS } from "@/actions/project/user";

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
        ...mapGetters(["project", "userBulkUsers", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.user.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkUsers;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_USERS, value);
            },
        },
    },
};
</script>
