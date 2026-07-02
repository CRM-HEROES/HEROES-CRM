<template>
    <bloc icon="fa fa-wifi icon-blue" :name="$t('user.profile.blocs.sessions')">
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <session-row
                    v-for="session in userSessions"
                    :key="session.id"
                    :session="session"
                    @banning-device="banDevice(session)"
                    @canceling-device="cancelBanDevice(session)"
                />
            </div>
        </template>
    </bloc>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import SessionService from "@/apis/user/session";

// Actions
import {
    FETCH_USER_SESSIONS,
    BAN_USER_DEVICE,
    CONFIRM_BAN_USER_DEVICE,
    CANCEL_BAN_USER_DEVICE,
} from "@/actions/user/session";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import SessionRow from "./SessionRow.vue";

export default {
    components: {
        Bloc,
        SessionRow,
    },

    data() {
        return {
            userSessions: [],
            fetchingUserSessions: false,
            tab: 0,
            banCode: "",
            banningDevice: false,
            confirmingBanDevice: false,
            cancelingBanDevice: false,
            session: null,
        };
    },

    created() {
        this.fetchUserSessions();
    },

    methods: {
        /**
         *
         */
        async fetchUserSessions() {
            this.fetchingUserSessions = true;

            try {
                const { data } = await SessionService.get(this.globalUser.id);
                this.userSessions = data;
            } finally {
                this.fetchingUserSessions = false;
            }
        },

        async banDevice(session) {
            this.session = session;
            this.banningDevice = true;

            try {
                await SessionService.ban(this.globalUser.id, session.id);
            } finally {
                this.banningDevice = false;
                this.tab = 1;
            }
        },

        async cancelBanDevice(session) {
            this.cancelingBanDevice = true;

            try {
                await SessionService.cancelBan(this.globalUser.id, session.id);
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
        ...mapGetters(["globalUser", "userFullName"]),
    },
};
</script>
