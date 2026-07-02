<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.attach_user.title')"
        icon="fa fa-user"
        style="width: 260px"
    >
        <div
            class="hc-flex-1 hc-flex-column"
            style="height: 100%; overflow: hidden; position: relative"
        >
            <search v-model="userKeyword" />
            <item-list class="hc-flex-1" padding="12px">
                <user-row
                    v-for="user in filteredUsers"
                    :key="user.id"
                    :user="user"
                    @dragging="dragging"
                    @dragged="dragged"
                />
            </item-list>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            name: "campaign-action-prospect-attach-user",
            tab: 0,
            userKeyword: "",
        };
    },

    methods: {
        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },

    computed: {
        ...mapGetters(["users", "slideOpen"]),

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
