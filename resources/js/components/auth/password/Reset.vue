<template>
    <div id="hc-password-reset-wrapper">
        <div id="hc-password-reset">
            <form
                id="hc-password-reset-form"
                action="javascript:void(0)"
                method="post"
            >
                <img id="hc-password-reset-form-logo" src="/images/logo.png" />
                <h1
                    id="hc-password-reset-form-title"
                    v-text="$t('auth.init_password.title')"
                ></h1>
                <div id="hc-password-reset-form-content">
                    <div id="hc-password-reset-form-inputs">
                        <label>
                            <input
                                type="email"
                                name="email"
                                v-model="email"
                                id="email"
                                :placeholder="$t('auth.email')"
                                class="form-control"
                            />
                        </label>
                    </div>
                    <button
                        id="hc-password-reset-form-submit"
                        type="submit"
                        :disabled="processing"
                        @click="resetPassword"
                    >
                        {{
                            processing
                                ? $t(
                                      "auth.init_password.send_verification_email"
                                  )
                                : $t(
                                      "auth.init_password.sending_verification_email"
                                  )
                        }}
                    </button>
                </div>
            </form>
        </div>

        <flash />
    </div>
</template>

<style>
#hc-password-reset-wrapper {
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

#hc-password-reset {
    max-width: 100%;
    width: auto;
    height: auto;
    display: flex;
    flex-direction: row;
    border-radius: 10px;
    overflow: hidden;
}

#hc-password-reset-form {
    width: 340px;
    padding: 30px;
    justify-content: center;
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 30px;
    background-color: #fff8;
}

#hc-password-reset-form-logo {
    width: 160px;
}

#hc-password-reset-form-title {
    font-size: 22px;
    font-weight: bold;
}

#hc-password-reset-form-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

#hc-password-reset-form-inputs {
    width: 100%;
    display: flex;
    flex-direction: column;
}

#hc-password-reset-form-inputs > label {
    height: 40px;
    width: 100%;
    margin-bottom: -1px;
}

#hc-password-reset-form-inputs > label > input {
    border: 1px solid #ccc;
    width: 100%;
    height: 100%;
    padding: 0 10px;
    font-size: 13px;
}

#hc-password-reset-form-input-password-reset {
    border-radius: 5px 5px 0 0;
}

#hc-password-reset-form-input-password {
    border-radius: 0 0 5px 5px;
}

#hc-password-reset-form-submit {
    background: linear-gradient(121deg, #7939b8 0%, #7a59d8 100%);
    border: none;
    border-radius: 5px;
    color: white;
    height: 40px;
}

#hc-password-reset-forgotten {
    text-align: right;
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-password-reset-form-other {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 3px;
}

#hc-password-reset-sign-in {
}

#hc-password-reset-sign-in > a {
    color: #7939b8;
    font-size: 14px;
    text-decoration: underline;
}

#hc-password-reset-login {
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
import { mapActions } from "vuex";
export default {
    name: "register",
    data() {
        return {
            email: "",
            emailSent: false,
            processing: false,
        };
    },
    methods: {
        async resetPassword() {
            this.processing = true;
            await axios
                .post("/password/email", {
                    email: this.email,
                })
                .then((response) => {
                    flashInfo({
                        title: this.$t("auth.init_password.title"),
                        body: this.$t("auth.init_password.success", {
                            email: this.email,
                        }),
                        duration: 5000,
                    });
                    this.emailSent = true;
                })
                .catch(({ response: { data } }) => {
                    flashError({
                        title: this.$t("auth.init_password.title"),
                        body: this.$t("auth.init_password.error"),
                    });
                })
                .finally(() => {
                    this.processing = false;
                });
        },
    },
};
</script>
