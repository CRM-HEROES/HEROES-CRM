<template>
    <item>
        <icon class="fa fa-user" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <icon
            :class="[
                'fa',
                'fa-star',
                'hc-show-on-hover',
                isDefault ? 'icon-orange' : 'icon-grey',
            ]"
            @click.prevent.stop="toggleDefault"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_PROJECT_USER_SETTING,
    REMOVE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";

export default {
    props: {
        user: {
            type: Object,
        },
    },

    methods: {
        toggleDefault() {
            if (this.isDefault) {
                store.dispatch(REMOVE_PROJECT_USER_SETTING, {
                    key: "events.default-user",
                });
            } else {
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "events.default-user",
                    value: this.user.id,
                });
            }
        },
    },

    computed: {
        ...mapGetters(["projectUserSettingsEventsDefaultUser"]),

        isDefault() {
            return (
                this.projectUserSettingsEventsDefaultUser &&
                this.projectUserSettingsEventsDefaultUser == this.user.id
            );
        },
    },
};
</script>
