<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div
                class="hc-flex-column"
                style="height: 100%; position: relative"
            >
                <item-list class="hc-flex-1" padding="5px">
                    <session-row
                        v-for="session in userSessions"
                        :key="session.id"
                        :session="session"
                        @banning-device="banDevice(session)"
                        @canceling-device="cancelBanDevice(session)"
                    />
                </item-list>
                <loading
                    :loading="
                        fetchingUserSessions ||
                        banningDevice ||
                        cancelingBanDevice
                    "
                />
            </div>
        </template>
        <template #2>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="confirmBanDevice"
            >
                <item @click="tab = 0" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('auth.session.ban_device')"
                    ></div>
                </item>
                <item-list gap="5px" class="hc-flex-1" padding="12px">
                    <v-field
                        :label="$t('auth.session.ban_code')"
                        required
                        v-slot="p"
                        ><input
                            required
                            :placeholder="p.label + ' ...'"
                            v-model="banCode"
                    /></v-field>
                </item-list>
                <buttons>
                    <button v-text="$t('auth.session.ban')"></button>
                </buttons>
                <loading :loading="confirmingBanDevice" />
            </form>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BAN_USER_DEVICE,
    CONFIRM_BAN_USER_DEVICE,
    CANCEL_BAN_USER_DEVICE,
} from "@/actions/user/session";

// Components
import SessionRow from "./SessionRow.vue";

export default {
    components: {
        SessionRow,
    },

    data() {
        return {
            name: "user-manage-sessions",
            sessionKeyword: "",
            fetchingUserSessions: false,
            tab: 0,
            banCode: "",
            banningDevice: false,
            confirmingBanDevice: false,
            cancelingBanDevice: false,
            session: null,
        };
    },

    methods: {
        async banDevice(session) {
            this.session = session;
            this.banningDevice = true;

            try {
                const data = await store.dispatch(BAN_USER_DEVICE, session);
            } finally {
                this.banningDevice = false;
                this.tab = 1;
            }
        },

        async cancelBanDevice(session) {
            this.cancelingBanDevice = true;

            try {
                const data = await store.dispatch(
                    CANCEL_BAN_USER_DEVICE,
                    session
                );
            } finally {
                this.cancelingBanDevice = false;
                session.banned_at = null;
            }
        },

        async confirmBanDevice() {
            this.confirmingBanDevice = true;

            try {
                const data = await store.dispatch(CONFIRM_BAN_USER_DEVICE, {
                    session: this.session,
                    params: {
                        code: this.banCode,
                    },
                });
            } finally {
                this.confirmingBanDevice = false;
                this.session.banned_at = new Date();
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters(["userSessions"]),
    },
};
</script>
