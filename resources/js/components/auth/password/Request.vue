<template>
    <div id="hc-password-request-wrapper">
        <div id="hc-password-request">
            <form
                id="hc-password-request-form"
                action="javascript:void(0)"
                method="post"
            >
                <img
                    id="hc-password-request-form-logo"
                    src="/images/logo.png"
                />
                <h1
                    id="hc-password-request-form-title"
                    v-text="$t('auth.init_password.title')"
                ></h1>
                <div id="hc-password-request-form-content">
                    <div id="hc-password-request-form-inputs">
                        <label>
                            <input
                                type="email"
                                name="email"
                                v-model="user.email"
                                id="email"
                                :placeholder="$t('auth.email')"
                                class="form-control"
                            />
                        </label>
                        <label>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                v-model="user.password"
                                id="password"
                                :placeholder="$t('auth.password')"
                                class="form-control"
                            />
                            <icon
                                @click.prevent="showPassword = !showPassword"
                                tag="a"
                                :class="
                                    showPassword
                                        ? 'fa fa-eye-slash'
                                        : 'fa fa-eye'
                                "
                                :size="40"
                                style="position: absolute; top: 0; right: 0"
                            />
                        </label>
                        <label>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password_confirmation"
                                v-model="user.password_confirmation"
                                id="password_confirmation"
                                :placeholder="$t('auth.confirm_password')"
                                class="form-control"
                            />
                        </label>
                    </div>
                    <button
                        id="hc-password-request-form-submit"
                        type="submit"
                        :disabled="processing"
                        @click="resetPassword"
                    >
                        {{
                            $t("auth.init_password.title") +
                            (processing ? " ..." : "")
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style>
#hc-password-request-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #f6f4f9;
    background-image: url(/images/bg.jpg);
    background-size: cover;
    background-position: center;
}

#hc-password-request {
    max-width: 100%;
    width: auto;
    height: auto;
    display: flex;
    flex-direction: row;
    border-radius: 10px;
    overflow: hidden;
}

#hc-password-request-form {
    width: 340px;
    padding: 30px;
    justify-content: center;
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 30px;
    background-color: #fff8;
}

#hc-password-request-form-logo {
    width: 160px;
}

#hc-password-request-form-title {
    font-size: 22px;
    font-weight: bold;
}

#hc-password-request-form-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#hc-password-request-form-inputs {
    width: 100%;
    display: flex;
    flex-direction: column;
}

#hc-password-request-form-inputs > label {
    height: 40px;
    width: 100%;
    margin-bottom: -1px;
    position: relative;
}

#hc-password-request-form-inputs > label > input {
    border: 1px solid #ccc;
    width: 100%;
    height: 100%;
    padding: 0 10px;
    font-size: 13px;
}

#hc-password-request-form-input-password-request {
    border-radius: 5px 5px 0 0;
}

#hc-password-request-form-input-password {
    border-radius: 0 0 5px 5px;
}

#hc-password-request-form-submit {
    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);
    border: none;
    border-radius: 5px;
    color: white;
    height: 40px;
}

#hc-password-request-forgotten {
    text-align: right;
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-password-request-form-other {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 3px;
}

#hc-password-request-sign-in {
}

#hc-password-request-sign-in > a {
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-password-request-login {
    border-radius: 5px;
    border: 2px solid #7939b8;
    background: linear-gradient(
        135deg,
        #f2eff5 0%,
        rgba(255, 255, 255, 0) 100%
    );
    height: 40px;
    color: #7939b8;
    text-align: center;
    line-height: 36px;
    font-weight: bold;
    font-size: 13px;
}
</style>

<script>
import { mapGetters, mapActions } from "vuex";
export default {
    name: "register",
    data() {
        return {
            showPassword: false,
            user: {
                email: "",
                password: "",
                password_confirmation: "",
            },
            processing: false,
        };
    },
    mounted() {
        this.user.email = this.$route.query.email;
    },
    methods: {
        ...mapActions({
            signIn: "auth/login",
        }),
        async resetPassword() {
            this.processing = true;
            await axios
                .post("/password/reset", {
                    ...this.user,
                    token: this.passwordResetToken,
                })
                .then((response) => {
                    this.signIn();
                })
                .catch(({ response: { data } }) => {
                    alert(data.message);
                })
                .finally(() => {
                    this.processing = false;
                });
        },
    },
    computed: {
        ...mapGetters("auth", ["passwordResetToken"]),
    },
};
</script>
