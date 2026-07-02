<template>
    <footer
        style="
            width: 100%;
            padding: 0px 20px;
            gap: 0px;
            color: #ffffff;
            background-color: #7939b8;
            height: 30px;
            line-height: 30px;
            font-size: 12px;
            text-align: center;
            position: relative;
            cursor: pointer;
        "
        @click.prevent="leaveImpersonation"
    >
        <span v-text="$t('auth.impersonate.back_to_my_account')"></span>
        <loading :loading="leavingImpersonation" />
    </footer>
</template>

<script>
import { mapActions } from "vuex";
import store from "@/store";

export default {
    data() {
        return {
            leavingImpersonation: false,
        };
    },

    methods: {
        ...mapActions({
            signIn: "auth/login",
        }),

        async leaveImpersonation() {
            this.leavingImpersonation = true;
            try {
                await axios.post("/api/user/impersonate/leave");
                await this.signIn();
                store.commit("auth/SET_IMPERSONATING", false);
                store.commit("auth/SET_IMPERSONATE_PROJECT", null);

                if (this.$route.name == "dashboard") {
                    window.location.reload();
                } else {
                    this.$router.push({
                        name: "dashboard",
                    });
                }
            } finally {
                this.leavingImpersonation = false;
            }
        },
    },
};
</script>
