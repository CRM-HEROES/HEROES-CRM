<template>
    <item tag="label">
        <icon class="fa fa-user-plus" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon
            v-if="can('all')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_USER_USER, REMOVE_USER_USER } from "@/actions/project/user/user";
import { SET_USER } from "@/actions/project/user";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        user: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked ? ADD_USER_USER : REMOVE_USER_USER,
                this.user
            );
            this.$emit("change", event);
        },

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
        ...mapGetters(["project", "can"]),
    },
};
</script>
